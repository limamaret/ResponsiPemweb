<?php
require_once __DIR__ . '/../includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (login_user($pdo, $email, $password, 'admin')) redirect('dashboard.php');
    $error = 'Kredensial Admin tidak valid.';
}
$title='Administrator Login'; $subtitle='Area terbatas untuk pengelola platform Rangkul.'; $roleLabel='ADMINISTRATOR';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= e($title) ?> — Rangkul</title><link rel="stylesheet" href="../assets/css/style.css"><script src="../assets/js/password-toggle.js" defer></script></head><body class="auth-body admin-auth">
<div class="admin-auth-card"><a class="brand" href="../index.php"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a><div class="admin-lock">♙</div><span class="eyebrow">RESTRICTED AREA</span><h2>Admin Sign In</h2><p class="muted">Masuk untuk mengelola dan memantau platform.</p>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<form method="post" class="form-stack"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email administrator<input type="email" name="email" placeholder="admin@rangkul.id" autocomplete="username" required></label>
<label>Password<input type="password" name="password" placeholder="Masukkan password admin" autocomplete="current-password" required></label>
<button class="btn btn-primary btn-block" type="submit">Login Administrator</button></form>
<a class="back-link centered" href="../index.php">← Kembali ke website</a><small class="muted">Akun Admin dibuat melalui setup database, bukan registrasi publik.</small></div><script src="../assets/js/custom-cursor.js" defer></script></body></html>
