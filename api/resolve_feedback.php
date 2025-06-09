<?php
require_once '../config/db.php';
// PHPMailer
require_once __DIR__ . '/send_mail.php'; // Unified mail helper

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;
session_start();

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if user is admin
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

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$feedbackId = $data['id'] ?? null;

if (!$feedbackId) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    // Get feedback info before deletion
    $stmt = $pdo->prepare("SELECT * FROM feedback WHERE id = ?");
    $stmt->execute([$feedbackId]);
    $feedback = $stmt->fetch();
    
    if (!$feedback) {
        http_response_code(404);
        echo json_encode(['error' => 'feedback not found']);
        exit;
    }

    $body_html = "<p>Шановний(а) <b>" . htmlspecialchars($feedback['username'] ?? '') . "</b>,</p>"
        . "<p>Ваше звернення було успішно оброблено.<br>"
        . "Дякуємо за вашу співпрацю!</p>"
        . "<p>З повагою,<br>Команда Ми в Україні</p>";
    send_custom_mail($feedback['email'] ?? 'recipient@example.com', $feedback['username'] ?? 'User', 'Ваше звернення оброблено', $body_html);

    // Delete feedback
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$feedbackId]);
    
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    error_log("DB Error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'message' => $e->getMessage()]);
} catch (\Exception $e) {
    error_log("General Error: {$e->getMessage()}");
    http_response_code(500);
    echo json_encode(['error' => 'Internal error', 'message' => $e->getMessage()]);
}
