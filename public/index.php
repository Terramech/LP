<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';

try {
    $stmt = $pdo->query("SELECT * FROM courses");
    $courses = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Ошибка загрузки данных.");
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/css/style.css">
    <title>InterSpeak English School</title>
</head>
<body>
    <div class="background-container">
        <section class="bg-section bg-classroom">
            <header>
                <nav>
                    <div style="display: flex; align-items: center; margin-top: 10px;">
                        <img src="assets/images/Icons/logo.png" alt="Logo" style="margin-right: 15px; margin-left: 15px;">
                        <div class="headerTitle">InterSpeak English school</div>
                    </div>
                    <div class="headerButtonRow">
                        <button class="headerBtn">Отзывы</button>
                        <button class="headerBtn" onclick='window.location.href="pages/catalog.php"'>программы</button>
                        <button class="headerBtn">Вопросы</button>
                        <button class="headerBtn">О нас</button>
                    </div>

                    <?php if (isAuth()): ?>
                    <div style="display: flex; align-items: center; gap: 12px; margin-right: 2vh;">
                        <a href="pages/profile.php" style="text-decoration: none; color: white; font-weight: 600; font-size: 14px;">
                            👤 <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </a>
                        <?php if (isAdmin()): ?>
                        <a onclick='window.location.href="/handlers/admin.php"' style="text-decoration: none; color: #f39c12; font-weight: 600; font-size: 13px;">⚙️</a>
                        <?php endif; ?>
                        <button class="headerBtn" onclick='window.location.href="/handlers/logout.php"' style="font-size: 13px;">Выйти</button>
                    </div>
                    <?php else: ?>
                    <button class="headerBtn" style="margin-right: 2vh;" id="mainPageRegBtn">Регистрация/вход</button>
                    <?php endif; ?>
                </nav>
            </header>

            <main>
                <section class="infoBlock">
                    <div class="infoText">
                        <h1 class="infoTextTitle">Онлайн Школа английского языка InterSpeak</h1>
                        <h2 class="infoTextContent">Заговорите уверенно на английском языке уже через 2 месяца регулярных занятий по нашей авторской методике.</h2>
                        <button class="infoBlockButton" onclick='window.location.href="pages/catalog.php"'>Записаться на курс</button>
                    </div>
                    <img src="assets/images/hero/heroBook.png" alt="heroBook">
                </section>

                <section class="statisticsBlock">
                    <div class="concurrentUsers">
                        <img src="assets/images/Icons/communityIcon.png" alt="users">
                        <p>5482 Активных пользователей</p>
                    </div>
                    <div class="totalRating">
                        <img src="assets/images/Icons/ratingIcon.png" alt="rating">
                        <p>80% положительных отзывов</p>
                    </div>
                </section>

                <section class="cardsList">
                    <div class="levelCard">
                        <h2 class="cardTitle">Основы изучения английского: с нуля до A1</h2>
                        <img src="assets/images/hero/A1levelHero.png" alt="level A1">
                        <p class="cardText">Заложите прочный фундамент для изучения языка.</p>
                        <button class="cardButton">Изучи основы!</button>
                    </div>
                    <div class="levelCard">
                        <h2 class="cardTitle">Разговорный английский: с нуля до B1</h2>
                        <img src="assets/images/hero/B1levelHero.png" alt="level B1">
                        <p class="cardText">Преодолейте языковой барьер и начните уверенно общаться.</p>
                        <button class="cardButton">Понимай больше!</button>
                    </div>
                    <div class="levelCard">
                        <h2 class="cardTitle">Продвинутый Уровень: с нуля до C1</h2>
                        <img src="assets/images/hero/C1levelHero.png" alt="level C1">
                        <p class="cardText">Достигните свободы в общении на любые темы.</p>
                        <button class="cardButton">Говори свободно!</button>
                    </div>
                </section>
                </section>
          <!-- misc cards -->
            </section>
          <section class="bg-section bg-books">
            <section class="bottomCards">
              <div class="bottomCard">
                <h2 class="cardTitle" style="grid-column: span 2;">Не знаете где начать?</h2>
                <img src="assets/images/hero/testHero.png" alt="test" class="bottomCardContent CardImage">
                <p class="cardText" style="grid-column: 2;">Постройте основу для будущего развития в сфере иностранного языка. Основные выражения, слова, конструкции.</p>
                <button class="cardButton bottomCardButton">Пройти тест</button>
              </div>
              <div class="bottomCard">
                <h2 class="cardTitle" style="grid-column: span 2; justify-self: center;">Есть вопросы?</h2>
                <img src="assets/images/hero/questionmark.jpg" alt="level A1" class="bottomCardContent bottomCardImage">
                <p class="cardText">Свяжитесь с нами — мы поможем подобрать подходящий уровень обучения, расскажем про формат занятий и ответим на любые вопросы.</p>
                <button class="cardButton bottomCardButton" style="grid-row: 3;">Задать вопрос</button>
              </div>
            </section>
        </section>
            </section>
        </main>
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

    <script src="assets/js/main.js"></script>
</body>
</html>