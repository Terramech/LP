<?php

function isAuth(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireAuth(string $redirect = '/index.php'): void {
    if (!isAuth()) {
        header("Location: $redirect");
        exit;
    }
}

function requireAdmin(string $redirect = '/index.php'): void {
    if (!isAuth() || !isAdmin()) {
        header("Location: $redirect");
        exit;
    }
}