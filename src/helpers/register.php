<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => []];

$input = array_merge($_POST, $_GET);

$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$confirmPassword = $input['confirm_password'] ?? '';

if (empty($name)) {
    $response['errors']['name'] = 'Введите имя';
}

if (empty($email)) {
    $response['errors']['email'] = 'Введите email';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['errors']['email'] = 'Некорректный email';
}

if (empty($password)) {
    $response['errors']['password'] = 'Введите пароль';
} elseif (mb_strlen($password) < 6) {
    $response['errors']['password'] = 'Пароль должен быть не менее 6 символов';
}

if (empty($confirmPassword)) {
    $response['errors']['confirm_password'] = 'Подтвердите пароль';
} elseif ($password !== $confirmPassword) {
    $response['errors']['confirm_password'] = 'Пароли не совпадают';
}

if (empty($response['errors'])) {
    $stmt = $pdo->prepare("SELECT id FROM user WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $response['errors']['email'] = 'Этот email уже зарегистрирован';
    }
}

if (!empty($response['errors'])) {
    echo json_encode($response);
    exit;
}

$passwordHash = password_hash($password, PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("INSERT INTO user (name, email, password_hash, role) VALUES (?, ?, ?, 'student')");
    $stmt->execute([$name, $email, $passwordHash]);

    $response['success'] = true;
    $response['message'] = 'Регистрация успешна! Теперь вы можете войти.';
} catch (PDOException $e) {
    $response['errors']['general'] = 'Ошибка при регистрации';
    echo $e->getMessage();
}

echo json_encode($response);