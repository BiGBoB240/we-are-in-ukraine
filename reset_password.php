<?php
session_start();
$token = $_GET['token'] ?? '';
if (isset($_SESSION['user_id']) || !$token) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Скидання пароля</title>
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
                    <a href="index.php">НА ГОЛОВНУ</a>
                    <a href="login.php">ВХІД</a>
                </nav>
            </div>
        </div>
    </header>
    <main class="main-content">
        <div class="container">
            <div class="auth-section">
                <h1>Скидання пароля</h1>
                <form id="resetForm">
                    <input type="hidden" id="token" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="password">Новий пароль*</label>
                        <input type="password" id="password" name="password" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Підтвердіть пароль*</label>
                        <input type="password" id="password_confirm" name="password_confirm" required minlength="8">
                    </div>
                    <button type="submit" class="buttons-style-one">Зберегти</button>
                </form>
                <div id="resetMessage"></div>
            </div>
        </div>
    </main>
    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>
    <script src="assets/js/reset_password.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/mobileMenu.js"></script>
    <script src="assets/js/customAlert.js"></script>
</body>
</html>
