<?php
require_once __DIR__ . '/../config/database.php';
session_start();

header('Content-Type: application/json');

$response = ['success' => false, 'errors' => []];

$input = array_merge($_POST, $_GET);

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    $response['errors']['general'] = 'Заполните все поля';
    echo json_encode($response);
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, email, password_hash, role FROM user WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $response['errors']['general'] = 'Неверный email или пароль';
    echo json_encode($response);
    exit;
}

if (!password_verify($password, $user['password_hash'])) {
    $response['errors']['general'] = 'Неверный email или пароль';
    echo json_encode($response);
    exit;
}

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_role'] = $user['role'];

$response['success'] = true;
$response['redirect'] = '/index.php';

echo json_encode($response);