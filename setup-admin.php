<?php
// Jalankan sekali melalui browser, lalu HAPUS file ini dari folder project.
require_once __DIR__ . '/config.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $secret = $_POST['setup_secret'] ?? '';
    if (!hash_equals('CHANGE-THIS-SETUP-KEY', $secret)) {
        $message = 'Setup key salah. Ubah setup key di file setup-admin.php sebelum digunakan.';
    } else {
        $name = trim($_POST['name'] ?? 'Administrator');
        $email = strtolower(trim($_POST['email'] ?? 'admin@rangkul.id'));
        $password = $_POST['password'] ?? '';
        if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 8) {
            $message = 'Isi nama, email valid, dan password minimal 8 karakter.';
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,'admin','active') ON DUPLICATE KEY UPDATE name=VALUES(name), password=VALUES(password), role='admin', status='active'");
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
            $message = 'Admin berhasil dibuat/diperbarui. HAPUS setup-admin.php sekarang.';
        }
    }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Setup Admin</title><link rel="stylesheet" href="assets/css/style.css"></head><body class="auth-body"><div class="admin-auth-card"><h2>Setup Administrator</h2><p class="muted">Ganti setup key di file sebelum menjalankan halaman ini. Hapus file setelah selesai.</p><?php if($message): ?><div class="notice"><?= htmlspecialchars($message,ENT_QUOTES,'UTF-8') ?></div><?php endif; ?><form method="post" class="form-stack"><label>Setup key<input name="setup_secret" required></label><label>Nama<input name="name" value="Administrator" required></label><label>Email<input type="email" name="email" value="admin@rangkul.id" required></label><label>Password (min. 8 karakter)<input type="password" name="password" minlength="8" required></label><button class="btn btn-primary btn-block">Buat Admin</button></form></div></body></html>
