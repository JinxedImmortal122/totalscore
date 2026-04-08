<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

// Fetch all results with student and course names
$query = "
    SELECT 
        r.result_id,
        s.full_name AS student_name,
        c.course_name AS course_name,
        c.course_code AS course_code,
        r.score,
        r.grade,
        r.semester,
        r.session,
        r.uploaded_at,
        a.name AS uploaded_by_name
    FROM results r
    JOIN students s ON r.student_id = s.student_id
    JOIN courses c ON r.course_id = c.course_id
    JOIN admins a ON r.uploaded_by = a.admin_id
    ORDER BY r.uploaded_at DESC
";

$results = $pdo->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>All Student Results</h2>

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>Student</th>
            <th>Course</th>
            <th>Score</th>
            <th>Grade</th>
            <th>Semester</th>
            <th>Session</th>
            <th>Uploaded By</th>
            <th>Uploaded At</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($results): ?>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['student_name']) ?></td>
                    <td><?= htmlspecialchars($row['course_name'] . ' (' . $row['course_code'] . ')') ?></td>
                    <td><?= htmlspecialchars($row['score']) ?></td>
                    <td><?= htmlspecialchars($row['grade']) ?></td>
                    <td><?= htmlspecialchars($row['semester']) ?></td>
                    <td><?= htmlspecialchars($row['session']) ?></td>
                    <td><?= htmlspecialchars($row['uploaded_by_name']) ?></td>
                    <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
                    <td>
                        <a href="edit_result.php?id=<?= $row['result_id'] ?>">Edit</a> |
                        <a href="delete_result.php?id=<?= $row['result_id'] ?>" onclick="return confirm('Are you sure you want to delete this result?');">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="9">No results found.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>