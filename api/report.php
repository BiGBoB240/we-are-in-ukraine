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
$stmt = $pdo->prepare('SELECT id FROM reports WHERE content_id = ? AND content_type = ? AND reported_by_id = ?');
$stmt->execute([$contentId, $contentType, $userId]);
if ($stmt->fetch()) {
    if ($contentType === 'post') {
        echo json_encode(['error' => 'Ви вже залишали повідомлення']);
    } else {
        echo json_encode(['error' => 'Ви вже залишали скаргу']);
    }
    exit;
}

// Insert report
$stmt = $pdo->prepare('INSERT INTO reports (content_id, content_type, reported_by_id) VALUES (?, ?, ?)');
try {
    $stmt->execute([$contentId, $contentType, $userId]);
    if ($contentType === 'post') {
        echo json_encode(['success' => 'Повідомлення надіслано']);
    } else {
        echo json_encode(['success' => 'Скаргу надіслано']);
    }
} catch (PDOException $e) {
    if ($contentType === 'post') {
        echo json_encode(['error' => 'Помилка при надсиланні повідомлення']);
    } else {
        echo json_encode(['error' => 'Помилка при надсиланні скарги']);
    }
}
