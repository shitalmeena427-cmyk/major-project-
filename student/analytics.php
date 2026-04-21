<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get score trend data
$stmt_trend = $pdo->prepare("SELECT DATE(attempt_date) as date, percentage FROM results WHERE user_id = ? ORDER BY attempt_date");
$stmt_trend->execute([$user_id]);
$trend_data = $stmt_trend->fetchAll(PDO::FETCH_ASSOC);

// Get subject performance
$stmt_subject = $pdo->prepare("SELECT q.subject, AVG(r.percentage) as avg_score, COUNT(*) as count FROM results r JOIN quizzes q ON r.quiz_id = q.id WHERE r.user_id = ? GROUP BY q.subject ORDER BY avg_score DESC");
$stmt_subject->execute([$user_id]);
$subject_data = $stmt_subject->fetchAll(PDO::FETCH_ASSOC);

// Total quizzes attempted
$stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM results WHERE user_id = ?");
$stmt_total->execute([$user_id]);
$total_quizzes = $stmt_total->fetch()['total'] ?? 0;

// Best score
$stmt_best = $pdo->prepare("SELECT MAX(percentage) as best FROM results WHERE user_id = ?");
$stmt_best->execute([$user_id]);
$best = $stmt_best->fetch()['best'] ?? 0;

// Average score
$stmt_avg = $pdo->prepare("SELECT AVG(percentage) as avg FROM results WHERE user_id = ?");
$stmt_avg->execute([$user_id]);
$avg = $stmt_avg->fetch()['avg'] ?? 0;

// Current rank (count students with better average)
$stmt_rank = $pdo->prepare("SELECT COUNT(DISTINCT user_id) as rank FROM results r1 WHERE (SELECT AVG(percentage) FROM results WHERE user_id = r1.user_id) > (SELECT AVG(percentage) FROM results WHERE user_id = ?)");
$stmt_rank->execute([$user_id]);
$rank = ($stmt_rank->fetch()['rank'] ?? 0) + 1;

// Accuracy % (correct out of total possible)
$stmt_accuracy = $pdo->prepare("SELECT COUNT(*) as correct FROM results WHERE user_id = ? AND percentage >= 80");
$stmt_accuracy->execute([$user_id]);
$high_scores = $stmt_accuracy->fetch()['correct'] ?? 0;
$accuracy = $total_quizzes > 0 ? round(($high_scores / $total_quizzes) * 100, 1) : 0;

// Completion rate (days with attempts)
$stmt_completion = $pdo->prepare("SELECT COUNT(DISTINCT DATE(attempt_date)) as days FROM results WHERE user_id = ?");
$stmt_completion->execute([$user_id]);
$active_days = $stmt_completion->fetch()['days'] ?? 0;
$completion_rate = min(100, $active_days * 10); // Max 100% at 10 active days

// Improvement % (compare first and last)
$stmt_first_last = $pdo->prepare("SELECT percentage FROM results WHERE user_id = ? ORDER BY attempt_date");
$stmt_first_last->execute([$user_id]);
$all_attempts = $stmt_first_last->fetchAll(PDO::FETCH_ASSOC);
$first = count($all_attempts) > 0 ? $all_attempts[0]['percentage'] : 0;
$last = count($all_attempts) > 0 ? end($all_attempts)['percentage'] : 0;
$improvement = $first ? (($last - $first) / $first) * 100 : 0;

// Weak subject
$weak_subject = '';
if (!empty($subject_data)) {
    $weakest = $subject_data[0];
    foreach ($subject_data as $subject) {
        if ($subject['avg_score'] < $weakest['avg_score']) {
            $weakest = $subject;
        }
    }
    $weak_subject = $weakest['subject'];
}

