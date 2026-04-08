<?php
require_once __DIR__ . '/../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['csrf_token']) || 
    !isset($_SESSION['csrf_token']) || 
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    http_response_code(403);
    die('CSRF validation failed');
}
// Proceed with logout: session_destroy(), redirect, etc.
unset($_SESSION['csrf_token']); // Optional: regenerate or clear

// Destroy the session and log out the admin
logout();

// Redirect to admin login page
header("Location: login.php");
exit;
?>