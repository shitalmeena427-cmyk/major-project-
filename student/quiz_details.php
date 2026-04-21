<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$quiz_id = $_GET['id'] ?? 0;
if (!$quiz_id) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM quizzes WHERE id = ? AND status = 'active'");
$stmt->execute([$quiz_id]);
$quiz = $stmt->fetch();
if (!$quiz) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions_count = $stmt->fetch()['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($quiz['title']); ?> - Quiz Details</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Quiz Details</h1>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php">My History</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="card">
            <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
            <p><strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject']); ?></p>
            <p><strong>Questions:</strong> <?php echo $questions_count; ?></p>
            <?php if (!empty($quiz['difficulty'])): ?>
            <p><strong>Difficulty:</strong> <?php echo htmlspecialchars(ucfirst($quiz['difficulty'])); ?></p>
            <?php endif; ?>
            <?php if (!empty($quiz['duration'])): ?>
            <p><strong>Duration:</strong> <?php echo intval($quiz['duration']); ?> minutes</p>
            <?php endif; ?>
            <?php if (!empty($quiz['description'])): ?>
            <p><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($quiz['description'])); ?></p>
            <?php else: ?>
            <p><strong>Description:</strong> This quiz checks your knowledge on the selected topic and helps you track your progress.</p>
            <?php endif; ?>
            <div class="question-actions">
                <a href="take_quiz.php?id=<?php echo $quiz_id; ?>" class="btn">Start Quiz</a>
                <a href="dashboard.php" class="btn admin-btn">Back to Dashboard</a>
            </div>
        </div>
    </div>
</body>
</html>
