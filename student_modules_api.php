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
    case 'get_modules':
        getModules($conn);
        break;
    case 'get_recent_modules':
        getRecentModules($conn);
        break;
    case 'access_module':
        accessModule($conn);
        break;
    case 'complete_module':
        completeModule($conn);
        break;
    case 'submit_quiz':
        submitQuiz($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function getModules($conn) {
    $student_id = $_SESSION['student_id'];
    
    $query = "
        SELECT 
            m.*,
            sma.accessed_at,
            sma.completed_at,
            sma.quiz_score
        FROM modules m
        LEFT JOIN student_module_access sma ON m.id = sma.module_id AND sma.student_id = ?
        WHERE m.is_active = 1
        ORDER BY m.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modules = [];
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'modules' => $modules]);
}

function getRecentModules($conn) {
    $student_id = $_SESSION['student_id'];
    
    $query = "
        SELECT 
            m.*,
            sma.accessed_at,
            sma.completed_at,
            sma.quiz_score
        FROM modules m
        LEFT JOIN student_module_access sma ON m.id = sma.module_id AND sma.student_id = ?
        WHERE m.is_active = 1
        ORDER BY m.created_at DESC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modules = [];
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'modules' => $modules]);
}

function accessModule($conn) {
    $student_id = $_SESSION['student_id'];
    $module_id = $_POST['module_id'] ?? '';
    
    if (empty($module_id)) {
        echo json_encode(['success' => false, 'error' => 'Module ID is required']);
        exit;
    }
    
    // Check if access already exists
    $check_query = "SELECT id FROM student_module_access WHERE student_id = ? AND module_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $student_id, $module_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update access time
        $update_query = "UPDATE student_module_access SET accessed_at = NOW() WHERE student_id = ? AND module_id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("ii", $student_id, $module_id);
        $stmt->execute();
    } else {
        // Create new access record
        $insert_query = "INSERT INTO student_module_access (student_id, module_id, accessed_at) VALUES (?, ?, NOW())";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $student_id, $module_id);
        $stmt->execute();
    }
    
    echo json_encode(['success' => true, 'message' => 'Module access recorded']);
}

function completeModule($conn) {
    $student_id = $_SESSION['student_id'];
    $module_id = $_POST['module_id'] ?? '';
    
    if (empty($module_id)) {
        echo json_encode(['success' => false, 'error' => 'Module ID is required']);
        exit;
    }
    
    // Update completion status
    $query = "UPDATE student_module_access SET completed_at = NOW() WHERE student_id = ? AND module_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $module_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Module completed']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to complete module']);
    }
    $stmt->close();
}

function submitQuiz($conn) {
    $student_id = $_SESSION['student_id'];
    $module_id = $_POST['module_id'] ?? '';
    $answers = $_POST['answers'] ?? [];
    $score = $_POST['score'] ?? 0;
    
    if (empty($module_id)) {
        echo json_encode(['success' => false, 'error' => 'Module ID is required']);
        exit;
    }
    
    // Update quiz score
    $query = "UPDATE student_module_access SET quiz_score = ? WHERE student_id = ? AND module_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iii", $score, $student_id, $module_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Quiz submitted successfully', 'score' => $score]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to submit quiz']);
    }
    $stmt->close();
}

$conn->close();
?>
