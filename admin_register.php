<?php
session_start();

// DB credentials
$servername = "localhost";
$username = "root"; // change if needed
$password = "";     // change if needed
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
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
    
    // Create admin_activity_log table
    $create_log_table = "CREATE TABLE admin_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
    )";
    
    if (!$conn->query($create_log_table)) {
        die("Error creating log table: " . $conn->error);
    }
}

// Get and sanitize input
$admin_username = trim($_POST['admin_username'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation
$errors = [];

// Check if all fields are provided
if (empty($admin_username)) {
    $errors[] = "Username is required.";
}
if (empty($password)) {
    $errors[] = "Password is required.";
}
if (empty($confirm_password)) {
    $errors[] = "Password confirmation is required.";
}
if (empty($full_name)) {
    $errors[] = "Full name is required.";
}
if (empty($email)) {
    $errors[] = "Email is required.";
}

// Validate username (alphanumeric and underscore only, 3-20 characters)
if (!empty($admin_username) && (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $admin_username))) {
    $errors[] = "Username must be 3-20 characters long and contain only letters, numbers, and underscores.";
}

// Validate password strength
if (!empty($password) && strlen($password) < 8) {
    $errors[] = "Password must be at least 8 characters long.";
}

// Check if passwords match
if (!empty($password) && !empty($confirm_password) && $password !== $confirm_password) {
    $errors[] = "Passwords do not match.";
}

// Validate email format
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Please enter a valid email address.";
}

// Check if username already exists
if (!empty($admin_username)) {
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    if ($stmt === false) {
        $errors[] = "Database error. Please try again.";
    } else {
        $stmt->bind_param("s", $admin_username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Username already exists. Please choose a different username.";
        }
        $stmt->close();
    }
}

// Check if email already exists
if (!empty($email)) {
    $stmt = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
    if ($stmt === false) {
        $errors[] = "Database error. Please try again.";
    } else {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please use a different email address.";
        }
        $stmt->close();
    }
}

// If there are errors, redirect back with error message
if (!empty($errors)) {
    $_SESSION['admin_register_error'] = implode(" ", $errors);
    header("Location: ../FRONTEND/admin_register.php");
    exit;
}

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert new admin into database
$stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, 'admin')");
if ($stmt === false) {
    $_SESSION['admin_register_error'] = "Database error. Please try again.";
    header("Location: ../FRONTEND/admin_register.php");
    exit;
}

$stmt->bind_param("ssss", $admin_username, $hashed_password, $full_name, $email);

if ($stmt->execute()) {
    // Get the new admin ID
    $new_admin_id = $conn->insert_id;
    
    // Log the registration activity
    $action = "admin_registered";
    $description = "New admin account created: " . $admin_username;
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
    
    $log_stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    if ($log_stmt !== false) {
        $log_stmt->bind_param("issss", $new_admin_id, $action, $description, $ip_address, $user_agent);
        $log_stmt->execute();
        $log_stmt->close();
    }
    
    $_SESSION['admin_register_success'] = "Admin account created successfully! You can now login.";
    header("Location: ../FRONTEND/admin_login.php");
    exit;
} else {
    $_SESSION['admin_register_error'] = "Registration failed. Please try again.";
    header("Location: ../FRONTEND/admin_register.php");
    exit;
}

$stmt->close();
$conn->close();
?>
