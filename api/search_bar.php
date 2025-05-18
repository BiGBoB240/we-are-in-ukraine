<?php
require_once '../config/db.php';
header('Content-Type: application/json; charset=utf-8');

$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$results = [];

if ($query !== '') {
    // Найти посты по title
    $stmt = $pdo->prepare("SELECT id, title FROM posts WHERE title LIKE ? LIMIT 5");
    $stmt->execute(["%$query%"]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => $row['id'],
            'title' => $row['title'],
            'type' => 'post'
        ];
    }
    // Найти пользователей по username, только верифицированных
    $stmt2 = $pdo->prepare("SELECT id, username FROM Users WHERE username LIKE ? AND verificated = 1 LIMIT 5");
    $stmt2->execute(["%$query%"]);
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => $row['id'],
            'username' => $row['username'],
            'type' => 'user'
        ];
    }
}

echo json_encode($results);
