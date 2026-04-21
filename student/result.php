<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = $_GET['quiz_id'] ?? 0;
if (!$quiz_id) {
    header('Location: dashboard.php');
    exit();
}

// Get result
$stmt = $pdo->prepare("SELECT * FROM results WHERE user_id = ? AND quiz_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$user_id, $quiz_id]);
$result = $stmt->fetch();
if (!$result) {
    echo "No result found.";
    exit();
}

// Get questions and answers
$stmt = $pdo->prepare("SELECT q.question, q.option1, q.option2, q.option3, q.option4, q.correct_option, a.selected_option FROM questions q JOIN answers a ON q.id = a.question_id WHERE q.quiz_id = ? AND a.user_id = ? ORDER BY q.id");
$stmt->execute([$quiz_id, $user_id]);
$details = $stmt->fetchAll();

$correct = 0;
foreach ($details as $item) {
    if ($item['selected_option'] == $item['correct_option']) {
        $correct++;
    }
}
$incorrect = count($details) - $correct;
$attempted = count($details);

// Rank
$stmt_rank = $pdo->prepare("SELECT COUNT(*) as rank FROM results WHERE quiz_id = ? AND percentage > ?");
$stmt_rank->execute([$quiz_id, $result['percentage']]);
$rank = $stmt_rank->fetch()['rank'] + 1;

// Class average
$stmt_avg = $pdo->prepare("SELECT AVG(percentage) as avg FROM results WHERE quiz_id = ?");
$stmt_avg->execute([$quiz_id]);
$avg = $stmt_avg->fetch()['avg'] ?? 0;

$suggestion = 'Great work! Keep practicing to keep your momentum up.';
if ($result['percentage'] < 70) {
    $suggestion = 'Focus on the questions you missed and review the concepts for stronger results.';
} elseif ($result['percentage'] < 90) {
    $suggestion = 'Nice score! A little more review will help you push into the top tier.';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz Result - Smart Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Quiz Result</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="history.php">My History</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="result-grid">
            <div class="result-card">
                <h3>Final Score</h3>
                <p><?php echo $result['score']; ?>/<?php echo $attempted; ?> (<?php echo number_format($result['percentage'], 2); ?>%)</p>
                <div class="performance-pill">Performance: <span><?php echo number_format($result['percentage'], 2); ?>%</span></div>
            </div>
            <div class="result-card">
                <h3>Class Average</h3>
                <p><?php echo number_format($avg, 2); ?>%</p>
                <p>Rank: <?php echo $rank; ?></p>
            </div>
            <div class="result-card">
                <h3>Accuracy</h3>
                <p><?php echo $correct; ?> correct</p>
                <p><?php echo $incorrect; ?> incorrect</p>
            </div>
        </div>

        <div class="chart-card">
            <div class="chart-header">
                <h3>Performance Comparison</h3>
                <span class="chart-badge">Score</span>
            </div>
            <div class="chart-container">
                <canvas id="scoreChart"></canvas>
            </div>
        </div>

        <section class="suggestions-section">
            <h2>Next Step</h2>
            <div class="suggestions-list">
                <div class="suggestion-item positive">
                    <div class="icon">💡</div>
                    <div>
                        <h4>Study Plan</h4>
                        <p><?php echo htmlspecialchars($suggestion); ?></p>
                    </div>
                </div>
                <div class="suggestion-item">
                    <div class="icon">📘</div>
                    <div>
                        <h4>Review Answers</h4>
                        <p>Use the breakdown below to sharpen your understanding of each topic.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="history-section">
            <h2>Question-wise Analysis</h2>
            <?php foreach ($details as $index => $d): ?>
            <?php $options = [$d['option1'], $d['option2'], $d['option3'], $d['option4']]; ?>
            <div class="question card">
                <h4><?php echo ($index + 1) . '. ' . htmlspecialchars($d['question']); ?></h4>
                <p>Your Answer: <span class="<?php echo ($d['selected_option'] == $d['correct_option']) ? 'correct' : 'incorrect'; ?>"><?php echo htmlspecialchars($options[$d['selected_option'] - 1] ?? 'No answer'); ?></span></p>
                <p>Correct Answer: <span class="correct"><?php echo htmlspecialchars($options[$d['correct_option'] - 1]); ?></span></p>
            </div>
            <?php endforeach; ?>
        </section>
    </div>

    <script>
        var ctx = document.getElementById('scoreChart').getContext('2d');
        var myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Your Score', 'Class Average'],
                datasets: [{
                    label: 'Percentage',
                    data: [<?php echo $result['percentage']; ?>, <?php echo $avg; ?>],
                    backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(16, 185, 129, 0.2)'],
                    borderColor: ['rgba(54, 162, 235, 1)', 'rgba(16, 185, 129, 1)'],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    </script>
</body>
</html>
