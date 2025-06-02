<?php
require_once '../config/db.php';
require_once __DIR__ . '/send_mail.php';
header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$login || !$password) {
        echo json_encode(['error' => 'Введіть логін і пароль.']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM superadmin WHERE login = ?');
    $stmt->execute([$login]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && password_verify($password, $admin['password_hash'])) {
        $verification_code = bin2hex(random_bytes(5));
        $verification_hash = password_hash($verification_code, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE superadmin SET verification_password_hash = ? WHERE id = ?');
        $stmt->execute([$verification_hash, $admin['id']]);
        $body_html = "<p>Ваш код для входу в адмін-панель: <b>$verification_code</b></p>";
        $result = send_custom_mail($admin['email'], $admin['login'], 'Код для входу в адмін-панель', $body_html);
        if ($result === true) {
            echo json_encode(['success' => 'Код відправлено на email.', 'admin_id' => $admin['id']]);
        } else {
            echo json_encode(['error' => 'Помилка при надсиланні листа: ' . $result]);
        }
    } else {
        echo json_encode(['error' => 'Невірний логін або пароль.']);
    }
    exit;
}

if ($action === 'verify') {
    $admin_id = $_POST['admin_id'] ?? null;
    $code = $_POST['code'] ?? '';
    if (!$admin_id || !$code) {
        echo json_encode(['error' => 'Введіть код підтвердження.']);
        exit;
    }
    $stmt = $pdo->prepare('SELECT * FROM superadmin WHERE id = ?');
    $stmt->execute([$admin_id]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($admin && $admin['verification_password_hash'] && password_verify($code, $admin['verification_password_hash'])) {
        $stmt = $pdo->prepare('UPDATE superadmin SET verification_password_hash = NULL WHERE id = ?');
        $stmt->execute([$admin['id']]);
        echo json_encode(['success' => 'Вхід успішний!']);
    } else {
        echo json_encode(['error' => 'Невірний код підтвердження.']);
    }
    exit;
}

echo json_encode(['error' => 'Невірна дія.']);
