<?php
require_once '../config/db.php';
// PHPMailer
require_once __DIR__ . '/../includes/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/src/SMTP.php';

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

$stmt = $pdo->prepare("SELECT * FROM Administrations WHERE user_id = ? AND verificated = 1");
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
    $stmt = $pdo->prepare("SELECT * FROM Feedback WHERE id = ?");
    $stmt->execute([$feedbackId]);
    $feedback = $stmt->fetch();
    
    if (!$feedback) {
        http_response_code(404);
        echo json_encode(['error' => 'Feedback not found']);
        exit;
    }

    try {
        $mail = new PHPMailer(true);
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mybloguasup77@gmail.com';
        $mail->Password = 'eehf lsbk yesl qxdu';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
        // Sender and recipient
        $mail->setFrom('mybloguasup77@gmail.com', 'Ми в Україні');
        $mail->addAddress($feedback['email'] ?? 'recipient@example.com', $feedback['username'] ?? 'User');

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Ваше звернення оброблено';
        $mail->Body = "<p>Шановний(а) <b>" . htmlspecialchars($feedback['username'] ?? '') . "</b>,</p>"
            . "<p>Ваше звернення було успішно оброблено.<br>"
            . "Дякуємо за вашу співпрацю!</p>"
            . "<p>З повагою,<br>Команда Ми в Україні</p>";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: {$mail->ErrorInfo}");
        // Ошибка отправки письма залогирована, продолжаем выполнение
    }

    // Delete feedback
    $stmt = $pdo->prepare("DELETE FROM Feedback WHERE id = ?");
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
