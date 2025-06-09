<?php
require_once '../config/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Validate input
require_once __DIR__ . '/../utils/filter_bad_words.php';
$username = trim($_POST['username'] ?? '');
$username = filter_bad_words($username);
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$phone = trim($_POST['phone'] ?? '');
$message = trim($_POST['message'] ?? '');
$message = filter_bad_words($message);

if (empty($username) || empty($email) || empty($phone) || empty($message)) {
    http_response_code(400);
    echo json_encode(['error' => 'Всі поля обов\'язкові для заповнення']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO feedback (username, email, phone_number, feedback_text) VALUES (?, ?, ?, ?)");
    $stmt->execute([$username, $email, $phone, $message]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Помилка при збереженні звернення']);
}
