<?php
require_once __DIR__ . '/includes/auth.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $company = trim($_POST['company_name'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if ($company === '' || $name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $error = 'Nama perusahaan, nama recruiter, dan email valid wajib diisi.';
    elseif (strlen($password) < 8) $error = 'Password minimal 8 karakter.';
    elseif ($password !== $confirm) $error = 'Konfirmasi password tidak sama.';
    else {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password,role,status) VALUES (?,?,?,'recruiter','pending')");
            $stmt->execute([$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
            $userId = (int)$pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO companies (user_id, company_name, phone, verification_status) VALUES (?,?,?,'pending')");
            $stmt->execute([$userId,$company,$phone]);
            $pdo->commit();
            flash('success','Pendaftaran Recruiter berhasil. Akun menunggu verifikasi Admin.');
            redirect('login-recruiter.php');
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = $e->getCode() === '23000' ? 'Email sudah terdaftar.' : 'Registrasi gagal. Silakan coba lagi.';
        }
    }
}
$title='Daftarkan perusahaan'; $subtitle='Buat akun Recruiter untuk mulai mencari kandidat.'; $roleLabel='REGISTRASI PERUSAHAAN';
include __DIR__ . '/includes/auth-form.php';
?>
<form method="post" class="form-stack">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Nama perusahaan<input name="company_name" value="<?= e($_POST['company_name'] ?? '') ?>" placeholder="PT Contoh Indonesia" required></label>
<label>Nama recruiter<input name="name" value="<?= e($_POST['name'] ?? '') ?>" placeholder="Nama lengkap" required></label>
<label>Email perusahaan<input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="hr@perusahaan.com" required></label>
<label>Nomor telepon<input type="tel" name="phone" value="<?= e($_POST['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx"></label>
<label>Password<input type="password" name="password" placeholder="Minimal 8 karakter" minlength="8" required></label>
<label>Konfirmasi password<input type="password" name="confirm_password" placeholder="Ulangi password" required></label>
<label class="check"><input type="checkbox" required> Saya menyetujui syarat dan ketentuan Rangkul.</label>
<button class="btn btn-primary btn-block" type="submit">Daftar sebagai Recruiter</button>
</form><p class="switch-auth">Sudah punya akun? <a href="login-recruiter.php">Login Recruiter</a></p>
</div></main></div><script src="assets/js/custom-cursor.js" defer></script></body></html>
