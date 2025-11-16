<?php
// Dashboard diagnostic script
session_start();

echo "<h2>Admin Dashboard Diagnostic</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// Test 1: Session Check
echo "<h3>1. Session Check</h3>";
if (isset($_SESSION['admin_id'])) {
    echo "<p class='success'>✅ Admin session exists</p>";
    echo "<p>Admin ID: " . $_SESSION['admin_id'] . "</p>";
    echo "<p>Admin Name: " . ($_SESSION['admin_name'] ?? 'Not set') . "</p>";
    echo "<p>Admin Role: " . ($_SESSION['admin_role'] ?? 'Not set') . "</p>";
} else {
    echo "<p class='error'>❌ No admin session found</p>";
    echo "<p><a href='admin_login.php'>Go to Admin Login</a></p>";
}

// Test 2: Database Connection
echo "<h3>2. Database Connection</h3>";
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}
echo "<p class='success'>✅ Database connection successful</p>";

// Test 3: Table Existence
echo "<h3>3. Table Existence Check</h3>";
$tables = ['admin_users', 'faculty_accounts', 'webinars', 'admin_activity_log'];
foreach ($tables as $table) {
    $result = $conn->query("SHOW TABLES LIKE '$table'");
    if ($result && $result->num_rows > 0) {
        echo "<p class='success'>✅ Table '$table' exists</p>";
    } else {
        echo "<p class='error'>❌ Table '$table' does not exist</p>";
    }
}

// Test 4: Data Counts
echo "<h3>4. Data Counts</h3>";
$faculty_count = $conn->query("SELECT COUNT(*) as count FROM faculty_accounts WHERE is_active = 1");
$admin_count = $conn->query("SELECT COUNT(*) as count FROM admin_users WHERE is_active = 1");
$webinar_count = $conn->query("SELECT COUNT(*) as count FROM webinars WHERE is_active = 1");

if ($faculty_count) {
    $faculty_num = $faculty_count->fetch_assoc()['count'];
    echo "<p class='info'>Faculty accounts: $faculty_num</p>";
} else {
    echo "<p class='error'>❌ Error counting faculty</p>";
}

if ($admin_count) {
    $admin_num = $admin_count->fetch_assoc()['count'];
    echo "<p class='info'>Admin accounts: $admin_num</p>";
} else {
    echo "<p class='error'>❌ Error counting admins</p>";
}

if ($webinar_count) {
    $webinar_num = $webinar_count->fetch_assoc()['count'];
    echo "<p class='info'>Webinars: $webinar_num</p>";
} else {
    echo "<p class='error'>❌ Error counting webinars</p>";
}

// Test 5: API Endpoints
echo "<h3>5. API Endpoint Tests</h3>";
$api_tests = [
    'get_dashboard_stats' => 'Dashboard Statistics',
    'get_recent_faculty' => 'Recent Faculty',
    'get_recent_webinars' => 'Recent Webinars',
    'get_faculty' => 'All Faculty',
    'get_admins' => 'All Admins',
    'get_webinars' => 'All Webinars'
];

foreach ($api_tests as $action => $description) {
    $url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/admin_api.php?action=$action";
    echo "<p><strong>$description:</strong> <a href='$url' target='_blank'>Test API</a></p>";
}

// Test 6: File Existence
echo "<h3>6. File Existence Check</h3>";
$files = [
    '../FRONTEND/admin_dashboard.php' => 'Admin Dashboard',
    '../FRONTEND/js/admin-dashboard.js' => 'Dashboard JavaScript',
    '../FRONTEND/css/admind.css' => 'Dashboard CSS',
    'admin_api.php' => 'Admin API'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $description exists</p>";
    } else {
        echo "<p class='error'>❌ $description missing: $file</p>";
    }
}

// Test 7: Recent Data
echo "<h3>7. Recent Data Check</h3>";
$recent_faculty = $conn->query("SELECT * FROM faculty_accounts ORDER BY created_at DESC LIMIT 3");
if ($recent_faculty && $recent_faculty->num_rows > 0) {
    echo "<p class='success'>✅ Recent faculty data available</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Faculty ID</th><th>Course</th><th>Created</th></tr>";
    while ($row = $recent_faculty->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['faculty_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['course']) . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No recent faculty data</p>";
}

$recent_webinars = $conn->query("SELECT * FROM webinars ORDER BY created_at DESC LIMIT 3");
if ($recent_webinars && $recent_webinars->num_rows > 0) {
    echo "<p class='success'>✅ Recent webinar data available</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Speaker</th><th>Date</th><th>Category</th></tr>";
    while ($row = $recent_webinars->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['speaker_name']) . "</td>";
        echo "<td>" . $row['webinar_date'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No recent webinar data</p>";
}

$conn->close();

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<ul>";
echo "<li><a href='../FRONTEND/admin_dashboard.php'>Go to Admin Dashboard</a></li>";
echo "<li><a href='../FRONTEND/test_dashboard.html'>Test Dashboard Functions</a></li>";
echo "<li><a href='admin_login.php'>Admin Login</a></li>";
echo "</ul>";
?>
