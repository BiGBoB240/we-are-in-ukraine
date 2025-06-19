<?php
require_once '../config/db.php';
session_start();

// Перевірити чи запит POST і JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Метод не підтримується']);
    exit;
}

// Отримати JSON даний
$data = json_decode(file_get_contents('php://input'), true);

// Перевірити чи користувач увійшов і адміністратор
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = $stmt->fetchColumn() !== false;
}

if (!$isAdmin) {
    echo json_encode(['error' => 'Доступ заборонено. Тільки адміністратори можуть видаляти профілі']);
    exit;
}

// Перевірити чи user_id надано
if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
    echo json_encode(['error' => 'Некоректний ID користувача']);
    exit;
}

$userId = (int)$data['user_id'];
$blockUser = isset($data['block_user']) ? (bool)$data['block_user'] : false;

// Не дозволити видалення власного профілю
if ($userId === (int)$_SESSION['user_id']) {
    echo json_encode(['error' => 'Ви не можете видалити власний профіль']);
    exit;
}

// Не дозволити видалення іншого адміністратора
$stmt = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
$stmt->execute([$userId]);
if ($stmt->fetchColumn() !== false) {
    echo json_encode(['error' => 'Ви не можете видалити профіль іншого адміністратора']);
    exit;
}

try {
    // Почати транзакцію
    $pdo->beginTransaction();
    // Якщо потрібно заблокувати користувача
    if ($blockUser) {
        // Отримати username та email користувача перед видаленням
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userInfo) {
            $stmt = $pdo->prepare('INSERT INTO blockedusers (username, email, blocked_by_id, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$userInfo['username'], $userInfo['email'], $_SESSION['user_id']]);
        }
    }
    
    // Видалити всі лайки користувача на коментарі
    $stmt = $pdo->prepare('DELETE FROM commentlikes WHERE user_id = ?');
    $stmt->execute([$userId]);

    // Видалити всі лайки користувача на постах
    $stmt = $pdo->prepare('DELETE FROM commentlikes WHERE comment_id IN (SELECT id FROM comments WHERE user_id = ?)');
    $stmt->execute([$userId]);
    
    // Видалити всі лайки користувача на постах
    $stmt = $pdo->prepare('DELETE FROM postlikes WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Видалити всі коментарі користувача
    $stmt = $pdo->prepare('DELETE FROM comments WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Видалити всі скарги створені користувачем
    $stmt = $pdo->prepare('DELETE FROM reports WHERE reported_by_id = ?');
    $stmt->execute([$userId]);
    
    // Видалити всі скарги про користувача
    $stmt = $pdo->prepare('DELETE FROM reports WHERE content_type = ? AND content_id IN (SELECT id FROM users WHERE id = ?)');
    $stmt->execute(['user', $userId]);
    
    // Видалити користувача з administrations
    $stmt = $pdo->prepare('DELETE FROM administrations WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Видалити користувача
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    
    // Підтвердити транзакцію
    $pdo->commit();
    
    echo json_encode(['success' => 'Профіль користувача успішно видалено']);
} catch (Exception $e) {
    // Підтвердити транзакцію
    $pdo->rollBack();
    echo json_encode(['error' => 'Помилка при видаленні профілю: ' . $e->getMessage()]);
}
