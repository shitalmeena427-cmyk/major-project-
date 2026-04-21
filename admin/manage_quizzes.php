<?php
include '../includes/db.php';
include '../includes/functions.php';

requireLogin();
requireAdmin();

if (isset($_GET['toggle'])) {
    $id = $_GET['toggle'];
    $stmt = $pdo->prepare("SELECT status, title FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    $quiz = $stmt->fetch();
    if ($quiz) {
        $new = $quiz['status'] === 'active' ? 'inactive' : 'active';
        $stmt_update = $pdo->prepare("UPDATE quizzes SET status = ? WHERE id = ?");
        $stmt_update->execute([$new, $id]);

        if ($new === 'active') {
            $stmt_users = $pdo->query("SELECT id FROM users WHERE role = 'student'");
            $users = $stmt_users->fetchAll(PDO::FETCH_COLUMN);
            foreach ($users as $user_id) {
                $stmt_notif = $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmt_notif->execute([$user_id, "Quiz activated: " . $quiz['title']]);
            }
        }
    }
    header('Location: manage_quizzes.php');
    exit();
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM quizzes WHERE id = ?");
    $stmt->execute([$id]);
    header('Location: manage_quizzes.php');
    exit();
}

$search = trim($_GET['search'] ?? '');
$status_filter = $_GET['status'] ?? '';

$where = " WHERE 1";
$params = [];
if ($search) {
    $where .= " AND (q.title LIKE ? OR q.subject LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($status_filter) {
    $where .= " AND q.status = ?";
    $params[] = $status_filter;
}

$query = "SELECT q.*, COUNT(r.id) as attempts FROM quizzes q LEFT JOIN results r ON q.id = r.quiz_id" . $where . " GROUP BY q.id ORDER BY q.id DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$quizzes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Quizzes - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <script>
        const savedTheme = localStorage.getItem('theme') || 'light';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>
    <script src="../js/script.js" defer></script>
</head>
<body>
    <header>
        <h1>Manage Quizzes</h1>
        <nav>
            <a href="dashboard.php">Dashboard</a>
            <a href="create_quiz.php">Create Quiz</a>
            <a href="manage_students.php">Manage Students</a>
            <a href="view_results.php">View Results</a>
            <a href="logout.php">Logout</a>
        </nav>
    </header>
    <div class="container">
        <div class="admin-actions">
            <form method="GET">
                <input type="search" name="search" class="search-input" placeholder="Search quizzes..." value="<?php echo htmlspecialchars($search); ?>">
                <select name="status">
                    <option value="">All statuses</option>
                    <option value="active" <?php if ($status_filter == 'active') echo 'selected'; ?>>Active</option>
                    <option value="inactive" <?php if ($status_filter == 'inactive') echo 'selected'; ?>>Inactive</option>
                </select>
                <button type="submit" class="btn">Search</button>
                <?php if ($search || $status_filter): ?>
                <a href="manage_quizzes.php" class="btn admin-btn">Reset</a>
                <?php endif; ?>
            </form>
            <a href="create_quiz.php" class="btn">New Quiz</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Subject</th>
                        <th>Status</th>
                        <th>Attempts</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quizzes as $quiz): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($quiz['title']); ?></td>
                        <td><?php echo htmlspecialchars($quiz['subject']); ?></td>
                        <td><span class="status-chip <?php echo $quiz['status'] == 'active' ? 'active' : 'inactive'; ?>"><?php echo htmlspecialchars(ucfirst($quiz['status'])); ?></span></td>
                        <td><?php echo $quiz['attempts']; ?></td>
                        <td>
                            <a href="?toggle=<?php echo $quiz['id']; ?>" class="btn admin-btn"><?php echo $quiz['status'] == 'active' ? 'Deactivate' : 'Activate'; ?></a>
                            <a href="?delete=<?php echo $quiz['id']; ?>" class="btn admin-btn" onclick="return confirm('Delete this quiz?');">Delete</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
