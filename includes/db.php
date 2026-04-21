<?php
// Database connection using PDO
$host = 'localhost';
$dbname = 'quiz_system';
$username = 'root'; // Change if different
$password = ''; // Change if different
$port = 3307; // Your MySQL port

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>