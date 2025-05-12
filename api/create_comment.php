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
    $adminCheck = $pdo->prepare('SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1');
    $adminCheck->execute([$currentUserId]);
    $isAdmin = $adminCheck->fetchColumn() !== false;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId = isset($data['post_id']) ? (int)$data['post_id'] : null;
$text = trim($data['comment_text'] ?? '');

if (!$postId || $text === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO Comments (post_id, user_id, comment_text) VALUES (?, ?, ?)");
    $stmt->execute([$postId, $currentUserId, $text]);
    $commentId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("SELECT c.id, c.comment_text, c.created_at, c.comments_likes, u.username, c.user_id
        FROM Comments c 
        LEFT JOIN Users u ON c.user_id = u.id
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
