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
    <title>Вхід - Ми в Україні</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="http://my-v-ukrayini.rv.ua/wp-content/uploads/2018/03/logoalphasmallwhite.png">
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
                    <a href="index.php">НА ГОЛОВНУ</a>
                    <a href="register.php">РЕЄСТРАЦІЯ</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="auth-section">
                <h1>ВХІД</h1>
                <form id="loginForm">
                    <div class="form-group">
                        <label for="email">Email*</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль*</label>
                        <input type="password" id="password" name="password" required>
                    </div>
                    <div class="auth-links">
                    <button type="submit" class="buttons-style-one">УВІТИ</button>
                    
                    <button type="button" class="buttons-style-one" onclick="window.location.href='register.php'">ЗАРЕЄСТРУВАТИСЯ</button>
                    <button type="button" class="buttons-style-one" onclick="window.location.href='forgot_password.php'">ЗАБУЛИ ПАРОЛЬ?</button>
                    </div>
                </form>
                <div id="loginMessage"></div>

            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>
    <script src="assets/js/login.js"></script>
    <script src="assets/js/mobileMenu.js"></script>
</body>
</html>
