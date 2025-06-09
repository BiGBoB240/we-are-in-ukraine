<?php
require_once '../config/db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check admin rights (optional, but recommended)
$stmt = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetchColumn()) {
    echo json_encode(['error' => 'Access denied']);
    exit;
}

// Отримуємо всі скарги
$sql = "SELECT * FROM reports ORDER BY created_at DESC";
$stmt = $pdo->query($sql);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($reports as &$report) {
    $report['type'] = $report['content_type']; // для зручності на фронті
    if ($report['content_type'] === 'post') {
        $stmt2 = $pdo->prepare("SELECT id, title, created_at FROM posts WHERE id = ?");
        $stmt2->execute([$report['content_id']]);
        $report['post'] = $stmt2->fetch(PDO::FETCH_ASSOC);
    }
    if ($report['content_type'] === 'user') {
        $stmt2 = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
        $stmt2->execute([$report['content_id']]);
        $report['user'] = $stmt2->fetch(PDO::FETCH_ASSOC);
    }
    if ($report['content_type'] === 'comment') {
        // Підвантажуємо розширену інформацію про коментар
        $stmt2 = $pdo->prepare("SELECT c.*, u.username, p.title as post_title,
            (SELECT COUNT(*) FROM commentlikes cl WHERE cl.comment_id = c.id) as likes_count
            FROM comments c
            LEFT JOIN users u ON c.user_id = u.id
            LEFT JOIN posts p ON c.post_id = p.id
            WHERE c.id = ?");
        $stmt2->execute([$report['content_id']]);
        $comment = $stmt2->fetch(PDO::FETCH_ASSOC);
        if ($comment) {
            $currentUserId = $_SESSION['user_id'] ?? null;
            // Чи лайкнув цей користувач
            $hasLiked = false;
            if ($currentUserId) {
                $likeStmt = $pdo->prepare('SELECT 1 FROM commentlikes WHERE comment_id = ? AND user_id = ?');
                $likeStmt->execute([$comment['id'], $currentUserId]);
                $hasLiked = $likeStmt->fetchColumn() !== false;
            }
            // Чи може редагувати/видаляти
            $isAdmin = false;
            if ($currentUserId) {
                $adminCheck = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
                $adminCheck->execute([$currentUserId]);
                $isAdmin = $adminCheck->fetchColumn() !== false;
            }
            $canEdit = $isAdmin || ($currentUserId && $currentUserId == $comment['user_id']);
            $canDelete = $canEdit;
            $report['comment'] = [
                'id' => $comment['id'],
                'user_id' => $comment['user_id'],
                'username' => $comment['username'],
                'comment_text' => htmlspecialchars($comment['comment_text']),
                'created_at' => date('d.m.Y H:i', strtotime($comment['created_at'])),
                'is_edited' => (bool)$comment['redacted'],
                'post_title' => $comment['post_title'] ? htmlspecialchars($comment['post_title']) : 'Пост видалено',
                'likes_count' => (int)$comment['likes_count'],
                'has_liked' => (bool)$hasLiked,
                'can_edit' => $canEdit,
                'can_delete' => $canDelete
            ];
        } else {
            $report['comment'] = null;
        }
    }
}
unset($report);
echo json_encode(['reports' => $reports]);
