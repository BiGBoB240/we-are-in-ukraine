<?php
require_once '../config/db.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'date-new';
$limit = 10;
$offset = ($page - 1) * $limit;

// Prepare the ORDER BY clause based on filter
$orderBy = match($filter) {
    'date-new' => 'created_at DESC',
    'date-old' => 'created_at ASC',
    'rating-high' => 'post_likes DESC',
    'rating-low' => 'post_likes ASC',
    default => 'created_at DESC'
};

try {
    // Get posts
    $query = "SELECT p.*, u.username as author_name 
              FROM Posts p 
              LEFT JOIN Users u ON p.author_id = u.id 
              ORDER BY {$orderBy} 
              LIMIT :limit OFFSET :offset";
    
    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $posts = $stmt->fetchAll();

    // Count total posts for pagination
    $stmt = $pdo->query("SELECT COUNT(*) FROM Posts");
    $total = $stmt->fetchColumn();
    
    // Process posts content based on type
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
        
        // Remove unnecessary fields
        unset($post['picture1_path'], $post['picture2_path'], $post['picture3_path']);
    }
    
    echo json_encode([
        'posts' => $posts,
        'hasMore' => ($offset + $limit) < $total
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
