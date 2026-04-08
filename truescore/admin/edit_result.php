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

// Handle update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $score = trim($_POST['score']);
    $grade = trim($_POST['grade']);
    $semester = trim($_POST['semester']);
    $session_acad = trim($_POST['session']);

    if ($score === '' || !$grade || !$semester || !$session_acad) {
        $error = "All fields are required.";
    } else {

        // ✅ Normalize values
        $score = number_format((float)$score, 2, '.', '');
        $grade = strtoupper(trim($grade));
        $semester = trim($semester);
        $session_acad = trim($session_acad);

        // Keep original immutable values
        $student_id = $result['student_id'];
        $course_id = $result['course_id'];
        $uploaded_at = $result['uploaded_at'];

        // ✅ Save current result to history BEFORE updating
        $history_stmt = $pdo->prepare("
            INSERT INTO result_history 
            (result_id, student_id, course_id, score, grade, semester, session, uploaded_by, uploaded_at, result_hash, changed_by, change_type)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'UPDATE')
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

        // ✅ Generate new hash using SAME rules
        $data_to_hash =
            $student_id . '|' .
            $course_id . '|' .
            $score . '|' .
            $grade . '|' .
            $semester . '|' .
            $session_acad . '|' .
            $admin_id . '|' .
            $uploaded_at;

        $new_hash = hash_hmac('sha256', $data_to_hash, HASH_SECRET_KEY);

        // ✅ Update result
        $update = $pdo->prepare("
            UPDATE results 
            SET score = ?, grade = ?, semester = ?, session = ?, uploaded_by = ?, result_hash = ?
            WHERE result_id = ?
        ");

        $updated = $update->execute([
            $score,
            $grade,
            $semester,
            $session_acad,
            $admin_id,
            $new_hash,
            $result_id
        ]);

        if ($updated) {

            // ✅ Log action
            $action = "Edited result ID: $result_id";
            $log = $pdo->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
            $log->execute([$admin_id, $action]);

            $success = "Result updated successfully!";

            // Refresh data
            $stmt->execute([$result_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

        } else {
            $error = "Failed to update result.";
        }
    }
}
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>Edit Result</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST">

    <p><strong>Student ID:</strong> <?= $result['student_id'] ?></p>
    <p><strong>Course ID:</strong> <?= $result['course_id'] ?></p>

    <label>Score:</label>
    <input type="number" name="score" step="0.01" value="<?= htmlspecialchars($result['score']) ?>" required>

    <label>Grade:</label>
    <input type="text" name="grade" maxlength="2" value="<?= htmlspecialchars($result['grade']) ?>" required>

    <label>Semester:</label>
    <select name="semester" required>
        <option value="First" <?= $result['semester'] == 'First' ? 'selected' : '' ?>>First</option>
        <option value="Second" <?= $result['semester'] == 'Second' ? 'selected' : '' ?>>Second</option>
    </select>

    <label>Session:</label>
    <input type="text" name="session" value="<?= htmlspecialchars($result['session']) ?>" required>

    <button type="submit">Update Result</button>
</form>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>