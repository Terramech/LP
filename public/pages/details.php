<?php
// Задание 3. Детальная страница
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';
session_start();

$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($courseId <= 0) {
    die("Курс не найден. <a href='catalog.php'>← Назад к каталогу</a>");
}

$stmt = $pdo->prepare("SELECT * FROM Courses WHERE id = ?");
$stmt->execute([$courseId]);
$course = $stmt->fetch();

if (!$course) {
    die("Курс не найден. <a href='catalog.php'>← Назад к каталогу</a>");
}

// Задание 5. Сохранение действия (заявки)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll']) && isAuth()) {
    // Проверяем, не записан ли уже
    $check = $pdo->prepare("SELECT id FROM Enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$_SESSION['user_id'], $courseId]);
    if (!$check->fetch()) {
        // Получаем ID статуса "new"
        $statusId = $pdo->query("SELECT id FROM Statuses WHERE name = 'new' LIMIT 1")->fetchColumn();
        $stmt = $pdo->prepare("INSERT INTO Enrollments (user_id, course_id, status_id, created_at) VALUES (?, ?, ?, CURDATE())");
        $stmt->execute([$_SESSION['user_id'], $courseId, $statusId]);
    }
    header("Location: profile.php");
    exit;
}

$levelColors = [
    'A1' => ['from' => '#E9FFF1', 'to' => '#98FFB3', 'text' => '#1D9200'],
    'A2' => ['from' => '#E0F7FA', 'to' => '#80DEEA', 'text' => '#00838F'],
    'B1' => ['from' => '#FFF3E0', 'to' => '#FFCC80', 'text' => '#EF6C00'],
    'B2' => ['from' => '#F3E5F5', 'to' => '#CE93D8', 'text' => '#7B1FA2'],
    'C1' => ['from' => '#E8F5E9', 'to' => '#66BB6A', 'text' => '#2E7D32'],
    'C2' => ['from' => '#FFEBEE', 'to' => '#EF5350', 'text' => '#C62828'],
];
$colors = $levelColors[$course['level']] ?? $levelColors['A1'];

$stmtModules = $pdo->prepare("SELECT title, description FROM `Subject-Module` WHERE course_id = ?");
$stmtModules->execute([$courseId]);
$modules = $stmtModules->fetchAll();

