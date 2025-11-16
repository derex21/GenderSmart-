<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
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

// Check and create additional tables if they don't exist
createAdditionalTables($conn);

// Get request method and action
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

// Set content type to JSON
header('Content-Type: application/json');

// Handle different actions
switch ($action) {
    // Dashboard Statistics
    case 'get_dashboard_stats':
        getDashboardStats($conn);
        break;
    case 'get_recent_faculty':
        getRecentFaculty($conn);
        break;
    case 'get_recent_webinars':
        getRecentWebinars($conn);
        break;
    
    // Faculty Management
    case 'get_faculty':
        getFaculty($conn);
        break;
    case 'add_faculty':
        addFaculty($conn);
        break;
    case 'update_faculty':
        updateFaculty($conn);
        break;
    case 'delete_faculty':
        deleteFaculty($conn);
        break;
    
    // Admin Management
    case 'get_admins':
        getAdmins($conn);
        break;
    case 'add_admin':
        addAdmin($conn);
        break;
    case 'update_admin':
        updateAdmin($conn);
        break;
    case 'delete_admin':
        deleteAdmin($conn);
        break;
    
    // Webinar Management
    case 'get_webinars':
        getWebinars($conn);
        break;
    case 'add_webinar':
        addWebinar($conn);
        break;
    case 'update_webinar':
        updateWebinar($conn);
        break;
    case 'delete_webinar':
        deleteWebinar($conn);
        break;
    
    // Profile Management
    case 'get_profile':
        getProfile($conn);
        break;
    case 'update_profile':
        updateProfile($conn);
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Invalid action']);
        break;
}

