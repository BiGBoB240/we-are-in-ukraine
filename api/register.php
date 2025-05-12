<?php
require_once '../config/db.php';
require_once '../includes/PHPMailer/src/PHPMailer.php';
require_once '../includes/PHPMailer/src/SMTP.php';
require_once '../includes/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

function isLatin($str) {
    return preg_match('/[a-zA-Z]/', $str);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

$username = $first_name;
if ($last_name !== '') {
    $username .= ' ' . $last_name;
}

if (!$email || !$first_name || !$password || !$password_confirm) {
    echo json_encode(['error' => 'Всі обов\'язкові поля мають бути заповнені.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['error' => 'Некоректний email.']);
    exit;
}
if (strlen($password) < 8 || !isLatin($password)) {
    echo json_encode(['error' => 'Пароль має бути не менше 8 символів і містити хоча б одну латинську букву.']);
    exit;
}
if ($password !== $password_confirm) {
    echo json_encode(['error' => 'Паролі не співпадають.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Користувач з таким email вже існує.']);
        exit;
    }
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO Users (username, email, password_hash, verificated, show_comments, created_at, verification_token) VALUES (?, ?, ?, 0, 1, NOW(), ?)");
    $stmt->execute([$username, $email, $hash, $token]);
    
    // Send verification email
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
    $mail->addAddress($email, $username);
    $mail->isHTML(true);
    $mail->Subject = 'Підтвердження реєстрації';
    $verifyLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/verify.php?token=' . $token;
    $mail->Body = "<p>Дякуємо за реєстрацію, $username!</p><p>Щоб підтвердити свій акаунт, перейдіть за <a href='$verifyLink'>цим посиланням</a>.</p>";
    $mail->send();
    
    echo json_encode(['success' => 'Реєстрація успішна! Перевірте email для підтвердження.']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Помилка при відправці email: ' . $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
}
