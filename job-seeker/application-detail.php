<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('job_seeker');

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);

$applicationId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$applicationId) {
    flash('error', 'Lamaran tidak valid.');
    redirect('applications.php');
}

$application = application_get_detail($applicationId, $seeker ? $seeker['id'] : 0);
if (!$application) {
    flash('error', 'Lamaran tidak ditemukan.');
    redirect('applications.php');
}

$success = flash('success');
$error = flash('error');

// Status timeline order
$statusOrder = ['Dikirim', 'Direview', 'Shortlist', 'Diterima', 'Ditolak', 'Dibatalkan'];
$currentStatusIndex = array_search($application['status'], $statusOrder);
if ($currentStatusIndex === false) $currentStatusIndex = 0;
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Detail Lamaran — Rangkul</title>
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

/* Status Timeline */
.timeline { display: flex; flex-direction: column; gap: 0; margin-bottom: 24px; }
.timeline-item { display: flex; gap: 16px; padding: 16px 0; position: relative; }
.timeline-item:not(:last-child)::before { content: ''; position: absolute; left: 11px; top: 44px; bottom: 0; width: 2px; background: #e2e8f0; }
.timeline-item.completed::before { background: #2563eb; }
.timeline-dot { width: 24px; height: 24px; border-radius: 50%; border: 3px solid #fff; background: #e2e8f0; flex-shrink: 0; position: relative; z-index: 1; display: flex; align-items: center; justify-content: center; }
.timeline-item.completed .timeline-dot { background: #2563eb; border-color: #2563eb; }
.timeline-item.completed .timeline-dot::after { content: '✓'; color: #fff; font-size: 12px; font-weight: bold; }
.timeline-item.current .timeline-dot { background: #2563eb; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,.2); }
.timeline-content { flex: 1; padding-top: 2px; }
.timeline-label { font-size: 13px; font-weight: 700; color: #1e293b; margin-bottom: 2px; }
.timeline-time { font-size: 12px; color: #94a3b8; }
.timeline-item.current .timeline-label { color: #2563eb; }

/* Cards */
.detail-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-bottom: 20px; }
.detail-card h2 { font-size: 16px; font-weight: 700; margin: 0 0 20px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.detail-card h2::before { content: ''; width: 8px; height: 8px; background: #2563eb; border-radius: 2px; }

/* Vacancy Header */
.vacancy-header { display: flex; gap: 16px; margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9; }
.vacancy-logo { width: 64px; height: 64px; border-radius: 14px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
.vacancy-info { flex: 1; }
.vacancy-title { font-size: 20px; font-weight: 700; color: #1e293b; margin: 0 0 8px; }
.vacancy-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 13px; color: #64748b; }
.vacancy-meta span { display: flex; align-items: center; gap: 6px; }
.vacancy-badges { display: flex; gap: 8px; margin-top: 12px; }
.badge { font-size: 10px; font-weight: 700; padding: 3px 10px; border-radius: 99px; text-transform: uppercase; }
.badge-verified { background: #eff6ff; color: #2563eb; }
.badge-inclusive { background: #ecfdf5; color: #059669; }

/* Info Grid */
.info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; }
.info-item { display: flex; flex-direction: column; gap: 4px; }
.info-label { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.info-value { font-size: 14px; color: #1e293b; }

/* Cover Letter & CV */
.content-block { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; white-space: pre-wrap; font-size: 14px; line-height: 1.7; color: #334155; }
.file-link { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 10px; color: #2563eb; font-weight: 600; font-size: 14px; text-decoration: none; transition: .15s; }
.file-link:hover { background: #dbeafe; }

/* Interview Card */
.interview-card { background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; padding: 24px; }
.interview-card h2 { color: #166534; }
.interview-card h2::before { background: #059669; }
.interview-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 20px; }
.interview-item { display: flex; flex-direction: column; gap: 4px; }
.interview-status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 99px; font-size: 12px; font-weight: 700; text-transform: uppercase; }
.interview-status-scheduled { background: #eff6ff; color: #2563eb; }
.interview-status-confirmed { background: #ecfdf5; color: #059669; }
.interview-status-cancelled { background: #fef2f2; color: #dc2626; }
.interview-status-completed { background: #f5f3ff; color: #7c3aed; }
.confirm-btn { padding: 12px 24px; font-size: 14px; font-weight: 700; border-radius: 10px; background: #059669; color: #fff; border: none; cursor: pointer; transition: .15s; }
.confirm-btn:hover { background: #047857; }
.confirm-btn:disabled { opacity: 0.6; cursor: not-allowed; background: #6ee7b7; }

/* Recruiter Note */
.recruiter-note { background: #fef3c7; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-top: 16px; }
.recruiter-note-label { font-size: 11px; font-weight: 700; color: #92400e; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
.recruiter-note-content { font-size: 14px; color: #78350f; }

/* Buttons */
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent; border-radius: 10px; padding: 12px 20px; font: 600 14px Inter; cursor: pointer; text-decoration: none !important; transition: .2s; }
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-outline { border-color: #d1d5db; color: #334155; background: #fff; }
.btn-outline:hover { border-color: #2563eb; color: #2563eb; }

/* Empty State */
.empty-state { text-align: center; padding: 40px 20px; color: #64748b; }
.empty-state svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.5; }

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
            <a href="applications.php" class="is-active"><span>📄</span> Riwayat Lamaran</a>
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
            <span class="eyebrow">RANGKUL / LAMARAN</span>
            <h1><?= e($application['vacancy_title']) ?></h1>
            <p>Detail lamaran ke <strong><?= e($application['company_name']) ?></strong></p>
        </div>

        <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

        <!-- Status Timeline -->
        <div class="detail-card">
            <h2>Status Lamaran</h2>
            <div class="timeline">
                <?php foreach ($statusOrder as $idx => $status): ?>
                    <?php
                    $isCompleted = $idx < $currentStatusIndex;
                    $isCurrent = $idx === $currentStatusIndex;
                    $class = 'timeline-item';
                    if ($isCompleted) $class .= ' completed';
                    if ($isCurrent) $class .= ' current';
                    ?>
                    <div class="<?= $class ?>">
                        <div class="timeline-dot"></div>
                        <div class="timeline-content">
                            <div class="timeline-label"><?= e($status) ?></div>
                            <div class="timeline-time">
                                <?php if ($isCompleted || $isCurrent): ?>
                                    <?= date('d M Y H:i', strtotime($application['created_at'])) ?>
                                    <?php if ($isCurrent && $application['status'] !== 'Dikirim'): ?>
                                        (Status saat ini)
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span style="color:#cbd5e1;">Belum</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Vacancy & Company Info -->
        <div class="vacancy-header">
            <?php if ($application['company_logo']): ?>
                <img class="vacancy-logo" src="../uploads/logos/<?= e($application['company_logo']) ?>" alt="<?= e($application['company_name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('<?= e($application['company_name']) ?>') + '&background=2563eb&color=fff&size=64'">
            <?php else: ?>
                <div class="vacancy-logo" style="display:grid;place-items:center;font-weight:800;color:#fff;background:#2563eb;font-size:24px;"><?= strtoupper(mb_substr($application['company_name'], 0, 1)) ?></div>
            <?php endif; ?>
            <div class="vacancy-info">
                <h3 class="vacancy-title"><?= e($application['vacancy_title']) ?></h3>
                <div class="vacancy-meta">
                    <span>🏢 <?= e($application['company_name']) ?></span>
                    <span>📍 <?= e($application['location']) ?></span>
                    <span><?= e(match($application['work_type']) { 'onsite' => '🏢 On-site', 'remote' => '🏠 Remote', 'hybrid' => '🔄 Hybrid' }) ?></span>
                    <?php if ($application['salary_min'] || $application['salary_max']): ?>
                        <span>💰 <?= e(format_salary($application['salary_min'], $application['salary_max'])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="vacancy-badges">
                    <?php if ($application['is_verified']): ?>
                        <span class="badge badge-verified">Terverifikasi</span>
                    <?php endif; ?>
                    <?php if ($application['is_inclusive']): ?>
                        <span class="badge badge-inclusive">Inklusif</span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Application Details -->
        <div class="detail-card">
            <h2>Informasi Lamaran</h2>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Tanggal Lamar</span>
                    <span class="info-value"><?= date('d F Y H:i', strtotime($application['created_at'])) ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        <span class="status-badge status-<?= strtolower($application['status']) ?>"><?= e($application['status']) ?></span>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">CV Digunakan</span>
                    <span class="info-value"><?= e($application['cv_file']) ?></span>
                </div>
            </div>
        </div>

        <!-- Cover Letter -->
        <?php if ($application['cover_letter']): ?>
        <div class="detail-card">
            <h2>Cover Letter</h2>
            <div class="content-block"><?= e($application['cover_letter']) ?></div>
        </div>
        <?php endif; ?>

        <!-- CV File -->
        <div class="detail-card">
            <h2>CV</h2>
            <a href="../uploads/cv/<?= e($application['cv_file']) ?>" target="_blank" class="file-link">📄 Lihat / Unduh CV (<?= e($application['cv_file']) ?>)</a>
        </div>

        <!-- Interview Info -->
        <?php if ($application['interview_id']): ?>
        <div class="detail-card interview-card">
            <h2>📅 Jadwal Wawancara</h2>
            <div class="interview-grid">
                <div class="interview-item">
                    <span class="info-label">Tanggal</span>
                    <span class="info-value"><?= date('d F Y', strtotime($application['interview_date'])) ?></span>
                </div>
                <div class="interview-item">
                    <span class="info-label">Waktu</span>
                    <span class="info-value"><?= date('H:i', strtotime($application['interview_time'])) ?></span>
                </div>
                <div class="interview-item">
                    <span class="info-label">Mode</span>
                    <span class="info-value"><?= e($application['interview_mode'] === 'online' ? '🌐 Online' : '📍 Offline') ?></span>
                </div>
                <div class="interview-item">
                    <span class="info-label">Lokasi / Link</span>
                    <span class="info-value" style="word-break:break-all;"><?= e($application['location_or_link']) ?></span>
                </div>
                <div class="interview-item" style="grid-column:1/-1;">
                    <span class="info-label">Status</span>
                    <span class="interview-status-badge interview-status-<?= strtolower($application['interview_status']) ?>"><?= e($application['interview_status']) ?></span>
                </div>
            </div>
            <?php if ($application['accommodation_note']): ?>
                <div style="padding-top:16px;border-top:1px solid #bbf7d0;">
                    <span class="info-label">Catatan Akomodasi</span>
                    <p style="margin:8px 0 0;font-size:14px;color:#166534;"><?= e($application['accommodation_note']) ?></p>
                </div>
            <?php endif; ?>
            <?php if ($application['interview_status'] === 'Scheduled'): ?>
                <form method="post" action="interview-confirm.php" style="margin-top:20px;">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <input type="hidden" name="interview_id" value="<?= $application['interview_id'] ?>">
                    <button type="submit" class="confirm-btn">✅ Konfirmasi Kehadiran</button>
                </form>
            <?php elseif ($application['interview_status'] === 'Confirmed'): ?>
                <div style="margin-top:20px;padding:16px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:10px;color:#065f46;">
                    ✅ Anda telah mengonfirmasi kehadiran untuk wawancara ini.
                </div>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="detail-card">
            <h2>Jadwal Wawancara</h2>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <h3>Belum ada jadwal wawancara</h3>
                <p>Jadwal akan muncul di sini setelah perusahaan menjadwalkan wawancara (status lamaran: Shortlist).</p>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recruiter Note -->
        <?php if ($application['recruiter_note']): ?>
        <div class="detail-card">
            <h2>Catatan Rekruter</h2>
            <div class="recruiter-note">
                <div class="recruiter-note-label">Catatan internal dari tim rekrutmen</div>
                <div class="recruiter-note-content"><?= e($application['recruiter_note']) ?></div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Actions -->
        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
            <a href="applications.php" class="btn btn-outline">← Kembali ke Riwayat</a>
        </div>
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