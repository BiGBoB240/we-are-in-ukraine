<?php
// API для закриття скарги: надсилання листів і видалення скарг за content_id
header('Content-Type: application/json');
require_once '../config/db.php';
require_once __DIR__ . '/send_mail.php'; // Unified mail helper

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$content_id = isset($_POST['content_id']) ? intval($_POST['content_id']) : 0;
$content_type = isset($_POST['content_type']) ? $_POST['content_type'] : '';
if (!$content_id || !$content_type) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing content_id or content_type']);
    exit;
}

// 1. Знаходимо всі скарги на цей елемент
$stmt = $pdo->prepare('SELECT r.reported_by_id, u.email FROM Reports r JOIN Users u ON r.reported_by_id = u.id WHERE r.content_id = ? AND r.content_type = ?');
$stmt->execute([$content_id, $content_type]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 2. Відправляємо листи через PHPMailer
$subject = 'Ваше повідомлення було розглянуто';
$message = 'Ваше повідомлення було розглянуто, дякуємо що допомагаєте стати кращими!';
foreach ($users as $user) {
    if (!empty($user['email'])) {
        $body_html = "<p>Доброго дня!</p><p>$message</p><p>З повагою,<br>Команда Ми в Україні</p>";
        send_custom_mail($user['email'], $user['email'], $subject, $body_html);
    }
}

// 3. Видаляємо всі скарги на цей елемент
$stmt = $pdo->prepare('DELETE FROM Reports WHERE content_id = ? AND content_type = ?');
$stmt->execute([$content_id, $content_type]);

http_response_code(200);
echo json_encode(['success' => true]);