// Проверяем, записан ли пользователь
$isEnrolled = false;
if (isAuth()) {
    $check = $pdo->prepare("SELECT id FROM Enrollments WHERE user_id = ? AND course_id = ?");
    $check->execute([$_SESSION['user_id'], $courseId]);
    $isEnrolled = $check->fetch() !== false;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title><?= htmlspecialchars($course['title']) ?> — InterSpeak</title>
</head>
<body>
    <div class="background-container">
        <section class="bg-section bg-classroom" style="min-height: 100vh; height: auto;">
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
                        <button class="headerBtn" onclick="location.href='catalog.php'">Программы</button>
                        <button class="headerBtn">Вопросы</button>
                        <button class="headerBtn">О нас</button>
                    </div>
                    <?php if (isAuth()): ?>
                    <div style="display: flex; align-items: center; gap: 12px; margin-right: 2vh;">
                        <a href="profile.php" style="text-decoration: none; color: white; font-weight: 600; font-size: 14px;">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></a>
                        <?php if (isAdmin()): ?>
                        <a href="../admin/index.php" style="text-decoration: none; color: #f39c12; font-weight: 600; font-size: 13px;">⚙️</a>
                        <?php endif; ?>
                        <button class="headerBtn" onclick='window.location.href="../handlers/logout.php"' style="font-size: 13px;">Выйти</button>
                    </div>
                    <?php else: ?>
                    <button class="headerBtn" style="margin-right: 2vh;" id="mainPageRegBtn">Регистрация/вход</button>
                    <?php endif; ?>
                </nav>
            </header>

            <main style="padding-top: 140px; padding-bottom: 60px; max-width: 1200px; margin: 0 auto;">
                <div style="font-family: 'Itim Cyrillic'; color: #1D9200; margin-bottom: 30px; font-size: 18px;">
                    <a href="../index.php" style="color: #1D9200; text-decoration: none;">Главная</a> / 
                    <a href="catalog.php" style="color: #1D9200; text-decoration: none;">Программы</a> / 
                    <span style="color: #00D962;"><?= htmlspecialchars($course['title']) ?></span>
                </div>

                <div class="infoBlock" style="margin-top: 0; height: auto; min-height: 400px; display: block; padding: 40px;">
                    <div style="display: flex; gap: 40px; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 300px;">
                            <div style="width: 100%; height: 250px; background: linear-gradient(135deg, <?= $colors['from'] ?>, <?= $colors['to'] ?>); border-radius: 40px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                <span style="font-family: 'Itim Cyrillic'; font-size: 100px; color: <?= $colors['text'] ?>;"><?= htmlspecialchars($course['level']) ?></span>
                            </div>
                        </div>
                        <div style="flex: 2; min-width: 300px;">
                            <h1 style="font-family: 'Itim Cyrillic'; color: #1D9200; font-size: 42px; margin: 0 0 20px 0;"><?= htmlspecialchars($course['title']) ?></h1>
                            <p style="font-family: 'Itim Cyrillic'; color: #1D9200; font-size: 20px; line-height: 1.6; margin-bottom: 30px;"><?= htmlspecialchars($course['description']) ?></p>
                            <div style="display: flex; gap: 40px; margin-bottom: 30px; flex-wrap: wrap;">
                                <div style="text-align: center;">
                                    <div style="font-family: 'Itim Cyrillic'; font-size: 36px; color: #00D962; font-weight: bold;">12</div>
                                    <div style="font-family: 'Itim Cyrillic'; color: #1D9200;">занятий</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-family: 'Itim Cyrillic'; font-size: 36px; color: #00D962; font-weight: bold;">24</div>
                                    <div style="font-family: 'Itim Cyrillic'; color: #1D9200;">часа</div>
                                </div>
                                <div style="text-align: center;">
                                    <div style="font-family: 'Itim Cyrillic'; font-size: 36px; color: #00D962; font-weight: bold;"><?= number_format($course['price'], 0, '', ' ') ?> ₽</div>
                                    <div style="font-family: 'Itim Cyrillic'; color: #1D9200;">стоимость</div>
                                </div>
                            </div>

                            <!-- Задание 4. Форма записи -->
                            <?php if (isAuth()): ?>
                                <?php if ($isEnrolled): ?>
                                    <div style="padding: 15px 30px; background: #E9FFF1; border-radius: 20px; border: 2px solid #1D9200; display: inline-block; color: #1D9200; font-family: 'Itim Cyrillic'; font-size: 20px;">✅ Вы записаны на курс</div>
                                <?php else: ?>
                                    <form method="POST">
                                        <button type="submit" name="enroll" value="1" class="infoBlockButton" style="width: 300px; height: 70px; font-size: 28px;"
                                                onmouseover="this.style.background='#1D9200'; this.style.color='white';"
                                                onmouseout="this.style.background='#E9FFF1'; this.style.color='#00D962';">
                                            Записаться на курс
                                        </button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="../index.php" class="infoBlockButton" style="width: 300px; height: 70px; font-size: 24px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none;"
                                   onmouseover="this.style.background='#1D9200'; this.style.color='white';"
                                   onmouseout="this.style.background='#E9FFF1'; this.style.color='#00D962';">
                                    Войдите, чтобы записаться
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Модули курса -->
                <div style="margin-top: 40px;">
                    <div class="infoBlock" style="margin-top: 0; height: auto; display: block; padding: 30px;">
                        <h2 style="font-family: 'Itim Cyrillic'; color: #1D9200; font-size: 32px; margin-top: 0;">Программа курса</h2>
                        <?php if (!empty($modules)): ?>
                            <?php foreach ($modules as $module): ?>
                            <div style="margin-bottom: 15px; padding: 20px; background: #F5FFF7; border-radius: 20px;">
                                <h3 style="font-family: 'Itim Cyrillic'; color: #1D9200; margin: 0 0 10px 0; font-size: 22px;"><?= htmlspecialchars($module['title']) ?></h3>
                                <p style="font-family: 'Itim Cyrillic'; color: #1D9200; margin: 0; font-size: 16px;"><?= htmlspecialchars($module['description']) ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="font-family: 'Itim Cyrillic'; color: #1D9200;">Модули курса в разработке.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </section>
    </div>
</body>
</html>