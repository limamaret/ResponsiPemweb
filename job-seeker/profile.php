<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_role('job_seeker');

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);
$success = flash('success');
$error = flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $data = [
        'phone' => trim($_POST['phone'] ?? ''),
        'birthdate' => $_POST['birthdate'] ?? null,
        'address' => trim($_POST['address'] ?? ''),
        'education' => trim($_POST['education'] ?? ''),
        'experience' => trim($_POST['experience'] ?? ''),
        'skills' => trim($_POST['skills'] ?? ''),
        'disability_type' => trim($_POST['disability_type'] ?? ''),
        'accommodation_needs' => trim($_POST['accommodation_needs'] ?? ''),
    ];
    // Convert empty strings to null for date/optional fields
    foreach ($data as $key => $val) {
        if ($val === '') $data[$key] = null;
    }

    // Handle CV upload
    if (!empty($_FILES['cv_file']['tmp_name']) && is_uploaded_file($_FILES['cv_file']['tmp_name'])) {
        $upload = upload_cv($_FILES['cv_file'], $user['id']);
        if ($upload['success']) {
            // Delete old CV if exists
            if ($seeker && $seeker['cv_file']) {
                delete_cv_file($seeker['cv_file']);
            }
            $data['cv_file'] = $upload['filename'];
        } else {
            $error = $upload['error'];
        }
    }

    if (!$error) {
        if ($seeker) {
            if (seeker_update($user['id'], $data)) {
                flash('success', 'Profil berhasil diperbarui.');
                redirect('profile.php');
            } else {
                $error = 'Gagal memperbarui profil.';
            }
        } else {
            $id = seeker_create($user['id'], $data);
            if ($id) {
                flash('success', 'Profil berhasil dibuat.');
                redirect('profile.php');
            } else {
                $error = 'Gagal membuat profil.';
            }
        }
    }

    // Refresh seeker data after POST
    $seeker = seeker_get_by_user_id($user['id']);
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Profil Saya — Rangkul</title>
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
.form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; }
.form-section { margin-bottom: 32px; padding-bottom: 32px; border-bottom: 1px solid #f1f5f9; }
.form-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.form-section h2 { font-size: 16px; font-weight: 700; margin: 0 0 20px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.form-section h2::before { content: ''; width: 8px; height: 8px; background: #2563eb; border-radius: 2px; }
.form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; }
.form-grid > .full { grid-column: 1 / -1; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 13px; font-weight: 600; color: #334155; }
.form-group small { font-size: 12px; color: #94a3b8; }
.form-group input[type="text"],
.form-group input[type="email"],
.form-group input[type="tel"],
.form-group input[type="date"],
.form-group input[type="number"],
.form-group textarea,
.form-group select { height: 46px; border: 1px solid #d1d5db; border-radius: 10px; padding: 0 14px; font: 14px Inter; background: #fff; transition: .15s; }
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.form-group textarea { min-height: 100px; padding: 12px 14px; resize: vertical; }
.form-group input[type="file"] { padding: 10px; background: #f9fafb; }
.current-file { font-size: 13px; color: #64748b; margin-top: 8px; }
.current-file a { color: #2563eb; font-weight: 500; }
.btn-submit { margin-top: 8px; }

@media (max-width: 900px) {
    .seeker-layout { grid-template-columns: 1fr; }
    .seeker-sidebar { position: fixed; left: -100%; top: 0; width: 280px; height: 100vh; z-index: 50; background: #fff; box-shadow: 4px 0 20px rgba(0,0,0,.1); transition: left .25s; padding-top: 80px; border-right: none; }
    .seeker-sidebar.open { left: 0; }
    .seeker-main { padding: 24px 16px; }
    .form-grid { grid-template-columns: 1fr; }
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
            <a href="profile.php" class="is-active"><span>👤</span> Profil Saya</a>
            <a href="applications.php"><span>📄</span> Riwayat Lamaran</a>
            <a href="interviews.php"><span>📅</span> Jadwal Wawancara</a>
            <a href="account.php"><span>⚙️</span> Akun Saya</a>
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
            <span class="eyebrow">RANGKUL / PROFIL</span>
            <h1>Profil Saya</h1>
            <p>Kelola informasi profesional dan kebutuhan akomodasi Anda. Data ini digunakan saat melamar lowongan.</p>
        </div>

        <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="form-card">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

            <section class="form-section">
                <h2>Informasi Pribadi</h2>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Nomor Telepon</label>
                        <input type="tel" name="phone" value="<?= e($seeker['phone'] ?? '') ?>" placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="form-group">
                        <label>Tanggal Lahir</label>
                        <input type="date" name="birthdate" value="<?= e($seeker['birthdate'] ?? '') ?>">
                    </div>
                    <div class="form-group full">
                        <label>Alamat Lengkap</label>
                        <textarea name="address" placeholder="Alamat tempat tinggal saat ini"><?= e($seeker['address'] ?? '') ?></textarea>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h2>Pendidikan & Pengalaman</h2>
                <div class="form-grid">
                    <div class="form-group full">
                        <label>Riwayat Pendidikan</label>
                        <textarea name="education" placeholder="Contoh: S1 Informatika - Universitas X (2018-2022); SMA Y (2015-2018)"><?= e($seeker['education'] ?? '') ?></textarea>
                        <small>Tulis singkat: jenjang, jurusan, institusi, tahun lulus</small>
                    </div>
                    <div class="form-group full">
                        <label>Pengalaman Kerja</label>
                        <textarea name="experience" placeholder="Contoh: Software Engineer di PT ABC (2022-sekarang); Magang di PT XYZ (2021)"><?= e($seeker['experience'] ?? '') ?></textarea>
                        <small>Tulis singkat: posisi, perusahaan, periode, deskripsi tugas singkat</small>
                    </div>
                    <div class="form-group full">
                        <label>Keahlian (Skills)</label>
                        <textarea name="skills" placeholder="Contoh: PHP, Laravel, MySQL, JavaScript, React, Git, REST API"><?= e($seeker['skills'] ?? '') ?></textarea>
                        <small>Pisahkan dengan koma. Contoh: PHP, Laravel, MySQL, JavaScript</small>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h2>Informasi Disabilitas & Akomodasi <span style="font-weight:400;font-size:13px;color:#64748b;">(Opsional)</span></h2>
                <p style="font-size:13px;color:#64748b;margin:0 0 16px;">Informasi ini <b>hanya ditampilkan kepada perusahaan yang menerima lamaran Anda</b>, tidak terlihat publik. Berguna agar perusahaan menyiapkan akomodasi selama proses seleksi.</p>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Jenis Disabilitas</label>
                        <select name="disability_type">
                            <option value="">-- Pilih --</option>
                            <option value="netra" <?= ($seeker['disability_type'] ?? '') === 'netra' ? 'selected' : '' ?>>Netra (Tunanetra)</option>
                            <option value="rungu" <?= ($seeker['disability_type'] ?? '') === 'rungu' ? 'selected' : '' ?>>Rungu (Tunarungu)</option>
                            <option value="fisik" <?= ($seeker['disability_type'] ?? '') === 'fisik' ? 'selected' : '' ?>>Fisik (Disabilitas Fisik)</option>
                            <option value="wicara" <?= ($seeker['disability_type'] ?? '') === 'wicara' ? 'selected' : '' ?>>Wicara (Tunawicara)</option>
                            <option value="mental" <?= ($seeker['disability_type'] ?? '') === 'mental' ? 'selected' : '' ?>>Mental/Psikososial</option>
                            <option value="intelektual" <?= ($seeker['disability_type'] ?? '') === 'intelektual' ? 'selected' : '' ?>>Intelektual</option>
                            <option value="lainnya" <?= ($seeker['disability_type'] ?? '') === 'lainnya' ? 'selected' : '' ?>>Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group full">
                        <label>Kebutuhan Akomodasi</label>
                        <textarea name="accommodation_needs" placeholder="Contoh: Butuh juru bahasa isyarat untuk wawancara; Akses kursi roda ke ruang wawancara; Waktu tambahan untuk tes tertulis; Materi dalam format Braille/akses screen reader"><?= e($seeker['accommodation_needs'] ?? '') ?></textarea>
                        <small>Jelaskan akomodasi yang dibutuhkan selama proses rekrutmen (wawancara, tes, dll)</small>
                    </div>
                </div>
            </section>

            <section class="form-section">
                <h2>Curriculum Vitae (CV)</h2>
                <div class="form-group">
                    <label>Upload CV (PDF, maksimal 2 MB)</label>
                    <input type="file" name="cv_file" accept="application/pdf">
                    <?php if ($seeker && $seeker['cv_file']): ?>
                        <div class="current-file">File saat ini: <a href="../uploads/cv/<?= e($seeker['cv_file']) ?>" target="_blank"><?= e($seeker['cv_file']) ?></a> — akan diganti jika upload file baru</div>
                    <?php endif; ?>
                    <small>Gunakan CV dari profil saat melamar, atau upload yang baru saat melamar.</small>
                </div>
            </section>

            <button type="submit" class="btn btn-primary btn-submit">Simpan Perubahan</button>
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
</script>
</body>
</html>