<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Доступ до адмін панелі</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container auth-section">
        <h1>Доступ до адмін панелі</h1>
        
        <section id="admin-login-section">
            <form id="admin-login-form" class="auth-form">
                <div class="form-group">
                    <label for="login">Логін</label>
                    <input type="text" id="login" name="login" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password">
                </div>
                <button type="submit" class="buttons-style-one">Увійти</button>
            </form>
        </section>
        <section id="admin-verify-section" style="display:none">
            <form id="admin-verify-form" class="auth-form">
                <div class="form-group">
                    <label for="verification_code">Код з пошти</label>
                    <input type="text" id="verification_code" name="verification_code" required autocomplete="one-time-code">
                </div>
                <button type="submit" class="buttons-style-one">Підтвердити</button>
            </form>
        </section>
    </div>
    <script src="assets/js/admin-access.js"></script>
    <script src="assets/js/customAlert.js"></script>
</body>
</html>
