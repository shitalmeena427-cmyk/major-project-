<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

$stmt = $pdo->query("SELECT r.id, u.name, q.title, r.score, r.percentage, r.attempt_date FROM results r JOIN users u ON r.user_id = u.id JOIN quizzes q ON r.quiz_id = q.id ORDER BY r.attempt_date DESC");
$results = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Results - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>All Student Results</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <table>
            <tr>
                <th>Student Name</th>
                <th>Quiz Title</th>
                <th>Score</th>
                <th>Percentage</th>
                <th>Attempt Date</th>
            </tr>
            <?php foreach ($results as $result): ?>
            <tr>
                <td><?php echo htmlspecialchars($result['name']); ?></td>
                <td><?php echo htmlspecialchars($result['title']); ?></td>
                <td><?php echo $result['score']; ?></td>
                <td><?php echo $result['percentage']; ?>%</td>
                <td><?php echo $result['attempt_date']; ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>