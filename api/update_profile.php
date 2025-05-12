<?php
require_once '../config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$firstName = trim($_POST['firstName'] ?? '');
$lastName = trim($_POST['lastName'] ?? '');
$userIdToUpdate = $_SESSION['user_id'];

if (isset($_POST['user_id']) && $_POST['user_id'] != $_SESSION['user_id']) {
    // Проверка: если админ, можно менять имя другого пользователя
    $stmtAdmin = $pdo->prepare("SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1");
    $stmtAdmin->execute([$_SESSION['user_id']]);
    if ($stmtAdmin->fetch()) {
        $userIdToUpdate = (int)$_POST['user_id'];
    } else {
        echo json_encode(['error' => 'Недостатньо прав']);
        exit;
    }
}

if (empty($firstName)) {
    echo json_encode(['error' => "Ім'я обов'язкове"]);
    exit;
}

// Combine first name and last name
$username = $firstName;
if (!empty($lastName)) {
    $username .= ' ' . $lastName;
}

try {
    $stmt = $pdo->prepare("UPDATE Users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $userIdToUpdate]);
    
    echo json_encode(['success' => "Ім'я успішно оновлено"]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при оновленні профілю']);
}
