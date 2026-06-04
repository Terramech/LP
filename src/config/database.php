<?php

$host = 'localhost';
$db   = 'proj';
$user = 'root';
$pass = '3LEETT0Glory';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";


try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("PDO Connection Error: " . $e->getMessage());
    die("Ошибка подключения к базе данных.");
}
