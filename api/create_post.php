<?php
//API для створення посту
require_once '../config/db.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Необхідно увійти']);
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT 1 FROM administrations WHERE user_id = ? AND verificated = 1');
$stmt->execute([$user_id]);
if ($stmt->fetchColumn() === false) {
    echo json_encode(['error' => 'Доступ заборонено']);
    exit;
}

$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
if ($title === '' || $content === '') {
    echo json_encode(['error' => 'Всі поля обовʼязкові']);
    exit;
}

$uploads_dir = '../assets/upload';
$image_fields = ['image1', 'image2', 'image3'];
$image_names = [];

foreach ($image_fields as $field) {
    if (!empty($_FILES[$field]['name'])) {
        $ext = pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION);
        $basename = uniqid('img_', true) . '.' . $ext;
        $target = $uploads_dir . '/' . $basename;
        if (move_uploaded_file($_FILES[$field]['tmp_name'], $target)) {
            $image_names[] = $basename;
        } else {
            $image_names[] = null;
        }
    } else {
        $image_names[] = null;
    }
}

try {
    $stmt = $pdo->prepare('INSERT INTO posts (author_id, title, content, picture1_path, picture2_path, picture3_path, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
    $stmt->execute([
        $user_id,
        $title,
        $content,
        $image_names[0],
        $image_names[1],
        $image_names[2]
    ]);
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['error' => 'DB error: ' . $e->getMessage()]);
}
