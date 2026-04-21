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
$keyword = trim($_GET['search'] ?? '');

$stmt_subjects = $pdo->prepare("SELECT DISTINCT subject FROM quizzes WHERE status = 'active'");
$stmt_subjects->execute();
$subjects = $stmt_subjects->fetchAll(PDO::FETCH_COLUMN);

$query = "SELECT * FROM quizzes WHERE status = 'active'";
$params = [];
if ($subject_filter) {
    $query .= " AND subject = ?";
    $params[] = $subject_filter;
}
if ($keyword) {
    $query .= " AND (title LIKE ? OR subject LIKE ?)";
    $params[] = "%$keyword%";
    $params[] = "%$keyword%";
}
$query .= " ORDER BY id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quizzes = $stmt->fetchAll();
$available_count = count($quizzes);
$subjects_count = count($subjects);
$latest_quiz = $quizzes[0] ?? null;
$user_level = $_SESSION['level'] ?? 1;
$user_xp = $_SESSION['xp'] ?? 0;
$user_name = htmlspecialchars($_SESSION['name'] ?? 'Student');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Smart Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Welcome back, <?php echo $user_name; ?>!</h1>
        <div class="user-info">
            <span class="badge">Level <?php echo $user_level; ?></span>
            <span class="badge"><?php echo $user_xp; ?> XP</span>
        </div>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <nav>
            <a href="analytics.php">Analytics</a>
            <a href="history.php">My History</a>
            <a href="leaderboard.php">Leaderboard</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Available quizzes</h3>
                <p><?php echo $available_count; ?></p>
            </div>
            <div class="summary-card">
                <h3>Subjects covered</h3>
                <p><?php echo $subjects_count; ?></p>
            </div>
            <div class="summary-card">
                <h3>Latest quiz</h3>
                <p><?php echo $latest_quiz ? htmlspecialchars($latest_quiz['title']) : 'No quizzes'; ?></p>
            </div>
        </div>

        <section class="filter-section">
            <h2>Browse Quizzes</h2>
            <form method="GET" class="filter-form search-panel">
                <input type="search" name="search" class="search-input" placeholder="Search quizzes or subjects..." value="<?php echo htmlspecialchars($keyword); ?>">
                <select name="subject">
                    <option value="">All Subjects</option>
                    <?php foreach ($subjects as $sub): ?>
                    <option value="<?php echo htmlspecialchars($sub); ?>" <?php if ($subject_filter == $sub) echo 'selected'; ?>><?php echo htmlspecialchars($sub); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn">Search</button>
                <?php if ($subject_filter || $keyword): ?>
                <a href="dashboard.php" class="btn admin-btn">Reset</a>
                <?php endif; ?>
            </form>
        </section>

        <section class="quizzes-section">
            <h2>Available Quizzes</h2>
            <div class="dashboard">
                <?php if (empty($quizzes)): ?>
                <div class="card">
                    <div class="icon">📝</div>
                    <h3>No Quizzes Available</h3>
                    <p>Check back later for new challenges and targeted practice.</p>
                </div>
                <?php else: ?>
                <?php foreach ($quizzes as $quiz): ?>
                <div class="card">
                    <div class="icon">📚</div>
                    <h3><?php echo htmlspecialchars($quiz['title']); ?></h3>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject']); ?></p>
                    <p>
                        <?php if (!empty($quiz['difficulty'])): ?>
                        <span class="status-chip"><?php echo htmlspecialchars(ucfirst($quiz['difficulty'])); ?></span>
                        <?php endif; ?>
                        <?php if (!empty($quiz['duration'])): ?>
                        <span class="status-chip"><?php echo htmlspecialchars($quiz['duration']); ?> min</span>
                        <?php endif; ?>
                        <span class="status-chip active">Active</span>
                    </p>
                    <div class="question-actions">
                        <a href="quiz_details.php?id=<?php echo $quiz['id']; ?>" class="btn admin-btn">Details</a>
                        <a href="take_quiz.php?id=<?php echo $quiz['id']; ?>" class="btn">Start Quiz</a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <section class="notifications-section">
            <h2>Recent Notifications</h2>
            <div class="notifications">
                <?php
                $stmt_notif = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
                $stmt_notif->execute([$user_id]);
                $notifications = $stmt_notif->fetchAll();
                if (empty($notifications)): ?>
                <div class="notification">
                    <p>No new notifications</p>
                </div>
                <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="notification <?php echo $notif['read_status'] ? '' : 'unread'; ?>">
                    <p><?php echo htmlspecialchars($notif['message']); ?></p>
                    <small><?php echo date('M d, Y H:i', strtotime($notif['created_at'])); ?></small>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</body>
</html>