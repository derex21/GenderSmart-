<?php
session_start();

// Set content type to JSON for AJAX requests
header('Content-Type: application/json');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed. Please try again.']);
    exit;
}

// Check if faculty_accounts table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'faculty_accounts'");
if ($table_check->num_rows == 0) {
    // Check if admin_users table exists before adding FK to avoid creation failure
    $admin_users_exists = $conn->query("SHOW TABLES LIKE 'admin_users'");

    // Build CREATE TABLE with optional foreign key
    $create_table = "CREATE TABLE faculty_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_id VARCHAR(20) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        course VARCHAR(200) NOT NULL,
        year_level VARCHAR(50) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT NULL DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL";

    if ($admin_users_exists && $admin_users_exists->num_rows > 0) {
        $create_table .= ",\n        FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE CASCADE";
    }

    $create_table .= "\n    )";

    if (!$conn->query($create_table)) {
        echo json_encode(['success' => false, 'error' => 'Failed to initialize faculty table: ' . $conn->error]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $faculty_id = trim($_POST['faculty_id'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($faculty_id) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Faculty ID and password are required.']);
        exit;
    }
    
// Check if faculty account exists and is active
    $stmt = $conn->prepare("SELECT id, faculty_id, password, full_name, email, course, year_level, is_active FROM faculty_accounts WHERE faculty_id = ?");
    $stmt->bind_param("s", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $faculty = $result->fetch_assoc();
        
        // Check if account is active
        if (!$faculty['is_active']) {
            echo json_encode(['success' => false, 'error' => 'Your faculty account has been deactivated. Please contact the administrator.']);
            exit;
        }
        
        // Verify password strictly against hash
        $isValid = password_verify($password, $faculty['password']);

        if ($isValid) {
            // Login successful
            $_SESSION['faculty_id'] = $faculty['id'];
            $_SESSION['faculty_faculty_id'] = $faculty['faculty_id'];
            $_SESSION['faculty_name'] = $faculty['full_name'];
            $_SESSION['faculty_email'] = $faculty['email'];
            $_SESSION['faculty_course'] = $faculty['course'];
            $_SESSION['faculty_year_level'] = $faculty['year_level'];
            
            // Update last login
            $update_stmt = $conn->prepare("UPDATE faculty_accounts SET last_login = NOW() WHERE id = ?");
            $update_stmt->bind_param("i", $faculty['id']);
            $update_stmt->execute();
            $update_stmt->close();
            
            echo json_encode(['success' => true, 'message' => 'Login successful!']);
            exit;
        } else {
            echo json_encode(['success' => false, 'error' => 'Incorrect password. Please check your password and try again.']);
            exit;
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'No faculty account found with that Faculty ID. Please check your Faculty ID or contact the administrator.']);
        exit;
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}
?>