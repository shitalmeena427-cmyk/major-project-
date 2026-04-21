<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function findByEmail($email) {
        $stmt = $this->pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$data['name'], $data['email'], $data['password'], $data['role']]);
    }

    public function updateXP($id, $xp) {
        $stmt = $this->pdo->prepare("UPDATE users SET xp = xp + ?, level = FLOOR((xp + ?)/100) + 1 WHERE id = ?");
        return $stmt->execute([$xp, $xp, $id]);
    }

    public function getLeaderboard($limit = 10) {
        $limit = (int) $limit;
        if ($limit <= 0) {
            $limit = 10;
        }
        if ($limit > 100) {
            $limit = 100;
        }

        $stmt = $this->pdo->prepare("SELECT name, xp, level FROM users WHERE role = 'student' ORDER BY xp DESC LIMIT $limit");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>