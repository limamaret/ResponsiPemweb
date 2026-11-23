<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/models.php';
require_role('job_seeker');

$user = current_user();
$success = flash('success');
$error = flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        if ($name === '' || strlen($name) > 120) {
            $error = 'Nama wajib diisi dan maksimal 120 karakter.';
        } else {
            try {
                $stmt = $pdo->prepare('UPDATE users SET name = ? WHERE id = ?');
                $stmt->execute([$name, $user['id']]);
                $_SESSION['user']['name'] = $name;
                flash('success', 'Nama berhasil diperbarui.');
                redirect('account.php');
            } catch (PDOException $e) {
                $error = 'Gagal memperbarui nama.';
            }
        }
    } elseif ($action === 'update_photo') {
        if (!empty($_FILES['photo']['tmp_name']) && is_uploaded_file($_FILES['photo']['tmp_name'])) {
            $file = $_FILES['photo'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 2 * 1024 * 1024;

            if (!in_array($file['type'], $allowedTypes)) {
                $error = 'Format gambar tidak didukung. Gunakan JPG, PNG, atau WebP.';
            } elseif ($file['size'] > $maxSize) {
                $error = 'Ukuran file maksimal 2 MB.';
            } else {
                $ext = match($file['type']) {
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp',
                    default => 'jpg'
                };
                $filename = 'photo_' . $user['id'] . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
                $uploadDir = __DIR__ . '/../uploads/photos/';
                if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
                $dest = $uploadDir . $filename;

                if (move_uploaded_file($file['tmp_name'], $dest)) {
                    // Delete old photo
                    if ($user['photo']) {
                        $oldPath = $uploadDir . $user['photo'];
                        if (is_file($oldPath)) @unlink($oldPath);
                    }
                    $stmt = $pdo->prepare('UPDATE users SET photo = ? WHERE id = ?');
                    $stmt->execute([$filename, $user['id']]);
                    $_SESSION['user']['photo'] = $filename;
                    flash('success', 'Foto profil berhasil diperbarui.');
                    redirect('account.php');
                } else {
                    $error = 'Gagal mengupload foto.';
                }
            }
        } else {
            $error = 'Pilih file foto terlebih dahulu.';
        }
    } elseif ($action === 'delete_photo') {
        if ($user['photo']) {
            $uploadDir = __DIR__ . '/../uploads/photos/';
            $oldPath = $uploadDir . $user['photo'];
            if (is_file($oldPath)) @unlink($oldPath);
            $stmt = $pdo->prepare('UPDATE users SET photo = NULL WHERE id = ?');
            $stmt->execute([$user['id']]);
            $_SESSION['user']['photo'] = null;
            flash('success', 'Foto profil dihapus.');
            redirect('account.php');
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Reload user with password hash from DB (session doesn't have it)
        $stmt = $pdo->prepare('SELECT password FROM users WHERE id = ?');
        $stmt->execute([$user['id']]);
        $dbUser = $stmt->fetch();

        if ($current === '' || $new === '' || $confirm === '') {
            $error = 'Semua kolom password wajib diisi.';
        } elseif (!$dbUser || !password_verify($current, $dbUser['password'])) {
            $error = 'Password saat ini salah.';
        } elseif (strlen($new) < 8) {
            $error = 'Password baru minimal 8 karakter.';
        } elseif ($new !== $confirm) {
            $error = 'Konfirmasi password tidak sama.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('UPDATE users SET password = ? WHERE id = ?');
            $stmt->execute([$hash, $user['id']]);
            flash('success', 'Password berhasil diubah.');
            redirect('account.php');
        }
    }
}

// Refresh user data
$stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$stmt->execute([$user['id']]);
$user = $stmt->fetch();
$_SESSION['user'] = $user;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Akun Saya — Rangkul</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
/* Job Seeker Layout */
.seeker-layout { display: grid; grid-template-columns: 260px 1fr; min-height: 100vh; }
.seeker-sidebar { background: #f8fafc; border-right: 1px solid #e2e8f0; padding: 24px 16px; position: sticky; top: 76px; height: calc(100vh - 76px); overflow-y: auto; }
.seeker-sidebar .brand { margin-bottom: 32px; }
.seeker-nav { display: flex; flex-direction: column; gap: 4px; }
.seeker-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; color: #475569; font-size: 14px; font-weight: 500; text-decoration: none; transition: .15s; }
.seeker-nav a:hover { background: #eef2ff; color: #2563eb; }
.seeker-nav a.is-active { background: #2563eb; color: #fff; }
.seeker-nav a.is-active:hover { background: #1d4ed8; color: #fff; }
.seeker-main { padding: 32px max(calc((100% - 1160px)/2), 24px); background: #f7f9fc; min-height: calc(100vh - 76px); }
.seeker-header { margin-bottom: 24px; }
.seeker-header .eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 1.3px; color: #2563eb; margin-bottom: 8px; }
.seeker-header h1 { font-size: 28px; letter-spacing: -1px; margin: 0 0 4px; }
.seeker-header p { color: #64748b; margin: 0; }

/* Form */
.form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; max-width: 720px; }
.form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid #f1f5f9; }
.form-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.form-section h2 { font-size: 16px; font-weight: 700; margin: 0 0 20px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.form-section h2::before { content: ''; width: 8px; height: 8px; background: #2563eb; border-radius: 2px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 13px; font-weight: 600; color: #334155; }
.form-group small { font-size: 12px; color: #94a3b8; }
.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="password"],
.form-group input[type="file"] { height: 46px; border: 1px solid #d1d5db; border-radius: 10px; padding: 0 14px; font: 14px Inter; background: #fff; transition: .15s; }
.form-group input:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.form-group input[type="file"] { padding: 10px; background: #f9fafb; }
.current-photo { display: flex; align-items: center; gap: 16px; margin-bottom: 16px; }
.current-photo img { width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #e2e8f0; }
.current-photo .photo-actions { display: flex; flex-direction: column; gap: 8px; }
.btn-danger { background: #fee2e2; color: #991b1b; border-color: #fecaca; }
.btn-danger:hover { background: #fecaca; color: #991b1b; }
.password-field { position: relative; }
.password-field input { padding-right: 120px !important; }
.password-toggle { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); border: 0; background: transparent; color: #2563eb; font: 600 11px Inter; padding: 7px 5px; cursor: pointer; border-radius: 6px; }
.password-toggle:hover { background: #eff6ff; }

@media (max-width: 900px) {
    .seeker-layout { grid-template-columns: 1fr; }
    .seeker-sidebar { position: fixed; left: -100%; top: 0; width: 280px; height: 100vh; z-index: 50; background: #fff; box-shadow: 4px 0 20px rgba(0,0,0,.1); transition: left .25s; padding-top: 80px; border-right: none; }
    .seeker-sidebar.open { left: 0; }
    .seeker-main { padding: 24px 16px; }
    .mobile-menu-btn { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 13px; font-weight: 600; }
}
@media (min-width: 901px) {
    .mobile-menu-btn { display: none; }
}
.overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 40; }
.overlay.show { display: block; }
</style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<div class="seeker-layout">
    <aside class="seeker-sidebar" id="sidebar">
        <a class="brand" href="dashboard.php"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a>
        <nav class="seeker-nav" aria-label="Navigasi Job Seeker">
            <a href="dashboard.php"><span>✦</span> Dashboard</a>
            <a href="profile.php"><span>👤</span> Profil Saya</a>
            <a href="applications.php"><span>📄</span> Riwayat Lamaran</a>
            <a href="interviews.php"><span>📅</span> Jadwal Wawancara</a>
            <a href="account.php" class="is-active"><span>⚙️</span> Akun Saya</a>
        </nav>
    </aside>
    <main class="seeker-main">
        <header class="topbar" style="position:sticky;top:0;z-index:30;">
            <a class="brand" href="../index.php"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a>
            <div class="user-nav" style="display:flex;align-items:center;gap:16px;">
                <button class="mobile-menu-btn" id="menuBtn" aria-label="Buka menu" aria-expanded="false">☰ Menu</button>
                <span><?= e($user['name']) ?></span>
                <a class="btn btn-outline" href="../logout.php">Logout</a>
            </div>
        </header>
        <div class="seeker-header">
            <span class="eyebrow">RANGKUL / AKUN</span>
            <h1>Akun Saya</h1>
            <p>Kelola nama, foto profil, dan password Anda.</p>
        </div>

        <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

        <!-- Profile Info -->
        <form method="post" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_profile">
            <section class="form-section">
                <h2>Informasi Akun</h2>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" value="<?= e($user['name']) ?>" required maxlength="120">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" value="<?= e($user['email']) ?>" disabled style="background:#f9fafb;color:#64748b;">
                    <small>Email tidak dapat diubah. Hubungi admin jika perlu mengubah email.</small>
                </div>
                <div class="form-group">
                    <label>Role</label>
                    <input type="text" value="<?= e(ucwords(str_replace('_', ' ', $user['role']))) ?>" disabled style="background:#f9fafb;color:#64748b;">
                </div>
            </section>
            <button type="submit" class="btn btn-primary">Simpan Nama</button>
        </form>

        <!-- Photo -->
        <form method="post" enctype="multipart/form-data" class="form-card" style="margin-top:20px;">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="update_photo">
            <section class="form-section">
                <h2>Foto Profil</h2>
                <div class="current-photo">
                    <img id="photoPreview" src="<?= $user['photo'] ? '../uploads/photos/' . e($user['photo']) : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=2563eb&color=fff&size=160' ?>" alt="Foto profil">
                    <div class="photo-actions">
                        <span style="font-size:14px;color:#334155;"><?= $user['photo'] ? 'Foto saat ini' : 'Menggunakan avatar default' ?></span>
                        <?php if ($user['photo']): ?>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Hapus foto profil?')">
                                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                <input type="hidden" name="action" value="delete_photo">
                                <button type="submit" class="btn btn-danger" style="padding:8px 14px;font-size:12px;">Hapus Foto</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-group">
                    <label>Ganti Foto (JPG/PNG/WebP, max 2 MB)</label>
                    <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" id="photoInput" onchange="previewPhoto(this)">
                    <small>Rekomendasi: foto persegi (1:1), wajah jelas, latar sederhana.</small>
                </div>
            </section>
            <button type="submit" class="btn btn-primary">Upload Foto</button>
        </form>

        <!-- Password -->
        <form method="post" class="form-card" style="margin-top:20px;">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="action" value="change_password">
            <section class="form-section">
                <h2>Ubah Password</h2>
                <div class="form-group">
                    <label>Password Saat Ini</label>
                    <div class="password-field">
                        <input type="password" name="current_password" autocomplete="current-password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">Tampilkan</button>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <div class="password-field">
                        <input type="password" name="new_password" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">Tampilkan</button>
                    </div>
                    <small>Minimal 8 karakter.</small>
                </div>
                <div class="form-group">
                    <label>Konfirmasi Password Baru</label>
                    <div class="password-field">
                        <input type="password" name="confirm_password" autocomplete="new-password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword(this)">Tampilkan</button>
                    </div>
                </div>
            </section>
            <button type="submit" class="btn btn-primary">Ubah Password</button>
        </form>
    </main>
</div>
<script>
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const menuBtn = document.getElementById('menuBtn');
menuBtn?.addEventListener('click', () => {
    const open = sidebar.classList.toggle('open');
    overlay.classList.toggle('show', open);
    menuBtn.setAttribute('aria-expanded', open);
});
overlay?.addEventListener('click', () => {
    sidebar.classList.remove('open');
    overlay.classList.remove('show');
    menuBtn.setAttribute('aria-expanded', 'false');
});

function togglePassword(btn) {
    const input = btn.previousElementSibling;
    if (input.type === 'password') {
        input.type = 'text';
        btn.textContent = 'Sembunyikan';
    } else {
        input.type = 'password';
        btn.textContent = 'Tampilkan';
    }
}

function previewPhoto(input) {
    const preview = document.getElementById('photoPreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => preview.src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html>