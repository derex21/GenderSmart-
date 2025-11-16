<?php
session_start();

// Check if faculty is logged in
if (!isset($_SESSION['faculty_id'])) {
    header("Location: ../FRONTEND/faculty_login.php");
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

// Get faculty info
$faculty_id = $_SESSION['faculty_id'];
$faculty_name = $_SESSION['faculty_name'];
$faculty_faculty_id = $_SESSION['faculty_faculty_id'];
$faculty_email = $_SESSION['faculty_email'];
$faculty_course = $_SESSION['faculty_course'];
$faculty_year_level = $_SESSION['faculty_year_level'];

// Get recent webinars for faculty
$recent_webinars_result = $conn->query("
    SELECT * FROM webinars 
    WHERE is_active = 1 
    ORDER BY webinar_date DESC 
    LIMIT 5
");

// Return data as JSON
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'data' => [
        'faculty_info' => [
            'id' => $faculty_id,
            'name' => $faculty_name,
            'faculty_id' => $faculty_faculty_id,
            'email' => $faculty_email,
            'course' => $faculty_course,
            'year_level' => $faculty_year_level
        ],
        'recent_webinars' => $recent_webinars_result ? $recent_webinars_result->fetch_all(MYSQLI_ASSOC) : []
    ]
]);

$conn->close();
?>
