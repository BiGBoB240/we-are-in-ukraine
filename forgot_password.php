<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Відновлення пароля</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="http://my-v-ukrayini.rv.ua/wp-content/uploads/2018/03/logoalphasmallwhite.png">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i,900,900i" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
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
                    <a href="login.php">ВХІД</a>
                    <a href="register.php">РЕЄСТРАЦІЯ</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="auth-section forgot-password">
                <h1>ВІДНОВЛЕННЯ ПАРОЛЯ</h1>
                <form id="forgotForm">
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <button type="submit" class="buttons-style-one">НАДІСЛАТИ ПОСИЛАННЯ ДЛЯ ВІДНОВЛЕННЯ</button>
                </form>
                <div id="forgotMessage"></div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>
    <script src="assets/js/forgot_password.js"></script>
    <script src="assets/js/mobileMenu.js"></script>
    <script src="assets/js/customAlert.js"></script>
</body>
</html>
