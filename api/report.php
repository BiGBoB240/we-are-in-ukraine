<?php
require_once '../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Ви повинні бути авторизовані']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$contentId = $data['content_id'] ?? null;
$contentType = $data['content_type'] ?? null;

if (!$contentId || !in_array($contentType, ['post', 'comment', 'user'])) {
    echo json_encode(['error' => 'Неправильні параметри']);
    exit;
}

$userId = $_SESSION['user_id'];

// Check if report already exists
$stmt = $pdo->prepare('SELECT id FROM Reports WHERE content_id = ? AND content_type = ? AND reported_by_id = ?');
$stmt->execute([$contentId, $contentType, $userId]);
if ($stmt->fetch()) {
    echo json_encode(['error' => 'Ви вже залишали скаргу на цей елемент']);
    exit;
}

// Insert report
$stmt = $pdo->prepare('INSERT INTO Reports (content_id, content_type, reported_by_id) VALUES (?, ?, ?)');
try {
    $stmt->execute([$contentId, $contentType, $userId]);
    echo json_encode(['success' => 'Скаргу надіслано']);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при надсиланні скарги']);
}
