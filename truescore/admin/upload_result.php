<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id']);
    $course_id = trim($_POST['course_id']);
    $score = trim($_POST['score']);
    $grade = trim($_POST['grade']);
    $semester = trim($_POST['semester']);
    $session_acad = trim($_POST['session']);

    $admin_id = $_SESSION[ADMIN_SESSION]['admin_id'];

    // Validation
    if (!$student_id || !$course_id || $score === '' || !$grade || !$semester || !$session_acad) {
        $error = "All fields are required.";
    } else {

        // STEP 1: Insert WITHOUT uploaded_at and result_hash
        $stmt = $pdo->prepare("
            INSERT INTO results (student_id, course_id, score, grade, semester, session, uploaded_by)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $inserted = $stmt->execute([
            $student_id,
            $course_id,
            $score,
            $grade,
            $semester,
            $session_acad,
            $admin_id
        ]);

        if ($inserted) {

            // STEP 2: Get inserted result ID
            $result_id = $pdo->lastInsertId();

            // STEP 3: Fetch EXACT uploaded_at from DB
            $stmt2 = $pdo->prepare("SELECT uploaded_at FROM results WHERE result_id = ?");
            $stmt2->execute([$result_id]);
            $uploaded_at = $stmt2->fetchColumn();

            // ✅ STEP 4: NORMALIZE VALUES BEFORE HASHING
            $score = number_format((float)$score, 2, '.', '');
            $grade = strtoupper(trim($grade));
            $semester = trim($semester);
            $session_acad = trim($session_acad);

            // Generate hash (NOW CORRECT)
            $data_to_hash = 
                $student_id . '|' .
                $course_id . '|' .
                $score . '|' .
                $grade . '|' .
                $semester . '|' .
                $session_acad . '|' .
                $admin_id . '|' .
                $uploaded_at;

            $result_hash = hash_hmac('sha256', $data_to_hash, HASH_SECRET_KEY);

            // STEP 5: Update the record with hash
            $stmt3 = $pdo->prepare("UPDATE results SET result_hash = ? WHERE result_id = ?");
            $stmt3->execute([$result_hash, $result_id]);

            // STEP 6: Log admin action
            $action = "Uploaded result for student_id: $student_id, course_id: $course_id";
            $log_stmt = $pdo->prepare("INSERT INTO admin_logs (admin_id, action) VALUES (?, ?)");
            $log_stmt->execute([$admin_id, $action]);

            $success = "Result uploaded successfully!";
        } else {
            $error = "Failed to upload result. Try again.";
        }
    }
}

// Fetch students and courses
$students = $pdo->query("SELECT student_id, full_name FROM students ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT course_id, course_name, course_code FROM courses ORDER BY course_name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>Upload Student Result</h2>

<?php if ($error): ?>
    <p style="color:red"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST">
    <label>Student:</label>
    <select name="student_id" required>
        <option value="">--Select Student--</option>
        <?php foreach($students as $student): ?>
            <option value="<?= $student['student_id'] ?>">
                <?= htmlspecialchars($student['full_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Course:</label>
    <select name="course_id" required>
        <option value="">--Select Course--</option>
        <?php foreach($courses as $course): ?>
            <option value="<?= $course['course_id'] ?>">
                <?= htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Score:</label>
    <input type="number" name="score" min="0" max="100" step="0.01" required>

    <label>Grade:</label>
    <input type="text" name="grade" maxlength="2" required placeholder="A, B+, C, etc.">

    <label>Semester:</label>
    <select name="semester" required>
        <option value="">--Select Semester--</option>
        <option value="First">First</option>
        <option value="Second">Second</option>
    </select>

    <label>Session:</label>
    <input type="text" name="session" placeholder="e.g., 2025/2026" required>

    <button type="submit">Upload Result</button>
</form>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>