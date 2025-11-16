<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to login page
header("Location: ../FRONTEND/student_login.php");
exit;
?>
