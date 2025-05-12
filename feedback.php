<?php
require_once 'config/db.php';
session_start();

// Check if user is admin
$isAdmin = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM Administrations WHERE user_id = ? AND verificated = 1");
    $stmt->execute([$_SESSION['user_id']]);
    $isAdmin = $stmt->rowCount() > 0;
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Зворотній зв'язок - Ми в Україні</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" type="image/png" href="http://my-v-ukrayini.rv.ua/wp-content/uploads/2018/03/logoalphasmallwhite.png">
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">ПАБ МИ В УКРАЇНІ</a>
                </div>
                <button class="nav-toggle" aria-label="Відкрити меню">
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                    <span class="nav-toggle-bar"></span>
                </button>
                <nav class="nav-links">
                    <a href="index.php">НА ГОЛОВНУ</a>
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
            <div class="feedback-section">
                <h1>ЗВОРОТНІЙ ЗВ'ЯЗОК</h1>
                
                <!-- Contact Information -->
                <div class="contact-info">
                    <h2>Прямий зв'язок</h2>
                    <div class="contact-details">
                        <!-- These will be filled by the client -->
                        <p>Email: <span id="contact-email">pub.my.v.ukrayini@gmail.com</span></p>
                        <p>Телефон: <span id="contact-phone">+380969949894</span></p>
                    </div>
                </div>

                <!-- Feedback Form -->
                <div class="feedback-form">
                    <h2>Форма зворотнього зв'язку</h2>
                    <form id="feedbackForm">
                        <div class="form-group">
                            <label for="username">Ім'я*</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email*</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Номер телефону*</label>
                            <input type="tel" id="phone" name="phone" pattern="[0-9]*" maxlength="10" inputmode="numeric" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Текст звернення*</label>
                            <textarea id="message" name="message" required></textarea>
                        </div>
                        
                        <button type="submit" class="buttons-style-one">Надіслати</button>
                    </form>
                </div>

                <?php if ($isAdmin): ?>
                <!-- Admin Section -->
                <div class="admin-section">
                    <h2>Звернення</h2>
                    <button id="showFeedbacks" class="buttons-style-one">Показати звернення</button>
                    
                    <div id="feedbacksList" class="feedbacks-list" style="display: none;">
                        <!-- Feedbacks will be loaded here -->
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="footer">
        <div class="container">
            <p>Copyright © Всі права захищені</p>
        </div>
    </footer>

    <script src="assets/js/feedback.js"></script>
    <script src="assets/js/main.js"></script>
    <script src="assets/js/mobileMenu.js"></script>
</body>
</html>
