<?php
require_once '../config/db.php';
require_once __DIR__ . '/send_mail.php'; // Unified mail helper

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
require_once __DIR__ . '/../utils/filter_bad_words.php';
$first_name = filter_bad_words(trim($_POST['first_name'] ?? ''));
$last_name = filter_bad_words(trim($_POST['last_name'] ?? ''));
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';


$username = $first_name;
if ($last_name !== '') {
    $username .= ' ' . $last_name;
}
$username = filter_bad_words($username);

if (!$email || !$first_name || !$password || !$password_confirm) {
    echo json_encode(['error' => 'Всі обов\'язкові поля мають бути заповнені.']);
    exit;
}

// Check for spaces in names
if (str_contains($first_name, ' ') || ($last_name && str_contains($last_name, ' '))) {
    echo json_encode(['error' => 'Ім\'я та прізвище не повинні містити пробілів.']);
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

// Проверка: заблокирован ли email
    $stmt = $pdo->prepare("SELECT 1 FROM blockedusers WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['error' => 'Цей email заблоковано для реєстрації.']);
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
    
        $verifyLink = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . '/verify.php?token=' . $token;
    $body_html = "<p>Дякуємо за реєстрацію, $username!</p><p>Щоб підтвердити свій акаунт, перейдіть за <a href='$verifyLink'>цим посиланням</a>.</p>";
    $result = send_custom_mail($email, $username, 'Підтвердження реєстрації', $body_html);
    if ($result === true) {
        echo json_encode(['success' => 'Реєстрація успішна! Перевірте email для підтвердження.']);
    } else {
        echo json_encode(['error' => 'Помилка при відправці email: ' . $result]);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Помилка при відправці email: ' . $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка бази даних: ' . $e->getMessage()]);
}
