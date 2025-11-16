<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Set content type to JSON
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

// Create tables if they don't exist
createTables($conn);

$action = $_POST['action'] ?? $_GET['action'] ?? '';

switch ($action) {
    case 'upload_module':
        uploadModule($conn);
        break;
    case 'create_activity':
        createActivity($conn);
        break;
    case 'get_modules':
        getModules($conn);
        break;
    case 'get_activities':
        getActivities($conn);
        break;
    case 'delete_module':
        deleteModule($conn);
        break;
    case 'delete_activity':
        deleteActivity($conn);
        break;
    case 'toggle_module_status':
        toggleModuleStatus($conn);
        break;
    case 'toggle_activity_status':
        toggleActivityStatus($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function createTables($conn) {
    // Create modules table
    $modules_table = "CREATE TABLE IF NOT EXISTS modules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(500) NOT NULL,
        file_type ENUM('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png') NOT NULL,
        file_size INT NOT NULL,
        module_type ENUM('lesson', 'assignment', 'reference') NOT NULL,
        quiz_questions TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE
    )";
    $conn->query($modules_table);

    // Create activities table
    $activities_table = "CREATE TABLE IF NOT EXISTS activities (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description TEXT,
        file_path VARCHAR(500),
        file_type ENUM('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'),
        file_size INT,
        activity_type ENUM('assignment', 'quiz', 'project', 'presentation') NOT NULL,
        deadline DATETIME NOT NULL,
        points INT NOT NULL,
        close_after_deadline BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE
    )";
    $conn->query($activities_table);

    // Create student module access table
    $student_module_access = "CREATE TABLE IF NOT EXISTS student_module_access (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        module_id INT NOT NULL,
        accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        completion_status ENUM('not_started', 'in_progress', 'completed') DEFAULT 'not_started',
        quiz_score DECIMAL(5,2),
        quiz_attempts INT DEFAULT 0,
        last_accessed TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE,
        UNIQUE KEY unique_student_module (student_id, module_id)
    )";
    $conn->query($student_module_access);

    // Create student activity submissions table
    $student_activity_submissions = "CREATE TABLE IF NOT EXISTS student_activity_submissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        activity_id INT NOT NULL,
        submission_file VARCHAR(500),
        submission_text TEXT,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        grade DECIMAL(5,2),
        feedback TEXT,
        is_late BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
        UNIQUE KEY unique_student_activity (student_id, activity_id)
    )";
    $conn->query($student_activity_submissions);
}

function uploadModule($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    $title = trim($_POST['module_title'] ?? '');
    $description = trim($_POST['module_description'] ?? '');
    $module_type = $_POST['module_type'] ?? '';
    $quiz_questions = trim($_POST['quiz_questions'] ?? '');

    if (empty($title) || empty($description) || empty($module_type)) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        return;
    }

    if (!isset($_FILES['module_file']) || $_FILES['module_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Please select a valid file']);
        return;
    }

    $file = $_FILES['module_file'];
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF, Word documents, and images are allowed']);
        return;
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = '../uploads/modules/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate unique filename
    $filename = uniqid() . '_' . $file['name'];
    $file_path = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        return;
    }

    // Insert module into database
    $stmt = $conn->prepare("INSERT INTO modules (faculty_id, title, description, file_path, file_type, file_size, module_type, quiz_questions) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiss", $faculty_id, $title, $description, $file_path, $file_extension, $file['size'], $module_type, $quiz_questions);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Module uploaded successfully']);
    } else {
        // Delete uploaded file if database insert fails
        unlink($file_path);
        echo json_encode(['success' => false, 'error' => 'Failed to save module information']);
    }
    $stmt->close();
}

