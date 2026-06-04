<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Каталог курсов — InterSpeak</title>
</head>
<?php
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
session_start();
// Получаем курсы из БД
$stmt = $pdo->query("SELECT id, title, description, level, price FROM courses ORDER BY id");
$courses = $stmt->fetchAll();

// Цвета для каждого уровня
$levelColors = [
    'A1' => ['from' => '#E9FFF1', 'to' => '#98FFB3', 'text' => '#1D9200'],
    'A2' => ['from' => '#E0F7FA', 'to' => '#80DEEA', 'text' => '#00838F'],
    "B1" => ['from' => '#FFF3E0', 'to' => '#FFCC80', 'text' => '#EF6C00'],
    "B2" => ['from' => '#F3E5F5', 'to' => '#CE93D8', 'text' => '#7B1FA2'],
    "C1" => ['from' => '#E8F5E9', 'to' => '#66BB6A', 'text' => '#2E7D32'],
    "C2" => ['from' => '#FFEBEE', 'to' => '#EF5350', 'text' => '#C62828']
];
?>
<body>
    <div class="background-container">
        <section class="bg-section bg-classroom">
            <header>
                <nav>
                    <div style="display: flex; align-items: center; margin-top: 10px;">
                        <img src="../assets/images/Icons/logo.png" alt="Logo" style="margin-right: 15px; margin-left: 15px;">
                        <a href="../index.php">
                        <div class="headerTitle">InterSpeak English school</div>
                        </a>
                    </div>
                    <div class="headerButtonRow">
                        <button class="headerBtn">Отзывы</button>
                        <button class="headerBtn" style="background: #E9FFF1;">Программы</button>
                        <button class="headerBtn">Вопросы</button>
                        <button class="headerBtn">О нас</button>
                    </div>

                    <?php if (isAuth()): ?>
                    <div style="display: flex; align-items: center; gap: 12px; margin-right: 2vh;">
                        <a href="pages/profile.php" style="text-decoration: none; color: white; font-weight: 600; font-size: 14px;">
                            👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <?php if (isAdmin()): ?>
                        <a href="pages/admin.php" style="text-decoration: none; color: #f39c12; font-weight: 600; font-size: 13px;">⚙️</a>
                        <?php endif; ?>
                        <button class="headerBtn" onclick='window.location.href="/handlers/logout.php"' style="font-size: 13px;">Выйти</button>
                    </div>
                    <?php else: ?>
                    <button class="headerBtn" style="margin-right: 2vh;" id="mainPageRegBtn">Регистрация/вход</button>
                    <?php endif; ?>
                </nav>
            </header>
            <main style="padding-top: 140px; padding-bottom: 60px;">
                <div style="font-family: 'Itim Cyrillic'; color: #1D9200; margin-bottom: 30px; font-size: 18px;">
                    <a href="../index.html" style="color: #1D9200; text-decoration: none;">Главная</a> / 
                    <span style="color: #44ff98;">Программы</span>
                </div>
                <h1 style="font-family: 'Itim Cyrillic'; color: #2feb00; font-size: 48px; text-align: center; margin-bottom: 40px;">
                    Каталог курсов
                </h1>
                <!-- Filter Bar -->
                <div class="catalogLevelFilter">
                    <button class="catalogFilterButton" style="background: #1D9200; color: white;">Все</button>
                    <button class="catalogFilterButton">Level A — Beginner</button>
                    <button class="catalogFilterButton">Level B — Intermediate</button>
                    <button class="catalogFilterButton">Level C — Advanced</button>
                </div>
                <!-- Course Cards Grid -->
                <div class="cardsList" style="flex-wrap: wrap; justify-content: center; gap: 40px; margin: 20px;">
                    <!-- Course Card-->
                    <?php foreach ($courses as $course):
                        $colors = $levelColors[$course['level']];
                    ?>
                    <div class="levelCard catalog-card" >
                        <div class="catalogCardLevelBg" style="background: linear-gradient(135deg, <?= $colors['from'] ?>, <?= $colors['to'] ?>);">
                            <span class="catalogCardLevel" style="color: <?= $colors['text'] ?>;">
                                <?= htmlspecialchars($course['level']) ?>
                            </span>
                        </div>
                        <h2 class="cardTitle"><?= htmlspecialchars($course['title']) ?></h2>
                        <p class="catalogCardText"><?= htmlspecialchars($course['description']) ?></p>
                        <div class="cardPrice"><?= number_format($course['price'], 0, '', ' ') ?> ₽</div>
                        <button class="cardButton" onclick="location.href='details.php?id=<?= $course['id'] ?>'">Подробнее</button>
                    </div>
                    <?php 
                    endforeach;
                    ?>
                </div>
                </div>
            </main>
        </section>
    </div>
    <footer>
        <div class="footer-container">
            <div class="footer-col">
                <div class="footer-logo">
                    <img src="assets/images/Icons/logo.png" alt="logo">
                </div>
                <p class="footer-text">Современные образовательные программы и курсы для вашего развития.</p>
            </div>
            <div class="footer-col">
                <h4>Навигация</h4>
                <ul class="footer-links">
                    <li><a href="index.html">Главная</a></li>
                    <li><a href="catalog.html">Каталог</a></li>
                    <li><a href="#">Вопросы</a></li>
                    <li><a href="#">Связаться</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Контакты</h4>
                <p class="footer-text">Email: info@interspeak.ru</p>
                <p class="footer-text">Телефон: +7 (999) 000-00-00</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 InterSpeak. Все права защищены.</p>
        </div>
    </footer>
    <?php if (!isAuth()): ?>
    <section class="popup visibleFalse" id="regPopup">
        <div class="popupShadow">
            <section class="popupAuthReg">
                <button class="popupCloseButton" id="registrationCloseBtn">Закрыть</button>
                <div class="authRegSelection">
                    <button class="authRegSelectionButton" id="registrationSwitchBtn" disabled>Регистрация</button>
                    <button class="authRegSelectionButton" id="authorizationSwitchBtn">Вход</button>
                </div>
                <div class="signSelection">
                    <form class="registrationForm" id="registrationFormBlock">
                        <div class="regFormSect" id="registrationNameFormField">
                            <label class="formFieldLabel">Имя</label>
                            <input type="text" name="name" class="formField" placeholder="Ваше имя">
                            <span class="formFieldError" data-field="name"></span>
                        </div>
                        <div class="regFormSect" id="registrationEmailFormField">
                            <label class="formFieldLabel">Email</label>
                            <input type="email" name="email" class="formField" placeholder="your@email.com">
                            <span class="formFieldError" data-field="email"></span>
                        </div>
                        <div class="regFormSect" id="registrationPasswordFormField">
                            <label class="formFieldLabel">Пароль</label>
                            <input type="password" name="password" class="formField" placeholder="Минимум 6 символов">
                            <span class="formFieldError" data-field="password"></span>
                        </div>
                        <div class="regFormSect" id="registrationConfPassFormField">
                            <label class="formFieldLabel">Подтвердите пароль</label>
                            <input type="password" name="confirm_password" class="formField" placeholder="Повторите пароль">
                            <span class="formFieldError" data-field="confirm_password"></span>
                        </div>
                        <span class="formFieldError" data-field="general" style="color: #e74c3c; text-align: center; display: block; margin: 8px 0;"></span>
                        <button type="submit" class="authRegButton">Зарегистрироваться</button>
                    </form>

                    <form class="registrationForm visibleFalse" id="authorizationFormBlock">
                        <div style="margin-top: 60px;">
                            <div class="regFormSect">
                                <label class="formFieldLabel">Email</label>
                                <input type="email" name="email" class="formField" placeholder="your@email.com">
                            </div>
                            <div class="regFormSect">
                                <label class="formFieldLabel">Пароль</label>
                                <input type="password" name="password" class="formField" placeholder="Ваш пароль">
                            </div>
                        </div>
                        <span class="formFieldError" data-field="auth-general" style="color: #e74c3c; text-align: center; display: block; margin: 15px 0;"></span>
                        <button type="submit" class="authRegButton" style="margin-top: 60px;">Войти</button>
                    </form>
                </div>
            </section>
        </div>
    </section>
    <?php endif; ?>
</body>
<script src="../assets/js/main.js"></script>
</html>