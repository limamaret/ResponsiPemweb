<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ? AND role = 'admin' LIMIT 1");
    $stmt->execute([$user['id']]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($currentPassword, $row['password'])) {
        $error = 'Password saat ini tidak sesuai.';
    } elseif ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Nama dan alamat email harus diisi dengan format yang valid.';
    } elseif ($newPassword !== '' && strlen($newPassword) < 8) {
        $error = 'Password baru minimal 8 karakter.';
    } elseif ($newPassword !== '' && $newPassword !== $confirmPassword) {
        $error = 'Konfirmasi password baru tidak sama.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
        $check->execute([$email, $user['id']]);
        if ($check->fetch()) {
            $error = 'Email tersebut sudah digunakan akun lain.';
        } else {
            if ($newPassword !== '') {
                $update = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ? AND role = 'admin'");
                $update->execute([$name, $email, password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
            } else {
                $update = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ? AND role = 'admin'");
                $update->execute([$name, $email, $user['id']]);
            }
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $user = current_user();
            $success = 'Informasi akun administrator berhasil diperbarui.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Pengaturan Akun Admin — Rangkul</title><link rel="stylesheet" href="../assets/css/style.css"><script src="../assets/js/password-toggle.js" defer></script></head>
<body class="auth-body admin-auth">
<main class="admin-auth-card">
<a class="brand" href="../index.php"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a>
<span class="eyebrow">PENGATURAN ADMINISTRATOR</span><h2>Ubah Informasi Akun</h2>
<p class="muted">Perbarui nama, email, atau password administrator.</p>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="notice"><?= e($success) ?></div><?php endif; ?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Nama administrator<input name="name" value="<?= e($user['name']) ?>" required></label>
<label>Email administrator<input type="email" name="email" value="<?= e($user['email']) ?>" required></label>
<label>Password saat ini<input type="password" name="current_password" autocomplete="current-password" required></label>
<hr>
<p class="muted">Kosongkan password baru jika hanya ingin mengubah nama atau email.</p>
<label>Password baru (minimal 8 karakter)<input type="password" name="new_password" minlength="8" autocomplete="new-password"></label>
<label>Konfirmasi password baru<input type="password" name="confirm_password" minlength="8" autocomplete="new-password"></label>
<button class="btn btn-primary btn-block" type="submit">Simpan Perubahan</button>
</form>
<a class="back-link centered" href="dashboard.php">← Kembali ke dashboard</a>
</main><script src="../assets/js/custom-cursor.js" defer></script></body></html>
