<?php
require_once '../config/db.php';
require_once '../includes/PHPMailer/src/PHPMailer.php';
require_once '../includes/PHPMailer/src/SMTP.php';
require_once '../includes/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}
$email = trim($_POST['email'] ?? '');
if (!$email) {
    echo json_encode(['error' => 'Введіть email.']);
    exit;
}
$stmt = $pdo->prepare("SELECT id, username FROM Users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    echo json_encode(['success' => 'Якщо email існує, ви отримаєте лист для відновлення пароля.']);
    exit;
}
$token = bin2hex(random_bytes(16));
$stmt = $pdo->prepare("UPDATE Users SET verification_token = ? WHERE id = ?");
$stmt->execute([$token, $user['id']]);

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'mybloguasup77@gmail.com';
    $mail->Password = 'eehf lsbk yesl qxdu';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->setFrom('mybloguasup77@gmail.com', 'Ми в Україні');
    $mail->CharSet = 'UTF-8';
    $mail->Encoding = 'base64';
    $mail->addAddress($email, $user['username']);
    $mail->isHTML(true);
    $mail->Subject = 'Відновлення пароля';
    $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/../reset_password.php?token=' . $token;
    $mail->Body = "<p>Щоб скинути пароль, перейдіть за <a href='$resetLink'>цим посиланням</a>.</p>";
    $mail->send();
    echo json_encode(['success' => 'Якщо email існує, ви отримаєте лист для відновлення пароля.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Помилка при надсиланні листа: ' . $mail->ErrorInfo]);
}
