<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');
$user = current_user();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    if (($_POST['action'] ?? '') === 'delete_photo') {
        $oldPhoto = $user['photo'] ?? null;
        $stmt = $pdo->prepare("UPDATE users SET photo = NULL WHERE id = ? AND role = 'admin'");
        $stmt->execute([$user['id']]);
        $_SESSION['user']['photo'] = null;
        if ($oldPhoto) {
            $oldPath = __DIR__ . '/../uploads/photos/' . basename($oldPhoto);
            if (is_file($oldPath)) { @unlink($oldPath); }
        }
        $user = current_user();
        $success = 'Foto profil berhasil dihapus.';
    } elseif (($_POST['action'] ?? '') === 'upload_photo') {
        $file = $_FILES['profile_photo'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Pilih foto profil yang valid untuk diunggah.';
        } elseif ($file['size'] > 2 * 1024 * 1024) {
            $error = 'Ukuran foto maksimal 2 MB.';
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($file['tmp_name']);
            $allowed = ['image/jpeg'=>'jpg', 'image/png'=>'png', 'image/webp'=>'webp'];
            if (!isset($allowed[$mime]) || @getimagesize($file['tmp_name']) === false) {
                $error = 'Format foto harus JPG, PNG, atau WebP.';
            } else {
                $uploadDir = __DIR__ . '/../uploads/photos';
                if (!is_dir($uploadDir)) { mkdir($uploadDir, 0755, true); }
                $filename = 'admin_' . (int)$user['id'] . '_' . bin2hex(random_bytes(12)) . '.' . $allowed[$mime];
                if (move_uploaded_file($file['tmp_name'], $uploadDir . '/' . $filename)) {
                    $oldPhoto = $user['photo'] ?? null;
                    $stmt = $pdo->prepare("UPDATE users SET photo = ? WHERE id = ? AND role = 'admin'");
                    $stmt->execute([$filename, $user['id']]);
                    $_SESSION['user']['photo'] = $filename;
                    $user = current_user();
                    if ($oldPhoto && basename($oldPhoto) !== $filename) {
                        $oldPath = $uploadDir . '/' . basename($oldPhoto);
                        if (is_file($oldPath)) { @unlink($oldPath); }
                    }
                    $success = 'Foto profil berhasil diperbarui.';
                } else { $error = 'Foto gagal disimpan. Coba lagi.'; }
            }
        }
    } else {
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
}
?>
<?php

