<?php
//API для відновлення пароля
require_once '../config/db.php';
require_once __DIR__ . '/send_mail.php';

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
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();
if (!$user) {
    echo json_encode(['success' => 'На ваш email було відправлено лист для відновлення пароля.']);
    exit;
}
$token = bin2hex(random_bytes(16));
$stmt = $pdo->prepare("UPDATE users SET verification_token = ? WHERE id = ?");
$stmt->execute([$token, $user['id']]);

$resetLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/../reset_password.php?token=' . $token;
$body_html = "<p>Щоб скинути пароль, перейдіть за <a href='$resetLink'>цим посиланням</a>.</p>";
$result = send_custom_mail($email, $user['username'], 'Відновлення пароля', $body_html);
if ($result === true) {
    echo json_encode(['success' => 'На ваш email було відправлено лист для відновлення пароля.']);
} else {
    echo json_encode(['error' => 'Помилка при надсиланні листа: ' . $result]);
}
