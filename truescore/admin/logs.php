<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';

if (!is_admin_logged_in()) {
    header("Location: login.php");
    exit;
}

// ===== ADMIN LOGS =====
$admin_logs_stmt = $pdo->prepare("
    SELECT al.*, a.name AS admin_name
    FROM admin_logs al
    JOIN admins a ON al.admin_id = a.admin_id
    ORDER BY al.action_time DESC
");
$admin_logs_stmt->execute();
$admin_logs = $admin_logs_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include __DIR__ . '/../includes/admin/header.php'; ?>

<h2>Admin Activity Logs</h2>

<?php if ($admin_logs): ?>
<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th>#</th>
            <th>Admin</th>
            <th>Action</th>
            <th>Action Time</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($admin_logs as $index => $row): ?>
        <tr>
            <td><?= $index + 1 ?></td>
            <td><?= htmlspecialchars($row['admin_name']) ?></td>
            <td><?= htmlspecialchars($row['action']) ?></td>
            <td><?= htmlspecialchars($row['action_time']) ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p>No admin activity logs found.</p>
<?php endif; ?>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>