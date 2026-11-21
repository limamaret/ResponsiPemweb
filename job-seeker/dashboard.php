<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_role('job_seeker');

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);
$stats = seeker_dashboard_stats($seeker ? $seeker['id'] : 0);
$hasProfile = $seeker && seeker_has_minimal_profile($user['id']);
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard — Rangkul</title>
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

/* Dashboard */
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
.stat-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 24px; display: flex; align-items: center; gap: 16px; transition: .2s; }
.stat-card:hover { box-shadow: 0 8px 24px rgba(20,43,85,.08); border-color: #cbd5e1; }
.stat-icon { width: 48px; height: 48px; border-radius: 12px; display: grid; place-items: center; font-size: 22px; }
.stat-icon.blue { background: #eff6ff; color: #2563eb; }
.stat-icon.green { background: #ecfdf5; color: #059669; }
.stat-icon.amber { background: #fffbeb; color: #d97706; }
.stat-icon.red { background: #fef2f2; color: #dc2626; }
.stat-icon.purple { background: #f5f3ff; color: #7c3aed; }
.stat-info { display: flex; flex-direction: column; }
.stat-value { font-size: 26px; font-weight: 800; line-height: 1.1; letter-spacing: -0.5px; }
.stat-label { font-size: 12px; color: #64748b; font-weight: 500; }

.dashboard-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
.dashboard-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; }
.dashboard-card h2 { font-size: 16px; font-weight: 700; margin: 0 0 20px; color: #1e293b; display: flex; align-items: center; gap: 10px; }
.dashboard-card h2::before { content: ''; width: 8px; height: 8px; background: #2563eb; border-radius: 2px; }

.interview-list { display: flex; flex-direction: column; gap: 12px; }
.interview-item { display: flex; align-items: center; gap: 16px; padding: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; transition: .15s; }
.interview-item:hover { border-color: #cbd5e1; background: #fff; }
.interview-date { width: 70px; flex-shrink: 0; text-align: center; }
.interview-date .day { font-size: 24px; font-weight: 800; color: #1e293b; line-height: 1.1; }
.interview-date .month { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.interview-info { flex: 1; min-width: 0; }
.interview-title { font-weight: 600; color: #1e293b; margin-bottom: 4px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.interview-company { font-size: 13px; color: #64748b; margin-bottom: 4px; }
.interview-meta { display: flex; gap: 16px; font-size: 12px; color: #94a3b8; }
.interview-meta span { display: flex; align-items: center; gap: 4px; }
.interview-status { padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.interview-status.scheduled { background: #eff6ff; color: #2563eb; }
.interview-status.confirmed { background: #ecfdf5; color: #059669; }
.interview-status.cancelled { background: #fef2f2; color: #dc2626; }
.interview-status.completed { background: #f5f3ff; color: #7c3aed; }
.empty-state { text-align: center; padding: 40px 20px; color: #64748b; }
.empty-state svg { width: 48px; height: 48px; margin-bottom: 12px; opacity: 0.5; }
.empty-state h3 { margin: 0 0 8px; font-size: 16px; color: #334155; }
.empty-state p { margin: 0 0 16px; font-size: 14px; }
.btn-link { color: #2563eb; font-weight: 600; font-size: 13px; text-decoration: none; }
.btn-link:hover { text-decoration: underline; }

.profile-notice { background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px; }
.profile-notice .notice-icon { width: 32px; height: 32px; border-radius: 8px; background: #fef3c7; color: #d97706; display: grid; place-items: center; flex-shrink: 0; font-size: 16px; }
.profile-notice .notice-text { flex: 1; }
.profile-notice .notice-text strong { display: block; margin-bottom: 4px; color: #92400e; }
.profile-notice .notice-text p { margin: 0; font-size: 13px; color: #78350f; }
.profile-notice .notice-text a { color: #2563eb; font-weight: 600; }

@media (max-width: 900px) {
    .seeker-layout { grid-template-columns: 1fr; }
    .seeker-sidebar { position: fixed; left: -100%; top: 0; width: 280px; height: 100vh; z-index: 50; background: #fff; box-shadow: 4px 0 20px rgba(0,0,0,.1); transition: left .25s; padding-top: 80px; border-right: none; }
    .seeker-sidebar.open { left: 0; }
    .seeker-main { padding: 24px 16px; }
    .dashboard-grid { grid-template-columns: 1fr; }
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
            <a href="dashboard.php" class="is-active"><span>✦</span> Dashboard</a>
            <a href="profile.php"><span>👤</span> Profil Saya</a>
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
            <span class="eyebrow">RANGKUL / DASHBOARD</span>
            <h1>Halo, <?= e($user['name']) ?> 👋</h1>
            <p>Ringkasan aktivitas lamaran dan jadwal wawancara Anda.</p>
        </div>

        <?php if (!$hasProfile): ?>
        <div class="profile-notice">
            <div class="notice-icon">⚠</div>
            <div class="notice-text">
                <strong>Profil belum lengkap</strong>
                <p>Lengkapi profil Anda (telepon, alamat, pendidikan, pengalaman, atau keahlian) agar dapat melamar lowongan dengan lancar.</p>
                <p style="margin-top:8px;"><a href="profile.php">Lengkapi profil sekarang →</a></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">📄</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['total_applications'] ?></div>
                    <div class="stat-label">Total Lamaran</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon blue">📤</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['applications']['Dikirim'] ?? 0 ?></div>
                    <div class="stat-label">Dikirim</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon amber">👀</div>
                <div class="stat-info">
                    <div class="stat-value"><?= ($stats['applications']['Direview'] ?? 0) + ($stats['applications']['Shortlist'] ?? 0) ?></div>
                    <div class="stat-label">Direview / Shortlist</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon green">✅</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['applications']['Diterima'] ?? 0 ?></div>
                    <div class="stat-label">Diterima</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon red">❌</div>
                <div class="stat-info">
                    <div class="stat-value"><?= $stats['applications']['Ditolak'] ?? 0 ?></div>
                    <div class="stat-label">Ditolak</div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Wawancara Mendatang</h2>
                <?php if (empty($stats['upcoming_interviews'])): ?>
                    <div class="empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <h3>Belum ada jadwal wawancara</h3>
                        <p>Jadwal akan muncul di sini setelah perusahaan menjadwalkan wawancara untuk lamaran Anda.</p>
                        <a href="applications.php" class="btn-link">Lihat riwayat lamaran →</a>
                    </div>
                <?php else: ?>
                    <div class="interview-list">
                        <?php foreach ($stats['upcoming_interviews'] as $iv): ?>
                            <div class="interview-item">
                                <div class="interview-date">
                                    <div class="day"><?= date('j', strtotime($iv['date'])) ?></div>
                                    <div class="month"><?= date('M', strtotime($iv['date'])) ?></div>
                                </div>
                                <div class="interview-info">
                                    <div class="interview-title"><?= e($iv['vacancy_title']) ?></div>
                                    <div class="interview-company"><?= e($iv['company_name']) ?></div>
                                    <div class="interview-meta">
                                        <span>🕐 <?= date('H:i', strtotime($iv['time'])) ?></span>
                                        <span><?= e($iv['mode'] === 'online' ? '🌐 Online' : '📍 Offline') ?></span>
                                    </div>
                                </div>
                                <span class="interview-status <?= strtolower($iv['status']) ?>"><?= e($iv['status']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="margin-top:16px;text-align:right;">
                        <a href="interviews.php" class="btn-link">Lihat semua jadwal →</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="dashboard-card">
                <h2>Aksi Cepat</h2>
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;">
                    <a href="profile.php" class="btn btn-outline" style="justify-content:center;padding:16px;text-decoration:none;">👤 Lengkapi Profil</a>
                    <a href="../vacancies.php" class="btn btn-primary" style="justify-content:center;padding:16px;text-decoration:none;">🔍 Cari Lowongan</a>
                    <a href="applications.php" class="btn btn-outline" style="justify-content:center;padding:16px;text-decoration:none;">📄 Riwayat Lamaran</a>
                    <a href="interviews.php" class="btn btn-outline" style="justify-content:center;padding:16px;text-decoration:none;">📅 Jadwal Wawancara</a>
                </div>
            </div>
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