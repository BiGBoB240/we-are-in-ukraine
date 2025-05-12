<?php
require_once 'config/db.php';
session_start();

// Проверяем, является ли пользователь админом
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    // $pdo уже определён в config/db.php
    $stmt = $pdo->prepare('SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1');
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = $stmt->fetchColumn() !== false;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ми в Україні - Блог</title>
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
                    <a href="index.php">ПАБ МИ В УКРАЇНІ</a>
                </div>
                <div class="search-bar">
                    <input type="text" id="search" placeholder="Пошук...">
                    <div id="search-results" class="search-results"></div>
                </div>
                <?php if (isset($_SESSION['user_id'])): ?><button class="report-btn" title="Поскаржитись на пост"></button><?php endif; ?>
                <button class="nav-toggle" aria-label="Відкрити меню">
    <span class="nav-toggle-bar"></span>
    <span class="nav-toggle-bar"></span>
    <span class="nav-toggle-bar"></span>
</button>
<nav class="nav-links">
                    <a href="feedback.php">ЗВОРОТНІЙ ЗВ'ЯЗОК</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <a href="profile.php?id=<?php echo (int)$_SESSION['user_id']; ?>">ПРОФІЛЬ</a>
                        <a href="logout.php">ВИЙТИ</a>
                    <?php else: ?>
                        <a href="login.php">ВХІД</a>
                        <a href="register.php">РЕЄСТРАЦІЯ</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="container">
            <div class="posts-section">
                <div class="posts-header">
                    <div class="filter-buttons">
                        <button class="buttons-style-one" id="filter-btn" data-filter="date-new">ЗА ДАТАЮ: НОВІШЕ</button>
                        <button class="buttons-style-one" id="filter-btn" data-filter="date-old">ЗА ДАТАЮ: ДАВНІШЕ</button>
                        <button class="buttons-style-one" id="filter-btn" data-filter="rating-high">ЗА ОЦІНКОЮ: ВІД БІЛЬШОЇ</button>
                        <button class="buttons-style-one" id="filter-btn" data-filter="rating-low">ЗА ОЦІНКОЮ: ВІД МЕНШОЇ</button>
                    </div>
                </div>
                <div id="posts-container">
                    <!-- Posts will be loaded here dynamically -->
                </div>
                <button id="load-more" class="load-more-btn">ЗАВАНТАЖИТИ ЩЕ</button>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>

    <script>
        window.isAdmin = <?php echo $isAdmin ? 'true' : 'false'; ?>;
        window.currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;
    </script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/editPostModal.js"></script>
    <script src="assets/js/mobileMenu.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
