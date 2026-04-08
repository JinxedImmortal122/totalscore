<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

if (!is_student_logged_in()) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION[STUDENT_SESSION]['student_id'];
$errors = [];
$result = null;

// Fetch all courses for dropdown
$courses = $pdo->query("SELECT course_id, course_code, course_name FROM courses ORDER BY course_code ASC")->fetchAll(PDO::FETCH_ASSOC);

$selected_course_id = $_POST['course_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selected_course_id) {
    // Lookup the result for this student and course
    $stmt = $pdo->prepare("
        SELECT r.*, c.course_code, c.course_name, s.full_name
        FROM results r
        JOIN courses c ON r.course_id = c.course_id
        JOIN students s ON r.student_id = s.student_id
        WHERE r.student_id = ? AND r.course_id = ?
        LIMIT 1
    ");
    $stmt->execute([$student_id, $selected_course_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        $errors[] = "No result found for this course.";
    } else {
        // Verify result hash
        $data_to_hash =
            $result['student_id'] . '|' .
            $result['course_id'] . '|' .
            number_format((float)$result['score'], 2, '.', '') . '|' .
            strtoupper(trim($result['grade'])) . '|' .
            trim($result['semester']) . '|' .
            trim($result['session']) . '|' .
            $result['uploaded_by'] . '|' .
            $result['uploaded_at'];

        $result['hash_valid'] = (hash_hmac('sha256', $data_to_hash, HASH_SECRET_KEY) === $result['result_hash']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Verify Result</title>
<style>
body { font-family: Arial, sans-serif; padding: 30px; background: #f4f4f9; }
.container { max-width: 500px; margin: auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
h2 { text-align: center; color: #333; }
.error { color: #d9534f; margin-bottom: 10px; }
label { display: block; margin-top: 10px; font-weight: bold; }
select { width: 100%; padding: 10px; margin-top: 5px; border-radius: 5px; border: 1px solid #ccc; }
button { margin-top: 15px; width: 100%; padding: 12px; border: none; background: #007bff; color: #fff; font-size: 16px; border-radius: 6px; cursor: pointer; }
button:hover { background: #0056b3; }
.result { margin-top: 20px; padding: 15px; border-radius: 6px; }
.result-original { background: #e6ffed; border-left: 4px solid #28a745; }
.result-tampered { background: #ffe6e6; border-left: 4px solid #dc3545; }
</style>
</head>
<body>

<div class="container">
<h2>Verify Your Result</h2>

<?php foreach ($errors as $err): ?>
    <div class="error"><?= htmlspecialchars($err) ?></div>
<?php endforeach; ?>

<form method="POST">
    <label>Select Course</label>
    <select name="course_id" required>
        <option value="">-- Select a course --</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= $course['course_id'] ?>" <?= ($selected_course_id == $course['course_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($course['course_code'] . ' - ' . $course['course_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Verify Result</button>
</form>

<?php if ($result): ?>
    <div class="result <?= $result['hash_valid'] ? 'result-original' : 'result-tampered' ?>">
        <p><strong>Student:</strong> <?= htmlspecialchars($result['full_name']) ?></p>
        <p><strong>Course:</strong> <?= htmlspecialchars($result['course_code'] . ' - ' . $result['course_name']) ?></p>
        <p><strong>Score:</strong> <?= htmlspecialchars($result['score']) ?></p>
        <p><strong>Grade:</strong> <?= htmlspecialchars($result['grade']) ?></p>
        <p><strong>Semester:</strong> <?= htmlspecialchars($result['semester']) ?></p>
        <p><strong>Session:</strong> <?= htmlspecialchars($result['session']) ?></p>
        <p><strong>Status:</strong>
            <?= $result['hash_valid'] ? '✅ Original / Verified' : '❌ Tampered / Invalid' ?>
        </p>
    </div>
<?php endif; ?>

</div>
</body>
</html>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>