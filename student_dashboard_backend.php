<?php
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['student_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Get student info
$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';
$student_student_id = $_SESSION['student_student_id'] ?? '';
$student_gender = $_SESSION['student_gender'] ?? '';
$student_course = $_SESSION['student_course'] ?? '';
$student_year_level = $_SESSION['student_year_level'] ?? '';

// Get recent webinars for students
$recent_webinars_data = [];
$recent_webinars_result = $conn->query("
    SELECT title, speaker_name, webinar_date, category 
    FROM webinars 
    WHERE is_active = 1 
    ORDER BY webinar_date DESC 
    LIMIT 5
");
if ($recent_webinars_result) {
    while ($row = $recent_webinars_result->fetch_assoc()) {
        $recent_webinars_data[] = $row;
    }
}

$conn->close();

echo json_encode([
    'success' => true,
    'data' => [
        'student_info' => [
            'id' => $student_id,
            'name' => $student_name,
            'student_id' => $student_student_id,
            'gender' => $student_gender,
            'course' => $student_course,
            'year_level' => $student_year_level
        ],
        'recent_webinars' => $recent_webinars_data
    ]
]);
?>
