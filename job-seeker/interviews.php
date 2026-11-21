<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('job_seeker');

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);

$filter = $_GET['filter'] ?? 'all';
if (!in_array($filter, ['all', 'upcoming', 'past'])) $filter = 'all';

$interviews = interview_get_by_seeker($seeker['id'], $filter === 'all' ? null : $filter);

$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Jadwal Wawancara — Rangkul</title>
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

/* Filter Tabs */
.filter-tabs { display: flex; gap: 8px; margin-bottom: 24px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 4px; }
.filter-tab { flex: 1; padding: 10px 16px; border: none; background: transparent; border-radius: 8px; font: 600 13px Inter; color: #64748b; cursor: pointer; transition: .15s; }
.filter-tab:hover { background: #f1f5f9; color: #1e293b; }
.filter-tab.active { background: #2563eb; color: #fff; }

/* Interview List */
.interview-list { display: flex; flex-direction: column; gap: 16px; }
.interview-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; transition: .2s; }
.interview-card:hover { box-shadow: 0 8px 24px rgba(20,43,85,.08); border-color: #cbd5e1; }
.interview-card-header { padding: 20px 24px; display: flex; align-items: flex-start; gap: 16px; border-bottom: 1px solid #f1f5f9; }
.interview-date { width: 80px; flex-shrink: 0; text-align: center; }
.interview-date .day { font-size: 28px; font-weight: 800; color: #1e293b; line-height: 1.1; }
.interview-date .month { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.interview-info { flex: 1; min-width: 0; }
.interview-title { font-weight: 600; color: #1e293b; margin-bottom: 6px; font-size: 16px; }
.interview-company { font-size: 14px; color: #64748b; margin-bottom: 8px; }
.interview-meta { display: flex; flex-wrap: wrap; gap: 16px; font-size: 12px; color: #94a3b8; }
.interview-meta span { display: flex; align-items: center; gap: 4px; }
.interview-status { padding: 4px 12px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
.interview-status.scheduled { background: #eff6ff; color: #2563eb; }
.interview-status.confirmed { background: #ecfdf5; color: #059669; }
.interview-status.cancelled { background: #fef2f2; color: #dc2626; }
.interview-status.completed { background: #f5f3ff; color: #7c3aed; }
.interview-card-footer { padding: 16px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; background: #fafbfc; }
.interview-actions { display: flex; gap: 10px; }
.action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 16px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-weight: 600; color: #334155; background: #fff; text-decoration: none; transition: .15s; }
.action-btn:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; }
.action-btn.primary { background: #2563eb; border-color: #2563eb; color: #fff; }
.action-btn.primary:hover { background: #1d4ed8; border-color: #1d4ed8; color: #fff; }
.action-btn:disabled { opacity: 0.5; pointer-events: none; }

/* Accommodation Note */
.accommodation-note { margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; font-size: 13px; color: #64748b; }
.accommodation-note strong { color: #334155; }

/* Empty State */
.empty-state { text-align: center; padding: 60px 20px; color: #64748b; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; }
.empty-state svg { width: 64px; height: 64px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h3 { margin: 0 0 8px; font-size: 18px; color: #334155; }
.empty-state p { margin: 0 0 20px; font-size: 14px; }

/* Stats Summary */
.stats-summary { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-box { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; }
.stat-box .value { font-size: 28px; font-weight: 800; color: #1e293b; line-height: 1.1; }
.stat-box .label { font-size: 12px; color: #64748b; font-weight: 500; margin-top: 4px; }
.stat-box.upcoming .value { color: #2563eb; }
.stat-box.confirmed .value { color: #059669; }
.stat-box.past .value { color: #64748b; }

@media (max-width: 900px) {
    .seeker-layout { grid-template-columns: 1fr; }
    .seeker-sidebar { position: fixed; left: -100%; top: 0; width: 280px; height: 100vh; z-index: 50; background: #fff; box-shadow: 4px 0 20px rgba(0,0,0,.1); transition: left .25s; padding-top: 80px; border-right: none; }
    .seeker-sidebar.open { left: 0; }
    .seeker-main { padding: 24px 16px; }
    .interview-card-header { flex-direction: column; align-items: flex-start; }
    .interview-date { width: auto; }
    .mobile-menu-btn { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 13px; font-weight: 600; }
    .interview-card-footer { flex-direction: column; align-items: stretch; }
    .interview-actions { justify-content: stretch; }
    .interview-actions .action-btn { flex: 1; justify-content: center; }
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
            <a href="interviews.php" class="is-active"><span>📅</span> Jadwal Wawancara</a>
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
            <span class="eyebrow">RANGKUL / WAWANCARA</span>
            <h1>Jadwal Wawancara</h1>
            <p>Kelola dan konfirmasi jadwal wawancara Anda.</p>
        </div>

        <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

        <!-- Stats Summary -->
        <?php
        $upcomingCount = count(array_filter($interviews, fn($i) => in_array($i['status'], ['Scheduled', 'Confirmed']) && strtotime($i['date']) >= time()));
        $confirmedCount = count(array_filter($interviews, fn($i) => $i['status'] === 'Confirmed'));
        $pastCount = count(array_filter($interviews, fn($i) => strtotime($i['date']) < time() || in_array($i['status'], ['Cancelled', 'Completed'])));
        ?>
        <div class="stats-summary">
            <div class="stat-box upcoming">
                <div class="value"><?= $upcomingCount ?></div>
                <div class="label">Mendatang</div>
            </div>
            <div class="stat-box confirmed">
                <div class="value"><?= $confirmedCount ?></div>
                <div class="label">Dikonfirmasi</div>
            </div>
            <div class="stat-box past">
                <div class="value"><?= $pastCount ?></div>
                <div class="label">Selesai / Batal</div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="filter-tabs" role="tablist">
            <a href="?filter=all" class="filter-tab <?= $filter === 'all' ? 'active' : '' ?>" role="tab" aria-selected="<?= $filter === 'all' ? 'true' : 'false' ?>">Semua</a>
            <a href="?filter=upcoming" class="filter-tab <?= $filter === 'upcoming' ? 'active' : '' ?>" role="tab" aria-selected="<?= $filter === 'upcoming' ? 'true' : 'false' ?>">Mendatang</a>
            <a href="?filter=past" class="filter-tab <?= $filter === 'past' ? 'active' : '' ?>" role="tab" aria-selected="<?= $filter === 'past' ? 'true' : 'false' ?>">Selesai</a>
        </div>

        <!-- Interview List -->
        <?php if (empty($interviews)): ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <h3><?= $filter === 'upcoming' ? 'Tidak ada wawancara mendatang' : ($filter === 'past' ? 'Tidak ada riwayat wawancara' : 'Belum ada jadwal wawancara') ?></h3>
                <p><?= $filter === 'upcoming' ? 'Jadwal wawancara yang akan datang akan muncul di sini.' : ($filter === 'past' ? 'Riwayat wawancara yang sudah selesai akan muncul di sini.' : 'Jadwal akan muncul setelah perusahaan menjadwalkan wawancara untuk lamaran Anda (status Shortlist).') ?></p>
                <?php if ($filter !== 'all'): ?>
                    <a href="?filter=all" class="btn btn-outline" style="padding:10px 20px;">Lihat Semua</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="interview-list">
                <?php foreach ($interviews as $iv): ?>
                    <article class="interview-card">
                        <div class="interview-card-header">
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
                        <div class="interview-card-footer">
                            <div class="accommodation-note">
                                <?php if ($iv['accommodation_note']): ?>
                                    <strong>🪑 Akomodasi:</strong> <?= e($iv['accommodation_note']) ?>
                                <?php else: ?>
                                    <span style="color:#94a3b8;">Tidak ada catatan akomodasi khusus</span>
                                <?php endif; ?>
                            </div>
                            <div class="interview-actions">
                                <a href="application-detail.php?id=<?= $iv['application_id'] ?>" class="action-btn">Detail Lamaran</a>
                                <?php if ($iv['status'] === 'Scheduled'): ?>
                                    <form method="post" action="interview-confirm.php" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                        <input type="hidden" name="interview_id" value="<?= $iv['id'] ?>">
                                        <button type="submit" class="action-btn primary">✅ Konfirmasi</button>
                                    </form>
                                <?php elseif ($iv['status'] === 'Confirmed'): ?>
                                    <span class="action-btn" style="background:#ecfdf5;border-color:#a7f3d0;color:#059669;cursor:default;">✅ Dikonfirmasi</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
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