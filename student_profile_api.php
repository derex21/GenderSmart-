<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'Student not logged in'
    ]);
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'gendersmart_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database connection failed: ' . $e->getMessage()
    ]);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'get_student_data':
        getStudentData($pdo);
        break;
    case 'update_profile':
        updateProfile($pdo);
        break;
    default:
        echo json_encode([
            'success' => false,
            'error' => 'Invalid action'
        ]);
        break;
}

function getStudentData($pdo) {
    try {
        $studentId = $_SESSION['student_id'];
        
        // Get student information
        $stmt = $pdo->prepare("
            SELECT 
                student_id,
                full_name,
                gender,
                course,
                year_level,
                status,
                created_at
            FROM student_accounts 
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$student) {
            echo json_encode([
                'success' => false,
                'error' => 'Student not found'
            ]);
            return;
        }
        
        // Get student statistics
        $stats = getStudentStats($pdo, $studentId);
        
        echo json_encode([
            'success' => true,
            'student' => $student,
            'stats' => $stats
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

function getStudentStats($pdo, $studentId) {
    try {
        $stats = [
            'modules_completed' => 0,
            'activities_completed' => 0,
            'total_points' => 0,
            'game_points' => 0
        ];
        
        // Get modules completed count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM student_module_progress 
            WHERE student_id = ? AND completed_at IS NOT NULL
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['modules_completed'] = $result['count'] ?? 0;
        
        // Get activities completed count
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM student_activity_submissions 
            WHERE student_id = ? AND submitted_at IS NOT NULL
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['activities_completed'] = $result['count'] ?? 0;
        
        // Get total points from modules
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(points_earned), 0) as total 
            FROM student_module_progress 
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_points'] += $result['total'] ?? 0;
        
        // Get total points from activities
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(points_earned), 0) as total 
            FROM student_activity_submissions 
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['total_points'] += $result['total'] ?? 0;
        
        // Get game points
        $stmt = $pdo->prepare("
            SELECT COALESCE(SUM(score), 0) as total 
            FROM student_game_scores 
            WHERE student_id = ?
        ");
        $stmt->execute([$studentId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $stats['game_points'] = $result['total'] ?? 0;
        
        return $stats;
        
    } catch (PDOException $e) {
        return [
            'modules_completed' => 0,
            'activities_completed' => 0,
            'total_points' => 0,
            'game_points' => 0
        ];
    }
}

function updateProfile($pdo) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid request method'
        ]);
        return;
    }
    
    try {
        $input = json_decode(file_get_contents('php://input'), true);
        $studentId = $_SESSION['student_id'];
        
        // Validate input
        $allowedFields = ['full_name', 'gender'];
        $updateFields = [];
        $values = [];
        
        foreach ($allowedFields as $field) {
            if (isset($input[$field]) && !empty($input[$field])) {
                $updateFields[] = "$field = ?";
                $values[] = $input[$field];
            }
        }
        
        if (empty($updateFields)) {
            echo json_encode([
                'success' => false,
                'error' => 'No valid fields to update'
            ]);
            return;
        }
        
        $values[] = $studentId;
        
        $sql = "UPDATE student_accounts SET " . implode(', ', $updateFields) . " WHERE student_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($values);
        
        echo json_encode([
            'success' => true,
            'message' => 'Profile updated successfully'
        ]);
        
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Database error: ' . $e->getMessage()
        ]);
    }
}
?>
