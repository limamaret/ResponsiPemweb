<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$uid = (int)current_user()['id'];
$errors = [];
$name = current_user()['name'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = trim($_POST['name'] ?? '');
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || mb_strlen($name) > 120) $errors['name'] = 'Nama wajib diisi (maksimal 120 karakter).';
    if ($new !== '') {
        $row = $pdo->prepare('SELECT password FROM users WHERE id = ? LIMIT 1'); $row->execute([$uid]); $hash = (string)$row->fetchColumn();
        if (!password_verify($current, $hash)) $errors['current_password'] = 'Password saat ini tidak sesuai.';
        if (strlen($new) < 8) $errors['new_password'] = 'Password baru minimal 8 karakter.';
        elseif ($new !== $confirm) $errors['confirm_password'] = 'Konfirmasi password baru tidak sama.';
    }
    $newPhoto = null;
    if (!$errors) {
        try { $newPhoto = save_upload($_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'photos', image_types()); }
        catch (RuntimeException $ex) { $errors['photo'] = $ex->getMessage() . ' Foto harus berformat JPG, PNG, atau WebP, maksimal 2 MB.'; }
    }
    if (!$errors) {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE users SET name = ? WHERE id = ?')->execute([$name, $uid]);
        if ($new !== '') $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $uid]);
        if ($newPhoto !== null) $pdo->prepare('UPDATE users SET photo = ? WHERE id = ?')->execute([$newPhoto, $uid]);
        $pdo->commit();
        if ($newPhoto !== null) delete_upload($company['user_photo'], 'photos');
        $_SESSION['user']['name'] = $name;
        if ($new !== '') session_regenerate_id(true);
        flash('success', 'Informasi akun berhasil diperbarui.');
        redirect('account.php');
    }
}
$err = fn(string $k) => isset($errors[$k]) ? '<span class="field-error">' . e($errors[$k]) . '</span>' : '';
company_header('Akun Saya', 'account', $company);
?>
<div class="page-head"><div><span class="eyebrow">AKUN SAYA</span><h1>Pengaturan akun</h1><p>Ubah nama, foto, atau password Anda. Email: <b><?= e(current_user()['email']) ?></b></p></div></div>
<?php if ($errors): ?><div class="notice error" role="alert">Masih ada isian yang perlu diperbaiki. Periksa pesan di bawah setiap kolom.</div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="co-form card" novalidate style="max-width:680px"><?= csrf_field() ?>
<div class="field"><label for="name">Nama <span class="req" aria-hidden="true">*</span></label><input id="name" name="name" value="<?= e($name) ?>" required maxlength="120" autocomplete="name"><?= $err('name') ?></div>
<div class="field"><label for="photo">Foto profil</label><?php if ($company['user_photo']): ?><img class="co-avatar" style="width:64px;height:64px" src="../uploads/photos/<?= e(rawurlencode(basename($company['user_photo']))) ?>" alt="Foto profil saat ini"><?php endif; ?>
<input id="photo" name="photo" type="file" accept="image/jpeg,image/png,image/webp"><span class="hint">JPG, PNG, atau WebP, maksimal 2 MB.</span><?= $err('photo') ?></div>
<fieldset class="field-group"><legend>Ganti password (opsional)</legend><div class="co-form">
<div class="field"><label for="current_password">Password saat ini</label><input id="current_password" name="current_password" type="password" autocomplete="current-password"><?= $err('current_password') ?></div>
<div class="field"><label for="new_password">Password baru (minimal 8 karakter)</label><input id="new_password" name="new_password" type="password" minlength="8" autocomplete="new-password"><?= $err('new_password') ?></div>
<div class="field"><label for="confirm_password">Konfirmasi password baru</label><input id="confirm_password" name="confirm_password" type="password" autocomplete="new-password"><?= $err('confirm_password') ?></div></div></fieldset>
<div class="actions"><button class="btn btn-primary" type="submit">Simpan perubahan</button></div>
</form>
<?php company_footer();
