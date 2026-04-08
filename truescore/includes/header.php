<?php
// includes/student/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../includes/auth.php';

if (!is_student_logged_in()) {
    header("Location: /public/login.php");
    exit;
}

$student = $_SESSION[STUDENT_SESSION];
$student_name = $student['name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: #f4f4f9;
        }

        header {
            background: #007bff;
            color: #fff;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header h1 {
            margin: 0;
            font-size: 20px;
        }

        nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
            font-weight: bold;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 0 15px;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?= htmlspecialchars($student_name) ?></h1>
    <nav>
        <a href="dashboard.php">Dashboard</a>
        <a href="view_result.php">View Results</a>
        <a href="verify.php">Verify Result</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<div class="container">