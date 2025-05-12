<?php
// API для оновлення поста
require_once '../config/db.php';
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Invalid request method']);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Необхідно увійти']);
    exit;
}

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$remaining_images = json_decode($_POST['remaining_images'] ?? '[]', true);

if (!$post_id || !$title || !$content) {
    echo json_encode(['error' => 'Всі поля обовʼязкові']);
    exit;
}

// Отримуємо старі дані поста
$stmt = $pdo->prepare('SELECT * FROM Posts WHERE id = ?');
$stmt->execute([$post_id]);
$post = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$post) {
    echo json_encode(['error' => 'Пост не знайдено']);
    exit;
}

// НОВИЙ КОД ДЛЯ ЗОБРАЖЕНЬ
$uploadDir = '../assets/upload/';
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
$updateFields = [];
$params = [$title, $content];

// Обробка кожного зображення
for ($i = 1; $i <= 3; $i++) {
    $fileField = 'image'.$i;
    
    if (!empty($_FILES[$fileField]['tmp_name'])) {
        // Перевірка типу файлу
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES[$fileField]['tmp_name']);
        
        if (in_array($mime, $allowedTypes)) {
            $fileName = uniqid('img_').'.'.pathinfo($_FILES[$fileField]['name'], PATHINFO_EXTENSION);
            move_uploaded_file($_FILES[$fileField]['tmp_name'], $uploadDir.$fileName);
            $updateFields[] = "picture{$i}_path = ?";
            $params[] = $fileName;
        }
    } elseif (isset($_POST['remove_image'.$i])) {
        // Видаляємо зображення з файлової системи
        $existingFile = $post["picture{$i}_path"];
        if ($existingFile) {
            $fullPath = $uploadDir . $existingFile;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        // Видалення зображення з бази даних
        $updateFields[] = "picture{$i}_path = NULL";
        // НЕ додаємо у $params
    } else {
        // Беремо лише ім'я файлу з бази даних
        $existingFile = $post["picture{$i}_path"];
        $fileName = basename($existingFile);
        $updateFields[] = "picture{$i}_path = ?";
        $params[] = $fileName;
    }
}

// Оновлюємо пост
$update_sql = 'UPDATE Posts SET title=?, content=?'.(count($updateFields) ? ', '.implode(', ', $updateFields) : '').' WHERE id=?';
$stmt = $pdo->prepare($update_sql);
$res = $stmt->execute(array_merge($params, [$post_id]));

if ($res) {
    echo json_encode(['success' => 'Пост оновлено']);
} else {
    echo json_encode(['error' => 'Помилка при оновленні поста']);
}