// Faculty Management Functions
function getFaculty($conn) {
    $result = $conn->query("
        SELECT f.*, a.username as created_by_name 
        FROM faculty_accounts f 
        LEFT JOIN admin_users a ON f.created_by = a.id 
        ORDER BY f.created_at DESC
    ");
    
    $faculty = [];
    while ($row = $result->fetch_assoc()) {
        $faculty[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $faculty]);
}

function addFaculty($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Debug: Log received data
    error_log("Faculty form data received: " . print_r($_POST, true));
    error_log("Request Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'not set'));
    error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
    
    // Check if course and year_level are in POST data
    if (!isset($_POST['course'])) {
        error_log("ERROR: 'course' field is missing from POST data");
        error_log("Available POST keys: " . implode(', ', array_keys($_POST)));
        
        // Try to parse from raw input if available
        $rawInput = file_get_contents('php://input');
        error_log("Raw POST data length: " . strlen($rawInput));
        if (strpos($rawInput, 'course=') !== false) {
            error_log("Found 'course=' in raw input");
            parse_str($rawInput, $parsed);
            error_log("Parsed data: " . print_r($parsed, true));
            if (isset($parsed['course'])) {
                $_POST['course'] = $parsed['course'];
            }
            if (isset($parsed['year_level'])) {
                $_POST['year_level'] = $parsed['year_level'];
            }
        }
    }
    if (!isset($_POST['year_level'])) {
        error_log("ERROR: 'year_level' field is missing from POST data");
        error_log("Available POST keys: " . implode(', ', array_keys($_POST)));
    }
    
    // Get values with better error handling
    $faculty_id = isset($_POST['faculty_id']) ? trim($_POST['faculty_id']) : '';
    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $course = isset($_POST['course']) ? trim($_POST['course']) : '';
    $year_level = isset($_POST['year_level']) ? trim($_POST['year_level']) : '';
    $created_by = $_SESSION['admin_id'];
    
    // Debug: Log course and year_level values
    error_log("Course value: '$course' (length: " . strlen($course) . ")");
    error_log("Year level value: '$year_level' (length: " . strlen($year_level) . ")");
    
    // Validation - course and year_level can contain multiple values separated by |
    // Check if course and year_level are provided first
    if (empty($course) || empty($year_level)) {
        $missing_fields = [];
        if (empty($faculty_id)) $missing_fields[] = 'faculty_id';
        if (empty($full_name)) $missing_fields[] = 'full_name';
        if (empty($email)) $missing_fields[] = 'email';
        if (empty($password)) $missing_fields[] = 'password';
        if (empty($course)) $missing_fields[] = 'course';
        if (empty($year_level)) $missing_fields[] = 'year_level';
        
        error_log("Missing fields (before split): " . implode(', ', $missing_fields));
        error_log("Course received: '$course'");
        error_log("Year level received: '$year_level'");
        error_log("POST data keys: " . implode(', ', array_keys($_POST)));
        
        echo json_encode(['error' => 'Required fields are missing: ' . implode(', ', $missing_fields)]);
        return;
    }
    
    // Split and check if at least one course and one year level is provided
    $courses = array_filter(array_map('trim', explode('|', $course)));
    $years = array_filter(array_map('trim', explode('|', $year_level)));
    
    // Debug: Log split results
    error_log("Courses array: " . print_r($courses, true));
    error_log("Years array: " . print_r($years, true));
    
    // Validate that we have at least one course and one year after splitting
    if (empty($courses)) {
        error_log("Courses array is empty after splitting '$course'");
        echo json_encode(['error' => 'At least one course is required']);
        return;
    }
    
    if (empty($years)) {
        error_log("Years array is empty after splitting '$year_level'");
        echo json_encode(['error' => 'At least one year level is required']);
        return;
    }
    
    if (empty($faculty_id) || empty($full_name) || empty($email) || empty($password)) {
        $missing_fields = [];
        if (empty($faculty_id)) $missing_fields[] = 'faculty_id';
        if (empty($full_name)) $missing_fields[] = 'full_name';
        if (empty($email)) $missing_fields[] = 'email';
        if (empty($password)) $missing_fields[] = 'password';
        if (empty($courses)) $missing_fields[] = 'course';
        if (empty($years)) $missing_fields[] = 'year_level';
        
        // Debug: Log what's missing
        error_log("Missing fields: " . implode(', ', $missing_fields));
        error_log("Course received: '$course'");
        error_log("Year level received: '$year_level'");
        
        echo json_encode(['error' => 'Required fields are missing: ' . implode(', ', $missing_fields)]);
        return;
    }
    
    // Rejoin courses and years for storage (in case they were split)
    $course = implode(' | ', $courses);
    $year_level = implode(' | ', $years);
    
    // Enforce stronger password rules
    if (strlen($password) < 8) {
        echo json_encode(['error' => 'Password must be at least 8 characters long']);
        return;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['error' => 'Invalid email format']);
        return;
    }
    
    // Check if faculty ID already exists
    $check = $conn->prepare("SELECT id FROM faculty_accounts WHERE faculty_id = ?");
    if (!$check) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        return;
    }
    $check->bind_param("s", $faculty_id);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'Faculty ID already exists']);
        return;
    }
    
    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM faculty_accounts WHERE email = ?");
    if (!$check) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        return;
    }
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'Email already exists']);
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert faculty
    $stmt = $conn->prepare("INSERT INTO faculty_accounts (faculty_id, password, full_name, email, course, year_level, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) {
        echo json_encode(['error' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("ssssssi", $faculty_id, $hashed_password, $full_name, $email, $course, $year_level, $created_by);
    
    if ($stmt->execute()) {
        $faculty_id_new = $conn->insert_id;
        error_log("Faculty account created successfully with ID: $faculty_id_new");
        
        // Log activity
        logActivity($conn, $_SESSION['admin_id'], 'add_faculty', "Added faculty account: $full_name ($faculty_id)");
        
        echo json_encode(['success' => true, 'message' => 'Faculty account created successfully', 'faculty_id' => $faculty_id_new]);
    } else {
        error_log("Failed to create faculty account: " . $stmt->error);
        echo json_encode(['error' => 'Failed to create faculty account: ' . $stmt->error]);
    }
}

function updateFaculty($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    $faculty_id = trim($_POST['faculty_id'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $is_active = $_POST['is_active'] ?? 1;
    
    if (empty($id)) {
        echo json_encode(['error' => 'Faculty ID is required']);
        return;
    }
    
    // Ensure course and year_level are properly formatted (split and rejoin to normalize)
    $courses = array_filter(array_map('trim', explode('|', $course)));
    $years = array_filter(array_map('trim', explode('|', $year_level)));
    
    if (empty($courses)) {
        echo json_encode(['error' => 'At least one course is required']);
        return;
    }
    
    if (empty($years)) {
        echo json_encode(['error' => 'At least one year level is required']);
        return;
    }
    
    // Rejoin for storage
    $course = implode(' | ', $courses);
    $year_level = implode(' | ', $years);
    
    $stmt = $conn->prepare("UPDATE faculty_accounts SET faculty_id = ?, full_name = ?, email = ?, course = ?, year_level = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssssii", $faculty_id, $full_name, $email, $course, $year_level, $is_active, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Faculty account updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update faculty account']);
    }
}

function deleteFaculty($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['error' => 'Faculty ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM faculty_accounts WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Faculty account deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete faculty account']);
    }
}

// Admin Management Functions
function getAdmins($conn) {
    $result = $conn->query("SELECT * FROM admin_users ORDER BY created_at DESC");
    
    $admins = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['password']); // Don't send password
        $admins[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $admins]);
}

