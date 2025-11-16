<?php
// Session is already started in the main file
// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../FRONTEND/admin_login.php");
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
    die("Connection failed: " . $conn->connect_error);
}

// Check and create additional tables if they don't exist
createAdditionalTables($conn);

// Get admin info
$admin_id = $_SESSION['admin_id'];
$admin_name = $_SESSION['admin_name'] ?? 'Admin';
$admin_role = $_SESSION['admin_role'] ?? 'admin';

// Debug: Log admin session info
error_log("Admin Dashboard - Admin ID: $admin_id, Name: $admin_name, Role: $admin_role");

// Get statistics with error handling
$faculty_count = getCount($conn, "SELECT COUNT(*) as count FROM faculty_accounts WHERE is_active = 1");
$admin_count = getCount($conn, "SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
$webinar_count = getCount($conn, "SELECT COUNT(*) as count FROM webinars WHERE is_active = 1");

// Debug: Log statistics
error_log("Admin Dashboard Backend - Faculty: $faculty_count, Admin: $admin_count, Webinar: $webinar_count");

// Make statistics available globally for potential server-side rendering
// But primary data loading is handled by admin_api.php via AJAX calls
// Store in PHP variables for server-side access if needed

// Helper functions
function createAdditionalTables($conn) {
    // Create faculty_accounts table
    $faculty_table = "CREATE TABLE IF NOT EXISTS faculty_accounts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        faculty_id VARCHAR(20) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        full_name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        course VARCHAR(200) NOT NULL,
        year_level VARCHAR(50) NOT NULL,
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE CASCADE
    )";
    $conn->query($faculty_table);
    
    // Create webinars table
    $webinars_table = "CREATE TABLE IF NOT EXISTS webinars (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(200) NOT NULL,
        description TEXT,
        speaker_name VARCHAR(100) NOT NULL,
        speaker_title VARCHAR(100),
        speaker_avatar VARCHAR(255),
        webinar_date DATETIME NOT NULL,
        duration INT NOT NULL,
        category ENUM('upcoming', 'recorded', 'popular') DEFAULT 'upcoming',
        webinar_link VARCHAR(500),
        google_form_link VARCHAR(500),
        image_url VARCHAR(255),
        is_active BOOLEAN DEFAULT TRUE,
        created_by INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES admin_users(id) ON DELETE CASCADE
    )";
    $conn->query($webinars_table);
    
    // Create admin_activity_log table
    $activity_table = "CREATE TABLE IF NOT EXISTS admin_activity_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        action VARCHAR(100) NOT NULL,
        description TEXT,
        ip_address VARCHAR(45),
        user_agent TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (admin_id) REFERENCES admin_users(id) ON DELETE CASCADE
    )";
    $conn->query($activity_table);
}

function getCount($conn, $query) {
    $result = $conn->query($query);
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['count'];
    }
    return 0;
}

// Don't close connection here - it will be closed when the page finishes
?>
