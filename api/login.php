<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if (!$email || !$password) {
    echo json_encode(['error' => 'Всі поля обов\'язкові.']);
    exit;
}
try {
    $stmt = $pdo->prepare("SELECT * FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        echo json_encode(['error' => 'Невірний email або пароль.']);
        exit;
    }
    if (!$user['verificated']) {
        echo json_encode(['error' => 'Аккаунт не підтверджено. Перевірте email.']);
        exit;
    }
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['username'] = $user['username'];
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
}
