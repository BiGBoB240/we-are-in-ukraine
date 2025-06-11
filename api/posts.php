<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');
$userId = $_SESSION['user_id'] ?? null; // або як у вас визначається користувач
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'date-new';
$limit = 1;
$offset = ($page - 1) * $limit;

// Prepare the ORDER BY clause based on filter
switch ($filter) {
    case 'date-new':
        $orderBy = 'created_at DESC';
        break;
    case 'date-old':
        $orderBy = 'created_at ASC';
        break;
    case 'rating-high':
        $orderBy = 'post_likes DESC';
        break;
    case 'rating-low':
        $orderBy = 'post_likes ASC';
        break;
    default:
        $orderBy = 'created_at DESC';
}

try {
    // Get posts
    $query = "SELECT p.*, u.username as author_name 
              FROM posts p 
              LEFT JOIN users u ON p.author_id = u.id 
              ORDER BY {$orderBy} 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Count total posts for pagination
    $stmt = $pdo->query("SELECT COUNT(*) FROM posts");
    $total = $stmt->fetchColumn();
    
    // Process posts content based on type
    $postIds = [];
    foreach ($posts as &$post) {
        $hasImages = !empty($post['picture1_path']) || !empty($post['picture2_path']) || !empty($post['picture3_path']);
        
        // Get images array (always 3 elements, even if some are empty)
        $post['images'] = [
            $post['picture1_path'],
            $post['picture2_path'],
            $post['picture3_path']
        ];
        
        // Truncate content based on type
        if ($hasImages && !empty($post['content'])) {
            $post['content'] = mb_substr($post['content'], 0, 150) . (mb_strlen($post['content']) > 150 ? '...' : '');
        } elseif (!$hasImages && !empty($post['content'])) {
            $post['content'] = mb_substr($post['content'], 0, 300) . (mb_strlen($post['content']) > 300 ? '...' : '');
        }
        
        // Format date
        $post['created_at'] = date('d.m.Y H:i', strtotime($post['created_at']));
        
        // Collect post IDs for like check
        $postIds[] = $post['id'];
        // Remove unnecessary fields
        unset($post['picture1_path'], $post['picture2_path'], $post['picture3_path']);
    }

    // --- Додаємо has_liked для кожного поста ---
    $likedPostIds = [];
    if ($userId && count($postIds) > 0) {
        $in = implode(',', array_fill(0, count($postIds), '?'));
        $params = $postIds;
        array_unshift($params, $userId); // userId має бути першим параметром
        $sql = "SELECT post_id FROM postlikes WHERE user_id = ? AND post_id IN ($in)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $likedPostIds = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
    }

    

    foreach ($posts as &$post) {
        $post['has_liked'] = $userId && in_array($post['id'], $likedPostIds);
    }

    echo json_encode([
        'posts' => $posts,
        'hasMore' => ($offset + $limit) < $total
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