function addAdmin($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = trim($_POST['role'] ?? '');
    
    // Validation
    if (empty($username) || empty($full_name) || empty($email) || empty($password) || empty($role)) {
        echo json_encode(['error' => 'All fields are required']);
        return;
    }
    
    // Check if username already exists
    $check = $conn->prepare("SELECT id FROM admin_users WHERE username = ?");
    $check->bind_param("s", $username);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'Username already exists']);
        return;
    }
    
    // Check if email already exists
    $check = $conn->prepare("SELECT id FROM admin_users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        echo json_encode(['error' => 'Email already exists']);
        return;
    }
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert admin
    $stmt = $conn->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Admin account created successfully']);
    } else {
        echo json_encode(['error' => 'Failed to create admin account']);
    }
}

function updateAdmin($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    $username = trim($_POST['username'] ?? '');
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? '');
    $is_active = $_POST['is_active'] ?? 1;
    
    if (empty($id)) {
        echo json_encode(['error' => 'Admin ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE admin_users SET username = ?, full_name = ?, email = ?, role = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("ssssii", $username, $full_name, $email, $role, $is_active, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Admin account updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update admin account']);
    }
}

function deleteAdmin($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['error' => 'Admin ID is required']);
        return;
    }
    
    // Don't allow deleting own account
    if ($id == $_SESSION['admin_id']) {
        echo json_encode(['error' => 'Cannot delete your own account']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Admin account deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete admin account']);
    }
}

