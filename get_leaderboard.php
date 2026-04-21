<?php
require_once 'models/User.php';
require_once 'includes/db.php';

header('Content-Type: application/json');

$userModel = new User($pdo);
$leaderboard = $userModel->getLeaderboard(20);

$html = '';
$rank = 1;
foreach ($leaderboard as $user) {
    $highlight = ($user['name'] == ($_SESSION['name'] ?? '')) ? 'highlight' : '';
    $html .= "<tr class='$highlight'>
        <td>{$rank}</td>
        <td>" . htmlspecialchars($user['name']) . "</td>
        <td>{$user['level']}</td>
        <td>{$user['xp']} XP</td>
    </tr>";
    $rank++;
}

echo json_encode(['html' => $html]);
?>