require_once __DIR__ . '/_layout.php';
admin_header('Pengaturan Akun','account');
?>
<section class="admin-page-intro"><div><h2>Jaga informasi akun administrator tetap akurat.</h2><p>Perbarui identitas akun atau ganti password. Verifikasi dengan password saat ini diperlukan saat menyimpan.</p></div></section>
<section class="account-layout">
<div class="account-sidebar-stack"><div class="account-side-note"><?php if (!empty($user['photo'])): ?><img class="account-avatar account-avatar-image" src="../uploads/photos/<?= e(rawurlencode(basename($user['photo']))) ?>" alt="Foto profil administrator"><?php else: ?><span class="account-avatar"><?= e(strtoupper(substr($user['name'] ?? 'A',0,1))) ?></span><?php endif; ?><h2><?= e($user['name']) ?></h2><p><?= e($user['email']) ?></p><span class="account-role">Administrator Rangkul</span></div>
<section class="admin-panel account-photo-panel"><div><h2>Foto profil</h2><p>Pilih foto dari perangkat, atur posisi dan zoom, lalu simpan hasil crop sebagai foto profil.</p></div><form method="post" enctype="multipart/form-data" class="account-photo-form" id="account-photo-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="upload_photo"><div class="account-photo-choices"><button type="button" class="btn btn-outline" id="profile-photo-camera">📷 Ambil Gambar</button><button type="button" class="btn btn-outline" id="profile-photo-upload">↥ Unggah Gambar</button></div><input type="file" id="profile-photo-source" accept="image/jpeg,image/png,image/webp" hidden><div class="account-photo-selected" id="account-photo-selected" aria-live="polite">Belum ada foto dipilih</div><input type="file" name="profile_photo" id="profile-photo-cropped" accept="image/jpeg,image/png,image/webp" hidden required><button class="btn btn-primary" type="submit" id="profile-photo-submit" disabled>Simpan Foto Profil</button></form><?php if (!empty($user['photo'])): ?><form method="post" class="account-photo-remove-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_photo"><button class="btn btn-outline btn-danger" type="submit" data-confirm-title="Hapus foto profil?" data-confirm="Foto profil administrator akan dihapus dan avatar kembali ke inisial nama." data-confirm-label="Ya, hapus foto" data-confirm-tone="danger">Hapus Foto Profil</button></form><?php endif; ?></section>
<div class="photo-camera-modal" id="photo-camera-modal" hidden role="dialog" aria-modal="true" aria-labelledby="photo-camera-title"><div class="photo-camera-dialog"><div class="photo-camera-head"><div><span class="admin-footer-kicker">KAMERA</span><h2 id="photo-camera-title">Ambil foto profil</h2><p>Posisikan wajah di tengah. Foto bisa disesuaikan setelah diambil.</p></div><button type="button" class="photo-crop-close" id="photo-camera-close" aria-label="Tutup kamera">×</button></div><div class="photo-camera-preview"><video id="photo-camera-video" autoplay playsinline muted></video></div><p class="photo-camera-message" id="photo-camera-message" role="status"></p><div class="photo-crop-actions"><button type="button" class="btn btn-outline" id="photo-camera-cancel">Batal</button><button type="button" class="btn btn-primary" id="photo-camera-capture" disabled>Ambil Foto</button></div></div></div>
<div class="photo-crop-modal" id="photo-crop-modal" hidden role="dialog" aria-modal="true" aria-labelledby="photo-crop-title"><div class="photo-crop-dialog"><div class="photo-crop-heading"><div><span class="admin-footer-kicker">PENYESUAIAN FOTO</span><h2 id="photo-crop-title">Atur foto profil</h2><p>Geser foto untuk mengatur posisi. Gunakan zoom agar hasilnya pas.</p></div><button type="button" class="photo-crop-close" id="photo-crop-close" aria-label="Tutup">×</button></div><div class="photo-crop-stage" id="photo-crop-stage"><img id="photo-crop-image" alt="Pratinjau crop foto profil" draggable="false"></div><div class="photo-crop-controls"><label>Perbesar / perkecil <input type="range" id="photo-crop-zoom" min="1" max="3" step="0.01" value="1"></label><span>Pratinjau berbentuk lingkaran di sidebar</span></div><div class="photo-crop-actions"><button type="button" class="btn btn-outline" id="photo-crop-cancel">Batal</button><button type="button" class="btn btn-primary" id="photo-crop-apply">Gunakan Foto</button></div></div></div></div>
<section class="admin-panel admin-form-panel account-form-panel">
<div class="account-form-heading"><span class="admin-footer-kicker">PENGATURAN ADMINISTRATOR</span><h2>Informasi akun</h2><p>Kelola identitas dan keamanan akun administrator Rangkul.</p></div>
<?php if ($error): ?><div class="notice error admin-toast"><?= e($error) ?></div><?php endif; ?>
<?php if ($success): ?><div class="notice success admin-toast"><?= e($success) ?></div><?php endif; ?>
<form method="post" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<label>Nama administrator<input name="name" value="<?= e($user['name']) ?>" required></label>
<label>Email administrator<input type="email" name="email" value="<?= e($user['email']) ?>" required></label>
<label>Password saat ini<input type="password" name="current_password" autocomplete="current-password" required></label>
<div class="account-form-divider"><h3>Keamanan akun</h3><p>Kosongkan password baru jika hanya ingin mengubah nama atau email.</p></div>
<label>Password baru (minimal 8 karakter)<input type="password" name="new_password" minlength="8" autocomplete="new-password"></label>
<label>Konfirmasi password baru<input type="password" name="confirm_password" minlength="8" autocomplete="new-password"></label>
<div class="admin-form-actions"><button class="btn btn-primary" type="submit">Simpan Perubahan</button><a class="btn btn-outline" href="dashboard.php">Kembali ke dashboard</a></div>
</form></section></section>
<?php admin_footer(); ?>
