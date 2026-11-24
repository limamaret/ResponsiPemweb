<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/models.php';
require_once __DIR__ . '/includes/functions.php';
require_role('job_seeker');

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);

$vacancyId = isset($_GET['vacancy']) ? (int)$_GET['vacancy'] : 0;
if (!$vacancyId) {
    flash('error', 'Lowongan tidak valid.');
    redirect('vacancies.php');
}

$vacancy = vacancy_get_public_detail($vacancyId);
if (!$vacancy) {
    flash('error', 'Lowongan tidak ditemukan atau sudah ditutup.');
    redirect('vacancies.php');
}

// Check if already applied
$seekerId = $seeker ? $seeker['id'] : 0;
if ($seekerId && application_exists($vacancyId, $seekerId)) {
    flash('error', 'Anda sudah melamar lowongan ini.');
    redirect('vacancy-detail.php?id=' . $vacancyId);
}

// Check minimal profile
if (!$seeker || !seeker_has_minimal_profile($user['id'])) {
    flash('error', 'Lengkapi profil minimal (telepon, alamat, pendidikan, pengalaman, atau keahlian) sebelum melamar.');
    redirect('profile.php');
}

$success = flash('success');
$error = flash('error');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    
    $cvChoice = $_POST['cv_choice'] ?? 'profile';
    $coverLetter = trim($_POST['cover_letter'] ?? '');
    $cvFile = null;

    if ($cvChoice === 'upload') {
        if (!empty($_FILES['cv_file']['tmp_name']) && is_uploaded_file($_FILES['cv_file']['tmp_name'])) {
            $upload = upload_cv($_FILES['cv_file'], $user['id']);
            if ($upload['success']) {
                $cvFile = $upload['filename'];
            } else {
                $error = $upload['error'];
            }
        } else {
            $error = 'File CV wajib diupload jika memilih "Upload CV baru".';
        }
    } else {
        // Use profile CV
        if ($seeker && $seeker['cv_file']) {
            $cvFile = $seeker['cv_file'];
        } else {
            $error = 'Anda belum mengupload CV di profil. Silakan upload CV baru atau lengkapi profil terlebih dahulu.';
        }
    }

    if (!$error) {
        $appId = application_create($vacancyId, $seekerId, $cvFile, $coverLetter ?: null);
        if ($appId) {
            flash('success', 'Lamaran berhasil dikirim! Perusahaan akan meninjau profil Anda.');
            redirect('job-seeker/applications.php');
        } else {
            $error = 'Gagal mengirim lamaran. Silakan coba lagi.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lamar Lowongan — Rangkul</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Layout */
.apply-layout { max-width: 800px; margin: 0 auto; padding: 40px 24px; }
.apply-header { margin-bottom: 32px; }
.apply-header .eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 1.3px; color: #2563eb; margin-bottom: 8px; }
.apply-header h1 { font-size: 28px; letter-spacing: -1px; margin: 0 0 8px; }
.apply-header p { color: #64748b; margin: 0; }

/* Vacancy Summary */
.vacancy-summary { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 24px; margin-bottom: 24px; display: flex; gap: 16px; }
.vacancy-summary-logo { width: 56px; height: 56px; border-radius: 12px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
.vacancy-summary-info { flex: 1; }
.vacancy-summary-title { font-size: 16px; font-weight: 700; color: #1e293b; margin: 0 0 4px; }
.vacancy-summary-company { font-size: 14px; color: #64748b; margin: 0 0 8px; }
.vacancy-summary-meta { display: flex; flex-wrap: wrap; gap: 12px; font-size: 13px; color: #64748b; }
.vacancy-summary-meta span { display: flex; align-items: center; gap: 6px; }

/* Form */
.form-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; }
.form-section { margin-bottom: 28px; padding-bottom: 28px; border-bottom: 1px solid #f1f5f9; }
.form-section:last-of-type { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.form-section h2 { font-size: 16px; font-weight: 700; margin: 0 0 16px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.form-section h2::before { content: ''; width: 8px; height: 8px; background: #2563eb; border-radius: 2px; }
.form-group { display: flex; flex-direction: column; gap: 8px; }
.form-group label { font-size: 13px; font-weight: 600; color: #334155; }
.form-group small { font-size: 12px; color: #94a3b8; }
.form-group input[type="radio"] { accent-color: #2563eb; }
.form-group textarea { min-height: 120px; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 10px; font: 14px Inter; resize: vertical; transition: .15s; }
.form-group textarea:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.form-group input[type="file"] { padding: 10px; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 10px; }
.radio-options { display: flex; flex-direction: column; gap: 12px; }
.radio-option { display: flex; align-items: flex-start; gap: 12px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px; cursor: pointer; transition: .15s; }
.radio-option:hover { border-color: #cbd5e1; background: #fafbfc; }
.radio-option input:checked + .radio-content { border-color: #2563eb; background: #eff6ff; }
.radio-option input:checked ~ .radio-content { }
.radio-content { flex: 1; }
.radio-content strong { display: block; color: #1e293b; margin-bottom: 4px; }
.radio-content p { margin: 0; font-size: 13px; color: #64748b; }
.current-cv { display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 10px; font-size: 13px; color: #166534; }
.current-cv a { color: #166534; font-weight: 600; }

/* Buttons */
.form-actions { display: flex; justify-content: flex-end; gap: 12px; margin-top: 8px; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent; border-radius: 10px; padding: 12px 20px; font: 600 14px Inter; cursor: pointer; text-decoration: none !important; transition: .2s; }
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-outline { border-color: #d1d5db; color: #334155; background: #fff; }
.btn-outline:hover { border-color: #2563eb; color: #2563eb; }

/* Notice */
.notice { padding: 14px 18px; border-radius: 10px; font-size: 14px; margin-bottom: 20px; }
.notice.success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.notice.error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

@media (max-width: 640px) {
    .vacancy-summary { flex-direction: column; align-items: flex-start; }
    .form-actions { flex-direction: column; }
    .form-actions .btn { width: 100%; }
}
</style>
</head>
<body class="landing-body">
<a class="skip-link" href="#main-content">Lewati ke konten utama</a>
<header class="landing-nav"><div class="landing-nav-inner">
<a class="landing-brand" href="index.php" aria-label="Rangkul, halaman utama"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a>
<nav class="landing-links" aria-label="Navigasi utama"><a href="index.php#tentang">Tentang</a><a href="index.php#keunggulan">Keunggulan</a><a href="index.php#cara-kerja">Cara kerja</a><a href="index.php#fitur">Fitur</a><a href="index.php#aksesibilitas">Aksesibilitas</a><a href="index.php#faq">FAQ</a></nav>
<div class="landing-nav-actions">
<?php if ($user): ?>
    <span style="font-size:13px;font-weight:600;color:#536078;"><?= e($user['name']) ?></span>
    <a class="nav-login" href="<?= dashboard_for_role($user['role']) ?>">Dashboard</a>
    <a class="btn btn-outline" href="logout.php" style="padding:10px 16px;font-size:12px;">Logout</a>
<?php else: ?>
    <a class="nav-login" href="login-job-seeker.php">Masuk</a>
    <a class="btn btn-primary" href="register-job-seeker.php" style="padding:10px 16px;font-size:12px;">Daftar</a>
<?php endif; ?>
<button class="mobile-menu" type="button" aria-label="Buka navigasi" aria-expanded="false">☰</button>
</div></div></header>
<main id="main-content" class="apply-layout">

    <?php if ($success): ?><div class="notice success"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>

    <header class="apply-header">
        <span class="eyebrow">RANGKUL / LAMARAN</span>
        <h1>Lamar: <?= e($vacancy['title']) ?></h1>
        <p>Isi formulir di bawah untuk mengirim lamaran ke <strong><?= e($vacancy['company_name']) ?></strong>.</p>
    </header>

    <div class="vacancy-summary">
        <?php if ($vacancy['company_logo']): ?>
            <img class="vacancy-summary-logo" src="../uploads/logos/<?= e($vacancy['company_logo']) ?>" alt="<?= e($vacancy['company_name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('<?= e($vacancy['company_name']) ?>') + '&background=2563eb&color=fff&size=56'">
        <?php else: ?>
            <div class="vacancy-summary-logo" style="display:grid;place-items:center;font-weight:800;color:#fff;background:#2563eb;"><?= strtoupper(mb_substr($vacancy['company_name'], 0, 1)) ?></div>
        <?php endif; ?>
        <div class="vacancy-summary-info">
            <h3 class="vacancy-summary-title"><?= e($vacancy['title']) ?></h3>
            <p class="vacancy-summary-company"><?= e($vacancy['company_name']) ?></p>
            <div class="vacancy-summary-meta">
                <span>📍 <?= e($vacancy['location']) ?></span>
                <span><?= e(match($vacancy['work_type']) { 'onsite' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid' }) ?></span>
                <span>📅 Batas: <?= date('d M Y', strtotime($vacancy['deadline'])) ?></span>
            </div>
        </div>
    </div>

    <form method="post" enctype="multipart/form-data" class="form-card">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <section class="form-section">
            <h2>Pilih CV</h2>
            <div class="radio-options">
                <label class="radio-option">
                    <input type="radio" name="cv_choice" value="profile" checked>
                    <div class="radio-content">
                        <strong>Gunakan CV dari Profil</strong>
                        <p><?= $seeker && $seeker['cv_file'] ? 'File: ' . e($seeker['cv_file']) : 'Belum ada CV di profil' ?></p>
                    </div>
                </label>
                <?php if ($seeker && $seeker['cv_file']): ?>
                    <div class="current-cv">
                        <span>CV saat ini: <a href="../uploads/cv/<?= e($seeker['cv_file']) ?>" target="_blank"><?= e($seeker['cv_file']) ?></a></span>
                        <span>Digunakan otomatis jika opsi ini dipilih</span>
                    </div>
                <?php endif; ?>
                <label class="radio-option">
                    <input type="radio" name="cv_choice" value="upload">
                    <div class="radio-content">
                        <strong>Upload CV Baru (PDF, max 2 MB)</strong>
                        <p>File akan disimpan ke profil Anda dan digunakan untuk lamaran ini</p>
                    </div>
                </label>
            </div>
            <div class="form-group" id="cvUploadGroup" style="display:none;margin-top:16px;">
                <label>File CV</label>
                <input type="file" name="cv_file" accept="application/pdf" id="cvFileInput">
                <small>Hanya file PDF, maksimal 2 MB.</small>
            </div>
        </section>

        <section class="form-section">
            <h2>Cover Letter <span style="font-weight:400;font-size:13px;color:#64748b;">(Opsional)</span></h2>
            <div class="form-group">
                <label>Tulis alasan Anda melamar, keunggulan Anda, atau catatan tambahan untuk rekruter</label>
                <textarea name="cover_letter" placeholder="Contoh: Saya tertarik dengan posisi ini karena..."></textarea>
            </div>
        </section>

        <div class="form-actions">
            <a href="vacancy-detail.php?id=<?= $vacancyId ?>" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">Kirim Lamaran</button>
        </div>
    </form>

</main>
<footer class="landing-footer"><div class="footer-main"><div class="footer-about"><a class="landing-brand footer-brand" href="index.php"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a><p class="footer-tagline">Merangkul potensi, membuka kesempatan.</p><p class="footer-description">Rangkul adalah ruang digital yang mempertemukan pencari kerja dan perusahaan dalam proses rekrutmen yang lebih terarah, mudah dipahami, dan inklusif. Kami percaya setiap orang berhak mendapatkan kesempatan untuk berkembang.</p></div><div class="footer-column"><b>Jelajahi</b><a href="index.php#tentang">Tentang Rangkul</a><a href="index.php#cara-kerja">Cara kerja</a><a href="index.php#fitur">Fitur platform</a></div><div class="footer-column"><b>Bantuan</b><a href="index.php#aksesibilitas">Aksesibilitas</a><a href="index.php#faq">Pertanyaan umum</a><a href="admin/login.php">Administrator</a></div><div class="footer-column footer-purpose"><b>Komitmen Rangkul</b><p>Mendorong kesempatan kerja yang lebih terbuka dan setara, sejalan dengan Tujuan Pembangunan Berkelanjutan nomor 8.</p><span>SDG 8 · Decent Work and Economic Growth</span></div></div><div class="footer-bottom"><span>© <?= date('Y') ?> Rangkul. Semua hak dilindungi.</span><span>Dibangun untuk kesempatan yang lebih inklusif.</span></div></footer>

<script>
const cvChoiceRadios = document.querySelectorAll('input[name="cv_choice"]');
const cvUploadGroup = document.getElementById('cvUploadGroup');
cvChoiceRadios.forEach(radio => {
    radio.addEventListener('change', () => {
        cvUploadGroup.style.display = radio.value === 'upload' && radio.checked ? 'block' : 'none';
    });
});
</script>
</body>
</html>