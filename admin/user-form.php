<?php
require_once __DIR__ . '/_layout.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$editing = $id > 0;
$errors = [];
$originalRole = null;
$data = ['name' => '', 'email' => '', 'role' => 'job_seeker', 'status' => 'active'];

if ($editing) {
    $q = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE id = ? LIMIT 1');
    $q->execute([$id]);
    $found = $q->fetch();
    if (!$found) {
        http_response_code(404);
        exit('Pengguna tidak ditemukan.');
    }
    $data = $found;
    $originalRole = $found['role'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $data['name'] = trim($_POST['name'] ?? '');
    $data['email'] = strtolower(trim($_POST['email'] ?? ''));
    $data['role'] = $_POST['role'] ?? 'job_seeker';
    $data['status'] = $_POST['status'] ?? 'active';
    $password = (string)($_POST['password'] ?? '');
    $isSelf = $editing && $id === (int)current_user()['id'];

    if ($data['name'] === '' || mb_strlen($data['name']) > 120) $errors[] = 'Nama wajib diisi dan maksimal 120 karakter.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 190) $errors[] = 'Email tidak valid.';
    if (!in_array($data['role'], ['admin', 'job_seeker', 'company'], true)) $errors[] = 'Role tidak valid.';
    if (!in_array($data['status'], ['active', 'pending', 'inactive'], true)) $errors[] = 'Status tidak valid.';
    if (!$editing && strlen($password) < 8) $errors[] = 'Password pengguna baru minimal 8 karakter.';
    if ($editing && $password !== '' && strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';

    if ($isSelf && ($data['role'] !== 'admin' || $data['status'] !== 'active')) {
        $errors[] = 'Admin tidak dapat mengubah role atau menonaktifkan akun sendiri.';
    }

    $q = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1');
    $q->execute([$data['email'], $editing ? $id : 0]);
    if ($q->fetch()) $errors[] = 'Email sudah digunakan.';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            if ($editing) {
                $sql = 'UPDATE users SET name = ?, email = ?, role = ?, status = ?';
                $params = [$data['name'], $data['email'], $data['role'], $data['status']];
                if ($password !== '') {
                    $sql .= ', password = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $sql .= ' WHERE id = ?';
                $params[] = $id;
                $pdo->prepare($sql)->execute($params);
                $userId = $id;

                // Jika akun recruiter pindah role, tutup lowongan lamanya dan
                // kembalikan perusahaan ke antrean verifikasi agar listing
                // tidak tetap tampil tanpa pemilik recruiter yang aktif.
                if ($originalRole === 'company' && $data['role'] !== 'company') {
                    $pdo->prepare("UPDATE vacancies SET status = 'closed' WHERE company_id IN (SELECT id FROM companies WHERE user_id = ?)")
                        ->execute([$userId]);
                    $pdo->prepare("UPDATE companies SET verification_status = 'pending' WHERE user_id = ?")
                        ->execute([$userId]);
                }
            } else {
                $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, ?)')
                    ->execute([$data['name'], $data['email'], password_hash($password, PASSWORD_DEFAULT), $data['role'], $data['status']]);
                $userId = (int)$pdo->lastInsertId();
            }

            // Jaga relasi users 1:1 dengan profil untuk role yang memerlukan profil.
            if ($data['role'] === 'job_seeker') {
                $pdo->prepare('INSERT IGNORE INTO job_seekers (user_id) VALUES (?)')->execute([$userId]);
            } elseif ($data['role'] === 'company') {
                $pdo->prepare("INSERT IGNORE INTO companies (user_id, name, verification_status) VALUES (?, ?, 'pending')")
                    ->execute([$userId, $data['name']]);
                if ($editing && $originalRole !== 'company') {
                    $pdo->prepare("UPDATE companies SET verification_status = 'pending' WHERE user_id = ?")
                        ->execute([$userId]);
                }
            }

            $pdo->commit();

            if ($isSelf) {
                $_SESSION['user']['name'] = $data['name'];
                $_SESSION['user']['email'] = $data['email'];
            }
            flash('success', $editing ? 'Data pengguna berhasil diperbarui.' : 'Pengguna baru berhasil ditambahkan.');
            redirect('users.php');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors[] = 'Data pengguna gagal disimpan. Periksa kembali email dan struktur database.';
        }
    }
}

admin_header($editing ? 'Edit Pengguna' : 'Tambah Pengguna', 'users');
?>
<section class="admin-page-intro"><div><h2><?= $editing ? 'Perbarui informasi akun pengguna.' : 'Buat akun baru untuk bergabung dengan Rangkul.' ?></h2><p><?= $editing ? 'Periksa kembali identitas, peran, dan status akun.' : 'Isi informasi dasar dan tentukan akses yang sesuai dengan peran pengguna.' ?></p></div></section>
<section class="admin-panel admin-form-panel user-editor-panel">
<?php if ($errors): ?><div class="notice error" role="alert"><ul><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<div class="form-section-heading"><h3>Informasi pengguna</h3><p>Identitas dan akses akun Rangkul.</p></div>
<form method="post" class="admin-form">
<input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
<input type="hidden" name="id" value="<?= $id ?>">
<label>Nama lengkap<input name="name" maxlength="120" value="<?= e($data['name']) ?>" required></label>
<label>Email<input type="email" name="email" maxlength="190" value="<?= e($data['email']) ?>" required></label>
<label>Role<select name="role" <?= $editing && $id === (int)current_user()['id'] ? 'disabled' : '' ?>>
<?php foreach (['admin'=>'Admin','job_seeker'=>'Job Seeker','company'=>'Perusahaan'] as $key => $label): ?>
<option value="<?= e($key) ?>"<?= $data['role'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?></select>
<?php if ($editing && $id === (int)current_user()['id']): ?><input type="hidden" name="role" value="admin"><?php endif; ?></label>
<label>Status<select name="status" <?= $editing && $id === (int)current_user()['id'] ? 'disabled' : '' ?>>
<?php foreach (['active'=>'Aktif','pending'=>'Menunggu','inactive'=>'Nonaktif'] as $key => $label): ?>
<option value="<?= e($key) ?>"<?= $data['status'] === $key ? ' selected' : '' ?>><?= e($label) ?></option>
<?php endforeach; ?></select>
<?php if ($editing && $id === (int)current_user()['id']): ?><input type="hidden" name="status" value="active"><?php endif; ?></label>
<label><?= $editing ? 'Password baru (opsional)' : 'Password awal' ?><input type="password" name="password" minlength="8" <?= $editing ? '' : 'required' ?> autocomplete="new-password"><small><?= $editing ? 'Kosongkan jika tidak ingin mengganti password.' : 'Minimal 8 karakter.' ?></small></label>
<div class="admin-form-actions"><button class="btn btn-primary" type="submit">Simpan Pengguna</button><a class="btn btn-outline" href="users.php">Batal</a></div>
</form></section>
<?php admin_footer(); ?>
