<?php
// Get webinars for public display
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if webinars table exists, if not create it
$table_check = $conn->query("SHOW TABLES LIKE 'webinars'");
if ($table_check->num_rows == 0) {
    // Create the webinars table
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
    
    if (!$conn->query($create_table)) {
        echo json_encode(['success' => false, 'error' => 'Failed to create webinars table: ' . $conn->error]);
        exit;
    }
}

// Get active webinars
$result = $conn->query("
    SELECT * FROM webinars 
    WHERE is_active = 1 
    ORDER BY webinar_date DESC
");

if (!$result) {
    echo json_encode(['success' => false, 'error' => 'Query failed: ' . $conn->error]);
    exit;
}

$webinars = [];
while ($row = $result->fetch_assoc()) {
    $webinars[] = $row;
}

echo json_encode(['success' => true, 'data' => $webinars, 'count' => count($webinars)]);

$conn->close();
?>
