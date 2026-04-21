<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

$stmt = $pdo->query("SELECT COUNT(*) as total_quizzes FROM quizzes");
$total_quizzes = $stmt->fetch()['total_quizzes'];

$stmt = $pdo->query("SELECT COUNT(*) as total_students FROM users WHERE role = 'student'");
$total_students = $stmt->fetch()['total_students'];

$stmt = $pdo->query("SELECT AVG(percentage) as avg_score FROM results");
$avg_score = $stmt->fetch()['avg_score'] ?? 0;

$stmt = $pdo->query("SELECT COUNT(*) as total_attempts FROM results");
$total_attempts = $stmt->fetch()['total_attempts'];

$stmt = $pdo->query("SELECT COUNT(*) as active_quizzes FROM quizzes WHERE status = 'active'");
$active_quizzes = $stmt->fetch()['active_quizzes'];

$stmt = $pdo->query("SELECT q.subject, COUNT(*) as attempts FROM results r JOIN quizzes q ON r.quiz_id = q.id GROUP BY q.subject ORDER BY attempts DESC LIMIT 6");
$subject_stats = $stmt->fetchAll();

$stmt = $pdo->query("SELECT u.name, ROUND(AVG(r.percentage), 2) as average_score, COUNT(r.id) as attempts FROM results r JOIN users u ON r.user_id = u.id WHERE u.role = 'student' GROUP BY u.id ORDER BY average_score DESC LIMIT 5");
$top_students = $stmt->fetchAll();

$stmt = $pdo->query("SELECT r.attempt_date, u.name, q.title, r.percentage FROM results r JOIN users u ON r.user_id = u.id JOIN quizzes q ON r.quiz_id = q.id ORDER BY r.attempt_date DESC LIMIT 5");
$recent_activity = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Smart Quiz System</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('subjectChart');
        if (!ctx) return;
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: [<?php echo implode(', ', array_map(function($row) { return "'" . addslashes($row['subject']) . "'"; }, $subject_stats)); ?>],
                datasets: [{
                    label: 'Attempts',
                    data: [<?php echo implode(', ', array_map(function($row) { return $row['attempts']; }, $subject_stats)); ?>],
                    backgroundColor: 'rgba(59, 130, 246, 0.45)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    });
    </script>
</head>
<body>
    <header>
        <h1>Admin Dashboard</h1>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
        <nav>
            <a href="create_quiz.php">Create Quiz</a>
            <a href="manage_quizzes.php">Manage Quizzes</a>
            <a href="manage_students.php">Manage Students</a>
            <a href="view_results.php">View Results</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="card-header">
                    <div class="icon-box primary">📚</div>
                    <div>
                        <p class="stat-title">Total Quizzes</p>
                        <p class="stat-value"><?php echo $total_quizzes; ?></p>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="icon-box success">👥</div>
                    <div>
                        <p class="stat-title">Students</p>
                        <p class="stat-value"><?php echo $total_students; ?></p>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="icon-box warning">📈</div>
                    <div>
                        <p class="stat-title">Average Score</p>
                        <p class="stat-value"><?php echo number_format($avg_score, 2); ?>%</p>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="icon-box info">⏱️</div>
                    <div>
                        <p class="stat-title">Recent Attempts</p>
                        <p class="stat-value"><?php echo $total_attempts; ?></p>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="card-header">
                    <div class="icon-box success">✅</div>
                    <div>
                        <p class="stat-title">Active Quizzes</p>
                        <p class="stat-value"><?php echo $active_quizzes; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <section class="charts-section">
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Attempts by Subject</h3>
                    <span class="chart-badge">Top Subjects</span>
                </div>
                <div class="chart-container">
                    <canvas id="subjectChart"></canvas>
                </div>
            </div>
            <div class="chart-card">
                <div class="chart-header">
                    <h3>Top Performing Students</h3>
                    <span class="chart-badge">Average Score</span>
                </div>
                <div class="subjects-section">
                    <table class="subjects-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Average Score</th>
                                <th>Attempts</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_students as $student): ?>
                            <tr>
                                <td class="subject-name"><?php echo htmlspecialchars($student['name']); ?></td>
                                <td class="score-value"><?php echo number_format($student['average_score'], 2); ?>%</td>
                                <td class="attempts-value"><?php echo $student['attempts']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="insights-section">
            <div class="insight-card info">
                <div class="insight-icon">🧠</div>
                <div>
                    <strong>Recent student activity</strong>
                    <p>Track the latest attempts and monitor quiz engagement for your top performers.</p>
                </div>
            </div>
            <div class="insight-card">
                <div class="insight-icon">📌</div>
                <div>
                    <strong>Action needed</strong>
                    <p>Review quizzes with low average scores and update question difficulty to improve learning outcomes.</p>
                </div>
            </div>
        </section>

        <section class="subjects-section">
            <h2>Recent Attempts</h2>
            <div class="table-container">
                <table class="subjects-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Student</th>
                            <th>Quiz</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_activity as $activity): ?>
                        <tr>
                            <td><?php echo date('M d, Y H:i', strtotime($activity['attempt_date'])); ?></td>
                            <td><?php echo htmlspecialchars($activity['name']); ?></td>
                            <td><?php echo htmlspecialchars($activity['title']); ?></td>
                            <td><?php echo number_format($activity['percentage'], 2); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
