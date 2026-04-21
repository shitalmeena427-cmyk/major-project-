<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

$search = trim($_GET['search'] ?? '');
$where = "WHERE role = 'student'";
$params = [];
if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR level LIKE ? OR xp LIKE ? )";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$stmt = $pdo->prepare("SELECT id, name, email, level, xp, last_login FROM users " . $where . " ORDER BY name ASC");
$stmt->execute($params);
$students = $stmt->fetchAll();

$stmt = $pdo->prepare("SELECT COUNT(*) as total_students, COALESCE(AVG(level), 0) as avg_level, COALESCE(AVG(xp), 0) as avg_xp FROM users WHERE role = 'student'");
$stmt->execute();
$stats = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Manage Students</h1>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_quizzes.php">Manage Quizzes</a>
            <a href="create_quiz.php">Create Quiz</a>
            <a href="view_results.php">View Results</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total Students</h3>
                <p><?php echo $stats['total_students']; ?></p>
            </div>
            <div class="summary-card">
                <h3>Average Level</h3>
                <p><?php echo number_format($stats['avg_level'], 2); ?></p>
            </div>
            <div class="summary-card">
                <h3>Average XP</h3>
                <p><?php echo number_format($stats['avg_xp'], 2); ?></p>
            </div>
        </div>

        <div class="admin-actions">
            <form method="GET">
                <input type="search" name="search" class="search-input" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn">Search</button>
                <?php if ($search): ?>
                <a href="manage_students.php" class="btn admin-btn">Reset</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="table-container">
            <table class="student-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Level</th>
                        <th>XP</th>
                        <th>Last Login</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($student['name']); ?></td>
                        <td><?php echo htmlspecialchars($student['email']); ?></td>
                        <td><?php echo htmlspecialchars($student['level']); ?></td>
                        <td><?php echo htmlspecialchars($student['xp']); ?></td>
                        <td><?php echo !empty($student['last_login']) ? date('M d, Y H:i', strtotime($student['last_login'])) : 'Never'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
