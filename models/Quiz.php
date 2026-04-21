<?php
class Quiz {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function getActive() {
        $stmt = $this->pdo->query("SELECT * FROM quizzes WHERE status = 'active'");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuestions($quiz_id, $difficulty = null) {
        $query = "SELECT * FROM questions WHERE quiz_id = ?";
        $params = [$quiz_id];
        if ($difficulty) {
            $query .= " AND difficulty = ?";
            $params[] = $difficulty;
        }
        $query .= " ORDER BY RAND() LIMIT 10"; // Random 10 questions
        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO quizzes (title, subject, status, sections, negative_marking, time_limit) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([$data['title'], $data['subject'], $data['status'], $data['sections'], $data['negative_marking'], $data['time_limit']]);
    }
}
?>