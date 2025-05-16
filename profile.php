<?php
require_once 'config/db.php';
session_start();

$profileUserId = null;
if (isset($_GET['id'])) {
    $profileUserId = (int)$_GET['id'];
} elseif (isset($_SESSION['user_id'])) {
    $profileUserId = (int)$_SESSION['user_id'];
} else {
    header('Location: login.php');
    exit;
}

// Get user data by user_id
$stmt = $pdo->prepare("SELECT username, created_at FROM Users WHERE id = ?");
$stmt->execute([$profileUserId]);
$user = $stmt->fetch();

if (!$user) {
    echo '<div style="margin:2rem;color:red;text-align:center;">Користувач не знайдений</div>';
    exit;
}

// Split username into first name and last name
$nameParts = explode(' ', $user['username']);
$firstName = $nameParts[0];
$lastName = isset($nameParts[1]) ? $nameParts[1] : '';
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Профіль - <?php echo htmlspecialchars($user['username']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/comments.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="icon" type="image/png" href="http://my-v-ukrayini.rv.ua/wp-content/uploads/2018/03/logoalphasmallwhite.png">
    <link href="https://fonts.googleapis.com/css?family=Montserrat:100,100i,200,200i,300,300i,400,400i,500,500i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Playfair+Display:400,400i,700,700i,900,900i" rel="stylesheet">
    <script>
        window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        window.profileUserId = <?php echo (int)$profileUserId; ?>;
        window.currentUserId = <?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 'null'; ?>;
    </script>
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
                <div class="search-bar">
                    <input type="text" id="search" autocomplete="off" placeholder="Пошук...">
                    <div id="search-results" class="search-results"></div>
                </div>
                <button class="nav-toggle" aria-label="Відкрити меню">
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                </button>
                <nav class="nav-links">
    <a href="index.php">НА ГОЛОВНУ</a>
    <a href="feedback.php">ЗВОРОТНІЙ ЗВ'ЯЗОК</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <a href="logout.php">ВИЙТИ</a>
    <?php else: ?>
        <a href="register.php">РЕЄСТРАЦІЯ</a>
        <a href="login.php">ВХІД</a>
    <?php endif; ?>
        </nav>
            </div>
        </div>
    </header>

    <?php
    // Initialize variables before using them
    $showProfileActions = false;
    $isAdmin = false;
    if (isset($_SESSION['user_id'])) {
        // Check if this is the user's own profile
        if ($_SESSION['user_id'] == $profileUserId) {
            $showProfileActions = true;
        } else {
            // Check if the current user is an admin
            $stmtAdmin = $pdo->prepare("SELECT 1 FROM Administrations WHERE user_id = ? AND verificated = 1");
            $stmtAdmin->execute([$_SESSION['user_id']]);
            if ($stmtAdmin->fetch()) {
                $showProfileActions = true;
                $isAdmin = true;
            }
        }
    }
    ?>
    <main class="main-content">
        <div class="container">
            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-top-row">
                        <h1 style="display:inline-block;vertical-align:middle;">
                            <?php echo htmlspecialchars($user['username']); ?>
                        </h1>
                        <div class="profile-actions-container">
                            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $profileUserId): ?>
                                <button class="report-btn" title="Поскаржитись на профіль" id="report-profile-btn">Поскаржитись</button>
                            <?php endif; ?>
                            
                            <?php if ($showProfileActions): ?>
                                <div class="profile-settings-dropdown">
                                    <button class="profile-settings-btn" title="Налаштування">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <div class="profile-dropdown-content">
                                        <?php if ($isAdmin && isset($_SESSION['user_id']) && $_SESSION['user_id'] != $profileUserId): ?>
                                            <script>window.profileUserIdForAdmin = <?php echo (int)$profileUserId; ?>;</script>
                                            <a href="#" onclick="openNameChangeModal(); return false;">Змінити ім'я</a>
                                            <a href="#" onclick="deleteUserProfile(<?php echo (int)$profileUserId; ?>); return false;" class="delete-action">Видалити профіль</a>
                                            <div class="admin-indicator">Адмін</div>
                                        <?php else: ?>
                                            <a href="#" onclick="openNameChangeModal(); return false;">Змінити ім'я</a>
                                            <a href="#" onclick="openPasswordChangeModal(); return false;">Змінити пароль</a>
                                            <a href="logout.php">Вийти з профілю</a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <p>Дата реєстрації: <?php echo date('d.m.Y', strtotime($user['created_at'])); ?></p>
                </div>
                <script>window.profileUserId = <?php echo (int)$profileUserId; ?>;</script>
                <div class="comments-section">
                  <!-- ! <h2><?php echo (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profileUserId) ? 'МОЇ КОМЕНТАРІ' : 'КОМЕНТАРІ КОРИСТУВАЧА'; ?></h2> -->
                    <div class="profile-actions-bars">
                    <div class="filter-buttons">
                        <button class="buttons-style-one filter-btn" id="filter-date-new" data-filter="date-new">ЗА ДАТОЮ: НОВІШЕ</button>
                        <button class="buttons-style-one filter-btn" id="filter-date-old" data-filter="date-old">ЗА ДАТОЮ: ДАВНІШЕ</button>
                        <button class="buttons-style-one filter-btn" id="filter-rating-high" data-filter="rating-high">ЗА ОЦІНКОЮ: ВІД БІЛЬШОЇ</button>
                        <button class="buttons-style-one filter-btn" id="filter-rating-low" data-filter="rating-low">ЗА ОЦІНКОЮ: ВІД МЕНШОЇ</button>
                    </div>
                    </div>
                    <div id="comments-container">
                        <!-- Comments will be loaded here dynamically -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal for name change -->
    <div id="nameChangeModal" class="modal">
        <div class="modal-content">
            <span class="close"></span>
            <h2>Змінити ім'я</h2>
            <form id="nameChangeForm">
                <div class="form-group">
                    <label for="firstName">Ім'я*</label>
                    <input type="text" id="firstName" name="firstName" value="<?php echo htmlspecialchars($firstName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="lastName">Прізвище</label>
                    <input type="text" id="lastName" name="lastName" value="<?php echo htmlspecialchars($lastName); ?>">
                </div>
                <button type="submit" class="buttons-style-one">Зберегти</button>
            </form>
            <div id="nameChangeMessage"></div>
        </div>
    </div>

    <!-- Modal for password change -->
    <div id="passwordChangeModal" class="modal">
        <div class="modal-content">
            <span class="close"></span>
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

    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>
    <script>window.currentUserId = <?php echo isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'null'; ?>;</script>
    <script src="assets/js/profile.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/mobileMenu.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var reportBtn = document.getElementById('report-profile-btn');
    if (reportBtn) {
        reportBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (confirm('Поскаржитись на цей профіль?')) {
                fetch('api/report.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        content_id: <?php echo (int)$profileUserId; ?>,
                        content_type: 'user'
                    })
                })
                .then(res => res.json())
                .then(data => alert(data.success || data.error))
                .catch(() => alert('Помилка при надсиланні скарги'));
            }
        });
    }
});
</script>

</html>