// Webinar Management Functions
function getWebinars($conn) {
    $result = $conn->query("
        SELECT w.*, a.username as created_by_name 
        FROM webinars w 
        LEFT JOIN admin_users a ON w.created_by = a.id 
        ORDER BY w.created_at DESC
    ");
    
    $webinars = [];
    while ($row = $result->fetch_assoc()) {
        $webinars[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $webinars]);
}

function addWebinar($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    // Debug: Log received data
    error_log("Webinar form data received: " . print_r($_POST, true));
    
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $speaker_name = trim($_POST['speaker_name'] ?? '');
    $speaker_title = trim($_POST['speaker_title'] ?? '');
    $webinar_date = $_POST['webinar_date'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $webinar_link = trim($_POST['webinar_link'] ?? '');
    $google_form_link = trim($_POST['google_form_link'] ?? '');
    $created_by = $_SESSION['admin_id'];
    
    // Debug: Log processed data
    error_log("Processed webinar data: title=$title, speaker=$speaker_name, date=$webinar_date, duration=$duration, category=$category");
    
    // Validation
    if (empty($title) || empty($speaker_name) || empty($webinar_date) || empty($duration) || empty($category)) {
        $missing_fields = [];
        if (empty($title)) $missing_fields[] = 'title';
        if (empty($speaker_name)) $missing_fields[] = 'speaker_name';
        if (empty($webinar_date)) $missing_fields[] = 'webinar_date';
        if (empty($duration)) $missing_fields[] = 'duration';
        if (empty($category)) $missing_fields[] = 'category';
        
        echo json_encode(['error' => 'Required fields are missing: ' . implode(', ', $missing_fields)]);
        return;
    }
    
    // Insert webinar
    $stmt = $conn->prepare("INSERT INTO webinars (title, description, speaker_name, speaker_title, webinar_date, duration, category, webinar_link, google_form_link, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        echo json_encode(['error' => 'Failed to prepare statement: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("sssssisssi", $title, $description, $speaker_name, $speaker_title, $webinar_date, $duration, $category, $webinar_link, $google_form_link, $created_by);
    
    if ($stmt->execute()) {
        $webinar_id = $conn->insert_id;
        error_log("Webinar inserted successfully with ID: $webinar_id");
        echo json_encode(['success' => true, 'message' => 'Webinar added successfully', 'webinar_id' => $webinar_id]);
    } else {
        error_log("Failed to insert webinar: " . $stmt->error);
        echo json_encode(['error' => 'Failed to add webinar: ' . $stmt->error]);
    }
}

function updateWebinar($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $speaker_name = trim($_POST['speaker_name'] ?? '');
    $speaker_title = trim($_POST['speaker_title'] ?? '');
    $webinar_date = $_POST['webinar_date'] ?? '';
    $duration = $_POST['duration'] ?? '';
    $category = trim($_POST['category'] ?? '');
    $webinar_link = trim($_POST['webinar_link'] ?? '');
    $google_form_link = trim($_POST['google_form_link'] ?? '');
    $is_active = $_POST['is_active'] ?? 1;
    
    if (empty($id)) {
        echo json_encode(['error' => 'Webinar ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("UPDATE webinars SET title = ?, description = ?, speaker_name = ?, speaker_title = ?, webinar_date = ?, duration = ?, category = ?, webinar_link = ?, google_form_link = ?, is_active = ? WHERE id = ?");
    $stmt->bind_param("sssssisssii", $title, $description, $speaker_name, $speaker_title, $webinar_date, $duration, $category, $webinar_link, $google_form_link, $is_active, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Webinar updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update webinar']);
    }
}

function deleteWebinar($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $id = $_POST['id'] ?? '';
    
    if (empty($id)) {
        echo json_encode(['error' => 'Webinar ID is required']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM webinars WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Webinar deleted successfully']);
    } else {
        echo json_encode(['error' => 'Failed to delete webinar']);
    }
}

// Profile Management
function updateProfile($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        return;
    }
    
    $admin_id = $_SESSION['admin_id'];
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Get current admin data
    $stmt = $conn->prepare("SELECT password FROM admin_users WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();
    
    // If changing password, verify current password
    if (!empty($new_password)) {
        if (empty($current_password)) {
            echo json_encode(['error' => 'Current password is required to change password']);
            return;
        }
        
        if (!password_verify($current_password, $admin['password'])) {
            echo json_encode(['error' => 'Current password is incorrect']);
            return;
        }
        
        if ($new_password !== $confirm_password) {
            echo json_encode(['error' => 'New passwords do not match']);
            return;
        }
        
        if (strlen($new_password) < 8) {
            echo json_encode(['error' => 'New password must be at least 8 characters long']);
            return;
        }
    }
    
    // Update profile
    if (!empty($new_password)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $full_name, $email, $hashed_password, $admin_id);
    } else {
        $stmt = $conn->prepare("UPDATE admin_users SET full_name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $full_name, $email, $admin_id);
    }
    
    if ($stmt->execute()) {
        // Update session data
        $_SESSION['admin_name'] = $full_name;
        echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
    } else {
        echo json_encode(['error' => 'Failed to update profile']);
    }
}

// Helper functions
// Dashboard Statistics Functions
function getDashboardStats($conn) {
    try {
        // Get faculty count
        $faculty_result = $conn->query("SELECT COUNT(*) as count FROM faculty_accounts WHERE is_active = 1");
        $faculty_count = $faculty_result ? $faculty_result->fetch_assoc()['count'] : 0;
        
        // Get admin count
        $admin_result = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
        $admin_count = $admin_result ? $admin_result->fetch_assoc()['count'] : 0;
        
        // Get webinar count
        $webinar_result = $conn->query("SELECT COUNT(*) as count FROM webinars WHERE is_active = 1");
        $webinar_count = $webinar_result ? $webinar_result->fetch_assoc()['count'] : 0;
        
        // Log the counts for debugging
        error_log("Dashboard Stats - Faculty: $faculty_count, Admin: $admin_count, Webinar: $webinar_count");
        
        echo json_encode([
            'success' => true,
            'data' => [
                'faculty_count' => (int)$faculty_count,
                'admin_count' => (int)$admin_count,
                'webinar_count' => (int)$webinar_count
            ]
        ]);
    } catch (Exception $e) {
        error_log("Dashboard Stats Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getRecentFaculty($conn) {
    try {
        $result = $conn->query("
            SELECT f.*, a.username as created_by_name 
            FROM faculty_accounts f 
            LEFT JOIN admin_users a ON f.created_by = a.id 
            ORDER BY f.created_at DESC 
            LIMIT 5
        ");
        
        $faculty = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $faculty[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $faculty]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getRecentWebinars($conn) {
    try {
        $result = $conn->query("
            SELECT w.*, a.username as created_by_name 
            FROM webinars w 
            LEFT JOIN admin_users a ON w.created_by = a.id 
            ORDER BY w.created_at DESC 
            LIMIT 5
        ");
        
        $webinars = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $webinars[] = $row;
            }
        }
        
        echo json_encode(['success' => true, 'data' => $webinars]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function getProfile($conn) {
    try {
        $admin_id = $_SESSION['admin_id'];
        $stmt = $conn->prepare("SELECT id, username, full_name, email, role, created_at, last_login FROM admin_users WHERE id = ?");
        $stmt->bind_param("i", $admin_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $admin = $result->fetch_assoc();
            echo json_encode(['success' => true, 'data' => $admin]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Admin not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}

function logActivity($conn, $admin_id, $action, $description = '') {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $stmt = $conn->prepare("INSERT INTO admin_activity_log (admin_id, action, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("issss", $admin_id, $action, $description, $ip_address, $user_agent);
        $stmt->execute();
    }
}

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
    
    // Update existing table to support multiple courses and year levels
    // This will modify the column sizes if the table already exists
    // MySQL will handle this gracefully even if columns are already the right size
    $table_check = $conn->query("SHOW TABLES LIKE 'faculty_accounts'");
    if ($table_check && $table_check->num_rows > 0) {
        // Update course field to support multiple courses (up to 200 characters)
        @$conn->query("ALTER TABLE faculty_accounts MODIFY COLUMN course VARCHAR(200) NOT NULL");
        
        // Update year_level field to support multiple year levels (up to 50 characters)
        @$conn->query("ALTER TABLE faculty_accounts MODIFY COLUMN year_level VARCHAR(50) NOT NULL");
    }
    
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

$conn->close();
?>
