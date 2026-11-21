<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/models.php';
require_once __DIR__ . '/includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: vacancies.php');
    exit;
}

$vacancy = vacancy_get_public_detail($id);
if (!$vacancy) {
    http_response_code(404);
    include __DIR__ . '/404.php';
    exit;
}

$user = current_user();
$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($vacancy['title']) ?> — Rangkul</title>
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/landing-interactions.js" defer></script>
<script src="assets/js/custom-cursor.js" defer></script>
<style>
/* Layout */
.detail-layout { max-width: 1000px; margin: 0 auto; padding: 40px 24px; }
.detail-header { margin-bottom: 32px; }
.detail-header .eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 1.3px; color: #2563eb; margin-bottom: 8px; }
.detail-header h1 { font-size: 32px; letter-spacing: -1px; margin: 0 0 16px; line-height: 1.2; }
.detail-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 14px; color: #64748b; }
.detail-meta span { display: flex; align-items: center; gap: 6px; }
.detail-meta strong { color: #1e293b; }

/* Company Card */
.company-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 24px; display: flex; gap: 20px; }
.company-logo-large { width: 80px; height: 80px; border-radius: 16px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
.company-info { flex: 1; }
.company-name-row { display: flex; align-items: center; gap: 10px; margin-bottom: 8px; }
.company-name-row h2 { font-size: 20px; margin: 0; }
.company-badges { display: flex; gap: 8px; margin-bottom: 12px; }
.badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.3px; }
.badge-verified { background: #eff6ff; color: #2563eb; }
.badge-inclusive { background: #ecfdf5; color: #059669; }
.company-desc { color: #64748b; font-size: 14px; line-height: 1.7; margin-bottom: 12px; }
.company-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: #64748b; }
.company-meta span { display: flex; align-items: center; gap: 6px; }

/* Sections */
.section-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 28px; margin-bottom: 24px; }
.section-title { font-size: 18px; font-weight: 700; margin: 0 0 16px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.section-title::before { content: ''; width: 8px; height: 8px; background: #2563eb; border-radius: 2px; }
.section-content { color: #475569; line-height: 1.8; font-size: 15px; }
.section-content h4 { font-size: 14px; font-weight: 700; color: #1e293b; margin: 20px 0 8px; }
.section-content ul { margin: 0 0 16px 20px; padding: 0; }
.section-content li { margin-bottom: 8px; }
.section-content li::marker { color: #2563eb; }

/* Tags */
.tags-list { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 8px; }
.tag { font-size: 12px; font-weight: 600; padding: 6px 12px; border-radius: 99px; background: #f1f5f9; color: #475569; }
.tag.category { background: #eff6ff; color: #2563eb; }
.tag.work-type { background: #fef3c7; color: #d97706; }
.tag.accessibility { background: #f0fdf4; color: #166534; }
.tag.salary { background: #fce7f3; color: #be185d; }

/* Apply Bar */
.apply-bar { position: sticky; bottom: 0; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px 24px; margin-top: 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; box-shadow: 0 -4px 20px rgba(0,0,0,.05); }
.apply-bar-info { display: flex; flex-direction: column; gap: 4px; }
.apply-bar-deadline { font-size: 13px; color: #64748b; }
.apply-bar-deadline.urgent { color: #dc2626; font-weight: 600; }
.apply-btn { padding: 14px 28px; font-size: 15px; font-weight: 700; border-radius: 12px; white-space: nowrap; }
.apply-btn:disabled { opacity: 0.6; cursor: not-allowed; }

/* Accessibility Facilities */
.facility-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-top: 12px; }
.facility-item { display: flex; align-items: center; gap: 10px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 13px; color: #334155; }
.facility-item svg { width: 20px; height: 20px; color: #2563eb; flex-shrink: 0; }

/* Empty/Not Found */
.not-found { text-align: center; padding: 80px 20px; }
.not-found svg { width: 80px; height: 80px; margin-bottom: 20px; opacity: 0.5; color: #94a3b8; }
.not-found h2 { font-size: 24px; margin: 0 0 12px; color: #1e293b; }
.not-found p { color: #64748b; margin: 0 0 24px; }

@media (max-width: 760px) {
    .company-card { flex-direction: column; align-items: flex-start; }
    .apply-bar { flex-direction: column; align-items: stretch; }
    .apply-btn { width: 100%; }
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
<main id="main-content" class="detail-layout">

    <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

    <header class="detail-header">
        <span class="eyebrow">RANGKUL / LOWONGAN</span>
        <h1><?= e($vacancy['title']) ?></h1>
        <div class="detail-meta">
            <span>🏢 <strong><?= e($vacancy['company_name']) ?></strong></span>
            <span>📍 <?= e($vacancy['location']) ?></span>
            <span><?= e(match($vacancy['work_type']) { 'onsite' => '🏢 On-site', 'remote' => '🏠 Remote', 'hybrid' => '🔄 Hybrid' }) ?></span>
            <span>🏷 <?= e($vacancy['category_name']) ?></span>
            <?php if ($vacancy['salary_min'] || $vacancy['salary_max']): ?>
                <span>💰 <?= e(format_salary($vacancy['salary_min'], $vacancy['salary_max'])) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <!-- Company Card -->
    <article class="company-card">
        <?php if ($vacancy['company_logo']): ?>
            <img class="company-logo-large" src="../uploads/logo/<?= e($vacancy['company_logo']) ?>" alt="<?= e($vacancy['company_name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('<?= e($vacancy['company_name']) ?>') + '&background=2563eb&color=fff&size=80'">
        <?php else: ?>
            <div class="company-logo-large" style="display:grid;place-items:center;font-weight:800;color:#fff;background:#2563eb;font-size:28px;"><?= strtoupper(mb_substr($vacancy['company_name'], 0, 1)) ?></div>
        <?php endif; ?>
        <div class="company-info">
            <div class="company-name-row">
                <h2><?= e($vacancy['company_name']) ?></h2>
                <div class="company-badges">
                    <?php if ($vacancy['is_verified']): ?>
                        <span class="badge badge-verified">Terverifikasi</span>
                    <?php endif; ?>
                    <?php if ($vacancy['is_inclusive']): ?>
                        <span class="badge badge-inclusive">Inklusif</span>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($vacancy['description']): ?>
                <p class="company-desc"><?= e($vacancy['description']) ?></p>
            <?php endif; ?>
            <div class="company-meta">
                <?php if ($vacancy['company_address']): ?>
                    <span>📍 <?= e($vacancy['company_address']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </article>

    <!-- Accessibility Facilities (Company Level) -->
    <?php if (!empty($vacancy['company_accessibility']) && is_array($vacancy['company_accessibility'])): ?>
    <article class="section-card">
        <h2 class="section-title">Fasilitas Aksesibilitas Perusahaan</h2>
        <div class="facility-grid">
            <?php
            $facilityIcons = [
                'akses_kursi_roda' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 11c0-1.1-.9-2-2-2H6a2 2 0 00-2 2v8a2 2 0 002 2h2"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>',
                'juru_bahasa_isyarat' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 3v10"/><path d="M8 13h8"/><path d="M8 17h8"/><path d="M12 3a6 6 0 000 12H4a2 2 0 01-2-2V5a2 2 0 012-2h8"/></svg>',
                'huruf_braille' => '<svg viewBox="0 0 24 24" fill="currentColor"><circle cx="8" cy="8" r="2"/><circle cx="16" cy="8" r="2"/><circle cx="8" cy="16" r="2"/><circle cx="16" cy="16" r="2"/></svg>',
                'screen_reader' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 18a3 3 0 100-6 3 3 0 000 6z"/><path d="M19 14c0 3.866-3.134 7-7 7"/><path d="M12 18v-7"/></svg>',
                'waktu_tambahan' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>',
                'ruang_tenang' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><path d="M9 22V12h6v10"/></svg>',
                'akses_lift' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M6 8h12"/><path d="M10 12h4"/><path d="M10 16h4"/></svg>',
                'toilet_inklusif' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2z"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>',
            ];
            $facilityLabels = [
                'akses_kursi_roda' => 'Akses Kursi Roda',
                'juru_bahasa_isyarat' => 'Juru Bahasa Isyarat',
                'huruf_braille' => 'Huruf Braille',
                'screen_reader' => 'Screen Reader',
                'waktu_tambahan' => 'Waktu Tambahan',
                'ruang_tenang' => 'Ruang Tenang',
                'akses_lift' => 'Akses Lift',
                'toilet_inklusif' => 'Toilet Inklusif',
            ];
            foreach ($vacancy['company_accessibility'] as $facility): ?>
                <div class="facility-item">
                    <?= $facilityIcons[$facility] ?? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>' ?>
                    <?= e($facilityLabels[$facility] ?? $facility) ?>
                </div>
            <?php endforeach; ?>
        </div>
    </article>
    <?php endif; ?>

    <!-- Job Description -->
    <article class="section-card">
        <h2 class="section-title">Deskripsi Pekerjaan</h2>
        <div class="section-content"><?= nl2br(e($vacancy['description'])) ?></div>
    </article>

    <!-- Requirements -->
    <article class="section-card">
        <h2 class="section-title">Persyaratan</h2>
        <div class="section-content"><?= nl2br(e($vacancy['requirements'])) ?></div>
    </article>

    <!-- Tags -->
    <article class="section-card">
        <h2 class="section-title">Informasi Lowongan</h2>
        <div class="tags-list">
            <span class="tag category"><?= e($vacancy['category_name']) ?></span>
            <span class="tag work-type"><?= e(match($vacancy['work_type']) { 'onsite' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid' }) ?></span>
            <?php if ($vacancy['salary_min'] || $vacancy['salary_max']): ?>
                <span class="tag salary"><?= e(format_salary($vacancy['salary_min'], $vacancy['salary_max'])) ?></span>
            <?php endif; ?>
            <?php if (!empty($vacancy['accessibility_tags']) && is_array($vacancy['accessibility_tags'])): ?>
                <?php foreach ($vacancy['accessibility_tags'] as $tag): ?>
                    <span class="tag accessibility"><?= e(match($tag) {
                        'akses_kursi_roda' => 'Akses Kursi Roda',
                        'juru_bahasa_isyarat' => 'Juru Bahasa Isyarat',
                        'huruf_braille' => 'Braille',
                        'screen_reader' => 'Screen Reader',
                        'waktu_tambahan' => 'Waktu Tambahan',
                        'ruang_tenang' => 'Ruang Tenang',
                        'akses_lift' => 'Akses Lift',
                        'toilet_inklusif' => 'Toilet Inklusif',
                        default => $tag
                    }) ?></span>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </article>

    <!-- Apply Bar -->
    <div class="apply-bar" id="applyBar">
        <div class="apply-bar-info">
            <?php
            $daysLeft = (int)ceil((strtotime($vacancy['deadline']) - time()) / 86400);
            $urgent = $daysLeft <= 7;
            $isExpired = $daysLeft < 0 || $vacancy['status'] !== 'open';
            ?>
            <span class="apply-bar-deadline <?= $urgent ? 'urgent' : '' ?>">
                <?= $isExpired ? '⛔ Lowongan ditutup' : ($urgent ? '⚠ ' : '📅') . 'Batas lamaran: ' . date('d F Y', strtotime($vacancy['deadline'])) . ($urgent && !$isExpired ? " ($daysLeft hari tersisa)" : '') ?>
            </span>
        </div>
        <?php if ($isExpired): ?>
            <button class="btn btn-primary apply-btn" disabled>Lowongan Ditutup</button>
        <?php elseif ($user && $user['role'] === 'job_seeker'): ?>
            <a href="apply.php?vacancy=<?= $vacancy['id'] ?>" class="btn btn-primary apply-btn">Lamar Sekarang</a>
        <?php elseif ($user && $user['role'] !== 'job_seeker'): ?>
            <a href="login-job-seeker.php" class="btn btn-primary apply-btn">Login sebagai Job Seeker untuk melamar</a>
        <?php else: ?>
            <a href="login-job-seeker.php" class="btn btn-primary apply-btn">Login untuk melamar</a>
        <?php endif; ?>
    </div>

</main>
<footer class="landing-footer"><div class="footer-main"><div class="footer-about"><a class="landing-brand footer-brand" href="index.php"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a><p class="footer-tagline">Merangkul potensi, membuka kesempatan.</p><p class="footer-description">Rangkul adalah ruang digital yang mempertemukan pencari kerja dan perusahaan dalam proses rekrutmen yang lebih terarah, mudah dipahami, dan inklusif. Kami percaya setiap orang berhak mendapatkan kesempatan untuk berkembang.</p></div><div class="footer-column"><b>Jelajahi</b><a href="index.php#tentang">Tentang Rangkul</a><a href="index.php#cara-kerja">Cara kerja</a><a href="index.php#fitur">Fitur platform</a></div><div class="footer-column"><b>Bantuan</b><a href="index.php#aksesibilitas">Aksesibilitas</a><a href="index.php#faq">Pertanyaan umum</a><a href="admin/login.php">Administrator</a></div><div class="footer-column footer-purpose"><b>Komitmen Rangkul</b><p>Mendorong kesempatan kerja yang lebih terbuka dan setara, sejalan dengan Tujuan Pembangunan Berkelanjutan nomor 8.</p><span>SDG 8 · Decent Work and Economic Growth</span></div></div><div class="footer-bottom"><span>© <?= date('Y') ?> Rangkul. Semua hak dilindungi.</span><span>Dibangun untuk kesempatan yang lebih inklusif.</span></div></footer>

</body>
</html>