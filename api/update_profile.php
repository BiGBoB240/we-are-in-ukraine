<?php
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return $needle !== '' && mb_strpos($haystack, $needle) !== false;
    }
}
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

require_once __DIR__ . '/../utils/filter_bad_words.php';
$firstName = filter_bad_words(trim($_POST['firstName'] ?? ''));
$lastName = filter_bad_words(trim($_POST['lastName'] ?? ''));
$userIdToUpdate = $_SESSION['user_id'];

if (isset($_POST['user_id']) && $_POST['user_id'] != $_SESSION['user_id']) {
    // Перевірка: якщо адмін, можна змінювати ім'я іншого користувача
    $stmtAdmin = $pdo->prepare("SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1");
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

// Перевірка на пробіли в імені та прізвищі
if (str_contains($firstName, ' ') || ($lastName && str_contains($lastName, ' '))) {
    echo json_encode(['error' => 'Ім\'я та прізвище не повинні містити пробілів.']);
    exit;
}

// Комбінування ім'я та прізвища
$username = $firstName;
if (!empty($lastName)) {
    $username .= ' ' . $lastName;
}
$username = filter_bad_words($username);

try {
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$username, $userIdToUpdate]);
    
    echo json_encode(['success' => "Ім'я успішно оновлено"]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Помилка при оновленні профілю']);
}
