<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = sanitize($_POST['title']);
    $subject = sanitize($_POST['subject']);
    $status = $_POST['status'];

    $stmt = $pdo->prepare("INSERT INTO quizzes (title, subject, status) VALUES (?, ?, ?)");
    $stmt->execute([$title, $subject, $status]);
    $quiz_id = $pdo->lastInsertId();

    if ($status == 'active') {
        // Notify all students
        $stmt_users = $pdo->query("SELECT id FROM users WHERE role = 'student'");
        $users = $stmt_users->fetchAll(PDO::FETCH_COLUMN);
        foreach ($users as $user_id) {
            $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
            $stmt_notif->execute([$user_id, "New quiz added: $title"]);
        }
    }

    header("Location: add_questions.php?id=$quiz_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quiz - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Create New Quiz</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <form method="POST">
            <input type="text" name="title" placeholder="Quiz Title" required>
            <input type="text" name="subject" placeholder="Subject" required>
            <select name="status" required>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
            <input type="submit" value="Create Quiz and Add Questions">
        </form>
    </div>
</body>
</html>