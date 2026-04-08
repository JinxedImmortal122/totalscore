<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

if (!is_student_logged_in()) {
    header("Location: login.php");
    exit;
}

function e($v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$student_id = $_SESSION[STUDENT_SESSION]['student_id'];

// Fetch student results
try {
    $stmt = $pdo->prepare("
        SELECT r.*, c.course_name, c.course_code
        FROM results r
        JOIN courses c ON r.course_id = c.course_id
        WHERE r.student_id = :student_id
        ORDER BY r.semester, r.session
    ");
    $stmt->execute([':student_id' => $student_id]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    error_log($e->getMessage());
    $results = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Results — TrueScore</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Jost', sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background: #007bff;
            color: #fff;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        .back-link {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #007bff;
            font-weight: 500;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>My Results</h1>

    <?php if ($results): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Course</th>
                    <th>Course Code</th>
                    <th>Score</th>
                    <th>Grade</th>
                    <th>Semester</th>
                    <th>Session</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($results as $index => $row): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= e($row['course_name']) ?></td>
                        <td><?= e($row['course_code']) ?></td>
                        <td><?= e($row['score']) ?></td>
                        <td><?= e($row['grade']) ?></td>
                        <td><?= e($row['semester']) ?></td>
                        <td><?= e($row['session']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="no-results">No results available yet.</div>
    <?php endif; ?>

    <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
</div>

</body>
</html>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>