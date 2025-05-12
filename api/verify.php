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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="../index.php">
                        <span class="logo-part1">ПАБ</span>
                        <span class="logo-part2">МИ В УКРАЇНІ</span>
                    </a>
                </div>
                <button class="nav-toggle" aria-label="Відкрити меню">
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                </button>
                <nav class="nav-links">
                    <a href="../index.php">НА ГОЛОВНУ</a>
                    <a href="../login.php">ВХІД</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">
    <div class="container" style="text-align: center;">
        <h1><?php echo htmlspecialchars($message); ?></h1>
        <br />
        <a class="buttons-style-one" href="../login.php">Перейти до входу</a>
    </div>

    </main>
    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>
    <script src="../assets/js/register.js"></script>
    <script src="../assets/js/register.js"></script>
    <script src="../assets/js/mobileMenu.js"></script>
</body>
</html>