// Performance insights
$insights = [];
if ($improvement > 0) {
    $insights[] = "🎯 Improved by " . number_format($improvement, 1) . "% from your first attempt!";
}
if ($accuracy >= 75) {
    $insights[] = "⭐ You're scoring high on " . round($accuracy) . "% of quizzes!";
}
if (!empty($weak_subject)) {
    $insights[] = "📚 Focus on <strong>" . $weak_subject . "</strong> for better results.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics Dashboard - Smart Quiz System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/script.js" defer></script>
</head>
<body class="analytics-page">
    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="loading-spinner">
        <div class="spinner-content">
            <div class="spinner"></div>
            <p>Loading your analytics...</p>
        </div>
    </div>

    <!-- Header -->
    <header class="analytics-header">
        <div class="header-container">
            <div class="header-left">
                <h1 class="page-title">📊 Analytics Dashboard</h1>
                <p class="page-subtitle">Your learning progress and performance insights</p>
            </div>
            <nav class="header-nav">
                <a href="dashboard.php" class="nav-link"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="history.php" class="nav-link"><i class="fas fa-history"></i> History</a>
                <a href="leaderboard.php" class="nav-link"><i class="fas fa-trophy"></i> Leaderboard</a>
                <a href="../logout.php" class="nav-link logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </div>
    </header>

    <main class="analytics-container">
        <!-- Top Stats Cards -->
        <section class="stats-cards-section">
            <div class="stats-grid">
                <!-- Total Quizzes Card -->
                <div class="stat-card fade-in" style="animation-delay: 0.1s">
                    <div class="card-header">
                        <div class="icon-box primary">
                            <i class="fas fa-list-check"></i>
                        </div>
                        <span class="stat-title">Total Quizzes</span>
                    </div>
                    <div class="card-content">
                        <div class="stat-value"><?php echo $total_quizzes; ?></div>
                        <p class="stat-description">Quiz attempts completed</p>
                    </div>
                </div>

                <!-- Average Score Card -->
                <div class="stat-card fade-in" style="animation-delay: 0.2s">
                    <div class="card-header">
                        <div class="icon-box success">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <span class="stat-title">Average Score</span>
                    </div>
                    <div class="card-content">
                        <div class="stat-value"><?php echo number_format($avg, 1); ?><span class="percent">%</span></div>
                        <p class="stat-description">Overall performance</p>
                    </div>
                </div>

                <!-- Best Score Card -->
                <div class="stat-card fade-in" style="animation-delay: 0.3s">
                    <div class="card-header">
                        <div class="icon-box warning">
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="stat-title">Best Score</span>
                    </div>
                    <div class="card-content">
                        <div class="stat-value"><?php echo number_format($best, 1); ?><span class="percent">%</span></div>
                        <p class="stat-description">Your highest score</p>
                    </div>
                </div>

                <!-- Current Rank Card -->
                <div class="stat-card fade-in" style="animation-delay: 0.4s">
                    <div class="card-header">
                        <div class="icon-box info">
                            <i class="fas fa-medal"></i>
                        </div>
                        <span class="stat-title">Current Rank</span>
                    </div>
                    <div class="card-content">
                        <div class="stat-value">#<?php echo $rank; ?></div>
                        <p class="stat-description"><?php echo $rank === 1 ? 'You\'re #1!' : 'Keep improving!'; ?></p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Progress Indicators -->
        <section class="progress-section fade-in" style="animation-delay: 0.5s">
            <h2 class="section-title"><i class="fas fa-tasks"></i> Progress Indicators</h2>
            <div class="progress-grid">
                <!-- Accuracy Progress -->
                <div class="progress-card">
                    <div class="progress-header">
                        <span class="progress-label">High Score Rate</span>
                        <span class="progress-value"><?php echo $accuracy; ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill" style="width: <?php echo $accuracy; ?>%"></div>
                        </div>
                    </div>
                    <p class="progress-description">Percentage of quizzes with 80%+ score</p>
                </div>

                <!-- Completion Rate Progress -->
                <div class="progress-card">
                    <div class="progress-header">
                        <span class="progress-label">Consistency</span>
                        <span class="progress-value"><?php echo round($completion_rate); ?>%</span>
                    </div>
                    <div class="progress-bar-container">
                        <div class="progress-bar">
                            <div class="progress-fill success" style="width: <?php echo min($completion_rate, 100); ?>%"></div>
                        </div>
                    </div>
                    <p class="progress-description"><?php echo $active_days; ?> active learning days</p>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <section class="charts-section fade-in" style="animation-delay: 0.6s">
            <h2 class="section-title"><i class="fas fa-chart-mixed"></i> Performance Trends</h2>
            <div class="charts-grid">
                <!-- Score Trend Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Score Trend Over Time</h3>
                        <span class="chart-badge">Last <?php echo count($trend_data); ?> Attempts</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="trendChart"></canvas>
                    </div>
                </div>

                <!-- Subject Performance Chart -->
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>Subject-wise Performance</h3>
                        <span class="chart-badge"><?php echo count($subject_data); ?> Subjects</span>
                    </div>
                    <div class="chart-container">
                        <canvas id="subjectChart"></canvas>
                    </div>
                </div>
            </div>
        </section>

        <!-- Performance Insights -->
        <section class="insights-section fade-in" style="animation-delay: 0.7s">
            <h2 class="section-title"><i class="fas fa-lightbulb"></i> Smart Insights</h2>
            <div class="insights-grid">
                <?php if (!empty($insights)): ?>
                    <?php foreach ($insights as $insight): ?>
                        <div class="insight-card">
                            <div class="insight-icon">💡</div>
                            <p><?php echo $insight; ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="insight-card positive">
                        <div class="insight-icon">🎉</div>
                        <p>Excellent! You're doing great. Keep taking more quizzes to unlock personalized insights.</p>
                    </div>
                <?php endif; ?>

                <?php if ($improvement > 0): ?>
                    <div class="insight-card positive">
                        <div class="insight-icon">📈</div>
                        <p><strong>Outstanding Progress!</strong> You've improved by <strong><?php echo number_format($improvement, 1); ?>%</strong> from your first attempt!</p>
                    </div>
                <?php endif; ?>

                <?php if ($total_quizzes < 5): ?>
                    <div class="insight-card info">
                        <div class="insight-icon">📝</div>
                        <p>Complete <strong><?php echo 5 - $total_quizzes; ?> more quizzes</strong> to unlock advanced analytics and recommendations.</p>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <!-- Subject Details Table -->
        <?php if (!empty($subject_data)): ?>
        <section class="subjects-section fade-in" style="animation-delay: 0.8s">
            <h2 class="section-title"><i class="fas fa-book"></i> Subject Performance Details</h2>
            <div class="table-container">
                <table class="subjects-table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-bookmark"></i> Subject</th>
                            <th><i class="fas fa-percentage"></i> Avg Score</th>
                            <th><i class="fas fa-check-circle"></i> Attempts</th>
                            <th><i class="fas fa-chart-bar"></i> Performance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($subject_data as $subject): ?>
                        <tr>
                            <td class="subject-name"><?php echo htmlspecialchars($subject['subject']); ?></td>
                            <td class="score-value"><?php echo number_format($subject['avg_score'], 1); ?>%</td>
                            <td class="attempts-value"><?php echo $subject['count']; ?></td>
                            <td class="performance-bar">
                                <div class="mini-bar">
                                    <div class="mini-fill" style="width: <?php echo $subject['avg_score']; ?>%"></div>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

        <!-- Study Recommendations -->
        <section class="recommendations-section fade-in" style="animation-delay: 0.9s">
            <h2 class="section-title"><i class="fas fa-graduation-cap"></i> Recommendations</h2>
            <div class="recommendations-grid">
                <div class="recommendation-card">
                    <div class="recommendation-icon">🎯</div>
                    <h4>Focus Areas</h4>
                    <p><?php echo !empty($weak_subject) ? 'Strengthen your knowledge in <strong>' . htmlspecialchars($weak_subject) . '</strong> for better overall performance.' : 'You\'re performing well across all subjects!'; ?></p>
                </div>
                <div class="recommendation-card">
                    <div class="recommendation-icon">⏰</div>
                    <h4>Regular Practice</h4>
                    <p>Maintain consistency by taking at least 2-3 quizzes per week to accelerate your learning progress.</p>
                </div>
                <div class="recommendation-card">
                    <div class="recommendation-icon">🏆</div>
                    <h4>Goal Setting</h4>
                    <p>Aim to achieve <?php echo $avg >= 80 ? '90%+' : '80%+'; ?> on your next quiz attempt for rapid improvement.</p>
                </div>
            </div>
        </section>
    </main>

    <!-- Scripts -->
    <script>
        // Hide loading spinner
        window.addEventListener('load', () => {
            document.getElementById('loadingSpinner').classList.add('hide');
        });

        // Trend Chart - Enhanced Configuration
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($trend_data, 'date')); ?>,
                datasets: [{
                    label: 'Score Percentage',
                    data: <?php echo json_encode(array_column($trend_data, 'percentage')); ?>,
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointRadius: 6,
                    pointBackgroundColor: '#6366f1',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointHoverRadius: 8,
                    pointHoverBackgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        labels: {
                            color: '#f1f5f9',
                            font: { size: 13, weight: 'bold' },
                            padding: 20
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#cbd5e1',
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(203, 213, 225, 0.1)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            color: '#cbd5e1',
                            font: { size: 12 }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Subject Chart - Enhanced Configuration
        const subjectCtx = document.getElementById('subjectChart').getContext('2d');
        const subjectChart = new Chart(subjectCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($subject_data, 'subject')); ?>,
                datasets: [{
                    label: 'Average Score',
                    data: <?php echo json_encode(array_column($subject_data, 'avg_score')); ?>,
                    backgroundColor: [
                        '#6366f1',
                        '#22c55e',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6'
                    ],
                    borderRadius: 8,
                    borderSkipped: false,
                    borderWidth: 0,
                    hoverBackgroundColor: [
                        '#4f46e5',
                        '#16a34a',
                        '#d97706',
                        '#dc2626',
                        '#7c3aed'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        labels: {
                            color: '#f1f5f9',
                            font: { size: 13, weight: 'bold' },
                            padding: 20
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            color: '#cbd5e1',
                            font: { size: 12 }
                        },
                        grid: {
                            color: 'rgba(203, 213, 225, 0.1)',
                            drawBorder: false
                        }
                    },
                    y: {
                        ticks: {
                            color: '#cbd5e1',
                            font: { size: 12 }
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });

        // Count up animation for stat values
        function animateValue(element, start, end, duration) {
            let startTimestamp = null;
            const step = (timestamp) => {
                if (!startTimestamp) startTimestamp = timestamp;
                const progress = Math.min((timestamp - startTimestamp) / duration, 1);
                const value = Math.floor(progress * (end - start) + start);
                element.textContent = value;
                if (progress < 1) {
                    window.requestAnimationFrame(step);
                }
            };
            window.requestAnimationFrame(step);
        }

        // Animate progress bars on scroll
        const observerOptions = {
            threshold: 0.2,
            rootMargin: '0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const progressFill = entry.target.querySelector('.progress-fill');
                    if (progressFill && !progressFill.classList.contains('animated')) {
                        const width = progressFill.style.width;
                        progressFill.style.width = '0%';
                        setTimeout(() => {
                            progressFill.style.width = width;
                            progressFill.classList.add('animated');
                        }, 100);
                    }
                }
            });
        }, observerOptions);

        document.querySelectorAll('.progress-bar-container').forEach(el => {
            observer.observe(el);
        });
    </script>
</body>
</html>