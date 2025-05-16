<?php
require_once '../config/db.php';
session_start();

header('Content-Type: application/json');

// Get current user id (if logged in)
$currentUserId = $_SESSION['user_id'] ?? null;

// Check if current user is admin
$isAdmin = false;
if ($currentUserId) {
    $adminCheck = $pdo->prepare('SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1');
    $adminCheck->execute([$currentUserId]);
    $isAdmin = $adminCheck->fetchColumn() !== false;
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Post ID is required']);
    exit;
}

$postId = (int)$_GET['id'];

try {
    // Get post with author info
    $query = "SELECT p.*, u.username as author_name 
              FROM Posts p 
              LEFT JOIN Users u ON p.author_id = u.id 
              WHERE p.id = :id";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':id', $postId, PDO::PARAM_INT);
    $stmt->execute();
    $post = $stmt->fetch();

    if (!$post) {
        http_response_code(404);
        echo json_encode(['error' => 'Post not found']);
        exit;
    }

    // Get post images
    $post['images'] = [
        $post['picture1_path'],
        $post['picture2_path'],
        $post['picture3_path']
    ];
    unset($post['picture1_path'], $post['picture2_path'], $post['picture3_path']);

    // Определяем, лайкал ли пользователь этот пост
    $post['has_liked'] = false;
    if ($currentUserId) {
        $likeStmt = $pdo->prepare('SELECT 1 FROM PostLikes WHERE post_id = ? AND user_id = ?');
        $likeStmt->execute([$postId, $currentUserId]);
        $post['has_liked'] = $likeStmt->fetchColumn() !== false;
    }



    // Get comments for the post
    $query = "SELECT c.*, u.username,
              CASE WHEN cl.id IS NOT NULL THEN 1 ELSE 0 END as has_liked
              FROM Comments c 
              LEFT JOIN Users u ON c.user_id = u.id 
              LEFT JOIN CommentLikes cl ON c.id = cl.comment_id AND cl.user_id = :current_user_id
              WHERE c.post_id = :post_id 
              ORDER BY c.created_at DESC";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':post_id', $postId, PDO::PARAM_INT);
    $stmt->bindValue(':current_user_id', $currentUserId, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll();

    // Format dates and add edit status for comments
    foreach ($comments as &$comment) {
        $comment['created_at'] = date('d.m.Y H:i', strtotime($comment['created_at']));
        $comment['is_edited'] = (bool)$comment['redacted'];
        $comment['can_edit'] = $isAdmin || ($currentUserId && $currentUserId == $comment['user_id']);
        $comment['can_delete'] = $isAdmin || ($currentUserId && $currentUserId == $comment['user_id']);
        $comment['has_liked'] = (bool)$comment['has_liked'];
        $comment['likes_count'] = (int)$comment['comments_likes'];
    }

    $post['comments'] = $comments;
    $post['created_at'] = date('d.m.Y H:i', strtotime($post['created_at']));

    echo json_encode($post);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
