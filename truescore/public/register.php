<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$full_name = '';
$email = '';
$matric_number = '';

/* ===========================
   Handle Student Registration
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $matric_number = trim($_POST['matric_number'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validation
    if ($full_name === '' || $email === '' || $matric_number === '' || $password === '' || $confirm_password === '') {
        $errors[] = "All fields are required.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email address.";
    }

    if (!$errors) {
        try {
            // Check if email or matric_number already exists
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM students WHERE email = :email OR matric_number = :matric");
            $stmt->execute([
                ':email' => $email,
                ':matric' => $matric_number
            ]);

            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Email or Matric number already exists.";
            } else {
                // Insert new student
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO students (full_name, email, matric_number, password, created_at) VALUES (:full_name, :email, :matric_number, :password, NOW())");
                $stmt->execute([
                    ':full_name' => $full_name,
                    ':email' => $email,
                    ':matric_number' => $matric_number,
                    ':password' => $hashed_password
                ]);

                // Redirect to login
                header("Location: login.php?registered=1");
                exit;
            }

        } catch (Throwable $e) {
            error_log($e->getMessage());
            $errors[] = "Registration failed. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Registration — TrueScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <style>
        body {
            font-family: 'Jost', sans-serif;
            background: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .auth-container {
            background: #fff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        .auth-header h1 {
            margin: 0 0 8px;
            font-size: 24px;
            color: #333;
        }

        .auth-header p {
            margin: 0 0 20px;
            color: #666;
        }

        .auth-alert-error {
            background: #f8d7da;
            color: #842029;
            border: 1px solid #f5c2c7;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
            text-align: left;
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            text-align: left;
        }

        .form-label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #555;
        }

        .form-input {
            width: 100%;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }

        .auth-btn {
            padding: 12px;
            border: none;
            border-radius: 6px;
            background-color: #28a745;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .auth-btn:hover {
            background-color: #218838;
        }

        .auth-footer {
            margin-top: 20px;
            font-size: 14px;
            color: #555;
        }

        .auth-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="auth-container">

    <div class="auth-header">
        <h1>Create an Account</h1>
        <p>Register as a student to access TrueScore</p>
    </div>

    <!-- Errors -->
    <?php if ($errors): ?>
        <div class="auth-alert auth-alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <form method="POST" class="auth-form" novalidate>
        <div class="form-group">
            <label for="full_name" class="form-label">Full Name</label>
            <input type="text" name="full_name" id="full_name" class="form-input" value="<?= e($full_name) ?>" required>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-input" value="<?= e($email) ?>" required>
        </div>

        <div class="form-group">
            <label for="matric_number" class="form-label">Matric Number</label>
            <input type="text" name="matric_number" id="matric_number" class="form-input" value="<?= e($matric_number) ?>" required>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" id="password" class="form-input" required>
        </div>

        <div class="form-group">
            <label for="confirm_password" class="form-label">Confirm Password</label>
            <input type="password" name="confirm_password" id="confirm_password" class="form-input" required>
        </div>

        <button type="submit" class="auth-btn">Register</button>
    </form>

    <div class="auth-footer">
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>

</div>

</body>
</html>