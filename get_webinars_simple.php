<?php
// Simple version of get_webinars.php for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set proper headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

try {
    // Connect to DB
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Database connection failed: " . $conn->connect_error);
    }

    // Check if webinars table exists
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
            throw new Exception("Failed to create webinars table: " . $conn->error);
        }
    }

    // Get active webinars
    $result = $conn->query("
        SELECT * FROM webinars 
        WHERE is_active = 1 
        ORDER BY webinar_date DESC
    ");

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $webinars = [];
    while ($row = $result->fetch_assoc()) {
        $webinars[] = $row;
    }

    echo json_encode([
        'success' => true, 
        'data' => $webinars, 
        'count' => count($webinars),
        'message' => 'Webinars loaded successfully'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => $e->getMessage(),
        'data' => []
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>
