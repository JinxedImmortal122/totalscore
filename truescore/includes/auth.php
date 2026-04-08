<?php
// includes/auth.php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Admin login check
function admin_login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION[ADMIN_SESSION] = [
            'admin_id' => $admin['admin_id'],
            'name' => $admin['name'],
            'email' => $admin['email']
        ];
        return true;
    }
    return false;
}

// Student login check
function student_login($email, $password) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION[STUDENT_SESSION] = [
            'student_id' => $student['student_id'],
            'name' => $student['full_name'],
            'email' => $student['email']
        ];
        return true;
    }
    return false;
}

// Check if admin is logged in
function is_admin_logged_in() {
    return isset($_SESSION[ADMIN_SESSION]);
}

// Check if student is logged in
function is_student_logged_in() {
    return isset($_SESSION[STUDENT_SESSION]);
}

// Logout
function logout() {
    session_destroy();
}
?>