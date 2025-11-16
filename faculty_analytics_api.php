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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_analytics':
        getAnalytics($conn);
        break;
    case 'get_leaderboard':
        getLeaderboard($conn);
        break;
    case 'get_incomplete_activities':
        getIncompleteActivities($conn);
        break;
    case 'get_courses':
        getCourses($conn);
        break;
    case 'get_student_filter':
        getStudentFilter($conn);
        break;
    default:
        echo json_encode(['success' => false, 'error' => 'Invalid action']);
        break;
}

function getAnalytics($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    $course_filter = $_GET['course'] ?? '';
    $year_filter = $_GET['year'] ?? '';
    $period_filter = $_GET['period'] ?? 'all';
    
    // Build date filter
    $date_filter = '';
    switch ($period_filter) {
        case 'week':
            $date_filter = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 WEEK)";
            break;
        case 'month':
            $date_filter = "AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            break;
        case 'semester':
            $date_filter = "AND created_at >= DATE_SUB(NOW(), INTERVAL 4 MONTH)";
            break;
    }
    
    // Build course and year filters
    $where_conditions = ["s.faculty_id = ?"];
    $params = [$faculty_id];
    $param_types = "i";
    
    if (!empty($course_filter)) {
        $where_conditions[] = "s.course = ?";
        $params[] = $course_filter;
        $param_types .= "s";
    }
    
    if (!empty($year_filter)) {
        $where_conditions[] = "s.year_level = ?";
        $params[] = $year_filter;
        $param_types .= "s";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Get student performance metrics
    $performance_query = "
        SELECT 
            AVG(COALESCE(sas.grade, 0)) as avg_grade,
            COUNT(DISTINCT s.id) as total_students,
            COUNT(DISTINCT CASE WHEN sas.submitted_at IS NOT NULL THEN s.id END) as active_students,
            COUNT(DISTINCT sma.student_id) as students_with_module_access,
            COUNT(sma.id) as total_module_views,
            COUNT(DISTINCT sma.module_id) as unique_modules_accessed,
            AVG(sma.quiz_score) as avg_quiz_score,
            COUNT(sas.id) as total_submissions,
            COUNT(CASE WHEN sas.is_late = 1 THEN 1 END) as late_submissions
        FROM students s
        LEFT JOIN student_module_access sma ON s.id = sma.student_id
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        WHERE $where_clause
    ";
    
    $stmt = $conn->prepare($performance_query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    $performance = $result->fetch_assoc();
    $stmt->close();
    
    // Get module engagement
    $module_engagement = getModuleEngagement($conn, $faculty_id, $where_clause, $params, $param_types);
    
    // Get activity engagement
    $activity_engagement = getActivityEngagement($conn, $faculty_id, $where_clause, $params, $param_types);
    
    // Get performance trends
    $performance_trends = getPerformanceTrends($conn, $faculty_id, $where_clause, $params, $param_types);
    
    // Get course engagement
    $course_engagement = getCourseEngagement($conn, $faculty_id);
    
    echo json_encode([
        'success' => true,
        'data' => [
            'performance' => $performance,
            'module_engagement' => $module_engagement,
            'activity_engagement' => $activity_engagement,
            'performance_trends' => $performance_trends,
            'course_engagement' => $course_engagement
        ]
    ]);
}

function getModuleEngagement($conn, $faculty_id, $where_clause, $params, $param_types) {
    $query = "
        SELECT 
            COUNT(sma.id) as total_views,
            COUNT(DISTINCT sma.student_id) as unique_students,
            AVG(sma.quiz_score) as avg_quiz_score,
            COUNT(CASE WHEN sma.quiz_attempts > 0 THEN 1 END) as quiz_attempts
        FROM students s
        LEFT JOIN student_module_access sma ON s.id = sma.student_id
        LEFT JOIN modules m ON sma.module_id = m.id
        WHERE $where_clause AND m.faculty_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $new_params = array_merge($params, [$faculty_id]);
    $new_types = $param_types . "i";
    $stmt->bind_param($new_types, ...$new_params);
    $stmt->execute();
    $result = $stmt->get_result();
    $engagement = $result->fetch_assoc();
    $stmt->close();
    
    return $engagement;
}

function getActivityEngagement($conn, $faculty_id, $where_clause, $params, $param_types) {
    $query = "
        SELECT 
            COUNT(sas.id) as total_submissions,
            COUNT(CASE WHEN sas.is_late = 1 THEN 1 END) as late_submissions,
            AVG(sas.grade) as avg_grade,
            COUNT(DISTINCT sas.student_id) as unique_students
        FROM students s
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        LEFT JOIN activities a ON sas.activity_id = a.id
        WHERE $where_clause AND a.faculty_id = ?
    ";
    
    $stmt = $conn->prepare($query);
    $new_params = array_merge($params, [$faculty_id]);
    $new_types = $param_types . "i";
    $stmt->bind_param($new_types, ...$new_params);
    $stmt->execute();
    $result = $stmt->get_result();
    $engagement = $result->fetch_assoc();
    $stmt->close();
    
    return $engagement;
}

function getPerformanceTrends($conn, $faculty_id, $where_clause, $params, $param_types) {
    $query = "
        SELECT 
            DATE(sas.submitted_at) as date,
            AVG(sas.grade) as avg_grade,
            COUNT(sas.id) as submissions
        FROM students s
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        LEFT JOIN activities a ON sas.activity_id = a.id
        WHERE $where_clause AND a.faculty_id = ? AND sas.submitted_at IS NOT NULL
        GROUP BY DATE(sas.submitted_at)
        ORDER BY date DESC
        LIMIT 30
    ";
    
    $stmt = $conn->prepare($query);
    $new_params = array_merge($params, [$faculty_id]);
    $new_types = $param_types . "i";
    $stmt->bind_param($new_types, ...$new_params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $trends = [];
    while ($row = $result->fetch_assoc()) {
        $trends[] = $row;
    }
    $stmt->close();
    
    return $trends;
}

function getCourseEngagement($conn, $faculty_id) {
    $query = "
        SELECT 
            s.course,
            COUNT(DISTINCT s.id) as total_students,
            COUNT(sma.id) as module_views,
            COUNT(sas.id) as activity_submissions,
            AVG(sma.quiz_score) as avg_quiz_score
        FROM students s
        LEFT JOIN student_module_access sma ON s.id = sma.student_id
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        WHERE s.faculty_id = ?
        GROUP BY s.course
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $engagement = [];
    while ($row = $result->fetch_assoc()) {
        $engagement[] = $row;
    }
    $stmt->close();
    
    return $engagement;
}

function getLeaderboard($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    $ranking_type = $_GET['ranking_type'] ?? 'overall';
    $course_filter = $_GET['course'] ?? '';
    $year_filter = $_GET['year'] ?? '';
    
    // Build filters
    $where_conditions = ["s.faculty_id = ?"];
    $params = [$faculty_id];
    $param_types = "i";
    
    if (!empty($course_filter)) {
        $where_conditions[] = "s.course = ?";
        $params[] = $course_filter;
        $param_types .= "s";
    }
    
    if (!empty($year_filter)) {
        $where_conditions[] = "s.year_level = ?";
        $params[] = $year_filter;
        $param_types .= "s";
    }
    
    $where_clause = implode(" AND ", $where_conditions);
    
    // Build ranking query based on type
    $order_by = '';
    switch ($ranking_type) {
        case 'modules':
            $order_by = "module_score DESC";
            break;
        case 'activities':
            $order_by = "activity_score DESC";
            break;
        case 'quizzes':
            $order_by = "quiz_score DESC";
            break;
        default: // overall
            $order_by = "overall_score DESC";
            break;
    }
    
    $query = "
        SELECT 
            s.id,
            s.student_id,
            s.full_name,
            s.course,
            s.year_level,
            COALESCE(AVG(sas.grade), 0) as activity_score,
            COALESCE(AVG(sma.quiz_score), 0) as quiz_score,
            COUNT(DISTINCT sma.module_id) as modules_completed,
            COUNT(DISTINCT sas.activity_id) as activities_completed,
            (COALESCE(AVG(sas.grade), 0) * 0.4 + COALESCE(AVG(sma.quiz_score), 0) * 0.3 + 
             COUNT(DISTINCT sma.module_id) * 10 * 0.2 + COUNT(DISTINCT sas.activity_id) * 5 * 0.1) as overall_score
        FROM students s
        LEFT JOIN student_module_access sma ON s.id = sma.student_id
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id
        WHERE $where_clause
        GROUP BY s.id, s.student_id, s.full_name, s.course, s.year_level
        ORDER BY $order_by
        LIMIT 50
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($param_types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $leaderboard = [];
    $rank = 1;
    while ($row = $result->fetch_assoc()) {
        $row['rank'] = $rank++;
        $leaderboard[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'leaderboard' => $leaderboard
    ]);
}

function getIncompleteActivities($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    
    $query = "
        SELECT 
            s.id,
            s.student_id,
            s.full_name,
            s.course,
            s.year_level,
            a.id as activity_id,
            a.title as activity_title,
            a.deadline,
            CASE 
                WHEN a.deadline < NOW() THEN 'Overdue'
                ELSE 'Pending'
            END as status
        FROM students s
        CROSS JOIN activities a
        LEFT JOIN student_activity_submissions sas ON s.id = sas.student_id AND a.id = sas.activity_id
        WHERE s.faculty_id = ? 
        AND a.faculty_id = ?
        AND a.is_active = 1
        AND sas.id IS NULL
        ORDER BY a.deadline ASC, s.full_name ASC
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $faculty_id, $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $incomplete = [];
    while ($row = $result->fetch_assoc()) {
        $incomplete[] = $row;
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'incomplete' => $incomplete
    ]);
}

function getCourses($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    
    $query = "
        SELECT DISTINCT course 
        FROM students 
        WHERE faculty_id = ? 
        ORDER BY course
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row['course'];
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);
}

function getStudentFilter($conn) {
    $faculty_id = $_SESSION['faculty_id'];
    
    $query = "
        SELECT DISTINCT course, year_level
        FROM students 
        WHERE faculty_id = ? 
        ORDER BY course, year_level
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $faculty_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $filters = [
        'courses' => [],
        'years' => []
    ];
    
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['course'], $filters['courses'])) {
            $filters['courses'][] = $row['course'];
        }
        if (!in_array($row['year_level'], $filters['years'])) {
            $filters['years'][] = $row['year_level'];
        }
    }
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'filters' => $filters
    ]);
}

$conn->close();
?>
