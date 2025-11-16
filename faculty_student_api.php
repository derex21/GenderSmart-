<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed']);
    exit;
}

// Create tables if they don't exist
createStudentTables($conn);

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Set content type to JSON
header('Content-Type: application/json');

// Handle different actions
switch ($action) {
    case 'get_students':
        getStudents($conn);
        break;
    case 'get_pending_students':
        getPendingStudents($conn);
        break;
    case 'get_accepted_students':
        getAcceptedStudents($conn);
        break;
    case 'get_rejected_students':
        getRejectedStudents($conn);
        break;
    case 'accept_student':
        acceptStudent($conn);
        break;
    case 'reject_student':
        rejectStudent($conn);
        break;
    case 'restore_student':
        restoreStudent($conn);
        break;
    case 'get_student_stats':
        getStudentStats($conn);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

function getStudents($conn) {
    try {
        $faculty_id = $_SESSION['faculty_id'];
        $result = $conn->query("
            SELECT * FROM students 
            WHERE faculty_id = $faculty_id 
            ORDER BY created_at DESC
        ");
        
        $students = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $students]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getPendingStudents($conn) {
    try {
        $faculty_id = $_SESSION['faculty_id'];
        $result = $conn->query("
            SELECT * FROM students 
            WHERE faculty_id = $faculty_id AND status = 'pending' 
            ORDER BY created_at DESC
        ");
        
        $students = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $students]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getAcceptedStudents($conn) {
    try {
        $faculty_id = $_SESSION['faculty_id'];
        $result = $conn->query("
            SELECT * FROM students 
            WHERE faculty_id = $faculty_id AND status = 'accepted' 
            ORDER BY accepted_at DESC
        ");
        
        $students = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $students]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getRejectedStudents($conn) {
    try {
        $faculty_id = $_SESSION['faculty_id'];
        $result = $conn->query("
            SELECT * FROM students 
            WHERE faculty_id = $faculty_id AND status = 'rejected' 
            ORDER BY rejected_at DESC
        ");
        
        $students = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $students]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function acceptStudent($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $student_id = $_POST['student_id'] ?? '';
    $faculty_id = $_SESSION['faculty_id'];
    
    if (empty($student_id)) {
        echo json_encode(['error' => 'Student ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE students SET status = 'accepted', accepted_at = NOW() WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $student_id, $faculty_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log activity
            logStudentActivity($conn, $student_id, 'accepted', 'Student accepted by faculty');
            echo json_encode(['success' => true, 'message' => 'Student accepted successfully']);
        } else {
            echo json_encode(['error' => 'Student not found or already processed']);
        }
    } else {
        echo json_encode(['error' => 'Failed to accept student']);
    }
}

function rejectStudent($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $student_id = $_POST['student_id'] ?? '';
    $rejection_reason = $_POST['rejection_reason'] ?? '';
    $faculty_id = $_SESSION['faculty_id'];
    
    if (empty($student_id)) {
        echo json_encode(['error' => 'Student ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE students SET status = 'rejected', rejected_at = NOW(), rejection_reason = ? WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("sii", $rejection_reason, $student_id, $faculty_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log activity
            logStudentActivity($conn, $student_id, 'rejected', 'Student rejected by faculty: ' . $rejection_reason);
            echo json_encode(['success' => true, 'message' => 'Student rejected successfully']);
        } else {
            echo json_encode(['error' => 'Student not found or already processed']);
        }
    } else {
        echo json_encode(['error' => 'Failed to reject student']);
    }
}

function restoreStudent($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $student_id = $_POST['student_id'] ?? '';
    $faculty_id = $_SESSION['faculty_id'];
    
    if (empty($student_id)) {
        echo json_encode(['error' => 'Student ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE students SET status = 'pending', accepted_at = NULL, rejected_at = NULL, rejection_reason = NULL WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $student_id, $faculty_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            // Log activity
            logStudentActivity($conn, $student_id, 'restored', 'Student restored from rejected status');
            echo json_encode(['success' => true, 'message' => 'Student restored successfully']);
        } else {
            echo json_encode(['error' => 'Student not found']);
        }
    } else {
        echo json_encode(['error' => 'Failed to restore student']);
    }
}

function getStudentStats($conn) {
    try {
        $faculty_id = $_SESSION['faculty_id'];
        
        $pending_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE faculty_id = $faculty_id AND status = 'pending'")->fetch_assoc()['count'];
        $accepted_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE faculty_id = $faculty_id AND status = 'accepted'")->fetch_assoc()['count'];
        $rejected_count = $conn->query("SELECT COUNT(*) as count FROM students WHERE faculty_id = $faculty_id AND status = 'rejected'")->fetch_assoc()['count'];
        
        echo json_encode([
            'success' => true,
            'data' => [
                'pending' => (int)$pending_count,
                'accepted' => (int)$accepted_count,
                'rejected' => (int)$rejected_count
            ]
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function logStudentActivity($conn, $student_id, $action, $description) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO student_activity_log (student_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $student_id, $action, $description, $ip_address, $user_agent);
        $stmt->execute();
    }
}

function createStudentTables($conn) {
    // Create students table
    $students_table = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(20) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        course VARCHAR(100) NOT NULL,
        year_level VARCHAR(20) NOT NULL,
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        faculty_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        accepted_at TIMESTAMP NULL,
        rejected_at TIMESTAMP NULL,
        rejection_reason TEXT,
        FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE
    )";
    $conn->query($students_table);
    
    // Create student_activity_log table
    $activity_table = "CREATE TABLE IF NOT EXISTS student_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
    )";
    $conn->query($activity_table);
}

$conn->close();
?>
