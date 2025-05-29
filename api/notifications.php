<?php
require_once '../config/db.php';
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$userId = $_SESSION['user_id'];
$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'get') {
    // Получить уведомления пользователя
    $stmt = $pdo->prepare("SELECT n.id, n.sender_user_id, n.post_id, n.comment_id, n.is_read, c.comment_text, u.username as sender_username
        FROM notifications n
        LEFT JOIN Comments c ON n.comment_id = c.id
        LEFT JOIN Users u ON n.sender_user_id = u.id
        WHERE n.recipient_user_id = ?
        ORDER BY n.id DESC LIMIT 50");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['success' => true, 'notifications' => $notifications]);
    exit;
} elseif ($action === 'mark_all_read') {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE recipient_user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);
    exit;
} elseif ($action === 'mark_one_read') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notifId = isset($input['id']) ? (int)$input['id'] : 0;
    if ($notifId) {
        $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE id = ? AND recipient_user_id = ?");
        $stmt->execute([$notifId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['error' => 'Invalid notification id']);
        exit;
    }
} elseif ($action === 'delete_one') {
    $input = json_decode(file_get_contents('php://input'), true);
    $notifId = isset($input['id']) ? (int)$input['id'] : 0;
    if ($notifId) {
        $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND recipient_user_id = ?");
        $stmt->execute([$notifId, $userId]);
        echo json_encode(['success' => true]);
        exit;
    } else {
        echo json_encode(['error' => 'Invalid notification id']);
        exit;
    }
} elseif ($action === 'delete_all') {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE recipient_user_id = ?");
    $stmt->execute([$userId]);
    echo json_encode(['success' => true]);
    exit;
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid action']);
    exit;
}
