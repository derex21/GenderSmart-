<?php
// Turn off error display to prevent HTML output
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set content type to JSON
header('Content-Type: application/json');

// Prevent any output before JSON
ob_start();

// Helper function to safely output JSON
function outputJson($data) {
    // Clear any output buffer
    if (ob_get_level() > 0) {
        ob_end_clean();
    }
    echo json_encode($data);
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
    outputJson(['success' => false, 'error' => 'Database connection failed']);
}

// Ensure proper charset
$conn->set_charset('utf8mb4');

// Create tables if they don't exist (faculty_accounts may or may not exist)
createStudentTables($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Log received data
    error_log("Registration form data received: " . print_r($_POST, true));
    
    $student_id = $_POST['student_id'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $course = $_POST['course'] ?? '';
    $year_level = $_POST['year_level'] ?? '';
    
    // Debug: Log processed data
    error_log("Processed registration data: student_id=$student_id, full_name=$full_name, gender=$gender, course=$course, year_level=$year_level");
    
    // Validation
    if (empty($student_id) || empty($full_name) || empty($password) || empty($confirm_password) || empty($gender) || empty($course) || empty($year_level)) {
        outputJson(['success' => false, 'error' => 'All required fields must be filled']);
    }
    
    // Validate password confirmation
    if ($password !== $confirm_password) {
        outputJson(['success' => false, 'error' => 'Passwords do not match']);
    }
    
    // Validate password strength
    if (strlen($password) < 8) {
        outputJson(['success' => false, 'error' => 'Password must be at least 8 characters long']);
    }
    
    // Check if student already exists
    $check_stmt = $conn->prepare("SELECT id FROM students WHERE student_id = ?");
    if (!$check_stmt) {
        error_log('Prepare failed (check existing student): ' . $conn->error);
        outputJson(['success' => false, 'error' => 'Server error while validating student.']);
    }
    $check_stmt->bind_param("s", $student_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        outputJson(['success' => false, 'error' => 'Student ID already exists']);
    }
    
    // Get faculty for the course if table exists; otherwise continue with NULL faculty_id
    $faculty_id = null;
    if (tableExists($conn, 'faculty_accounts')) {
        $faculty_stmt = $conn->prepare("SELECT id FROM faculty_accounts WHERE course = ? LIMIT 1");
        if ($faculty_stmt) {
            $faculty_stmt->bind_param("s", $course);
            $faculty_stmt->execute();
            $faculty_result = $faculty_stmt->get_result();
            if ($faculty_result && $faculty_result->num_rows > 0) {
                $faculty_row = $faculty_result->fetch_assoc();
                $faculty_id = (int)$faculty_row['id'];
            } else {
                // No faculty found for course; proceed with NULL faculty_id
                error_log('No faculty found for course ' . $course . ' — proceeding with NULL faculty_id');
            }
        } else {
            error_log('Prepare failed (faculty lookup): ' . $conn->error);
        }
    } else {
        error_log('faculty_accounts table missing — proceeding with NULL faculty_id');
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert student
    $stmt = $conn->prepare("INSERT INTO students (student_id, full_name, password, gender, course, year_level, faculty_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        error_log('Prepare failed (insert student): ' . $conn->error);
        outputJson(['success' => false, 'error' => 'Server error while creating the student account.']);
    }
    // Bind faculty_id as integer but allow NULL
    $stmt->bind_param("ssssssi", $student_id, $full_name, $hashed_password, $gender, $course, $year_level, $faculty_id);
    
    if ($stmt->execute()) {
        $student_id_inserted = $conn->insert_id;
        logStudentActivity($conn, $student_id_inserted, 'registered', 'Student registered and pending approval');
        
        error_log("Student registration successful: ID $student_id_inserted");
        outputJson(['success' => true, 'message' => 'Registration successful! Your account is pending faculty approval.']);
    } else {
        error_log("Student registration failed: " . $stmt->error);
        outputJson(['success' => false, 'error' => 'Registration failed: ' . htmlspecialchars($stmt->error)]);
    }
} else {
    outputJson(['success' => false, 'error' => 'Invalid request method']);
}

// Close connection
$conn->close();

function createStudentTables($conn) {
    $hasFaculty = tableExists($conn, 'faculty_accounts');
    $fkClause = $hasFaculty ? ",\n        FOREIGN KEY (faculty_id) REFERENCES faculty_accounts(id) ON DELETE CASCADE" : "";
    $students_table = "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(20) UNIQUE NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        password VARCHAR(255) NOT NULL,
        gender VARCHAR(20) NOT NULL,
        course VARCHAR(100) NOT NULL,
        year_level VARCHAR(20) NOT NULL,
        status ENUM('pending', 'accepted', 'rejected') DEFAULT 'pending',
        faculty_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        accepted_at TIMESTAMP NULL,
        rejected_at TIMESTAMP NULL,
        rejection_reason TEXT$fkClause
    )";
    if (!$conn->query($students_table)) {
        error_log('Failed creating students table: ' . $conn->error);
    }
    
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
    if (!$conn->query($activity_table)) {
        error_log('Failed creating student_activity_log table: ' . $conn->error);
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

function tableExists($conn, $tableName) {
    $safe = $conn->real_escape_string($tableName);
    $sql = "SHOW TABLES LIKE '$safe'";
    $res = $conn->query($sql);
    return $res && $res->num_rows > 0;
}

?>
