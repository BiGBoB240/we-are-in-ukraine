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
                    <a href="#" id="openPasswordChangeModal" style="display:none; margin-left:1rem;">ЗМІНИТИ ПАРОЛЬ</a>
                    
                </nav>
            </div>
        </div>
    </header>
    <div class="container">
    <h1 style="text-align:center; margin:1rem 0;">Доступ до адмін панелі</h1>
    <div class="container auth-section">
        <!-- Форма реєстрації супер-адміна -->
        <section id="admin-register-section" style="display:none">
            <form id="admin-register-form" class="auth-form">
                <div class="form-group">
                    <label for="register_login">Логін</label>
                    <input type="text" id="register_login" name="login" required autocomplete="username">
                </div>
                <div class="form-group">
                    <label for="register_email">Email</label>
                    <input type="email" id="register_email" name="email" required autocomplete="email">
                </div>
                <div class="form-group">
                    <label for="register_password">Пароль</label>
                    <input type="password" id="register_password" name="password" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="register_confirm">Підтвердіть пароль</label>
                    <input type="password" id="register_confirm" name="confirm" required autocomplete="new-password">
                </div>
                <button type="submit" class="buttons-style-one">Зареєструвати</button>
            </form>
        </section>
        <!-- Підтвердження email супер-адміна -->
        <section id="admin-register-verify-section" style="display:none">
            <form id="admin-register-verify-form" class="auth-form">
                <div class="form-group">
                    <label for="register_verification_code">Код з пошти</label>
                    <input type="text" id="register_verification_code" name="verification_code" required autocomplete="one-time-code">
                </div>
                <button type="submit" class="buttons-style-one">Підтвердити</button>
            </form>
        </section>
        <!-- Стара логін/верифікація -->
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
        <div class="modal-list-item" style="margin-top:2rem;display:flex;gap:2rem;flex-wrap:wrap;justify-content: center;">
            <form id="add-admin-form" class="auth-form" style="max-width:300px;">
                <h4>Додати розширений доступ</h4>
                <input type="number" name="user_id" placeholder="ID користувача" required style="width:100%;margin-bottom:0.5rem;padding:0.5rem;">
                <button type="submit" class="buttons-style-one">OK</button>
            </form>
            <form id="remove-admin-form" class="auth-form" style="max-width:300px;">
                <h4>Позбавити розширеного доступу</h4>
                <input type="number" name="user_id" placeholder="ID користувача" required style="width:100%;margin-bottom:0.5rem;padding:0.5rem;">
                <button type="submit" class="buttons-style-one">OK</button>
            </form>
        </div>
        <div class="modal-list-item" style="margin-bottom:1rem;">
            <input type="text" id="user-search-input" placeholder="Пошук за username..." style="width: 100%; padding: 0.5rem;">
        </div>
        <div class="modal-list-item" style="display:flex; gap:2rem; flex-wrap:wrap;">
            <div style="flex:1; min-width:300px;">
                <h3>Всі користувачі</h3>
                <table id="users-table" class="modal-list-item" style="width:100%;margin-bottom:1rem;"></table>
            </div>
            <div style="flex:1; min-width:300px;">
                <h3>Адміністратори</h3>
                <table id="admins-table" class="modal-list-item" style="width:100%;margin-bottom:1rem;"></table>
            </div>
        </div>
    </section>
    </div>

     <!-- Modal for password change -->
     <div id="passwordChangeModal" class="modal">
        <div class="modal-content">
            <button class="modal-close">&times;</button>
            <h2>Змінити пароль</h2>
            <form id="passwordChangeForm">
                <div class="form-group">
                    <label for="currentPassword">Поточний пароль*</label>
                    <input type="password" id="currentPassword" name="currentPassword" required>
                </div>
                <div class="form-group">
                    <label for="newPassword">Новий пароль*</label>
                    <input type="password" id="newPassword" name="newPassword" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="confirmPassword">Підтвердіть новий пароль*</label>
                    <input type="password" id="confirmPassword" name="confirmPassword" required minlength="8">
                </div>
                <button type="submit" class="buttons-style-one">Зберегти</button>
            </form>
            <div id="passwordChangeMessage"></div>
        </div>
    </div>

    <style>
        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        tr {margin-right:1rem;}
    </style>
    
<script src="assets/js/admin-access.js"></script>
<script src="assets/js/customAlert.js"></script>
 </body>
 </html>
