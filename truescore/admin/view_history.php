<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

$student_id = $_GET['student_id'] ?? '';
$course_id = $_GET['course_id'] ?? '';

// Fetch students and courses for filter dropdown
$students = $pdo->query("SELECT student_id, full_name FROM students ORDER BY full_name ASC")->fetchAll(PDO::FETCH_ASSOC);
$courses = $pdo->query("SELECT course_id, course_name, course_code FROM courses ORDER BY course_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$history = [];

if ($student_id && $course_id) {
    // Fetch history first
    $stmt = $pdo->prepare("
        SELECT rh.*, a.name AS changed_by_name, c.course_name, c.course_code, s.full_name AS student_name
        FROM result_history rh
        JOIN admins a ON rh.changed_by = a.admin_id
        JOIN courses c ON rh.course_id = c.course_id
        JOIN students s ON rh.student_id = s.student_id
        WHERE rh.student_id = ? AND rh.course_id = ?
        ORDER BY rh.changed_at DESC
    ");
    $stmt->execute([$student_id, $course_id]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verify history hashes
    foreach ($history as &$row) {
        $score = number_format((float)$row['score'], 2, '.', '');
        $grade = strtoupper(trim($row['grade']));
        $semester = trim($row['semester']);
        $session_acad = trim($row['session']);

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
        $row['hash_valid'] = ($hash_check === $row['result_hash']);
    }
    unset($row);

    // ✅ Fetch deleted results from history
    $deleted_stmt = $pdo->prepare("
        SELECT rh.*, a.name AS changed_by_name, c.course_name, c.course_code, s.full_name AS student_name
        FROM result_history rh
        JOIN admins a ON rh.changed_by = a.admin_id
        JOIN courses c ON rh.course_id = c.course_id
        JOIN students s ON rh.student_id = s.student_id
        WHERE rh.student_id = ? AND rh.course_id = ? AND rh.change_type = 'DELETE'
        ORDER BY rh.changed_at DESC
    ");
    $deleted_stmt->execute([$student_id, $course_id]);
    $deleted_history = $deleted_stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($deleted_history as &$row) {
        $score = number_format((float)$row['score'], 2, '.', '');
        $grade = strtoupper(trim($row['grade']));
        $semester = trim($row['semester']);
        $session_acad = trim($row['session']);

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
        $row['hash_valid'] = ($hash_check === $row['result_hash']);
    }
    unset($row);

    // Merge deleted entries at the top of $history
    $history = array_merge($deleted_history, $history);

    // ✅ Fetch current live result from results table
    $current_stmt = $pdo->prepare("
        SELECT r.*, a.name AS uploaded_by_name, c.course_name, c.course_code, s.full_name AS student_name
        FROM results r
        JOIN admins a ON r.uploaded_by = a.admin_id
        JOIN courses c ON r.course_id = c.course_id
        JOIN students s ON r.student_id = s.student_id
        WHERE r.student_id = ? AND r.course_id = ?
    ");
    $current_stmt->execute([$student_id, $course_id]);
    $current_result = $current_stmt->fetch(PDO::FETCH_ASSOC);

    if ($current_result) {
        $score = number_format((float)$current_result['score'], 2, '.', '');
        $grade = strtoupper(trim($current_result['grade']));
        $semester = trim($current_result['semester']);
        $session_acad = trim($current_result['session']);

        $data_to_hash =
            $current_result['student_id'] . '|' .
            $current_result['course_id'] . '|' .
            $score . '|' .
            $grade . '|' .
            $semester . '|' .
            $session_acad . '|' .
            $current_result['uploaded_by'] . '|' .
            $current_result['uploaded_at'];

        $hash_check = hash_hmac('sha256', $data_to_hash, HASH_SECRET_KEY);

        array_unshift($history, [
            'score' => $current_result['score'],
            'grade' => $current_result['grade'],
            'semester' => $current_result['semester'],
            'session' => $current_result['session'],
            'uploaded_by' => $current_result['uploaded_by'],
            'uploaded_at' => $current_result['uploaded_at'],
            'changed_by_name' => '(Current record)',
            'change_type' => 'CURRENT',
            'changed_at' => $current_result['uploaded_at'],
            'result_hash' => $current_result['result_hash'],
            'student_name' => $current_result['student_name'],
            'course_name' => $current_result['course_name'],
            'course_code' => $current_result['course_code'],
            'hash_valid' => ($hash_check === $current_result['result_hash'])
        ]);
    }
}
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>View Result History</h2>

<form method="GET">
    <label>Student:</label>
    <select name="student_id" required>
        <option value="">--Select Student--</option>
        <?php foreach ($students as $student): ?>
            <option value="<?= $student['student_id'] ?>" <?= ($student_id == $student['student_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($student['full_name']) ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label>Course:</label>
    <select name="course_id" required>
        <option value="">--Select Course--</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= $course['course_id'] ?>" <?= ($course_id == $course['course_id']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($course['course_name'] . ' (' . $course['course_code'] . ')') ?>
            </option>
        <?php endforeach; ?>
    </select>

    <button type="submit">View History</button>
</form>

<?php if ($history): ?>
    <h3>Result History for <?= htmlspecialchars($history[0]['student_name']) ?> - <?= htmlspecialchars($history[0]['course_name'] . ' (' . $history[0]['course_code'] . ')') ?></h3>

    <table border="1" cellpadding="5" cellspacing="0">
        <thead>
            <tr>
                <th>#</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Semester</th>
                <th>Session</th>
                <th>Uploaded By</th>
                <th>Original Upload Time</th>
                <th>Change Made By</th>
                <th>Change Type</th>
                <th>Changed At</th>
                <th>Result Hash</th>
                <th>Hash Valid?</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($history as $index => $row): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($row['score']) ?></td>
                    <td><?= htmlspecialchars($row['grade']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['session']) ?></td>
                    <td><?= htmlspecialchars($row['uploaded_by']) ?></td>
                    <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                    <td><?= htmlspecialchars($row['changed_by_name']) ?></td>
                    <td><?= htmlspecialchars($row['change_type']) ?></td>
                    <td><?= htmlspecialchars($row['changed_at']) ?></td>
                    <td><?= htmlspecialchars($row['result_hash']) ?></td>
                    <td style="color:<?= $row['hash_valid'] ? 'green' : 'red' ?>;">
                        <?= $row['hash_valid'] ? '✅' : '❌' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif ($student_id && $course_id): ?>
    <p>No history found for this result.</p>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>