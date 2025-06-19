<?php
require_once '../config/db.php';
session_start();

$filter = $_GET['filter'] ?? 'date-new';

// Отримуємо ID профілю
if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'User ID not specified']);
    exit;
}
$profileUserId = (int)$_GET['id'];

// Отримуємо ID поточного користувача
$currentUserId = $_SESSION['user_id'] ?? null;

// Перевіряємо чи користувач адміністратор
$isAdmin = false;
if ($currentUserId) {
    $adminCheck = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
    $adminCheck->execute([$currentUserId]);
    $isAdmin = $adminCheck->fetchColumn() !== false;
}

// Заміняємо PHP8 match() на асоціативний масив для сумісності
$allowedFilters = [
    'date-new'    => 'c.created_at DESC',
    'date-old'    => 'c.created_at ASC',
    'rating-high' => 'c.comments_likes DESC, c.created_at DESC',
    'rating-low'  => 'c.comments_likes ASC, c.created_at DESC',
];
$orderBy = $allowedFilters[$filter] ?? 'c.created_at DESC';

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.user_id,
            c.comment_text,
            c.created_at,
            c.redacted,
            p.title as post_title,
            c.comments_likes,
            CASE WHEN cl.id IS NOT NULL THEN 1 ELSE 0 END as has_liked
        FROM comments c
        LEFT JOIN posts p ON c.post_id = p.id
        LEFT JOIN commentlikes cl ON c.id = cl.comment_id AND cl.user_id = :current_user_id
        WHERE c.user_id = :profile_user_id
        ORDER BY {$orderBy}
    ");
    $stmt->execute([
        ':current_user_id' => $currentUserId,
        ':profile_user_id' => $profileUserId
    ]);
    $comments = $stmt->fetchAll();

    // Форматуємо дані
    $formattedcomments = array_map(function($comment) use ($isAdmin, $currentUserId, $profileUserId) {
        return [
            'id' => $comment['id'],
            'user_id' => $comment['user_id'],
            'comment_text' => htmlspecialchars($comment['comment_text']),
            'created_at' => date('d.m.Y H:i', strtotime($comment['created_at'])),
            'is_edited' => (bool)$comment['redacted'],
            'post_title' => $comment['post_title'] ? htmlspecialchars($comment['post_title']) : 'Пост видалено',
            'likes_count' => $comment['comments_likes'],
            'has_liked' => (bool)$comment['has_liked'],
            'can_edit' => $isAdmin || ($currentUserId && $currentUserId == $comment['user_id']),
            'can_delete' => $isAdmin || ($currentUserId && $currentUserId == $comment['user_id'])
        ];
    }, $comments);

    echo json_encode(['comments' => $formattedcomments]);
} catch (PDOException $e) {
    echo json_encode([
        'error' => 'Помилка при отриманні коментарів',
        'message' => $e->getMessage()
    ]);
}
