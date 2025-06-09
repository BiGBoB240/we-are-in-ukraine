<?php
require_once '../config/db.php';
session_start();

// Check if request is POST and JSON
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Метод не підтримується']);
    exit;
}

// Get JSON data
$data = json_decode(file_get_contents('php://input'), true);

// Check if user is logged in and is admin
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

// Check if user_id is provided
if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
    echo json_encode(['error' => 'Некоректний ID користувача']);
    exit;
}

$userId = (int)$data['user_id'];
$blockUser = isset($data['block_user']) ? (bool)$data['block_user'] : false;

// Don't allow deleting own account
if ($userId === (int)$_SESSION['user_id']) {
    echo json_encode(['error' => 'Ви не можете видалити власний профіль']);
    exit;
}

// Don't allow deleting other admins
$stmt = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
$stmt->execute([$userId]);
if ($stmt->fetchColumn() !== false) {
    echo json_encode(['error' => 'Ви не можете видалити профіль іншого адміністратора']);
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    // Если нужно заблокировать пользователя
    if ($blockUser) {
        // Получить username и email пользователя перед удалением
        $stmt = $pdo->prepare('SELECT username, email FROM users WHERE id = ?');
        $stmt->execute([$userId]);
        $userInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($userInfo) {
            $stmt = $pdo->prepare('INSERT INTO blockedusers (username, email, blocked_by_id, created_at) VALUES (?, ?, ?, NOW())');
            $stmt->execute([$userInfo['username'], $userInfo['email'], $_SESSION['user_id']]);
        }
    }
    
    // Удалить все лайки пользователя на комментарии
    $stmt = $pdo->prepare('DELETE FROM commentlikes WHERE user_id = ?');
    $stmt->execute([$userId]);

    // Delete user's comments likes (на комментарии, где он автор комментария)
    $stmt = $pdo->prepare('DELETE FROM commentlikes WHERE comment_id IN (SELECT id FROM comments WHERE user_id = ?)');
    $stmt->execute([$userId]);
    
    // Delete user's post likes
    $stmt = $pdo->prepare('DELETE FROM postlikes WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Delete user's comments
    $stmt = $pdo->prepare('DELETE FROM comments WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Delete reports created by the user
    $stmt = $pdo->prepare('DELETE FROM reports WHERE reported_by_id = ?');
    $stmt->execute([$userId]);
    
    // Delete reports about the user
    $stmt = $pdo->prepare('DELETE FROM reports WHERE content_type = ? AND content_id IN (SELECT id FROM users WHERE id = ?)');
    $stmt->execute(['user', $userId]);
    
    // Delete user from administrations if exists
    $stmt = $pdo->prepare('DELETE FROM administrations WHERE user_id = ?');
    $stmt->execute([$userId]);
    
    // Finally delete the user
    $stmt = $pdo->prepare('DELETE FROM users WHERE id = ?');
    $stmt->execute([$userId]);
    
    // Commit transaction
    $pdo->commit();
    
    echo json_encode(['success' => 'Профіль користувача успішно видалено']);
} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    echo json_encode(['error' => 'Помилка при видаленні профілю: ' . $e->getMessage()]);
}
