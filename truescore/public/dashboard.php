<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/header.php';

if (!is_student_logged_in()) {
    header("Location: login.php");
    exit;
}

// Get student info from session
$student = $_SESSION[STUDENT_SESSION];
$student_id = $student['student_id'];
$student_name = $student['name'];

// Fetch the student's results
$stmt = $pdo->prepare("
    SELECT r.*, c.course_name, c.course_code
    FROM results r
    JOIN courses c ON r.course_id = c.course_id
    WHERE r.student_id = ?
    ORDER BY c.course_name ASC
");
$stmt->execute([$student_id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            margin: 0;
            padding: 0;
        }

        header {
            background: #007bff;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .container {
            max-width: 900px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 12px;
            text-align: left;
        }

        th {
            background: #007bff;
            color: #fff;
        }

        tr:nth-child(even) {
            background: #f9f9f9;
        }

        a {
            color: #007bff;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .logout-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: #dc3545;
            color: #fff;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s;
        }

        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>

<header>
    <h1>Welcome, <?= htmlspecialchars($student_name) ?></h1>
</header>

<div class="container">
    <h2>Your Results</h2>

    <?php if ($results): ?>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Course Name</th>
                <th>Course Code</th>
                <th>Score</th>
                <th>Grade</th>
                <th>Semester</th>
                <th>Session</th>
                <th>View Details</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $index => $row): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($row['course_name']) ?></td>
                <td><?= htmlspecialchars($row['course_code']) ?></td>
                <td><?= htmlspecialchars($row['score']) ?></td>
                <td><?= htmlspecialchars($row['grade']) ?></td>
                <td><?= htmlspecialchars($row['semester']) ?></td>
                <td><?= htmlspecialchars($row['session']) ?></td>
                <td><a href="view_result.php?course_id=<?= $row['course_id'] ?>">View Result</a></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php else: ?>
        <p>You currently have no results available.</p>
    <?php endif; ?>

    <a class="logout-btn" href="logout.php">Logout</a>
</div>

</body>
</html>

<?php include __DIR__ . '/../includes/admin/footer.php'; ?>