<?php
// Simple script to check webinars in database
header('Content-Type: text/html; charset=UTF-8');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

echo "<h2>Webinar Database Check</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
</style>";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p class='success'>✅ Database connection successful</p>";

// Check if webinars table exists
$table_check = $conn->query("SHOW TABLES LIKE 'webinars'");
if ($table_check->num_rows == 0) {
    echo "<p class='warning'>⚠️ Webinars table does not exist</p>";
    exit;
}

// Get all webinars
$result = $conn->query("SELECT * FROM webinars ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    echo "<h3>All Webinars in Database (" . $result->num_rows . " total):</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Speaker</th><th>Date</th><th>Category</th><th>Active</th><th>Created</th><th>Created By</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['speaker_name']) . "</td>";
        echo "<td>" . $row['webinar_date'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "<td>" . $row['created_by'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No webinars found in database</p>";
}

// Check recent activity
echo "<h3>Recent Database Activity:</h3>";
$recent_result = $conn->query("SELECT * FROM webinars WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR) ORDER BY created_at DESC");
if ($recent_result && $recent_result->num_rows > 0) {
    echo "<p class='success'>✅ Found " . $recent_result->num_rows . " webinars created in the last hour</p>";
} else {
    echo "<p class='warning'>⚠️ No webinars created in the last hour</p>";
}

$conn->close();

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<ul>";
echo "<li><a href='debug_webinar.php'>Run Full Debug Test</a></li>";
echo "<li><a href='../FRONTEND/test_webinar_form.html'>Test Webinar Form</a></li>";
echo "<li><a href='../FRONTEND/admin_dashboard.php'>Admin Dashboard</a></li>";
echo "</ul>";
?>
