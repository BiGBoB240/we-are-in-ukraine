<?php
require_once '../config/db.php';

$token = $_GET['token'] ?? '';
$message = '';

if ($token) {
    $stmt = $pdo->prepare("SELECT id FROM Users WHERE verification_token = ? AND verificated = 0");
    $stmt->execute([$token]);
    $user = $stmt->fetch();
    if ($user) {
        $stmt = $pdo->prepare("UPDATE Users SET verificated = 1, verification_token = NULL WHERE id = ?");
        $stmt->execute([$user['id']]);
        $message = 'Ваш акаунт успішно підтверджено! Тепер ви можете увійти.';
    } else {
        $message = 'Посилання недійсне або акаунт вже підтверджено.';
    }
} else {
    $message = 'Некоректне посилання для підтвердження.';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Підтвердження акаунта</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 5rem; text-align: center;">
        <h1><?php echo htmlspecialchars($message); ?></h1>
        <a href="../login.php">Перейти до входу</a>
    </div>
</body>
</html>
