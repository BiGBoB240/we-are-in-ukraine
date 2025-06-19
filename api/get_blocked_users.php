<?php
//API для отримання заблокованих користувачів
require_once '../config/db.php';

$sql = "SELECT b.id, b.username, b.email, b.blocked_by_id, u.username AS admin_username
        FROM blockedusers b
        LEFT JOIN users u ON b.blocked_by_id = u.id
        ORDER BY b.created_at DESC";
$stmt = $pdo->query($sql);

$blocked = $stmt->fetchAll();
echo json_encode(['blocked_users' => $blocked]);
?>