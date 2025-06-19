<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Ви не авторизовані']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? null;

if (!$commentId) {
    echo json_encode(['error' => 'Некоректний запит']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM commentlikes WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $_SESSION['user_id']]);
    $existingLike = $stmt->fetch();

    if ($existingLike) {
        // Прибрати лайк
        $stmt = $pdo->prepare("DELETE FROM commentlikes WHERE comment_id = ? AND user_id = ?");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        
        // Оновити лайки
        $stmt = $pdo->prepare("UPDATE comments SET comments_likes = comments_likes - 1 WHERE id = ?");
        $stmt->execute([$commentId]);
        
        $action = 'unliked';
    } else {
        // Додати лайк
        $stmt = $pdo->prepare("INSERT INTO commentlikes (comment_id, user_id) VALUES (?, ?)");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        
        // Оновити лайки
        $stmt = $pdo->prepare("UPDATE comments SET comments_likes = comments_likes + 1 WHERE id = ?");
        $stmt->execute([$commentId]);
        
        $action = 'liked';
    }

    // Отримати оновлені лайки
    $stmt = $pdo->prepare("SELECT comments_likes FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $likesCount = $stmt->fetchColumn();

    $pdo->commit();
    
    echo json_encode([
        'success' => true,
        'action' => $action,
        'likes_count' => $likesCount
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode([
        'error' => 'Помилка при оновленні лайків',
        'message' => $e->getMessage()
    ]);
}
