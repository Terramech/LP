<?php
// Задание 6, 7, 8, 9. Личный кабинет + редактирование + аватар
require_once __DIR__ . '/../../src/config/database.php';
require_once __DIR__ . '/../../src/helpers/auth.php';

session_start();
requireAuth('/index.php');

$userId = $_SESSION['user_id'];

// Редактирование профиля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $level = $_POST['level'] ?? null;

    $stmt = $pdo->prepare("UPDATE USER SET name = ?, email = ?, level = ? WHERE id = ?");
    $stmt->execute([$name, $email, $level, $userId]);

    $_SESSION['user_name'] = $name;
    header('Location: profile.php');
    exit;
}

// Загрузка аватара
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $uploadDir = __DIR__ . '/../../public/assets/images/avatars/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
    $filename = 'avatar_' . $userId . '.' . $ext;
    $filepath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['avatar']['tmp_name'], $filepath)) {
        $pdo->prepare("UPDATE USER SET avatar_link = ? WHERE id = ?")->execute([$filename, $userId]);
    }
    header('Location: profile.php');
    exit;
}

// Данные пользователя
$stmt = $pdo->prepare("SELECT * FROM USER WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// Мои заявки
$stmt = $pdo->prepare("
    SELECT e.*, c.title as course_title, c.level, s.display_name_rus as status_name, s.name as status_code
    FROM Enrollments e
    JOIN Courses c ON e.course_id = c.id
    JOIN Statuses s ON e.status_id = s.id
    WHERE e.user_id = ?
    ORDER BY e.created_at DESC
");
$stmt->execute([$userId]);
$myEnrollments = $stmt->fetchAll();

$statusColors = [
    'new' => '#3498db',
    'in_progress' => '#f39c12',
    'confirmed' => '#2ecc71',
    'rejected' => '#e74c3c',
    'completed' => '#9b59b6',
];

$roleLabels = [
    'student' => 'Студент',
    'teacher' => 'Преподаватель',
    'admin' => 'Администратор',
];

$avatarPath = $user['avatar_link'] ? '../assets/images/avatars/' . $user['avatar_link'] : '../assets/images/Icons/default-avatar.png';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="../assets/css/style.css">
    <title>Личный кабинет — InterSpeak</title>
    <style>
        @font-face {
            font-family: 'Itim Cyrillic';
            src: url('../assets/fonts/Itim-Cyrillic.woff2') format('woff2');
            font-weight: normal;
            font-style: normal;
        }
        .profile-page {
            font-family: 'Itim Cyrillic', sans-serif;
            min-height: 100vh;
            padding-top: 120px;
            padding-bottom: 60px;
        }
        .profile-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .profile-card {
            background: #FCFCFC;
            border-radius: 60px;
            border: 2px solid #1D9200;
            padding: 40px;
            margin-bottom: 30px;
            transition: all 0.3s ease;
        }
        .profile-card:hover {
            box-shadow: 0 20px 40px rgba(29, 146, 0, 0.15);
        }
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #1D9200;
            object-fit: cover;
            margin-bottom: 15px;
            background: #E9FFF1;
        }
        .profile-avatar-placeholder {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid #1D9200;
            background: #E9FFF1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            margin: 0 auto 15px;
        }
        .profile-name {
            font-size: 32px;
            color: #1D9200;
            margin-bottom: 5px;
        }
        .profile-email {
            color: #99ffa3;
            font-size: 18px;
            margin-bottom: 10px;
        }
        .profile-role {
            display: inline-block;
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 14px;
            color: white;
            font-weight: bold;
        }
        .profile-section-title {
            font-size: 24px;
            color: #1D9200;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .profile-info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }
        .profile-info-item {
            background: #F5FFF7;
            padding: 20px;
            border-radius: 20px;
            border: 1px solid #E9FFF1;
        }
        .profile-info-label {
            color: #99ffa3;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .profile-info-value {
            color: #1D9200;
            font-size: 18px;
            font-weight: 600;
        }
        .enrollment-card {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #F5FFF7;
            padding: 20px;
            border-radius: 20px;
            border: 1px solid #E9FFF1;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .enrollment-card:hover {
            transform: translateX(5px);
            border-color: #1D9200;
        }
        .enrollment-info h3 {
            color: #1D9200;
            font-size: 20px;
            margin-bottom: 5px;
        }
        .enrollment-info p {
            color: #99ffa3;
            font-size: 14px;
        }
        .enrollment-status {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #99ffa3;
            font-size: 18px;
        }
        .profile-btn {
            padding: 12px 24px;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-family: 'Itim Cyrillic';
            font-size: 18px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 0px rgba(0, 0, 0, 0.15);
        }
        .profile-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 0px rgba(0, 0, 0, 0.2);
        }
        .profile-btn-primary {
            background: #E9FFF1;
            color: #00D962;
            border: 2px solid #1D9200;
        }
        .profile-btn-primary:hover {
            background: #1D9200;
            color: white;
        }
        .profile-btn-logout {
            background: #FFEBEE;
            color: #e74c3c;
            border: 2px solid #e74c3c;
        }
        .profile-btn-logout:hover {
            background: #e74c3c;
            color: white;
        }
        .edit-form {
            display: none;
        }
        .edit-form.active {
            display: block;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            color: #1D9200;
            font-size: 18px;
            margin-bottom: 8px;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px;
            background: #F5FFF7;
            border: 2px solid #98FFB3;
            border-radius: 20px;
            color: #1D9200;
            font-family: 'Itim Cyrillic';
            font-size: 16px;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #1D9200;
        }
        .avatar-upload {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }
        .avatar-upload input[type="file"] {
            display: none;
        }
        .avatar-upload-label {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #1D9200;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            cursor: pointer;
            border: 3px solid #FCFCFC;
        }
    </style>
</head>
<body>
    <div class="background-container">
        <section class="bg-section bg-classroom" style="min-height: 100vh; height: auto;">
            <header>
                <nav>
                    <div style="display: flex; align-items: center; margin-top: 10px;">
                        <img src="../assets/images/Icons/logo.png" alt="Logo" style="margin-right: 15px; margin-left: 15px;">
                        <div class="headerTitle">InterSpeak English school</div>
                    </div>
                    <div class="headerButtonRow">
                        <button class="headerBtn" onclick="location.href='../index.php'">Главная</button>
                        <button class="headerBtn" onclick="location.href='catalog.php'">Программы</button>
                        <button class="headerBtn">Вопросы</button>
                        <button class="headerBtn">О нас</button>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px; margin-right: 2vh;">
                        <span style="color: white; font-weight: 600; font-size: 14px;">👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                        <?php if (isAdmin()): ?>
                        <a href="../admin/index.php" style="text-decoration: none; color: #f39c12; font-weight: 600; font-size: 13px;">⚙️</a>
                        <?php endif; ?>
                        <button class="headerBtn" onclick='window.location.href="../handlers/logout.php"' style="font-size: 13px;">Выйти</button>
                    </div>
                </nav>
            </header>

            <div class="profile-page">
                <div class="profile-container">
                    <!-- Профиль -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <!-- Аватар -->
                            <div class="avatar-upload">
                                <?php if ($user['avatar_link']): ?>
                                <img src="<?= $avatarPath ?>" alt="Avatar" class="profile-avatar" id="avatarImg">
                                <?php else: ?>
                                <div class="profile-avatar-placeholder" id="avatarPlaceholder">👤</div>
                                <?php endif; ?>
                                <form method="POST" enctype="multipart/form-data" id="avatarForm">
                                    <input type="file" name="avatar" id="avatarInput" accept="image/*" onchange="document.getElementById('avatarForm').submit()">
                                    <label for="avatarInput" class="avatar-upload-label">📷</label>
                                </form>
                            </div>

                            <h1 class="profile-name"><?= htmlspecialchars($user['name']) ?></h1>
                            <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                            <span class="profile-role" style="background: <?= ['student'=>'#3498db','teacher'=>'#f39c12','admin'=>'#e74c3c'][$user['role']] ?? '#667eea' ?>;">
                                <?= $roleLabels[$user['role']] ?? $user['role'] ?>
                            </span>
                        </div>

                        <!-- Просмотр данных -->
                        <div id="profileView">
                            <div class="profile-section-title">📋 Мои данные</div>
                            <div class="profile-info-grid">
                                <div class="profile-info-item">
                                    <div class="profile-info-label">ID пользователя</div>
                                    <div class="profile-info-value">#<?= $user['id'] ?></div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Уровень английского</div>
                                    <div class="profile-info-value"><?= htmlspecialchars($user['level'] ?? 'Не указан') ?></div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Дата регистрации</div>
                                    <div class="profile-info-value"><?= $user['created_at'] ?></div>
                                </div>
                                <div class="profile-info-item">
                                    <div class="profile-info-label">Email</div>
                                    <div class="profile-info-value"><?= htmlspecialchars($user['email']) ?></div>
                                </div>
                            </div>
                            <div style="text-align: center; margin-top: 25px;">
                                <button class="profile-btn profile-btn-primary" onclick="toggleEdit()">✏️ Редактировать профиль</button>
                            </div>
                        </div>

                        <!-- Форма редактирования -->
                        <div id="profileEdit" class="edit-form">
                            <div class="profile-section-title">✏️ Редактирование профиля</div>
                            <form method="POST">
                                <input type="hidden" name="save_profile" value="1">
                                <div class="form-group">
                                    <label>Имя</label>
                                    <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Email</label>
                                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Уровень английского</label>
                                    <select name="level">
                                        <option value="">—</option>
                                        <?php foreach (['elementary','pre-intermediate','intermediate','upper-intermediate','advanced','proficient'] as $lvl): ?>
                                        <option value="<?= $lvl ?>" <?= ($user['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div style="display: flex; gap: 15px; justify-content: center;">
                                    <button type="submit" class="profile-btn profile-btn-primary">💾 Сохранить</button>
                                    <button type="button" class="profile-btn profile-btn-secondary" onclick="toggleEdit()" style="background: #F5FFF7; color: #1D9200; border: 2px solid #98FFB3;">Отмена</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Мои заявки -->
                    <div class="profile-card">
                        <div class="profile-section-title">📝 Мои заявки на курсы</div>
                        <?php if (!empty($myEnrollments)): ?>
                            <?php foreach ($myEnrollments as $enrollment): 
                                $color = $statusColors[$enrollment['status_code']] ?? '#666';
                            ?>
                            <div class="enrollment-card">
                                <div class="enrollment-info">
                                    <h3><?= htmlspecialchars($enrollment['course_title']) ?></h3>
                                    <p>Уровень: <?= htmlspecialchars($enrollment['level']) ?> | Дата: <?= $enrollment['created_at'] ?></p>
                                </div>
                                <span class="enrollment-status" style="background: <?= $color ?>20; color: <?= $color ?>;">
                                    <?= htmlspecialchars($enrollment['status_name']) ?>
                                </span>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="empty-state">
                                У вас пока нет заявок на курсы.<br>
                                <a href="catalog.php" style="color: #1D9200; text-decoration: underline;">Посмотреть каталог</a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="text-align: center;">
                        <a href="../handlers/logout.php" class="profile-btn profile-btn-logout">🚪 Выйти из аккаунта</a>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
    function toggleEdit() {
        document.getElementById('profileView').style.display = 
            document.getElementById('profileView').style.display === 'none' ? 'block' : 'none';
        document.getElementById('profileEdit').classList.toggle('active');
    }
    </script>
</body>
</html>