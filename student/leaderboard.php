<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get leaderboard
$stmt = $pdo->prepare("SELECT u.name, AVG(r.percentage) as avg_score FROM users u JOIN results r ON u.id = r.user_id WHERE u.role = 'student' GROUP BY u.id ORDER BY avg_score DESC LIMIT 10");
$stmt->execute();
$leaderboard = $stmt->fetchAll();

// Get current user rank
$stmt_rank = $pdo->prepare("SELECT COUNT(*) + 1 as rank FROM (SELECT u.id, AVG(r.percentage) as avg_score FROM users u JOIN results r ON u.id = r.user_id WHERE u.role = 'student' GROUP BY u.id HAVING avg_score > (SELECT AVG(percentage) FROM results WHERE user_id = ?)) as temp");
$stmt_rank->execute([$user_id]);
$rank = $stmt_rank->fetch()['rank'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - Smart Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Leaderboard</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="analytics.php">Analytics</a>
            <a href="history.php">History</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Your Rank</h3>
                <p>#<?php echo $rank; ?></p>
            </div>
            <div class="summary-card">
                <h3>Top Score</h3>
                <p><?php echo number_format($leaderboard[0]['avg_score'] ?? 0, 2); ?>%</p>
            </div>
            <div class="summary-card">
                <h3>Leaderboard</h3>
                <p><?php echo count($leaderboard); ?> entries</p>
            </div>
        </div>

        <section class="history-section">
            <h2>Top 10 Students</h2>
            <div class="table-container">
                <table class="subjects-table">
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Average Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($leaderboard as $lb): ?>
                        <tr class="<?php echo $lb['name'] == $_SESSION['name'] ? 'highlight' : ''; ?>">
                            <td><?php echo $i++; ?></td>
                            <td><?php echo htmlspecialchars($lb['name']); ?></td>
                            <td><?php echo number_format($lb['avg_score'], 2); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="recommendations-section">
            <h2>Leaderboard Insights</h2>
            <div class="recommendations-grid">
                <div class="recommendation-card">
                    <div class="recommendation-icon">🚀</div>
                    <h4>Stay Consistent</h4>
                    <p>Practice regularly to move up the leaderboard and keep your learning momentum strong.</p>
                </div>
                <div class="recommendation-card">
                    <div class="recommendation-icon">📈</div>
                    <h4>Target Growth</h4>
                    <p>Use your dashboard analytics to sharpen weak subjects and increase your average score.</p>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
