<?php
// Comprehensive webinar debugging script
session_start();

// Set admin session for testing
$_SESSION['admin_id'] = 1;

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

echo "<h2>Webinar Database Debug</h2>";
echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .success { color: green; }
    .error { color: red; }
    .warning { color: orange; }
    .info { color: blue; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
    th { background-color: #f2f2f2; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
</style>";

// Test 1: Database Connection
echo "<h3>1. Database Connection Test</h3>";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}
echo "<p class='success'>✅ Database connection successful</p>";

// Test 2: Check if webinars table exists
echo "<h3>2. Webinars Table Check</h3>";
$table_check = $conn->query("SHOW TABLES LIKE 'webinars'");
if ($table_check->num_rows == 0) {
    echo "<p class='warning'>⚠️ Webinars table does not exist. Creating it...</p>";
    
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
        echo "<p class='success'>✅ Webinars table created successfully</p>";
    } else {
        echo "<p class='error'>❌ Failed to create webinars table: " . $conn->error . "</p>";
        exit;
    }
} else {
    echo "<p class='success'>✅ Webinars table exists</p>";
}

// Test 3: Check current webinars in database
echo "<h3>3. Current Webinars in Database</h3>";
$result = $conn->query("SELECT * FROM webinars ORDER BY created_at DESC");
if ($result && $result->num_rows > 0) {
    echo "<p class='info'>Found " . $result->num_rows . " webinars in database:</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Speaker</th><th>Date</th><th>Category</th><th>Active</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td>" . htmlspecialchars($row['speaker_name']) . "</td>";
        echo "<td>" . $row['webinar_date'] . "</td>";
        echo "<td>" . $row['category'] . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No webinars found in database</p>";
}

// Test 4: Test direct webinar insertion
echo "<h3>4. Test Direct Webinar Insertion</h3>";
$test_webinar = [
    'title' => 'Test Webinar ' . date('Y-m-d H:i:s'),
    'description' => 'This is a test webinar created at ' . date('Y-m-d H:i:s'),
    'speaker_name' => 'Dr. Test Speaker',
    'speaker_title' => 'Professor',
    'webinar_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
    'duration' => 60,
    'category' => 'upcoming',
    'webinar_link' => 'https://zoom.us/j/test123',
    'google_form_link' => 'https://forms.google.com/test',
    'created_by' => 1
];

$stmt = $conn->prepare("INSERT INTO webinars (title, description, speaker_name, speaker_title, webinar_date, duration, category, webinar_link, google_form_link, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo "<p class='error'>❌ Failed to prepare statement: " . $conn->error . "</p>";
} else {
    $stmt->bind_param("sssssisssi", 
        $test_webinar['title'], 
        $test_webinar['description'], 
        $test_webinar['speaker_name'], 
        $test_webinar['speaker_title'], 
        $test_webinar['webinar_date'], 
        $test_webinar['duration'], 
        $test_webinar['category'], 
        $test_webinar['webinar_link'], 
        $test_webinar['google_form_link'], 
        $test_webinar['created_by']
    );
    
    if ($stmt->execute()) {
        $webinar_id = $conn->insert_id;
        echo "<p class='success'>✅ Test webinar inserted successfully with ID: $webinar_id</p>";
    } else {
        echo "<p class='error'>❌ Failed to insert test webinar: " . $stmt->error . "</p>";
    }
}

// Test 5: Test the admin API endpoint
echo "<h3>5. Test Admin API Endpoint</h3>";
echo "<p class='info'>Testing the admin API with simulated form data...</p>";

// Simulate form submission
$_POST = [
    'title' => 'API Test Webinar',
    'description' => 'Testing API endpoint',
    'speaker_name' => 'Dr. API Test',
    'speaker_title' => 'Expert',
    'webinar_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
    'duration' => 90,
    'category' => 'upcoming',
    'webinar_link' => 'https://zoom.us/j/api123',
    'google_form_link' => 'https://forms.google.com/api'
];

$_GET['action'] = 'add_webinar';

// Capture API output
ob_start();
include 'admin_api.php';
$api_output = ob_get_clean();

echo "<p class='info'>API Response:</p>";
echo "<pre>" . htmlspecialchars($api_output) . "</pre>";

// Test 6: Check if API test webinar was inserted
echo "<h3>6. Verify API Test Webinar</h3>";
$api_test_result = $conn->query("SELECT * FROM webinars WHERE title = 'API Test Webinar'");
if ($api_test_result && $api_test_result->num_rows > 0) {
    echo "<p class='success'>✅ API test webinar was inserted successfully</p>";
    $api_webinar = $api_test_result->fetch_assoc();
    echo "<pre>" . print_r($api_webinar, true) . "</pre>";
} else {
    echo "<p class='error'>❌ API test webinar was NOT inserted</p>";
}

// Test 7: Check PHP error logs
echo "<h3>7. PHP Error Log Check</h3>";
$error_log_path = ini_get('error_log');
if ($error_log_path && file_exists($error_log_path)) {
    echo "<p class='info'>Error log path: $error_log_path</p>";
    $recent_errors = file_get_contents($error_log_path);
    if (strpos($recent_errors, 'webinar') !== false) {
        echo "<p class='warning'>⚠️ Found webinar-related errors in log:</p>";
        echo "<pre>" . htmlspecialchars($recent_errors) . "</pre>";
    } else {
        echo "<p class='success'>✅ No webinar-related errors found in log</p>";
    }
} else {
    echo "<p class='warning'>⚠️ Could not access PHP error log</p>";
}

// Test 8: Check session
echo "<h3>8. Session Check</h3>";
echo "<p class='info'>Current session data:</p>";
echo "<pre>" . print_r($_SESSION, true) . "</pre>";

$conn->close();

echo "<hr>";
echo "<h3>Next Steps:</h3>";
echo "<ol>";
echo "<li>If the direct insertion worked but API didn't, there's an issue with the API</li>";
echo "<li>If neither worked, there's a database issue</li>";
echo "<li>Check the admin dashboard form submission in browser console</li>";
echo "<li>Look for JavaScript errors in the browser console</li>";
echo "</ol>";
?>
