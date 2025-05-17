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
    <link rel="stylesheet" href="assets/css/comments.css">
    <link rel="icon" type="image/png" href="http://my-v-ukrayini.rv.ua/wp-content/uploads/2018/03/logoalphasmallwhite.png">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i,900,900i" rel="stylesheet">
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
    <script src="../assets/js/customAlert.js"></script>
</body>
</html>
