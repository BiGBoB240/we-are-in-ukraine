<?php
require_once '../config/db.php';
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

function isLatin($str) {
    return preg_match('/[a-zA-Z]/', $str);
}

$token = $_POST['token'] ?? '';
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';
if (!$token || !$password || !$password_confirm) {
    echo json_encode(['error' => 'Всі поля обов\'язкові.']);
    exit;
}
if (strlen($password) < 8 || !isLatin($password)) {
    echo json_encode(['error' => 'Пароль має бути не менше 8 символів і містити хоча б одну латинську букву.']);
    exit;
}
if ($password !== $password_confirm) {
    echo json_encode(['error' => 'Паролі не співпадають.']);
    exit;
}
$stmt = $pdo->prepare("SELECT id FROM Users WHERE verification_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();
if (!$user) {
    echo json_encode(['error' => 'Недійсний токен.']);
    exit;
}
$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = $pdo->prepare("UPDATE Users SET password_hash = ?, verification_token = NULL WHERE id = ?");
$stmt->execute([$hash, $user['id']]);
echo json_encode(['success' => 'Пароль успішно змінено!']);
