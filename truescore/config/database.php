<?php
// config/database.php

$host = 'localhost';
$db_name = 'truescore_db';
$username = 'root';      // change if your MySQL user is different
$password = '';          // change if your MySQL has a password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    // Set PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>