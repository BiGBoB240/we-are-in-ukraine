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

// Check if user is admin
$isAdmin = false;
$adminCheck = $pdo->prepare('SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1');
$adminCheck->execute([$_SESSION['user_id']]);
$isAdmin = $adminCheck->fetchColumn() !== false;

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? null;
$text = trim($data['text'] ?? '');

if (!$commentId || empty($text)) {
    echo json_encode(['error' => 'Неправильні параметри']);
    exit;
}

if (strlen($text) > 300) {
    echo json_encode(['error' => 'Коментар не може бути довшим за 300 символів']);
    exit;
}

try {
    // Verify comment ownership or admin status
    if (!$isAdmin) {
        $stmt = $pdo->prepare("SELECT id FROM Comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Коментар не знайдено або у вас немає прав для його редагування']);
            exit;
        }
    } else {
        // Admin just needs to verify comment exists
        $stmt = $pdo->prepare("SELECT id FROM Comments WHERE id = ?");
        $stmt->execute([$commentId]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Коментар не знайдено']);
            exit;
        }
    }

    if ($isAdmin) {
        // Admin: just update text, do not set redacted, do not reset likes
        $stmt = $pdo->prepare("
            UPDATE Comments 
            SET comment_text = ?
            WHERE id = ?
        ");
        $stmt->execute([$text, $commentId]);
    } else {
        // User: update text, set redacted, reset likes
        $stmt = $pdo->prepare("
            UPDATE Comments 
            SET comment_text = ?, 
                redacted = 1
            WHERE id = ?
        ");
        $stmt->execute([$text, $commentId]);

        // Remove all likes for this comment
        $delStmt = $pdo->prepare("DELETE FROM CommentLikes WHERE comment_id = ?");
        $delStmt->execute([$commentId]);

        // Reset comments_likes counter in Comments table
        $resetLikes = $pdo->prepare("UPDATE Comments SET comments_likes = 0 WHERE id = ?");
        $resetLikes->execute([$commentId]);
    }

    // Get post_id for the updated comment
    $stmt = $pdo->prepare("SELECT post_id FROM Comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $postId = $stmt->fetchColumn();

    echo json_encode(['success' => 'Коментар оновлено', 'post_id' => $postId]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при оновленні коментаря']);
}
