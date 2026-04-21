<?php
session_start();
require_once 'models/User.php';
require_once 'includes/db.php';

$userModel = new User($pdo);
$leaderboard = $userModel->getLeaderboard(20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Global Leaderboard - Smart Quiz System</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>🏆 Global Leaderboard</h1>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <nav>
            <a href="index.php">Home</a>
            <a href="student/login.php">Student Login</a>
        </nav>
    </header>
    <div class="container fade-in">
        <div class="card">
            <h3>Top Performers</h3>
            <table>
                <thead>
                    <tr>
                        <th>Rank</th>
                        <th>Student</th>
                        <th>Level</th>
                        <th>XP</th>
                    </tr>
                </thead>
                <tbody id="leaderboard">
                    <?php $rank = 1; foreach ($leaderboard as $user): ?>
                    <tr class="<?php echo ($user['name'] == ($_SESSION['name'] ?? '')) ? 'highlight' : ''; ?>">
                        <td><?php echo $rank++; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo $user['level']; ?></td>
                        <td><?php echo $user['xp']; ?> XP</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>