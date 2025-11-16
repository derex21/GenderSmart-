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

// Create game scores table if it doesn't exist
createGameScoresTable($conn);

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_game_stats':
        getGameStats($conn);
        break;
    case 'save_score':
        saveScore($conn);
        break;
    case 'get_leaderboard':
        getGameLeaderboard($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function createGameScoresTable($conn) {
    $query = "
        CREATE TABLE IF NOT EXISTS student_game_scores (
            id INT AUTO_INCREMENT PRIMARY KEY,
            student_id INT NOT NULL,
            game_type VARCHAR(50) DEFAULT 'gender_quiz',
            score INT NOT NULL,
            max_score INT NOT NULL,
            percentage DECIMAL(5,2) NOT NULL,
            played_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            total_points INT DEFAULT 0,
            games_played INT DEFAULT 0,
            high_score INT DEFAULT 0,
            FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
            INDEX idx_student_game (student_id, game_type),
            INDEX idx_total_points (total_points DESC)
        )
    ";
    
    $conn->query($query);
}

function getGameStats($conn) {
    $student_id = $_SESSION['student_id'];
    
    $query = "
        SELECT 
            COALESCE(SUM(score), 0) as total_points,
            COUNT(*) as games_played,
            COALESCE(MAX(score), 0) as high_score,
            COALESCE(AVG(percentage), 0) as average_percentage
        FROM student_game_scores 
        WHERE student_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();
    
    echo json_encode(['success' => true, 'stats' => $stats]);
}

function saveScore($conn) {
    $student_id = $_SESSION['student_id'];
    $score = $_POST['score'] ?? 0;
    $max_score = $_POST['max_score'] ?? 100;
    
    if (empty($score)) {
        echo json_encode(['success' => false, 'error' => 'Score is required']);
        exit;
    }
    
    $percentage = ($score / $max_score) * 100;
    
    // Insert game score
    $insert_query = "INSERT INTO student_game_scores (student_id, score, max_score, percentage) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiid", $student_id, $score, $max_score, $percentage);
    
    if ($stmt->execute()) {
        // Update total points and stats
        updateStudentGameStats($conn, $student_id);
        echo json_encode(['success' => true, 'message' => 'Score saved successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to save score']);
    }
    $stmt->close();
}

function updateStudentGameStats($conn, $student_id) {
    // Get current stats
    $stats_query = "
        SELECT 
            SUM(score) as total_points,
            COUNT(*) as games_played,
            MAX(score) as high_score
        FROM student_game_scores 
        WHERE student_id = ?
    ";
    
    $stmt = $conn->prepare($stats_query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stats = $result->fetch_assoc();
    $stmt->close();
    
    // Update or insert student stats
    $update_query = "
        INSERT INTO student_game_scores (student_id, total_points, games_played, high_score) 
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE 
        total_points = VALUES(total_points),
        games_played = VALUES(games_played),
        high_score = VALUES(high_score)
    ";
    
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("iiii", $student_id, $stats['total_points'], $stats['games_played'], $stats['high_score']);
    $stmt->execute();
    $stmt->close();
}

function getGameLeaderboard($conn) {
    $query = "
        SELECT 
            s.student_id,
            s.full_name,
            s.course,
            s.year_level,
            COALESCE(gs.total_points, 0) as game_points,
            COALESCE(gs.games_played, 0) as games_played,
            COALESCE(gs.high_score, 0) as high_score
        FROM students s
        LEFT JOIN (
            SELECT 
                student_id,
                SUM(score) as total_points,
                COUNT(*) as games_played,
                MAX(score) as high_score
            FROM student_game_scores 
            GROUP BY student_id
        ) gs ON s.id = gs.student_id
        WHERE s.status = 'accepted'
        ORDER BY game_points DESC
        LIMIT 20
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
    
    echo json_encode(['success' => true, 'leaderboard' => $leaderboard]);
}

$conn->close();
?>
