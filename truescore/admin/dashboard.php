<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';  // Use your existing database.php

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

// CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Safely extract admin data
$admin_name = isset($_SESSION[ADMIN_SESSION]['name']) ? htmlspecialchars($_SESSION[ADMIN_SESSION]['name']) : 'Admin';

// Use your existing global $pdo from database.php
global $pdo;

// Initialize stats with safe defaults
$total_students = 0;
$total_results = 0;
$logs = 0;

if (isset($pdo)) {
    try {
        // Total results
        $stmt = $pdo->query("SELECT COUNT(*) FROM results");
        $total_results = $stmt ? $stmt->fetchColumn() : 0;
        
        // Total logs
        $stmt = $pdo->query("SELECT COUNT(*) FROM verification_logs");
        $logs = $stmt ? $stmt->fetchColumn() : 0;
        
        // Total students
        $stmt = $pdo->query("SELECT COUNT(*) FROM students");
        $total_students = $stmt ? $stmt->fetchColumn() : 0;

    } catch (Exception $e) {
        error_log("Dashboard stats error: " . $e->getMessage());
    }
}
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<div class="admin-dashboard">
    <header class="dashboard-header">
        <h1>Welcome, <?= $admin_name ?>!</h1>
        <p>Manage results, view logs, and verify students efficiently.</p>
        <?php if (!isset($pdo) || !$pdo): ?>
            <div class="alert alert-warning">
                <strong>Stats unavailable:</strong> Check if database.php is loading correctly.
            </div>
        <?php endif; ?>
    </header>

    <main class="dashboard-main">
        <!-- Quick Stats Widgets (3 cards only) -->
        <section class="stats-grid">
            <div class="stat-card total-students">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3><?= number_format($total_students) ?></h3>
                    <p>Total Students</p>
                </div>
            </div>

            <div class="stat-card total-results">
                <div class="stat-icon">📊</div>
                <div class="stat-content">
                    <h3><?= number_format($total_results) ?></h3>
                    <p>Total Results</p>
                </div>
            </div>

            <div class="stat-card logs">
                <div class="stat-icon">📋</div>
                <div class="stat-content">
                    <h3><?= number_format($logs) ?></h3>
                    <p>Logs</p>
                </div>
            </div>
        </section>

        <div class="dashboard-grid">
            <article class="card">
                <h2>Upload Results</h2>
                <p>Add new student results to the system.</p>
                <a href="upload_result.php" class="btn btn-primary">Go</a>
            </article>

            <article class="card">
                <h2>View Results</h2>
                <p>Check existing student results.</p>
                <a href="view_results.php" class="btn btn-primary">Go</a>
            </article>

            <article class="card">
                <h2>Verify Result History</h2>
                <p>Review result verification history.</p>
                <a href="view_history.php" class="btn btn-primary">Go</a>
            </article>

            <article class="card">
                <h2>Verify Results</h2>
                <p>Verify existing student results.</p>
                <a href="verify.php" class="btn btn-primary">Go</a>
            </article>

            <article class="card">
                <h2>Verification Logs</h2>
                <p>Review all fraud detection activities.</p>
                <a href="logs.php" class="btn btn-primary">Go</a>
            </article>
        </div>

        <div class="logout-section">
            <form method="POST" action="logout.php" style="display: inline;">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to logout?')">Logout</button>
            </form>
        </div>
    </main>
</div>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>

<style>
:root {
    --primary-color: #007bff;
    --success-color: #28a745;
    --warning-color: #ffc107;
    --danger-color: #dc3545;
    --info-color: #17a2b8;
    --card-bg: #f8f9fa;
    --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    --transition: all 0.3s ease;
}

.admin-dashboard {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.alert {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    color: #856404;
    padding: 0.75rem 1rem;
    border-radius: 8px;
    margin: 1rem 0;
}

.dashboard-header {
    text-align: center;
    margin-bottom: 2rem;
}

.dashboard-header h1 {
    font-size: 2.5rem;
    margin-bottom: 0.5rem;
    color: #333;
}

.dashboard-header p {
    font-size: 1.2rem;
    color: #666;
}

.dashboard-main {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
    width: 100%;
    margin-bottom: 3rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 12px;
    box-shadow: var(--shadow);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: var(--transition);
    border-left: 4px solid;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
}

.stat-icon {
    font-size: 2.5rem;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    flex-shrink: 0;
}

.total-students { border-left-color: var(--primary-color); }
.total-students .stat-icon { background: rgba(0, 123, 255, 0.1); }

.total-results { border-left-color: var(--info-color); }
.total-results .stat-icon { background: rgba(23, 162, 184, 0.1); }

.logs { border-left-color: #6c757d; }
.logs .stat-icon { background: rgba(108, 117, 125, 0.1); }

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin: 0 0 0.25rem 0;
    color: #333;
}

.stat-content p {
    margin: 0;
    color: #666;
    font-size: 0.95rem;
}

.dashboard-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    width: 100%;
    margin-bottom: 3rem;
}

.card {
    background: var(--card-bg);
    padding: 2rem;
    border-radius: 12px;
    box-shadow: var(--shadow);
    transition: var(--transition);
    text-align: center;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.card h2 {
    margin: 0 0 1rem 0;
    color: #333;
    font-size: 1.4rem;
}

.card p {
    color: #666;
    margin-bottom: 1.5rem;
    line-height: 1.5;
}

.btn {
    display: inline-block;
    padding: 0.75rem 1.5rem;
    color: white;
    text-decoration: none;
    border-radius: 8px;
    font-weight: 500;
    transition: var(--transition);
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: var(--primary-color);
}

.btn-primary:hover {
    background: #0056b3;
    transform: translateY(-2px);
}

.btn-danger {
    background: var(--danger-color);
}

.btn-danger:hover {
    background: #c82333;
    transform: translateY(-2px);
}

.logout-section {
    text-align: center;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
    }
    
    .dashboard-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    
    .dashboard-header h1 {
        font-size: 2rem;
    }
    
    .stat-card {
        padding: 1rem;
        gap: 0.75rem;
    }
    
    .stat-content h3 {
        font-size: 1.5rem;
    }
}

@media (max-width: 480px) {
    .admin-dashboard {
        margin: 1rem auto;
        padding: 0 0.5rem;
    }
    
    .card {
        padding: 1.5rem;
    }
}
</style>