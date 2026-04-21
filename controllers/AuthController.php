<?php
require_once '../models/User.php';

class AuthController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new User($pdo);
    }

    public function login($email, $password) {
        $user = $this->userModel->findByEmail($email);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['level'] = $user['level'];
            $_SESSION['xp'] = $user['xp'];
            return true;
        }
        return false;
    }

    public function register($data) {
        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        return $this->userModel->create($data);
    }

    public function logout() {
        session_destroy();
    }
}
?>