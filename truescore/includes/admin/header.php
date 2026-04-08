<?php
require_once __DIR__ . '/../../includes/auth.php';

if (!is_admin_logged_in()) {
    header("Location: ../../admin/login.php");
    exit;
}

// Safely extract admin data
$admin_name = isset($_SESSION[ADMIN_SESSION]['name']) ? htmlspecialchars($_SESSION[ADMIN_SESSION]['name']) : 'Admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - TrueScore</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --primary-dark: #0056b3;
            --danger-color: #dc3545;
            --danger-dark: #c82333;
            --text-color: #333;
            --text-light: #666;
            --bg-light: #f8f9fa;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            color: var(--text-color);
        }

        .admin-navbar {
            background: rgba(0, 123, 255, 0.95);
            backdrop-filter: blur(10px);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .logo i {
            font-size: 1.8rem;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.75rem 1.25rem;
            border-radius: 25px;
            font-weight: 500;
            transition: var(--transition);
            white-space: nowrap;
        }

        .nav-links a:hover {
            background: rgba(255,255,255,0.2);
            transform: translateY(-1px);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.2);
            border-radius: 25px;
            margin-left: 1rem;
        }

        .admin-user i {
            font-size: 1.2rem;
        }

        .logout-link {
            background: var(--danger-color) !important;
            margin-left: 0.5rem;
        }

        .logout-link:hover {
            background: var(--danger-dark) !important;
        }

        /** FIXED: Admin content now starts immediately below navbar **/
        .admin-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1.5rem 1rem;
        }

        /* Mobile Menu */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0.5rem;
        }

        @media (max-width: 768px) {
            .mobile-menu-toggle {
                display: block;
            }
            
            .nav-links {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: rgba(0, 123, 255, 0.98);
                flex-direction: column;
                padding: 1rem;
                box-shadow: var(--shadow);
            }
            
            .nav-links.active {
                display: flex;
            }
            
            .admin-user {
                margin-left: 0;
            }
            
            .admin-content {
                padding: 1rem 0.5rem;
            }
        }

        /* Loading state */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <nav class="admin-navbar">
        <div class="logo">
            <i class="fas fa-shield-check"></i>
            <span>TrueScore Admin</span>
        </div>
        
        <button class="mobile-menu-toggle" onclick="toggleMobileMenu()">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="nav-links" id="navLinks">
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="upload_result.php"><i class="fas fa-upload"></i> Upload Results</a>
            <a href="view_results.php"><i class="fas fa-table"></i> View Results</a>
            <a href="view_history.php"><i class="fas fa-history"></i> Result History</a>
            <a href="verify.php"><i class="fas fa-check-circle"></i> Verify Results</a>
            <a href="logs.php"><i class="fas fa-file-alt"></i> Logs</a>
            <div class="admin-user">
                <i class="fas fa-user"></i>
                <span><?= $admin_name ?></span>
            </div>
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>

    <main class="admin-content">
        <!-- Page content goes here - starts immediately below navbar -->
    </main>

    <script>
        function toggleMobileMenu() {
            const navLinks = document.getElementById('navLinks');
            navLinks.classList.toggle('active');
        }

        // Close mobile menu when clicking a link
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                document.getElementById('navLinks').classList.remove('active');
            });
        });

        // Close mobile menu on outside click
        document.addEventListener('click', (e) => {
            const navbar = document.querySelector('.admin-navbar');
            const navLinks = document.getElementById('navLinks');
            if (!navbar.contains(e.target)) {
                navLinks.classList.remove('active');
            }
        });
    </script>
</body>
</html>