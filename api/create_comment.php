<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get current user id (if logged in)
$currentUserId = $_SESSION['user_id'] ?? null;

if (!$currentUserId) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if current user is admin
$isAdmin = false;
if ($currentUserId) {
    $adminCheck = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
    $adminCheck->execute([$currentUserId]);
    $isAdmin = $adminCheck->fetchColumn() !== false;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int)$data['post_id'] : null;
$replyTo = isset($data['reply_to']) ? (int)$data['reply_to'] : null;
require_once __DIR__ . '/../utils/filter_bad_words.php';
$text = trim($data['comment_text'] ?? '');
$text = filter_bad_words($text);

if (!$postId || $text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $currentUserId, $text]);
    $commentId = $pdo->lastInsertId();

    // Если это reply, создаём уведомление
    if ($replyTo) {
        // Получить user_id родительского комментария
        $stmtReply = $pdo->prepare("SELECT user_id FROM comments WHERE id = ?");
        $stmtReply->execute([$replyTo]);
        $parentUserId = $stmtReply->fetchColumn();
        if ($parentUserId && $parentUserId != $currentUserId) { // не уведомлять себя
            $stmtNotif = $pdo->prepare("INSERT INTO notifications (recipient_user_id, sender_user_id, post_id, comment_id, is_read) VALUES (?, ?, ?, ?, 0)");
            $stmtNotif->execute([$parentUserId, $currentUserId, $postId, $commentId]);
        }
    }

    $stmt = $pdo->prepare("SELECT c.id, c.comment_text, c.created_at, c.comments_likes, u.username, c.user_id
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch();
    if ($comment) {
        $comment['created_at'] = date('d.m.Y H:i', strtotime($comment['created_at']));
        $comment['can_edit'] = $isAdmin || ($currentUserId == $comment['user_id']);
        $comment['can_delete'] = $isAdmin || ($currentUserId == $comment['user_id']);
        $comment['is_edited'] = false;
        $comment['comments_likes'] = (int)$comment['comments_likes'];
        echo json_encode(['success' => true, 'comment' => $comment]);
    } else {
        echo json_encode(['error' => 'Comment created but cannot fetch']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
}
