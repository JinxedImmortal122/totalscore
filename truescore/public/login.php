<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$errors = [];
$email = '';

/* ===========================
   Handle Student Login
=========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Email and password are required.";
    }

    if (!$errors) {
        if (student_login($email, $password)) {
            // Regenerate session ID for security
            session_regenerate_id(true);
            header("Location: dashboard.php");
            exit;
        } else {
            $errors[] = "Invalid email or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Login — TrueScore</title>
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
            background-color: #007bff;
            color: #fff;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .auth-btn:hover {
            background-color: #0056b3;
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

        .password-field {
            display: flex;
            align-items: center;
        }

        .password-toggle {
            background: none;
            border: none;
            cursor: pointer;
            margin-left: -35px;
        }

        svg {
            stroke: currentColor;
        }
    </style>
</head>
<body>

<div class="auth-container">

    <div class="auth-header">
        <h1>Welcome Back</h1>
        <p>Login to access your TrueScore account</p>
    </div>

    <!-- Errors -->
    <?php if ($errors): ?>
        <div class="auth-alert auth-alert-error">
            <?php foreach ($errors as $error): ?>
                <div><?= e($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Login Form -->
    <form method="POST" class="auth-form" novalidate>
        <div class="form-group">
            <label for="email" class="form-label">Email Address</label>
            <input type="email" name="email" id="email" class="form-input" placeholder="your@email.com" value="<?= e($email) ?>" required autofocus>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Password</label>
            <div class="password-field">
                <input type="password" name="password" id="password" class="form-input" placeholder="Enter your password" required>
                <button type="button" class="password-toggle" onclick="togglePassword()" aria-label="Toggle password visibility">
                    <svg id="eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </button>
            </div>
        </div>

        <button type="submit" class="auth-btn">Login</button>
    </form>

    <div class="auth-footer">
        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</div>

<script>
function togglePassword() {
    const pw = document.getElementById('password');
    const eye = document.getElementById('eye-icon');
    if (pw.type === 'password') {
        pw.type = 'text';
        eye.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
    } else {
        pw.type = 'password';
        eye.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
    }
}
</script>

</body>
</html>