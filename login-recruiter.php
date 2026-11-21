<?php
require_once __DIR__ . '/includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    if (login_user($pdo, $email, $password, 'recruiter')) redirect('recruiter/dashboard.php');
    $error = 'Email atau password salah, akun tidak aktif, atau akun bukan Recruiter.';
}
$title='Masuk ke akun Recruiter'; $subtitle='Kelola lowongan dan temukan kandidat terbaik untuk perusahaanmu.'; $roleLabel='RECRUITER / PERUSAHAAN';
include __DIR__ . '/includes/auth-form.php';
?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Email perusahaan<input type="email" name="email" placeholder="hr@perusahaan.com" autocomplete="username" required></label>
<label>Password<input type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required></label>
<div class="form-row"><label class="check"><input type="checkbox" name="remember_hint"> Ingat saya</label><span class="muted tiny">Lupa password? Hubungi admin.</span></div>
<button class="btn btn-primary btn-block" type="submit">Masuk sebagai Recruiter</button>
</form><p class="switch-auth">Belum punya akun perusahaan? <a href="register-recruiter.php">Daftar Recruiter</a></p>
<p class="switch-auth muted tiny">Sedang mencari pekerjaan? <a href="login-job-seeker.php">Login Job Seeker</a></p>
</div></main></div><script src="assets/js/custom-cursor.js" defer></script></body></html>
