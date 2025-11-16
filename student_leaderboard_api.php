<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

header('Content-Type: application/json');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_leaderboard':
        getLeaderboard($conn);
        break;
    case 'get_my_rank':
        getMyRank($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function getLeaderboard($conn) {
    $student_id = $_SESSION['student_id'];
    
    // Get all students with their scores
    $query = "
        SELECT 
            s.id,
            s.student_id,
            s.full_name,
            s.course,
            s.year_level,
            COALESCE(AVG(sas.grade), 0) as activity_score,
            COALESCE(AVG(sma.quiz_score), 0) as quiz_score,
            COUNT(DISTINCT sma.module_id) as modules_completed,
            COUNT(DISTINCT sas.activity_id) as activities_completed,
            COALESCE(gs.total_points, 0) as game_points,
            (COALESCE(AVG(sas.grade), 0) * 0.4 + COALESCE(AVG(sma.quiz_score), 0) * 0.3 + 
             COUNT(DISTINCT sma.module_id) * 10 * 0.2 + COUNT(DISTINCT sas.activity_id) * 5 * 0.1 + 
             COALESCE(gs.total_points, 0) * 0.1) as overall_score
        FROM students s
        LEFT JOIN student_module_access sma ON s.id = sma.student_id
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        LEFT JOIN student_game_scores gs ON s.id = gs.student_id
        WHERE s.status = 'accepted'
        GROUP BY s.id, s.student_id, s.full_name, s.course, s.year_level, gs.total_points
        ORDER BY overall_score DESC
        LIMIT 50
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaderboard = [];
    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        $row['rank'] = $rank++;
        $leaderboard[] = $row;
    }
    $stmt->close();
    
    // Get current student's rank
    $my_rank = getMyRankData($conn, $student_id);
    
    echo json_encode([
        'success' => true, 
        'leaderboard' => $leaderboard,
        'my_rank' => $my_rank
    ]);
}

function getMyRank($conn) {
    $student_id = $_SESSION['student_id'];
    $my_rank = getMyRankData($conn, $student_id);
    
    echo json_encode(['success' => true, 'my_rank' => $my_rank]);
}

function getMyRankData($conn, $student_id) {
    // Get student's overall score
    $query = "
        SELECT 
            s.id,
            s.student_id,
            s.full_name,
            s.course,
            s.year_level,
            COALESCE(AVG(sas.grade), 0) as activity_score,
            COALESCE(AVG(sma.quiz_score), 0) as quiz_score,
            COUNT(DISTINCT sma.module_id) as modules_completed,
            COUNT(DISTINCT sas.activity_id) as activities_completed,
            COALESCE(gs.total_points, 0) as game_points,
            (COALESCE(AVG(sas.grade), 0) * 0.4 + COALESCE(AVG(sma.quiz_score), 0) * 0.3 + 
             COUNT(DISTINCT sma.module_id) * 10 * 0.2 + COUNT(DISTINCT sas.activity_id) * 5 * 0.1 + 
             COALESCE(gs.total_points, 0) * 0.1) as overall_score
        FROM students s
        LEFT JOIN student_module_access sma ON s.id = sma.student_id
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        LEFT JOIN student_game_scores gs ON s.id = gs.student_id
        WHERE s.id = ?
        GROUP BY s.id, s.student_id, s.full_name, s.course, s.year_level, gs.total_points
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();
    $stmt->close();
    
    if (!$student) {
        return null;
    }
    
    // Get rank by counting students with higher scores
    $rank_query = "
        SELECT COUNT(*) + 1 as rank
        FROM (
            SELECT 
                s.id,
                (COALESCE(AVG(sas.grade), 0) * 0.4 + COALESCE(AVG(sma.quiz_score), 0) * 0.3 + 
                 COUNT(DISTINCT sma.module_id) * 10 * 0.2 + COUNT(DISTINCT sas.activity_id) * 5 * 0.1 + 
                 COALESCE(gs.total_points, 0) * 0.1) as overall_score
            FROM students s
            LEFT JOIN student_module_access sma ON s.id = sma.student_id
            LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
            LEFT JOIN student_game_scores gs ON s.id = gs.student_id
            WHERE s.status = 'accepted'
            GROUP BY s.id, gs.total_points
            HAVING overall_score > ?
        ) as ranked_students
    ";
    
    $stmt = $conn->prepare($rank_query);
    $stmt->bind_param("d", $student['overall_score']);
    $stmt->execute();
    $result = $stmt->get_result();
    $rank_data = $result->fetch_assoc();
    $stmt->close();
    
    $student['rank'] = $rank_data['rank'];
    
    return $student;
}

$conn->close();
?>
