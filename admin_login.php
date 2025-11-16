<?php
session_start();

// Set content type to JSON for AJAX requests
header('Content-Type: application/json');

// DB credentials
$servername = "localhost";
$username = "root"; // change if needed
$password = "";     // change if needed
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed. Please try again.']);
    exit;
}

// Check if admin_users table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($table_check->num_rows == 0) {
    // Create the admin_users table
    $create_table = "CREATE TABLE admin_users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        role ENUM('super_admin', 'admin', 'moderator') DEFAULT 'admin',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        last_login TIMESTAMP NULL
    )";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
    
    // Insert default super admin (password: admin123)
    $default_password = password_hash('admin123', PASSWORD_DEFAULT);
    $insert_default = "INSERT INTO admin_users (username, password, full_name, email, role) VALUES ('superadmin', '$default_password', 'Super Administrator', 'admin@gendersmart.com', 'super_admin')";
    $conn->query($insert_default);
}

// Get and sanitize input
$admin_username = trim($_POST['admin_username'] ?? '');
$password_input = $_POST['password'] ?? '';

// Validate fields
if (empty($admin_username) || empty($password_input)) {
    echo json_encode(['success' => false, 'error' => 'Username and password are required.']);
    exit;
}

// Fetch admin from DB
$stmt = $conn->prepare("SELECT id, username, password, full_name, role, email, is_active FROM admin_users WHERE username = ?");
$stmt->bind_param("s", $admin_username);
$stmt->execute();
$result = $stmt->get_result();

// Check if admin exists
if ($result->num_rows === 1) {
    $admin = $result->fetch_assoc();
    
    // Check if account is active
    if (!$admin['is_active']) {
        echo json_encode(['success' => false, 'error' => 'Your admin account has been deactivated. Please contact the system administrator.']);
        exit;
    }
    
    if (password_verify($password_input, $admin['password'])) {
        // Password correct - start admin session
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_email'] = $admin['email'] ?? '';

        // Update last login
        $update_login = $conn->prepare("UPDATE admin_users SET last_login = NOW() WHERE id = ?");
        $update_login->bind_param("i", $admin['id']);
        $update_login->execute();
        $update_login->close();

        echo json_encode(['success' => true, 'message' => 'Login successful!']);
        exit;
    } else {
        echo json_encode(['success' => false, 'error' => 'Incorrect password. Please check your password and try again.']);
        exit;
    }
} else {
    echo json_encode(['success' => false, 'error' => 'No admin account found with that username. Please check your username or contact the administrator.']);
    exit;
}

$stmt->close();
$conn->close();
?>
