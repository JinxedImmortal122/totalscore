<?php
require_once __DIR__ . '/../includes/auth.php';

// Destroy the session and log out the admin
logout();

// Redirect to admin login page
header("Location: login.php");
exit;
?>