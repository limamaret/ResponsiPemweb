<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

function login_user(PDO $pdo, string $email, string $password, string $expectedRole): bool {
    $stmt = $pdo->prepare('SELECT id, name, email, password, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return false;
    }
    if ($user['role'] !== $expectedRole || $user['status'] !== 'active') {
        return false;
    }

    session_regenerate_id(true);
    unset($user['password']);
    $_SESSION['user'] = $user;
    return true;
}
