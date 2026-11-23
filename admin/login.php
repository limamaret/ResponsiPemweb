<?php
require_once __DIR__ . '/../includes/auth.php';
$error = '';
$success = flash('success');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (login_user($pdo, $email, $password, 'admin')) redirect('dashboard.php');
    $error = 'Kredensial Admin tidak valid.';
}
$title='Administrator Login'; $subtitle='Area terbatas untuk pengelola platform Rangkul.'; $roleLabel='ADMINISTRATOR';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($title) ?> — Rangkul</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet"><link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="admin.css"><script src="../assets/js/password-toggle.js" defer></script><script src="../assets/js/custom-cursor.js" defer></script></head><body class="auth-body admin-auth rangkul-admin-login">
<div class="admin-auth-card"><a class="brand" href="../index.php"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a><span class="eyebrow">AREA ADMINISTRATOR</span><h2>Masuk sebagai Admin</h2><p class="muted">Kelola pengguna, perusahaan, dan aktivitas platform Rangkul.</p>
<?php if ($success): ?><div class="notice success" role="status"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email administrator<input type="email" name="email" placeholder="admin@rangkul.id" autocomplete="username" required></label>
<label>Password<input type="password" name="password" placeholder="Masukkan password admin" autocomplete="current-password" required></label>
<button class="btn btn-primary btn-block" type="submit">Masuk</button></form>
<a class="back-link centered" href="../index.php">← Kembali ke website</a></div></body></html>
