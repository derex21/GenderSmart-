<?php
// Test script to verify webinar functionality
header('Content-Type: text/html; charset=UTF-8');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

echo "<h2>Webinar System Test</h2>";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<p style='color: red;'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p style='color: green;'>✅ Database connection successful</p>";

// Check if webinars table exists
$table_check = $conn->query("SHOW TABLES LIKE 'webinars'");
if ($table_check->num_rows == 0) {
    echo "<p style='color: orange;'>⚠️ Webinars table does not exist. Creating it...</p>";
    
    $create_table = "CREATE TABLE webinars (
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
        created_by INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    if ($conn->query($create_table)) {
        echo "<p style='color: green;'>✅ Webinars table created successfully</p>";
    } else {
        echo "<p style='color: red;'>❌ Failed to create webinars table: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p style='color: green;'>✅ Webinars table exists</p>";
}

// Check webinar count
$count_result = $conn->query("SELECT COUNT(*) as count FROM webinars WHERE is_active = 1");
if ($count_result) {
    $count = $count_result->fetch_assoc()['count'];
    echo "<p style='color: blue;'>📊 Active webinars: " . $count . "</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to count webinars: " . $conn->error . "</p>";
}

// List all webinars
$result = $conn->query("SELECT * FROM webinars ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    echo "<h3>📋 All Webinars:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>ID</th><th>Title</th><th>Speaker</th><th>Date</th><th>Category</th><th>Active</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['speaker_name']) . "</td>";
        echo "<td>" . $row['webinar_date'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: orange;'>⚠️ No webinars found in database</p>";
}

// Test the API endpoint
echo "<h3>🔗 Testing API Endpoint:</h3>";
$api_url = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/get_webinars.php";
echo "<p>API URL: <a href='$api_url' target='_blank'>$api_url</a></p>";

$api_response = file_get_contents($api_url);
if ($api_response) {
    $api_data = json_decode($api_response, true);
    if ($api_data && $api_data['success']) {
        echo "<p style='color: green;'>✅ API working correctly</p>";
        echo "<p>API Response: " . htmlspecialchars($api_response) . "</p>";
    } else {
        echo "<p style='color: red;'>❌ API returned error: " . ($api_data['error'] ?? 'Unknown error') . "</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Failed to fetch API response</p>";
}

$conn->close();

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>Go to the admin dashboard</li>";
echo "<li>Add a new webinar</li>";
echo "<li>Check the gadwebinars.html page to see if it appears</li>";
echo "<li>If not working, check browser console for errors</li>";
echo "</ol>";
?>
