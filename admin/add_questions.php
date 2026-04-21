<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

$quiz_id = $_GET['id'] ?? 0;
if (!$quiz_id) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $question = sanitize($_POST['question']);
    $option1 = sanitize($_POST['option1']);
    $option2 = sanitize($_POST['option2']);
    $option3 = sanitize($_POST['option3']);
    $option4 = sanitize($_POST['option4']);
    $correct = (int)$_POST['correct'];

    $stmt = $pdo->prepare("INSERT INTO questions (quiz_id, question, option1, option2, option3, option4, correct_option) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$quiz_id, $question, $option1, $option2, $option3, $option4, $correct]);
    $success = "Question added successfully. Add another question below.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Questions - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Add Questions to Quiz</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="create_quiz.php">Create Another Quiz</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <?php if (isset($success)) echo "<p style='color:green;'>$success</p>"; ?>
        <form method="POST">
            <textarea name="question" placeholder="Question" required rows="3"></textarea>
            <input type="text" name="option1" placeholder="Option 1" required>
            <input type="text" name="option2" placeholder="Option 2" required>
            <input type="text" name="option3" placeholder="Option 3" required>
            <input type="text" name="option4" placeholder="Option 4" required>
            <select name="correct" required>
                <option value="">Select Correct Option</option>
                <option value="1">Option 1</option>
                <option value="2">Option 2</option>
                <option value="3">Option 3</option>
                <option value="4">Option 4</option>
            </select>
            <input type="submit" value="Add Question">
        </form>
        <a href="dashboard.php" class="btn">Finish and Go to Dashboard</a>
    </div>
</body>
</html>