function createActivity($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    $title = trim($_POST['activity_title'] ?? '');
    $description = trim($_POST['activity_description'] ?? '');
    $activity_type = $_POST['activity_type'] ?? '';
    $deadline = $_POST['deadline'] ?? '';
    $points = intval($_POST['points'] ?? 0);
    $close_after_deadline = isset($_POST['close_after_deadline']) ? 1 : 0;

    if (empty($title) || empty($description) || empty($activity_type) || empty($deadline) || $points <= 0) {
        echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
        return;
    }

    $file_path = null;
    $file_type = null;
    $file_size = null;

    // Handle optional file upload
    if (isset($_FILES['activity_file']) && $_FILES['activity_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['activity_file'];
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            echo json_encode(['success' => false, 'error' => 'Invalid file type. Only PDF, Word documents, and images are allowed']);
            return;
        }

        // Create uploads directory if it doesn't exist
        $upload_dir = '../uploads/activities/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        // Generate unique filename
        $filename = uniqid() . '_' . $file['name'];
        $file_path = $upload_dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $file_path)) {
            echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
            return;
        }

        $file_type = $file_extension;
        $file_size = $file['size'];
    }

    // Insert activity into database
    $stmt = $conn->prepare("INSERT INTO activities (faculty_id, title, description, file_path, file_type, file_size, activity_type, deadline, points, close_after_deadline) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiisii", $faculty_id, $title, $description, $file_path, $file_type, $file_size, $activity_type, $deadline, $points, $close_after_deadline);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Activity created successfully']);
    } else {
        // Delete uploaded file if database insert fails
        if ($file_path && file_exists($file_path)) {
            unlink($file_path);
        }
        echo json_encode(['success' => false, 'error' => 'Failed to save activity information']);
    }
    $stmt->close();
}

function getModules($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    $stmt = $conn->prepare("SELECT * FROM modules WHERE faculty_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $modules = [];
    while ($row = $result->fetch_assoc()) {
        $modules[] = $row;
    }
    
    echo json_encode(['success' => true, 'modules' => $modules]);
    $stmt->close();
}

function getActivities($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    $stmt = $conn->prepare("SELECT * FROM activities WHERE faculty_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $activities = [];
    while ($row = $result->fetch_assoc()) {
        $activities[] = $row;
    }
    
    echo json_encode(['success' => true, 'activities' => $activities]);
    $stmt->close();
}

function deleteModule($conn) {
    $module_id = intval($_POST['module_id'] ?? 0);
    $faculty_id = $_SESSION['faculty_id'];

    if ($module_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid module ID']);
        return;
    }

    // Get module info to delete file
    $stmt = $conn->prepare("SELECT file_path FROM modules WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $module_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Module not found']);
        return;
    }
    
    $module = $result->fetch_assoc();
    $stmt->close();

    // Delete module from database
    $stmt = $conn->prepare("DELETE FROM modules WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $module_id, $faculty_id);
    
    if ($stmt->execute()) {
        // Delete file if it exists
        if ($module['file_path'] && file_exists($module['file_path'])) {
            unlink($module['file_path']);
        }
        echo json_encode(['success' => true, 'message' => 'Module deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete module']);
    }
    $stmt->close();
}

function deleteActivity($conn) {
    $activity_id = intval($_POST['activity_id'] ?? 0);
    $faculty_id = $_SESSION['faculty_id'];

    if ($activity_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid activity ID']);
        return;
    }

    // Get activity info to delete file
    $stmt = $conn->prepare("SELECT file_path FROM activities WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $activity_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Activity not found']);
        return;
    }
    
    $activity = $result->fetch_assoc();
    $stmt->close();

    // Delete activity from database
    $stmt = $conn->prepare("DELETE FROM activities WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $activity_id, $faculty_id);
    
    if ($stmt->execute()) {
        // Delete file if it exists
        if ($activity['file_path'] && file_exists($activity['file_path'])) {
            unlink($activity['file_path']);
        }
        echo json_encode(['success' => true, 'message' => 'Activity deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete activity']);
    }
    $stmt->close();
}

function toggleModuleStatus($conn) {
    $module_id = intval($_POST['module_id'] ?? 0);
    $faculty_id = $_SESSION['faculty_id'];

    if ($module_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid module ID']);
        return;
    }

    $stmt = $conn->prepare("UPDATE modules SET is_active = NOT is_active WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $module_id, $faculty_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Module status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update module status']);
    }
    $stmt->close();
}

function toggleActivityStatus($conn) {
    $activity_id = intval($_POST['activity_id'] ?? 0);
    $faculty_id = $_SESSION['faculty_id'];

    if ($activity_id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid activity ID']);
        return;
    }

    $stmt = $conn->prepare("UPDATE activities SET is_active = NOT is_active WHERE id = ? AND faculty_id = ?");
    $stmt->bind_param("ii", $activity_id, $faculty_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Activity status updated successfully']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update activity status']);
    }
    $stmt->close();
}

$conn->close();
?>
