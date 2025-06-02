<!DOCTYPE html>
<html lang="uk">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Access</title>
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
                <nav class="nav-links">
                    <a href="index.php">НА ГОЛОВНУ</a>
                </nav>
            </div>
        </div>
    </header>

    <div class="container auth-section">
    <h1 style="text-align:center; margin-top:1rem;">Доступ до адмін панелі</h1>
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
    <section id="admin-panel-section" style="display:none; margin-top:2rem;">
        <h2 style="text-align:center; margin-top:1rem;">Адмін-панель</h2>
        <div style="margin-bottom:1rem;">
            <input type="text" id="user-search-input" placeholder="Пошук за username..." style="max-width:300px; padding:0.5rem;">
        </div>
        <div style="display:flex; gap:2rem; flex-wrap:wrap;">
            <div style="flex:1; min-width:300px;">
                <h3>Всі користувачі</h3>
                <table id="users-table" class="modal-list-item" style="width:100%;margin-bottom:1rem;"></table>
            </div>
            <div style="flex:1; min-width:300px;">
                <h3>Адміністратори</h3>
                <table id="admins-table" class="modal-list-item" style="width:100%;margin-bottom:1rem;"></table>
            </div>
        </div>
        <div style="margin-top:2rem;display:flex;gap:2rem;flex-wrap:wrap;">
            <form id="add-admin-form" class="auth-form" style="max-width:300px;">
                <h4>Додати розширений доступ</h4>
                <input type="number" name="user_id" placeholder="ID користувача" min="1" required style="width:100%;margin-bottom:0.5rem;">
                <button type="submit" class="buttons-style-one">OK</button>
            </form>
            <form id="remove-admin-form" class="auth-form" style="max-width:300px;">
                <h4>Позбавити розширеного доступу</h4>
                <input type="number" name="user_id" placeholder="ID користувача" min="1" required style="width:100%;margin-bottom:0.5rem;">
                <button type="submit" class="buttons-style-one">OK</button>
            </form>
        </div>
    </section>
    <script src="assets/js/admin-access.js"></script>
    <script src="assets/js/customAlert.js"></script>
 </body>
 </html>
