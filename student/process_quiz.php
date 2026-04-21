<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
if (isAdmin()) {
    header('Location: ../admin/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = $_POST['quiz_id'];

$stmt = $pdo->prepare("SELECT * FROM questions WHERE quiz_id = ?");
$stmt->execute([$quiz_id]);
$questions = $stmt->fetchAll();

$score = 0;
$total = count($questions);

foreach ($questions as $q) {
    $selected = $_POST['q' . $q['id']] ?? 0;
    if ($selected == $q['correct_option']) {
        $score++;
    }
    // Insert answer
    $stmt_ans = $pdo->prepare("INSERT INTO answers (user_id, question_id, selected_option) VALUES (?, ?, ?)");
    $stmt_ans->execute([$user_id, $q['id'], $selected]);
}

$percentage = ($score / $total) * 100;

// Insert result
$stmt_res = $pdo->prepare("INSERT INTO results (user_id, quiz_id, score, percentage) VALUES (?, ?, ?, ?)");
$stmt_res->execute([$user_id, $quiz_id, $score, $percentage]);

header("Location: result.php?quiz_id=$quiz_id");
exit();
?>