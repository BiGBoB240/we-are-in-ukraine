<?php
require_once '../config/db.php';
session_start();

header('Content-Type: application/json');

// Перевірка авторизації
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Ви не авторизовані']);
    exit;
}

// Перевірка даних
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['post_id']) || !is_numeric($data['post_id'])) {
    echo json_encode(['error' => 'Некоректний ID поста']);
    exit;
}

$postId = (int)$data['post_id'];
$userId = (int)$_SESSION['user_id'];

try {
    // Перевірка, чи є користувач автором поста
    $stmt = $pdo->prepare('SELECT author_id FROM Posts WHERE id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();
    
    if (!$post) {
        echo json_encode(['error' => 'Пост не знайдено']);
        exit;
    }
    
    // Перевірка прав доступу
    if ($post['author_id'] !== $userId) {
        // Перевірка, чи є користувач адміністратором
        $stmt = $pdo->prepare('SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1');
        $stmt->execute([$userId]);
        if (!$stmt->fetchColumn()) {
            echo json_encode(['error' => 'Ви не маєте прав для видалення цього поста']);
            exit;
        }
    }

    // Отримуємо шляхи до зображень для видалення
    $stmt = $pdo->prepare('SELECT picture1_path, picture2_path, picture3_path FROM Posts WHERE id = ?');
    $stmt->execute([$postId]);
    $images = $stmt->fetch();

    // Видаляємо зображення з файлової системи
    $uploadDir = __DIR__ . '/../assets/upload/';
    foreach ($images as $imagePath) {
        if ($imagePath) {
            $fullPath = $uploadDir . $imagePath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    // Видалення поста та пов'язаних даних
    $pdo->beginTransaction();
    
    try {
        // Видалення лайків поста
        $stmt = $pdo->prepare('DELETE FROM PostLikes WHERE post_id = ?');
        $stmt->execute([$postId]);
        
        // Видалення коментарів
        $stmt = $pdo->prepare('DELETE FROM Comments WHERE post_id = ?');
        $stmt->execute([$postId]);
        
        // Видалення лайків коментарів
        $stmt = $pdo->prepare('DELETE FROM CommentLikes WHERE comment_id IN (SELECT id FROM Comments WHERE post_id = ?)');
        $stmt->execute([$postId]);
        
        // Видалення звітів про пост
        $stmt = $pdo->prepare('DELETE FROM Reports WHERE content_id = ? AND content_type = ?');
        $stmt->execute([$postId, 'post']);
        
        // Видалення звітів про коментарі до цього поста
        $stmt = $pdo->prepare('DELETE FROM Reports WHERE content_id IN (SELECT id FROM Comments WHERE post_id = ?) AND content_type = ?');
        $stmt->execute([$postId, 'comment']);
        
        // Видалення поста
        $stmt = $pdo->prepare('DELETE FROM Posts WHERE id = ?');
        $stmt->execute([$postId]);
        
        $pdo->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Помилка при видаленні поста: ' . $e->getMessage()]);
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Помилка при видаленні поста: ' . $e->getMessage()]);
}
