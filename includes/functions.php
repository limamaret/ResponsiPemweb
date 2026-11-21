<?php
declare(strict_types=1);

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Sesi formulir tidak valid atau sudah kedaluwarsa. Silakan kembali dan coba lagi.');
    }
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function require_role(string $role): void {
    $user = current_user();
    if (!$user) {
        redirect('../index.php');
    }
    if (($user['role'] ?? '') !== $role) {
        http_response_code(403);
        exit('Akses ditolak. Akun Anda tidak memiliki izin untuk halaman ini.');
    }
}

function dashboard_for_role(string $role): string {
    return match ($role) {
        'admin' => 'admin/dashboard.php',
        'job_seeker' => 'job-seeker/dashboard.php',
        'recruiter' => 'recruiter/dashboard.php',
        default => 'index.php',
    };
}

function flash(string $key, ?string $message = null): ?string {
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}
