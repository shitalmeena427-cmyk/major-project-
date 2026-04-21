<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <link rel="manifest" href="/manifest.json">
    <link rel="icon" href="/assets/images/icon-192.png">
    <title>Smart Quiz & Performance Analysis System</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Smart Quiz & Performance Analysis System</h1>
        <button id="theme-toggle" class="theme-toggle">🌙</button>
    </header>
    <div class="container fade-in">
        <p class="hero-text">An advanced online mock test system with real-time analytics, gamification, and personalized insights.</p>
        <div class="dashboard">
            <div class="card">
                <div class="icon">🎓</div>
                <h3>Student Portal</h3>
                <p>Take adaptive quizzes, track progress, earn XP</p>
                <a href="student/login.php" class="btn student-btn">Student Login</a>
                <a href="student/register.php" class="btn student-btn">Register</a>
            </div>
            <div class="card">
                <div class="icon">👨‍🏫</div>
                <h3>Admin Portal</h3>
                <p>Manage quizzes, view analytics, monitor performance</p>
                <a href="admin/login.php" class="btn admin-btn">Admin Login</a>
            </div>
            <div class="card">
                <div class="icon">🏆</div>
                <h3>Leaderboard</h3>
                <p>See top performers and rankings</p>
                <a href="leaderboard.php" class="btn">View Rankings</a>
            </div>
        </div>
        <div id="install-btn" style="display: none; text-align: center; margin-top: 20px;">
            <button class="btn">Install App</button>
        </div>
    </div>
</body>
</html>