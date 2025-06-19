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

// Перевірка: якщо адмін, можна редагувати коментарі
$isAdmin = false;
$adminCheck = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
$adminCheck->execute([$_SESSION['user_id']]);
$isAdmin = $adminCheck->fetchColumn() !== false;

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? null;
require_once __DIR__ . '/../utils/filter_bad_words.php';
$text = trim($data['text'] ?? '');
$text = filter_bad_words($text);

if (!$commentId || empty($text)) {
    echo json_encode(['error' => 'Неправильні параметри']);
    exit;
}

if (strlen($text) > 300) {
    echo json_encode(['error' => 'Коментар не може бути довшим за 300 символів']);
    exit;
}

try {
    // Перевірка: якщо адмін, можна редагувати коментарі
    if (!$isAdmin) {
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Коментар не знайдено або у вас немає прав для його редагування']);
            exit;
        }
    } else {
        // Перевірка: якщо адмін, можна редагувати коментарі
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Коментар не знайдено']);
            exit;
        }
    }

    if ($isAdmin) {
        // Перевірка: якщо адмін, можна редагувати коментарі
        $stmt = $pdo->prepare("
            UPDATE comments 
            SET comment_text = ?
            WHERE id = ?
        ");
        $stmt->execute([$text, $commentId]);
    } else {
        $stmt = $pdo->prepare("
            UPDATE comments 
            SET comment_text = ?, 
                redacted = 1
            WHERE id = ?
        ");
        $stmt->execute([$text, $commentId]);

        // Видаляємо всі лайки для цього коментаря
        $delStmt = $pdo->prepare("DELETE FROM commentlikes WHERE comment_id = ?");
        $delStmt->execute([$commentId]);

        // Перевірка: якщо адмін, можна редагувати коментарі
        $resetLikes = $pdo->prepare("UPDATE comments SET comments_likes = 0 WHERE id = ?");
        $resetLikes->execute([$commentId]);
    }

    // Отримуємо post_id для оновленого коментаря
    $stmt = $pdo->prepare("SELECT post_id FROM comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $postId = $stmt->fetchColumn();

    echo json_encode(['success' => 'Коментар оновлено', 'post_id' => $postId]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при оновленні коментаря']);
}
