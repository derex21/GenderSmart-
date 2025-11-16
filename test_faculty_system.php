<?php
// Test script to verify faculty system integration
session_start();

echo "<h2>Faculty System Test</h2>";
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
    .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
</style>";

// DB credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "gendersmart_db";

// Connect to DB
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo "<p class='error'>❌ Database connection failed: " . $conn->connect_error . "</p>";
    exit;
}

echo "<p class='success'>✅ Database connection successful</p>";

// Test 1: Check if faculty_accounts table exists
echo "<div class='test-section'>";
echo "<h3>1. Faculty Accounts Table Check</h3>";
$table_check = $conn->query("SHOW TABLES LIKE 'faculty_accounts'");
if ($table_check->num_rows == 0) {
    echo "<p class='error'>❌ Faculty accounts table does not exist</p>";
} else {
    echo "<p class='success'>✅ Faculty accounts table exists</p>";
}
echo "</div>";

// Test 2: Check if admin_users table exists (needed for foreign key)
echo "<div class='test-section'>";
echo "<h3>2. Admin Users Table Check</h3>";
$admin_table_check = $conn->query("SHOW TABLES LIKE 'admin_users'");
if ($admin_table_check->num_rows == 0) {
    echo "<p class='error'>❌ Admin users table does not exist (needed for faculty creation)</p>";
} else {
    echo "<p class='success'>✅ Admin users table exists</p>";
}
echo "</div>";

