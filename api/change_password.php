<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    echo json_encode(['error' => 'Всі поля обов\'язкові']);
    exit;
}

if ($newPassword !== $confirmPassword) {
    echo json_encode(['error' => 'Паролі не співпадають']);
    exit;
}

if (strlen($newPassword) < 8 || !preg_match('/[a-zA-Z]/', $newPassword)) {
    echo json_encode(['error' => 'Пароль має бути не менше 8 символів і містити хоча б одну латинську букву']);
    exit;
}

try {
    // Перевірка поточного паролю
    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if (!password_verify($currentPassword, $user['password_hash'])) {
        echo json_encode(['error' => 'Неправильний поточний пароль']);
        exit;
    }

    // Оновлення паролю
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
    $stmt->execute([$hash, $_SESSION['user_id']]);
    
    // Очищення сесії після зміни паролю
    session_destroy();
    echo json_encode(['success' => 'Пароль успішно змінено. Увійдіть знову з новим паролем.']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при зміні пароля']);
}   