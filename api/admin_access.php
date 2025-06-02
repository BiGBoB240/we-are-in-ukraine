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

// --- Вибірка всіх користувачів
if ($action === 'get_users') {
    $users = $pdo->query('SELECT id, username, email FROM users ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['users' => $users]);
    exit;
}
// --- Вибірка всіх адміністраторів
if ($action === 'get_admins') {
    $admins = $pdo->query('SELECT u.id, u.username, u.email FROM administrations a JOIN users u ON a.user_id = u.id ORDER BY u.id DESC')->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['admins' => $admins]);
    exit;
}
// --- Додати адміністратора
if ($action === 'add_admin') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if (!$user_id) { echo json_encode(['error' => 'Вкажіть ID користувача.']); exit; }
    // Перевірити чи вже є адміністратором
    $stmt = $pdo->prepare('SELECT id FROM administrations WHERE user_id = ?');
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) { echo json_encode(['error' => 'Користувач вже адміністратор.']); exit; }
    $stmt = $pdo->prepare('INSERT INTO administrations (user_id, verificated, verification_token) VALUES (?, 1, "")');
    $stmt->execute([$user_id]);
    echo json_encode(['success' => 'Додано доступ адміністраторa.']);
    exit;
}
// --- Видалити адміністратора
if ($action === 'remove_admin') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    if (!$user_id) { echo json_encode(['error' => 'Вкажіть ID користувача.']); exit; }
    $stmt = $pdo->prepare('DELETE FROM administrations WHERE user_id = ?');
    $stmt->execute([$user_id]);
    echo json_encode(['success' => 'Доступ адміністратора видалено.']);
    exit;
}

// --- Зміна паролю супер-адміна
if ($action === 'change_password') {
    header('Content-Type: application/json');
    $currentPassword = $_POST['currentPassword'] ?? '';
    $newPassword = $_POST['newPassword'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    if (!$currentPassword || !$newPassword || !$confirmPassword) {
        echo json_encode(['error' => 'Всі поля обовʼязкові.']); exit;
    }
    if ($newPassword !== $confirmPassword) {
        echo json_encode(['error' => 'Паролі не співпадають.']); exit;
    }
    // Беремо першого супер-адміна (LIMIT 1)
    $stmt = $pdo->query('SELECT * FROM superadmin LIMIT 1');
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$admin) {
        echo json_encode(['error' => 'Запис не знайдений.']); exit;
    }
    // Перевірка старого паролю
    if (!password_verify($currentPassword, $admin['password_hash'])) {
        echo json_encode(['error' => 'Невірний поточний пароль.']); exit;
    }
    // Оновлення паролю
    $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('UPDATE superadmin SET password_hash = ? WHERE id = ?');
    $stmt->execute([$newHash, $admin['id']]);
    echo json_encode(['success' => 'Пароль власника сайту змінено успішно.']);
    exit;
}

echo json_encode(['error' => 'Невірна дія.']);
