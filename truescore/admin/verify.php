<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';

$verified = null; // null = no check yet
$result_details = [];
$log_notes = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];

    // Fetch the result along with uploaded_at for hash check
    $stmt = $pdo->prepare("
        SELECT r.*, s.full_name AS student_name, c.course_name, c.course_code, a.name AS uploaded_by_name
        FROM results r
        JOIN students s ON r.student_id = s.student_id
        JOIN courses c ON r.course_id = c.course_id
        JOIN admins a ON r.uploaded_by = a.admin_id
        WHERE r.student_id = ? AND r.course_id = ?
    ");
    $stmt->execute([$student_id, $course_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Normalize values
        $score = number_format((float)$row['score'], 2, '.', '');
        $grade = strtoupper(trim($row['grade']));
        $semester = trim($row['semester']);
        $session_acad = trim($row['session']);

        // Rebuild hash
        $data_to_hash =
            $row['student_id'] . '|' .
            $row['course_id'] . '|' .
            $score . '|' .
            $grade . '|' .
            $semester . '|' .
            $session_acad . '|' .
            $row['uploaded_by'] . '|' .
            $row['uploaded_at'];

        $hash_check = hash_hmac('sha256', $data_to_hash, HASH_SECRET_KEY);
        $verified = ($hash_check === $row['result_hash']);
        $result_details = $row;

        // Log the verification attempt
        $log_stmt = $pdo->prepare("
            INSERT INTO verification_logs (student_id, course_id, verified_at, status, notes)
            VALUES (:student_id, :course_id, NOW(), :status, :notes)
        ");
        $status = $verified ? 'original' : 'tampered';
        $log_notes = $verified ? 'Result verified successfully' : 'Result hash mismatch detected';
        $log_stmt->execute([
            ':student_id' => $student_id,
            ':course_id' => $course_id,
            ':status' => $status,
            ':notes' => $log_notes
        ]);

    } else {
        $verified = false;
        $log_notes = 'No result found';
        // Optionally log this too
        $log_stmt = $pdo->prepare("
            INSERT INTO verification_logs (student_id, course_id, verified_at, status, notes)
            VALUES (:student_id, :course_id, NOW(), :status, :notes)
        ");
        $log_stmt->execute([
            ':student_id' => $student_id,
            ':course_id' => $course_id,
            ':status' => 'not_found',
            ':notes' => $log_notes
        ]);
    }
}
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>Verify Student Result</h2>

<form method="POST">
    <label>Student:</label>
    <select name="student_id" required>
        <option value="">--Select Student--</option>
        <?php
        $students = $pdo->query("SELECT student_id, full_name FROM students ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach($students as $student):
        ?>
            <option value="<?= $student['student_id'] ?>" <?= (isset($student_id) && $student_id == $student['student_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($student['full_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Course:</label>
    <select name="course_id" required>
        <option value="">--Select Course--</option>
        <?php
        $courses = $pdo->query("SELECT course_id, course_name, course_code FROM courses ORDER BY course_name ASC")->fetchAll(PDO::FETCH_ASSOC);
        foreach($courses as $course):
        ?>
            <option value="<?= $course['course_id'] ?>" <?= (isset($course_id) && $course_id == $course['course_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">Verify Result</button>
</form>

<?php if ($verified !== null): ?>
    <?php if ($verified): ?>
        <p style="color:green;">✅ Result is ORIGINAL</p>
    <?php else: ?>
        <p style="color:red;">❌ Result has been tampered with or does not exist!</p>
    <?php endif; ?>

    <?php if ($result_details): ?>
        <h3>Result Details:</h3>
        <ul>
            <li>Student: <?= htmlspecialchars($result_details['student_name']) ?></li>
            <li>Course: <?= htmlspecialchars($result_details['course_name'] . ' (' . $result_details['course_code'] . ')') ?></li>
            <li>Score: <?= htmlspecialchars($result_details['score']) ?></li>
            <li>Grade: <?= htmlspecialchars($result_details['grade']) ?></li>
            <li>Semester: <?= htmlspecialchars($result_details['semester']) ?></li>
            <li>Session: <?= htmlspecialchars($result_details['session']) ?></li>
            <li>Uploaded By: <?= htmlspecialchars($result_details['uploaded_by_name']) ?></li>
            <li>Uploaded At: <?= htmlspecialchars($result_details['uploaded_at']) ?></li>
            <li>Verification Note: <?= htmlspecialchars($log_notes) ?></li>
        </ul>
    <?php endif; ?>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>