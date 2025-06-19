<?php
require_once '../config/db.php';
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Ви не авторизовані']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int)$data['post_id'] : 0;
if (!$postId) {
    echo json_encode(['error' => 'Некоректний запит']);
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM postlikes WHERE post_id = ? AND user_id = ?");
    $stmt->execute([$postId, $_SESSION['user_id']]);
    $existingLike = $stmt->fetch();

    if ($existingLike) {
        // Видаляємо лайк
        $stmt = $pdo->prepare("DELETE FROM postlikes WHERE post_id = ? AND user_id = ?");
        $stmt->execute([$postId, $_SESSION['user_id']]);
        // Оновлюємо лайки
        $stmt = $pdo->prepare("UPDATE posts SET post_likes = post_likes - 1 WHERE id = ?");
        $stmt->execute([$postId]);
        $action = 'unliked';
    } else {
        // Додаємо лайк
        $stmt = $pdo->prepare("INSERT INTO postlikes (post_id, user_id) VALUES (?, ?)");
        $stmt->execute([$postId, $_SESSION['user_id']]);
        // Оновлюємо лайки
        $stmt = $pdo->prepare("UPDATE posts SET post_likes = post_likes + 1 WHERE id = ?");
        $stmt->execute([$postId]);
        $action = 'liked';
    }

    // Отримуємо оновлені лайки
    $stmt = $pdo->prepare("SELECT post_likes FROM posts WHERE id = ?");
    $stmt->execute([$postId]);
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
