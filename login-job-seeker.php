<?php
require_once __DIR__ . '/includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (login_user($pdo, $email, $password, 'job_seeker')) redirect('job-seeker/dashboard.php');
    $error = 'Email atau password salah, akun tidak aktif, atau akun bukan Job Seeker.';
}
$title='Selamat datang kembali'; $subtitle='Masuk untuk menemukan dan mengelola peluang kariermu.'; $roleLabel='JOB SEEKER';
include __DIR__ . '/includes/auth-form.php';
?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email<input type="email" name="email" placeholder="nama@email.com" autocomplete="email" required></label>
<label>Password<input type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required></label>
<div class="form-row"><label class="check"><input type="checkbox" name="remember_hint"> Ingat saya</label><span class="muted tiny">Lupa password? Hubungi admin.</span></div>
<button class="btn btn-primary btn-block" type="submit">Masuk sebagai Job Seeker</button>
</form><p class="switch-auth">Belum punya akun? <a href="register-job-seeker.php">Daftar Job Seeker</a></p>
<p class="switch-auth muted tiny">Mewakili perusahaan? <a href="login-recruiter.php">Login Perusahaan</a></p>
</div></main></div><script src="assets/js/custom-cursor.js" defer></script></body></html>
