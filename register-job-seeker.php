<?php
require_once __DIR__ . '/includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Nama dan email yang valid wajib diisi.';
    elseif (strlen($password) < 8) $error = 'Password minimal 8 karakter.';
    elseif ($password !== $confirm) $error = 'Konfirmasi password tidak sama.';
    else {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,'job_seeker','active')");
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
            flash('success','Akun Job Seeker berhasil dibuat. Silakan login.');
            redirect('login-job-seeker.php');
        } catch (PDOException $e) {
            $error = $e->getCode() === '23000' ? 'Email sudah terdaftar.' : 'Registrasi gagal. Silakan coba lagi.';
        }
    }
}
$title='Buat akun Job Seeker'; $subtitle='Mulai temukan peluang kerja yang sesuai dengan potensimu.'; $roleLabel='REGISTRASI PENCARI KERJA';
include __DIR__ . '/includes/auth-form.php';
?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Nama lengkap<input name="name" value="<?= e($_POST['name'] ?? '') ?>" placeholder="Nama sesuai identitas" autocomplete="name" required></label>
<label>Email<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="nama@email.com" autocomplete="email" required></label>
<label>Password<input type="password" name="password" placeholder="Minimal 8 karakter" autocomplete="new-password" minlength="8" required></label>
<label>Konfirmasi password<input type="password" name="confirm_password" placeholder="Ulangi password" autocomplete="new-password" required></label>
<label class="check"><input type="checkbox" required> Saya menyetujui syarat dan ketentuan Rangkul.</label>
<button class="btn btn-primary btn-block" type="submit">Buat Akun Job Seeker</button>
</form><p class="switch-auth">Sudah punya akun? <a href="login-job-seeker.php">Login</a></p>
<p class="switch-auth muted tiny">Mewakili perusahaan? <a href="register-recruiter.php">Daftar sebagai Perusahaan</a></p>
</div></main></div><script src="assets/js/custom-cursor.js" defer></script></body></html>
