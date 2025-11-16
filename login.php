<?php
header('Content-Type: application/json');
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "gendersmart_db");
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed.']);
    exit;
}

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($student_id) || empty($password)) {
        echo json_encode(['success' => false, 'error' => 'Student ID and password are required.']);
        exit;
    }

    // Find student record
    $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Account not found. Please check your Student ID or register.',
            'status' => 'not_found'
        ]);
        exit;
    }

    $student = $result->fetch_assoc();
    $stmt->close();

    // Check account status
    if ($student['status'] === 'pending') {
        echo json_encode([
            'success' => false,
            'error' => 'Your account is still pending faculty approval.',
            'status' => 'pending'
        ]);
        exit;
    }

    if ($student['status'] === 'rejected') {
        $reason = $student['rejection_reason'] ?? 'No reason provided.';
        echo json_encode([
            'success' => false,
            'error' => "Your registration was rejected.<br>Reason: " . htmlspecialchars($reason),
            'status' => 'rejected'
        ]);
        exit;
    }

    if ($student['status'] !== 'accepted') {
        echo json_encode([
            'success' => false,
            'error' => 'Your account is not approved for login.',
            'status' => 'not_accepted'
        ]);
        exit;
    }

    // ✅ Check password (supports hashed or plain text)
    $db_password = $student['password'];
    $isValid = password_verify($password, $db_password) || $password === $db_password;

    if (!$isValid) {
        echo json_encode([
            'success' => false,
            'error' => 'Incorrect password. Please try again.',
            'status' => 'password_error'
        ]);
        exit;
    }

    // ✅ Save session data
    $_SESSION['student_id'] = $student['id'];
    $_SESSION['student_student_id'] = $student['student_id'];
    $_SESSION['student_name'] = $student['full_name'];
    $_SESSION['student_gender'] = $student['gender'];
    $_SESSION['student_course'] = $student['course'];
    $_SESSION['student_year_level'] = $student['year_level'];
    $_SESSION['student_status'] = $student['status'];

    // Optional: log login activity
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $log = $conn->prepare("INSERT INTO student_activity_log (student_id, action, description, ip_address, user_agent) VALUES (?, 'login', 'Student logged in successfully', ?, ?)");
    $log->bind_param("iss", $student['id'], $ip, $agent);
    $log->execute();

    echo json_encode(['success' => true, 'message' => 'Login successful', 'redirect' => '../student_dashboard.php']);
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
}
$conn->close();
?>
