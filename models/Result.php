<?php
class Result {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO results (user_id, quiz_id, score, percentage, time_taken, xp_earned) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['user_id'], $data['quiz_id'], $data['score'], $data['percentage'], $data['time_taken'], $data['xp_earned']]);
    }

    public function getUserHistory($user_id, $subject = null) {
        $query = "SELECT r.*, q.title FROM results r JOIN quizzes q ON r.quiz_id = q.id WHERE r.user_id = ?";
        $params = [$user_id];
        if ($subject) {
            $query .= " AND q.subject = ?";
            $params[] = $subject;
        }
        $query .= " ORDER BY r.attempt_date DESC";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>