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
    case 'get_activities':
        getActivities($conn);
        break;
    case 'get_recent_activities':
        getRecentActivities($conn);
        break;
    case 'submit_activity':
        submitActivity($conn);
        break;
    case 'get_submission':
        getSubmission($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function getActivities($conn) {
    $student_id = $_SESSION['student_id'];
    
    $query = "
        SELECT 
            a.*,
            sas.submitted_at,
            sas.grade,
            sas.feedback,
            sas.is_late
        FROM activities a
        LEFT JOIN student_activity_submissions sas ON a.id = sas.activity_id AND sas.student_id = ?
        WHERE a.is_active = 1
        ORDER BY a.deadline ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'activities' => $activities]);
}

function getRecentActivities($conn) {
    $student_id = $_SESSION['student_id'];
    
    $query = "
        SELECT 
            a.*,
            sas.submitted_at,
            sas.grade,
            sas.feedback,
            sas.is_late
        FROM activities a
        LEFT JOIN student_activity_submissions sas ON a.id = sas.activity_id AND sas.student_id = ?
        WHERE a.is_active = 1
        ORDER BY a.deadline ASC
        LIMIT 5
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'activities' => $activities]);
}

function submitActivity($conn) {
    $student_id = $_SESSION['student_id'];
    $activity_id = $_POST['activity_id'] ?? '';
    $submission_text = $_POST['submission_text'] ?? '';
    $submission_file = $_FILES['submission_file'] ?? null;
    
    if (empty($activity_id)) {
        echo json_encode(['success' => false, 'error' => 'Activity ID is required']);
        exit;
    }
    
    // Check if already submitted
    $check_query = "SELECT id FROM student_activity_submissions WHERE student_id = ? AND activity_id = ?";
    $stmt = $conn->prepare($check_query);
    $stmt->bind_param("ii", $student_id, $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Activity already submitted']);
        exit;
    }
    
    // Get activity deadline
    $deadline_query = "SELECT deadline FROM activities WHERE id = ?";
    $stmt = $conn->prepare($deadline_query);
    $stmt->bind_param("i", $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $activity = $result->fetch_assoc();
    $stmt->close();
    
    $is_late = new DateTime() > new DateTime($activity['deadline']);
    
    $file_path = null;
    if ($submission_file && $submission_file['error'] === 0) {
        $upload_dir = '../uploads/submissions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = uniqid('submission_', true) . '_' . $submission_file['name'];
        $file_destination = $upload_dir . $file_name;
        
        if (move_uploaded_file($submission_file['tmp_name'], $file_destination)) {
            $file_path = $file_destination;
        }
    }
    
    // Insert submission
    $insert_query = "INSERT INTO student_activity_submissions (student_id, activity_id, submission_text, submission_file_path, is_late) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iissi", $student_id, $activity_id, $submission_text, $file_path, $is_late);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Activity submitted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to submit activity']);
    }
    $stmt->close();
}

function getSubmission($conn) {
    $student_id = $_SESSION['student_id'];
    $activity_id = $_GET['activity_id'] ?? '';
    
    if (empty($activity_id)) {
        echo json_encode(['success' => false, 'error' => 'Activity ID is required']);
        exit;
    }
    
    $query = "
        SELECT 
            sas.*,
            a.title as activity_title
        FROM student_activity_submissions sas
        JOIN activities a ON sas.activity_id = a.id
        WHERE sas.student_id = ? AND sas.activity_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $student_id, $activity_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $submission = $result->fetch_assoc();
        echo json_encode(['success' => true, 'submission' => $submission]);
    } else {
        echo json_encode(['success' => false, 'error' => 'No submission found']);
    }
    $stmt->close();
}

$conn->close();
?>
