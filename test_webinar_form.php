<?php
// Test script to verify webinar form submission
session_start();

// Simulate admin session for testing
$_SESSION['admin_id'] = 1;

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

echo "<h2>Webinar Form Test</h2>";

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

// Test webinar insertion
echo "<h3>Testing Webinar Insertion</h3>";

$test_data = [
    'title' => 'Test Webinar',
    'description' => 'This is a test webinar description',
    'speaker_name' => 'Dr. Test Speaker',
    'speaker_title' => 'Professor',
    'webinar_date' => '2024-12-31 14:00:00',
    'duration' => 60,
    'category' => 'upcoming',
    'webinar_link' => 'https://zoom.us/j/123456789',
    'google_form_link' => 'https://forms.google.com/test',
    'created_by' => 1
];

$stmt = $conn->prepare("INSERT INTO webinars (title, description, speaker_name, speaker_title, webinar_date, duration, category, webinar_link, google_form_link, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssisssi", 
    $test_data['title'], 
    $test_data['description'], 
    $test_data['speaker_name'], 
    $test_data['speaker_title'], 
    $test_data['webinar_date'], 
    $test_data['duration'], 
    $test_data['category'], 
    $test_data['webinar_link'], 
    $test_data['google_form_link'], 
    $test_data['created_by']
);

if ($stmt->execute()) {
    echo "<p style='color: green;'>✅ Test webinar inserted successfully</p>";
    echo "<p>Webinar ID: " . $conn->insert_id . "</p>";
} else {
    echo "<p style='color: red;'>❌ Failed to insert test webinar: " . $stmt->error . "</p>";
}

// Test the API endpoint
echo "<h3>Testing API Endpoint</h3>";

// Simulate POST request
$_POST = $test_data;
$_POST['action'] = 'add_webinar';

// Include the admin_api.php file
ob_start();
include 'admin_api.php';
$api_output = ob_get_clean();

echo "<p>API Response:</p>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Check if webinar was actually inserted
$result = $conn->query("SELECT * FROM webinars ORDER BY created_at DESC LIMIT 1");
if ($result && $result->num_rows > 0) {
    $webinar = $result->fetch_assoc();
    echo "<h3>Latest Webinar in Database:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Field</th><th>Value</th></tr>";
    foreach ($webinar as $field => $value) {
        echo "<tr><td>$field</td><td>" . htmlspecialchars($value) . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'>❌ No webinars found in database</p>";
}

$conn->close();

echo "<hr>";
echo "<p><strong>Instructions:</strong></p>";
echo "<ol>";
echo "<li>If the test webinar was inserted successfully, the form should work</li>";
echo "<li>Check the admin dashboard to see if you can add webinars</li>";
echo "<li>Check gadwebinars.html to see if the webinar appears</li>";
echo "</ol>";
?>
