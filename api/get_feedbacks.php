<?php
//API для отримання feedback
require_once '../config/db.php';
session_start();

header('Content-Type: application/json');

// Перевіряємо чи користувач увійшов і адміністратор
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM administrations WHERE user_id = ? AND verificated = 1");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->rowCount() === 0) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

try {
    // Отримуємо всі feedback
    $stmt = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC");
    $feedbacks = $stmt->fetchAll();
    
    // Форматуємо дати
    foreach ($feedbacks as &$feedback) {
        $feedback['created_at'] = date('d.m.Y H:i', strtotime($feedback['created_at']));
    }
    
    echo json_encode(['feedbacks' => $feedbacks]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