// Test 3: Check current faculty accounts
echo "<div class='test-section'>";
echo "<h3>3. Current Faculty Accounts</h3>";
$faculty_result = $conn->query("SELECT * FROM faculty_accounts ORDER BY created_at DESC");
if ($faculty_result && $faculty_result->num_rows > 0) {
    echo "<p class='success'>✅ Found " . $faculty_result->num_rows . " faculty accounts</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Faculty ID</th><th>Name</th><th>Email</th><th>Course</th><th>Year Level</th><th>Active</th><th>Created</th></tr>";
    while ($row = $faculty_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['faculty_id']) . "</td>";
        echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['course']) . "</td>";
        echo "<td>" . htmlspecialchars($row['year_level']) . "</td>";
        echo "<td>" . ($row['is_active'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='warning'>⚠️ No faculty accounts found</p>";
}
echo "</div>";

// Test 4: Test faculty login simulation
echo "<div class='test-section'>";
echo "<h3>4. Faculty Login Test</h3>";
$test_faculty_id = 'TEST001';
$test_password = 'testpass123';

// Check if test faculty exists
$stmt = $conn->prepare("SELECT id, faculty_id, password, full_name, email, course, year_level, is_active FROM faculty_accounts WHERE faculty_id = ?");
$stmt->bind_param("s", $test_faculty_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $faculty = $result->fetch_assoc();
    echo "<p class='info'>Test faculty account found: " . htmlspecialchars($faculty['full_name']) . "</p>";
    
    if (password_verify($test_password, $faculty['password'])) {
        echo "<p class='success'>✅ Password verification successful</p>";
        echo "<p class='info'>Faculty can login with:</p>";
        echo "<ul>";
        echo "<li>Faculty ID: " . htmlspecialchars($faculty['faculty_id']) . "</li>";
        echo "<li>Name: " . htmlspecialchars($faculty['full_name']) . "</li>";
        echo "<li>Email: " . htmlspecialchars($faculty['email']) . "</li>";
        echo "<li>Course: " . htmlspecialchars($faculty['course']) . "</li>";
        echo "<li>Year Level: " . htmlspecialchars($faculty['year_level']) . "</li>";
        echo "<li>Active: " . ($faculty['is_active'] ? 'Yes' : 'No') . "</li>";
        echo "</ul>";
    } else {
        echo "<p class='error'>❌ Password verification failed</p>";
    }
} else {
    echo "<p class='warning'>⚠️ Test faculty account not found</p>";
    echo "<p class='info'>You can create a test faculty account through the admin dashboard</p>";
}
echo "</div>";

// Test 5: Check webinars for faculty
echo "<div class='test-section'>";
echo "<h3>5. Webinars for Faculty</h3>";
$webinars_result = $conn->query("SELECT * FROM webinars WHERE is_active = 1 ORDER BY webinar_date DESC LIMIT 5");
if ($webinars_result && $webinars_result->num_rows > 0) {
    echo "<p class='success'>✅ Found " . $webinars_result->num_rows . " active webinars</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Title</th><th>Speaker</th><th>Date</th><th>Category</th></tr>";
    while ($row = $webinars_result->fetch_assoc()) {
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
    echo "<p class='warning'>⚠️ No active webinars found</p>";
}
echo "</div>";

// Test 6: File existence check
echo "<div class='test-section'>";
echo "<h3>6. File Existence Check</h3>";
$files = [
    '../FRONTEND/faculty_login.php' => 'Faculty Login Page',
    '../FRONTEND/faculty_dashboard.php' => 'Faculty Dashboard',
    '../FRONTEND/css/faculty_login.css' => 'Faculty Login CSS',
    '../FRONTEND/css/faculty_dashboard.css' => 'Faculty Dashboard CSS',
    '../FRONTEND/js/faculty-dashboard.js' => 'Faculty Dashboard JavaScript',
    'faculty_login.php' => 'Faculty Login Backend',
    'faculty_logout.php' => 'Faculty Logout Backend'
];

foreach ($files as $file => $description) {
    if (file_exists($file)) {
        echo "<p class='success'>✅ $description exists</p>";
    } else {
        echo "<p class='error'>❌ $description missing: $file</p>";
    }
}
echo "</div>";

// Test 7: Create test faculty account if none exists
echo "<div class='test-section'>";
echo "<h3>7. Create Test Faculty Account</h3>";
$test_faculty_check = $conn->query("SELECT id FROM faculty_accounts WHERE faculty_id = 'TEST001'");
if ($test_faculty_check->num_rows == 0) {
    // Get first admin as creator
    $admin_result = $conn->query("SELECT id FROM admin_users LIMIT 1");
    if ($admin_result && $admin_result->num_rows > 0) {
        $admin = $admin_result->fetch_assoc();
        $created_by = $admin['id'];
        
        $hashed_password = password_hash('testpass123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO faculty_accounts (faculty_id, password, full_name, email, course, year_level, created_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", 
            'TEST001',
            $hashed_password,
            'Dr. Test Faculty',
            'test.faculty@example.com',
            'Computer Science',
            '1st Year',
            $created_by
        );
        
        if ($stmt->execute()) {
            echo "<p class='success'>✅ Test faculty account created successfully</p>";
            echo "<p class='info'>Login credentials:</p>";
            echo "<ul>";
            echo "<li>Faculty ID: TEST001</li>";
            echo "<li>Password: testpass123</li>";
            echo "</ul>";
        } else {
            echo "<p class='error'>❌ Failed to create test faculty account: " . $stmt->error . "</p>";
        }
    } else {
        echo "<p class='error'>❌ No admin users found. Cannot create test faculty account.</p>";
    }
} else {
    echo "<p class='info'>Test faculty account already exists</p>";
}
echo "</div>";

$conn->close();

echo "<hr>";
echo "<h3>Quick Actions:</h3>";
echo "<ul>";
echo "<li><a href='../FRONTEND/faculty_login.php'>Faculty Login Page</a></li>";
echo "<li><a href='../FRONTEND/admin_dashboard.php'>Admin Dashboard (to create faculty accounts)</a></li>";
echo "<li><a href='../FRONTEND/admin_login.php'>Admin Login</a></li>";
echo "</ul>";

echo "<h3>How to Test the Faculty System:</h3>";
echo "<ol>";
echo "<li>Go to Admin Dashboard and create a faculty account</li>";
echo "<li>Use the faculty credentials to login at Faculty Login page</li>";
echo "<li>Access the Faculty Dashboard to view webinars and profile</li>";
echo "<li>Test the logout functionality</li>";
echo "</ol>";
?>
