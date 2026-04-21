<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$subject_filter = $_GET['subject'] ?? '';
$search = trim($_GET['search'] ?? '');
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';

$stmt_subjects = $pdo->prepare("SELECT DISTINCT q.subject FROM results r JOIN quizzes q ON r.quiz_id = q.id WHERE r.user_id = ?");
$stmt_subjects->execute([$user_id]);
$subjects = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);

$baseQuery = "FROM results r JOIN quizzes q ON r.quiz_id = q.id WHERE r.user_id = ?";
$params = [$user_id];

if ($subject_filter) {
    $baseQuery .= " AND q.subject = ?";
    $params[] = $subject_filter;
}
if ($search) {
    $baseQuery .= " AND (q.title LIKE ? OR q.subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($start_date) {
    $baseQuery .= " AND r.attempt_date >= ?";
    $params[] = $start_date . ' 00:00:00';
}
if ($end_date) {
    $baseQuery .= " AND r.attempt_date <= ?";
    $params[] = $end_date . ' 23:59:59';
}

$historyQuery = "SELECT q.id AS quiz_id, q.title, q.subject, r.score, r.percentage, r.attempt_date " . $baseQuery . " ORDER BY r.attempt_date DESC";
$stmt = $pdo->prepare($historyQuery);
$stmt->execute($params);
$history = $stmt->fetchAll();

$statsQuery = "SELECT COUNT(*) AS total_attempts, COALESCE(AVG(r.percentage), 0) AS avg_percentage, COALESCE(MAX(r.percentage), 0) AS best_percentage " . $baseQuery;
$stmt_stats = $pdo->prepare($statsQuery);
$stmt_stats->execute($params);
$stats = $stmt_stats->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History - Smart Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>My Quiz History</h1>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="analytics.php">Analytics</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Total attempts</h3>
                <p><?php echo $stats['total_attempts']; ?></p>
            </div>
            <div class="summary-card">
                <h3>Average score</h3>
                <p><?php echo number_format($stats['avg_percentage'], 2); ?>%</p>
            </div>
            <div class="summary-card">
                <h3>Best attempt</h3>
                <p><?php echo number_format($stats['best_percentage'], 2); ?>%</p>
            </div>
        </div>

        <section class="filter-section">
            <h2>Filter Results</h2>
            <form method="GET" class="filter-form search-panel">
                <input type="search" name="search" class="search-input" placeholder="Search by quiz or subject..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="subject">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $sub): ?>
                    <option value="<?php echo htmlspecialchars($sub); ?>" <?php if ($subject_filter == $sub) echo 'selected'; ?>><?php echo htmlspecialchars($sub); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
                <input type="date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
                <button type="submit" class="btn">Apply</button>
                <?php if ($subject_filter || $search || $start_date || $end_date): ?>
                <a href="history.php" class="btn admin-btn">Reset</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="history-section">
            <h2>Quiz Attempts</h2>
            <?php if (empty($history)): ?>
            <div class="empty-state">
                <div class="icon">📝</div>
                <h3>No quiz attempts yet</h3>
                <p>Take your first quiz to see your history here!</p>
                <a href="dashboard.php" class="btn">Go to Dashboard</a>
            </div>
            <?php else: ?>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Quiz Title</th>
                            <th>Score</th>
                            <th>Percentage</th>
                            <th>Attempt Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($history as $h): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($h['title']); ?></td>
                            <td><?php echo $h['score']; ?></td>
                            <td><?php echo number_format($h['percentage'], 2); ?>%</td>
                            <td><?php echo date('M d, Y H:i', strtotime($h['attempt_date'])); ?></td>
                            <td><a href="result.php?quiz_id=<?php echo $h['quiz_id']; ?>" class="btn admin-btn">View Result</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </section>
    </div>
</body>
</html>