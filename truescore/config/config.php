<?php
// config/config.php

// Project settings
define('PROJECT_NAME', 'TrueScore');
define('BASE_URL', 'http://localhost/TrueScore/'); // change to your server URL

// Session settings
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Default timezone
date_default_timezone_set('Africa/Lagos');

// Other global constants
define('ADMIN_SESSION', 'admin_logged_in');
define('STUDENT_SESSION', 'student_logged_in');
define('RESULT_UPLOAD_PATH', __DIR__ . '/../assets/images/results/'); // scanned results folder

define('HASH_SECRET_KEY', 'frtits17@gmail');
?>