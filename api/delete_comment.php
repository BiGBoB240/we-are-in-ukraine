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
$adminCheck = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
$adminCheck->execute([$_SESSION['user_id']]);
$isAdmin = $adminCheck->fetchColumn() !== false;

$data = json_decode(file_get_contents('php://input'), true);
$commentId = $data['comment_id'] ?? null;

if (!$commentId) {
    echo json_encode(['error' => 'Неправильні параметри']);
    exit;
}

try {
    // Verify comment ownership or admin status
    if (!$isAdmin) {
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ? AND user_id = ?");
        $stmt->execute([$commentId, $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Коментар не знайдено або у вас немає прав для його видалення']);
            exit;
        }
    } else {
        // Admin just needs to verify comment exists
        $stmt = $pdo->prepare("SELECT id FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        if (!$stmt->fetch()) {
            echo json_encode(['error' => 'Коментар не знайдено']);
            exit;
        }
    }

    // Start transaction
    $pdo->beginTransaction();
    
    try {
        // Delete comment
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$commentId]);
        
        // Delete related likes
        $stmt = $pdo->prepare("DELETE FROM commentlikes WHERE comment_id = ?");
        $stmt->execute([$commentId]);
        
        // Delete related reports without sending email
        $stmt = $pdo->prepare("DELETE FROM reports WHERE content_id = ? AND content_type = 'comment'");
        $stmt->execute([$commentId]);
        
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE comment_id = ?");
        $stmt->execute([$commentId]);

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Коментар видалено']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['error' => 'Помилка при видаленні коментаря: ' . $e->getMessage()]);
    }
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при видаленні коментаря']);
}
