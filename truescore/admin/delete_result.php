<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

$error = '';
$success = '';

if (!isset($_GET['id'])) {
    die("Invalid request.");
}

$result_id = $_GET['id'];
$admin_id = $_SESSION[ADMIN_SESSION]['admin_id'];

// Fetch result
$stmt = $pdo->prepare("SELECT * FROM results WHERE result_id = ?");
$stmt->execute([$result_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Result not found.");
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $pdo->beginTransaction();

        // 1️⃣ Save current result to history BEFORE deletion
        $history_stmt = $pdo->prepare("
            INSERT INTO result_history 
            (result_id, student_id, course_id, score, grade, semester, session, uploaded_by, uploaded_at, result_hash, changed_by, change_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'DELETE')
        ");
        $history_stmt->execute([
            $result['result_id'],
            $result['student_id'],
            $result['course_id'],
            $result['score'],
            $result['grade'],
            $result['semester'],
            $result['session'],
            $result['uploaded_by'],
            $result['uploaded_at'],
            $result['result_hash'],
            $admin_id
        ]);

        // 2️⃣ Delete result from main table
        $delete_stmt = $pdo->prepare("DELETE FROM results WHERE result_id = ?");
        $delete_stmt->execute([$result_id]);

        // 3️⃣ Log admin action
        $action = "Deleted result ID: $result_id";
        $log_stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
        $log_stmt->execute([$admin_id, $action]);

        $pdo->commit();
        $success = "Result deleted successfully!";

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to delete result: " . $e->getMessage();
    }
}
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>Delete Result</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php else: ?>
    <p>Are you sure you want to delete the result for student <strong><?= htmlspecialchars($result['student_id']) ?></strong> in course <strong><?= htmlspecialchars($result['course_id']) ?></strong>?</p>

    <form method="POST">
        <button type="submit">Yes, Delete Result</button>
        <a href="results.php"><button type="button">Cancel</button></a>
    </form>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>