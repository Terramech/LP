<?php
// ============================================
// АДМИН-ПАНЕЛЬ InterSpeak — стиль сайта
// ============================================
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/helpers/auth.php';

session_start();
requireAdmin('/index.php');

// ====== ОБРАБОТКА ДЕЙСТВИЙ ======

// Удаление курса
if (isset($_GET['delete_course'])) {
    $id = (int)$_GET['delete_course'];
    $pdo->prepare("DELETE FROM Courses WHERE id = ?")->execute([$id]);
    header('Location: /admin/index.php?tab=catalog');
    exit;
}

// Удаление пользователя
if (isset($_GET['delete_user'])) {
    $id = (int)$_GET['delete_user'];
    if ($id != $_SESSION['user_id']) {
        $pdo->prepare("DELETE FROM USER WHERE id = ?")->execute([$id]);
    }
    header('Location: /admin/index.php?tab=users');
    exit;
}

// Удаление модуля
if (isset($_GET['delete_module'])) {
    $id = (int)$_GET['delete_module'];
    $pdo->prepare("DELETE FROM `Subject-Module` WHERE id = ?")->execute([$id]);
    header('Location: /admin/index.php?tab=modules');
    exit;
}

// Сохранение курса
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_course'])) {
    $id = (int)($_POST['course_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $level = $_POST['level'] ?? 'A1';
    $price = (float)($_POST['price'] ?? 0);

    if ($id > 0) {
        $pdo->prepare("UPDATE Courses SET title=?, description=?, level=?, price=? WHERE id=?")
            ->execute([$title, $description, $level, $price, $id]);
    } else {
        $pdo->prepare("INSERT INTO Courses (title, description, level, price) VALUES (?, ?, ?, ?)")
            ->execute([$title, $description, $level, $price]);
    }
    header('/Location: /admin/index.php?tab=catalog');
    exit;
}

// Сохранение пользователя
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_user'])) {
    $id = (int)($_POST['user_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = $_POST['role'] ?? 'student';
    $level = $_POST['level'] ?? null;

    if ($id > 0) {
        $pdo->prepare("UPDATE USER SET name=?, email=?, role=?, level=? WHERE id=?")
            ->execute([$name, $email, $role, $level, $id]);
    } else {
        $passwordHash = password_hash($_POST['password'] ?? 'password123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO USER (name, email, password_hash, role, level, created_at) VALUES (?, ?, ?, ?, ?, CURDATE())")
            ->execute([$name, $email, $passwordHash, $role, $level]);
    }
    header('Location: /admin/index.php?tab=users');
    exit;
}

// Сохранение модуля
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_module'])) {
    $id = (int)($_POST['module_id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $courseId = (int)($_POST['course_id'] ?? 0);

    if ($id > 0) {
        $pdo->prepare("UPDATE `Subject-Module` SET title=?, description=?, course_id=? WHERE id=?")
            ->execute([$title, $description, $courseId, $id]);
    } else {
        $pdo->prepare("INSERT INTO `Subject-Module` (title, description, course_id) VALUES (?, ?, ?)")
            ->execute([$title, $description, $courseId]);
    }
    header('Location: /admin/index.php?tab=modules');
    exit;
}

// Изменение статуса заявки
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_status'])) {
    $enrollId = (int)$_POST['enrollment_id'];
    $statusId = (int)$_POST['status_id'];
    $pdo->prepare("UPDATE Enrollments SET status_id=? WHERE id=?")->execute([$statusId, $enrollId]);
    header('Location: /admin/index.php?tab=enrollments');
    exit;
}

// ====== ДАННЫЕ ======
$stats = [
    'courses' => $pdo->query("SELECT COUNT(*) FROM Courses")->fetchColumn(),
    'users' => $pdo->query("SELECT COUNT(*) FROM USER")->fetchColumn(),
    'enrollments' => $pdo->query("SELECT COUNT(*) FROM Enrollments")->fetchColumn(),
    'modules' => $pdo->query("SELECT COUNT(*) FROM `Subject-Module`")->fetchColumn(),
];

$courses = $pdo->query("SELECT * FROM Courses ORDER BY id DESC")->fetchAll();
$users = $pdo->query("SELECT * FROM USER ORDER BY id DESC")->fetchAll();
$modules = $pdo->query("
    SELECT m.*, c.title as course_title 
    FROM `Subject-Module` m 
    LEFT JOIN Courses c ON m.course_id = c.id 
    ORDER BY m.id DESC
")->fetchAll();
$statuses = $pdo->query("SELECT * FROM Statuses ORDER BY id")->fetchAll();
$enrollments = $pdo->query("
    SELECT e.*, u.name as user_name, u.email, c.title as course_title, s.display_name_rus as status_name, s.name as status_code
    FROM Enrollments e
    JOIN USER u ON e.user_id = u.id
    JOIN Courses c ON e.course_id = c.id
    JOIN Statuses s ON e.status_id = s.id
    ORDER BY e.created_at DESC
")->fetchAll();

$editCourse = null;
if (isset($_GET['edit_course'])) {
    $stmt = $pdo->prepare("SELECT * FROM Courses WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_course']]);
    $editCourse = $stmt->fetch();
}

$editUser = null;
if (isset($_GET['edit_user'])) {
    $stmt = $pdo->prepare("SELECT * FROM USER WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_user']]);
    $editUser = $stmt->fetch();
}

$editModule = null;
if (isset($_GET['edit_module'])) {
    $stmt = $pdo->prepare("SELECT * FROM `Subject-Module` WHERE id = ?");
    $stmt->execute([(int)$_GET['edit_module']]);
    $editModule = $stmt->fetch();
}

$tab = $_GET['tab'] ?? 'dashboard';

$statusColors = [
    'new' => '#3498db',
    'in_progress' => '#f39c12',
    'confirmed' => '#2ecc71',
    'rejected' => '#e74c3c',
    'completed' => '#9b59b6',
];

$roleColors = [
    'student' => '#3498db',
    'teacher' => '#f39c12',
    'admin' => '#e74c3c',
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель — InterSpeak</title>
    <link rel="stylesheet" href="/../assets/css/style.css">
</head>
<body class="admin-body">
    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <div class="admin-logo">
                <h2>⚙️ InterSpeak</h2>
                <p>Админ-панель</p>
            </div>
            <ul class="admin-menu">
                <li><a href="?tab=dashboard" class="<?= $tab === 'dashboard' ? 'active' : '' ?>">📊 Главная</a></li>
                <li><a href="?tab=catalog" class="<?= $tab === 'catalog' || $tab === 'course_form' ? 'active' : '' ?>">📚 Курсы</a></li>
                <li><a href="?tab=modules" class="<?= $tab === 'modules' || $tab === 'module_form' ? 'active' : '' ?>">📖 Модули</a></li>
                <li><a href="?tab=users" class="<?= $tab === 'users' || $tab === 'user_form' ? 'active' : '' ?>">👥 Пользователи</a></li>
                <li><a href="?tab=enrollments" class="<?= $tab === 'enrollments' ? 'active' : '' ?>">📝 Заявки</a></li>
                <li><a href="../index.php">🏠 На сайт</a></li>
                <li><a href="../handlers/logout.php">🚪 Выйти</a></li>
            </ul>
        </aside>

        <main class="admin-content">
            <div class="admin-header">
                <h1>
                    <?php
                    switch($tab) {
                        case 'dashboard': echo '📊 Панель управления'; break;
                        case 'catalog': echo '📚 Управление курсами'; break;
                        case 'course_form': echo $editCourse ? '✏️ Редактирование курса' : '➕ Добавление курса'; break;
                        case 'modules': echo '📖 Управление модулями'; break;
                        case 'module_form': echo $editModule ? '✏️ Редактирование модуля' : '➕ Добавление модуля'; break;
                        case 'users': echo '👥 Пользователи'; break;
                        case 'user_form': echo $editUser ? '✏️ Редактирование пользователя' : '➕ Добавление пользователя'; break;
                        case 'enrollments': echo '📝 Заявки пользователей'; break;
                        default: echo 'Панель управления';
                    }
                    ?>
                </h1>
                <div class="admin-user">
                    <span>👤 <?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="../handlers/logout.php" class="btn btn-delete">Выйти</a>
                </div>
            </div>

            <!-- ====== ГЛАВНАЯ ====== -->
            <?php if ($tab === 'dashboard'): ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Курсов</h3>
                    <div class="number"><?= $stats['courses'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Модулей</h3>
                    <div class="number"><?= $stats['modules'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Пользователей</h3>
                    <div class="number"><?= $stats['users'] ?></div>
                </div>
                <div class="stat-card">
                    <h3>Заявок</h3>
                    <div class="number"><?= $stats['enrollments'] ?></div>
                </div>
            </div>
            <div class="welcome-section">
                <h2>Добро пожаловать, <?= htmlspecialchars($_SESSION['user_name']) ?>!</h2>
                <p>Управляйте курсами, модулями, пользователями и заявками через меню слева.</p>
            </div>
            <?php endif; ?>

            <!-- ====== КУРСЫ ====== -->
            <?php if ($tab === 'catalog'): ?>
            <a href="?tab=course_form" class="btn btn-primary" style="margin-bottom: 25px;">+ Добавить курс</a>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Название</th><th>Уровень</th><th>Цена</th><th>Действия</th></tr></thead>
                <tbody>
                    <?php foreach ($courses as $course): ?>
                    <tr>
                        <td><?= $course['id'] ?></td>
                        <td><?= htmlspecialchars($course['title']) ?></td>
                        <td><span class="level-badge"><?= htmlspecialchars($course['level']) ?></span></td>
                        <td><?= number_format($course['price'], 0, '', ' ') ?> ₽</td>
                        <td>
                            <a href="?tab=course_form&edit_course=<?= $course['id'] ?>" class="btn btn-edit">✏️</a>
                            <a href="?tab=catalog&delete_course=<?= $course['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить курс?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- ====== ФОРМА КУРСА ====== -->
            <?php if ($tab === 'course_form'): ?>
            <form method="POST" style="max-width: 600px;">
                <input type="hidden" name="course_id" value="<?= $editCourse['id'] ?? 0 ?>">
                <input type="hidden" name="save_course" value="1">
                <div class="form-group">
                    <label>Название курса</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($editCourse['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" required><?= htmlspecialchars($editCourse['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Уровень</label>
                    <select name="level">
                        <?php foreach (['A1','A2','B1','B2','C1','C2'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= ($editCourse['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Цена (₽)</label>
                    <input type="number" name="price" value="<?= $editCourse['price'] ?? 0 ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
                <a href="?tab=catalog" class="btn btn-secondary">Отмена</a>
            </form>
            <?php endif; ?>

            <!-- ====== МОДУЛИ ====== -->
            <?php if ($tab === 'modules'): ?>
            <a href="?tab=module_form" class="btn btn-primary" style="margin-bottom: 25px;">+ Добавить модуль</a>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Название</th><th>Курс</th><th>Действия</th></tr></thead>
                <tbody>
                    <?php foreach ($modules as $module): ?>
                    <tr>
                        <td><?= $module['id'] ?></td>
                        <td><?= htmlspecialchars($module['title']) ?></td>
                        <td><?= htmlspecialchars($module['course_title'] ?? '—') ?></td>
                        <td>
                            <a href="?tab=module_form&edit_module=<?= $module['id'] ?>" class="btn btn-edit">✏️</a>
                            <a href="?tab=modules&delete_module=<?= $module['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить модуль?')">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- ====== ФОРМА МОДУЛЯ ====== -->
            <?php if ($tab === 'module_form'): ?>
            <form method="POST" style="max-width: 600px;">
                <input type="hidden" name="module_id" value="<?= $editModule['id'] ?? 0 ?>">
                <input type="hidden" name="save_module" value="1">
                <div class="form-group">
                    <label>Название модуля</label>
                    <input type="text" name="title" value="<?= htmlspecialchars($editModule['title'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Описание</label>
                    <textarea name="description" required><?= htmlspecialchars($editModule['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Курс</label>
                    <select name="course_id" required>
                        <?php foreach ($courses as $course): ?>
                        <option value="<?= $course['id'] ?>" <?= ($editModule['course_id'] ?? 0) == $course['id'] ? 'selected' : '' ?>><?= htmlspecialchars($course['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
                <a href="?tab=modules" class="btn btn-secondary">Отмена</a>
            </form>
            <?php endif; ?>

            <!-- ====== ПОЛЬЗОВАТЕЛИ ====== -->
            <?php if ($tab === 'users'): ?>
            <a href="?tab=user_form" class="btn btn-primary" style="margin-bottom: 25px;">+ Добавить пользователя</a>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th>Уровень</th><th>Действия</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= $user['id'] ?></td>
                        <td><?= htmlspecialchars($user['name']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><span class="role-badge" style="background: <?= $roleColors[$user['role']] ?? '#666' ?>"><?= $user['role'] ?></span></td>
                        <td><?= htmlspecialchars($user['level'] ?? '—') ?></td>
                        <td>
                            <a href="?tab=user_form&edit_user=<?= $user['id'] ?>" class="btn btn-edit">✏️</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="?tab=users&delete_user=<?= $user['id'] ?>" class="btn btn-delete" onclick="return confirm('Удалить пользователя?')">🗑️</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>

            <!-- ====== ФОРМА ПОЛЬЗОВАТЕЛЯ ====== -->
            <?php if ($tab === 'user_form'): ?>
            <form method="POST" style="max-width: 600px;">
                <input type="hidden" name="user_id" value="<?= $editUser['id'] ?? 0 ?>">
                <input type="hidden" name="save_user" value="1">
                <div class="form-group">
                    <label>Имя</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($editUser['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($editUser['email'] ?? '') ?>" required>
                </div>
                <?php if (!$editUser): ?>
                <div class="form-group">
                    <label>Пароль</label>
                    <input type="password" name="password" placeholder="Оставьте пустым для 'password123'">
                </div>
                <?php endif; ?>
                <div class="form-group">
                    <label>Роль</label>
                    <select name="role">
                        <?php foreach (['student' => 'Студент', 'teacher' => 'Преподаватель', 'admin' => 'Администратор'] as $role => $label): ?>
                        <option value="<?= $role ?>" <?= ($editUser['role'] ?? '') === $role ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Уровень английского</label>
                    <select name="level">
                        <option value="">—</option>
                        <?php foreach (['elementary', 'pre-intermediate', 'intermediate', 'upper-intermediate', 'advanced', 'proficient'] as $lvl): ?>
                        <option value="<?= $lvl ?>" <?= ($editUser['level'] ?? '') === $lvl ? 'selected' : '' ?>><?= $lvl ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">💾 Сохранить</button>
                <a href="?tab=users" class="btn btn-secondary">Отмена</a>
            </form>
            <?php endif; ?>

            <!-- ====== ЗАЯВКИ ====== -->
            <?php if ($tab === 'enrollments'): ?>
            <table class="admin-table">
                <thead><tr><th>ID</th><th>Пользователь</th><th>Email</th><th>Курс</th><th>Дата</th><th>Статус</th><th>Изменить</th></tr></thead>
                <tbody>
                    <?php foreach ($enrollments as $e): 
                        $color = $statusColors[$e['status_code']] ?? '#666';
                    ?>
                    <tr>
                        <td><?= $e['id'] ?></td>
                        <td><?= htmlspecialchars($e['user_name']) ?></td>
                        <td><?= htmlspecialchars($e['email']) ?></td>
                        <td><?= htmlspecialchars($e['course_title']) ?></td>
                        <td><?= $e['created_at'] ?></td>
                        <td><span class="status-badge" style="background: <?= $color ?>20; color: <?= $color ?>;"><?= htmlspecialchars($e['status_name']) ?></span></td>
                        <td>
                            <form method="POST" class="status-form">
                                <input type="hidden" name="enrollment_id" value="<?= $e['id'] ?>">
                                <input type="hidden" name="change_status" value="1">
                                <select name="status_id">
                                    <?php foreach ($statuses as $s): ?>
                                    <option value="<?= $s['id'] ?>" <?= $e['status_id'] == $s['id'] ? 'selected' : '' ?>><?= htmlspecialchars($s['display_name_rus']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-primary" style="padding: 8px 16px; font-size: 14px;">✓</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>