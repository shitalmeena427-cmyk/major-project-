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

$stmt_quiz = $pdo->prepare("SELECT * FROM quizzes WHERE id = ?");
$stmt_quiz->execute([$quiz_id]);
$quiz = $stmt_quiz->fetch();
if (!$quiz) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

if (empty($questions)) {
    echo "No questions in this quiz.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Quiz - Smart Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Exam Mode</h1>
        <div id="timer" class="timer"><?php echo !empty($quiz['duration']) ? intval($quiz['duration']) . ':00' : '05:00'; ?></div>
    </header>
    <div class="container">
        <form method="POST" action="process_quiz.php" id="quizForm">
            <input type="hidden" name="quiz_id" value="<?php echo $quiz_id; ?>">
            <div class="card">
                <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
                <p><strong>Subject:</strong> <?php echo htmlspecialchars($quiz['subject']); ?></p>
                <p><strong>Questions:</strong> <?php echo count($questions); ?></p>
                <?php if (!empty($quiz['difficulty'])): ?>
                <p><strong>Difficulty:</strong> <?php echo htmlspecialchars(ucfirst($quiz['difficulty'])); ?></p>
                <?php endif; ?>
                <?php if (!empty($quiz['duration'])): ?>
                <p><strong>Time allotted:</strong> <?php echo intval($quiz['duration']); ?> minutes</p>
                <?php endif; ?>
            </div>

            <div class="exam-shell">
                <aside class="question-sidebar">
                    <h3>Question Navigator</h3>
                    <div class="question-steps">
                        <?php foreach ($questions as $index => $q): ?>
                        <button type="button" class="question-step" data-question-index="<?php echo $index; ?>"><?php echo $index + 1; ?></button>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="reviewToggle" class="btn review-btn">Mark for review</button>
                    <div class="review-info" id="reviewInfo">Not marked</div>
                </aside>
                <main>
                    <?php foreach ($questions as $index => $q): ?>
                    <section class="question-card" data-index="<?php echo $index; ?>" data-review="false">
                        <div class="question-header">
                            <h3>Question <?php echo $index + 1; ?></h3>
                            <span class="status-chip">Question <?php echo $index + 1; ?></span>
                        </div>
                        <p><?php echo htmlspecialchars($q['question']); ?></p>
                        <div class="options">
                            <label><input type="radio" name="q<?php echo $q['id']; ?>" value="1" required> <?php echo htmlspecialchars($q['option1']); ?></label>
                            <label><input type="radio" name="q<?php echo $q['id']; ?>" value="2"> <?php echo htmlspecialchars($q['option2']); ?></label>
                            <label><input type="radio" name="q<?php echo $q['id']; ?>" value="3"> <?php echo htmlspecialchars($q['option3']); ?></label>
                            <label><input type="radio" name="q<?php echo $q['id']; ?>" value="4"> <?php echo htmlspecialchars($q['option4']); ?></label>
                        </div>
                    </section>
                    <?php endforeach; ?>
                    <div class="question-actions">
                        <button type="button" class="btn admin-btn" id="prevBtn">Previous</button>
                        <button type="button" class="btn" id="nextBtn">Next</button>
                    </div>
                    <div class="question-actions" style="margin-top: 22px; width: 100%;">
                        <input type="submit" class="btn" value="Submit Quiz">
                    </div>
                </main>
            </div>
        </form>
    </div>
</body>
</html>
