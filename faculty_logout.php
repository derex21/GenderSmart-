<?php
session_start();

// Destroy all session data
session_destroy();

// Redirect to faculty login page
header("Location: ../FRONTEND/faculty_login.php");
exit;
?>
