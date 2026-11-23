<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('job_seeker');

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);
if (!$seeker) {
    flash('error', 'Profil tidak ditemukan. Lengkapi profil terlebih dahulu.');
    redirect('profile.php');
}

$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$applications = application_get_by_seeker($seeker['id'], $perPage, $offset);
$total = application_count_by_seeker($seeker['id']);
$totalPages = max(1, (int)ceil($total / $perPage));

$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Riwayat Lamaran — Rangkul</title>
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

/* Applications Table */
.applications-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; }
.applications-table { width: 100%; border-collapse: collapse; }
.applications-table th,
.applications-table td { padding: 16px 20px; text-align: left; border-bottom: 1px solid #f1f5f9; }
.applications-table th { background: #f8fafc; font-size: 12px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }
.applications-table tr:last-child td { border-bottom: none; }
.applications-table tr:hover td { background: #fafbfc; }

.vacancy-cell { min-width: 220px; }
.vacancy-title { font-weight: 600; color: #1e293b; margin-bottom: 4px; }
.vacancy-company { font-size: 13px; color: #64748b; }

.status-badge { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 99px; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.3px; }
.status-dikirim { background: #eff6ff; color: #2563eb; }
.status-direview { background: #fffbeb; color: #d97706; }
.status-shortlist { background: #e0e7ff; color: #4f46e5; }
.status-diterima { background: #ecfdf5; color: #059669; }
.status-ditolak { background: #fef2f2; color: #dc2626; }
.status-dibatalkan { background: #f1f5f9; color: #64748b; }

.date-cell { font-size: 14px; color: #475569; white-space: nowrap; }
.action-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; font-weight: 600; color: #334155; background: #fff; text-decoration: none; transition: .15s; }
.action-btn:hover { border-color: #2563eb; color: #2563eb; background: #eff6ff; }
.action-btn.cancel { border-color: #fecaca; color: #dc2626; }
.action-btn.cancel:hover { background: #fef2f2; border-color: #dc2626; color: #dc2626; }
.action-btn:disabled { opacity: 0.5; pointer-events: none; }

/* Empty State */
.empty-state { text-align: center; padding: 60px 20px; color: #64748b; }
.empty-state svg { width: 64px; height: 64px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h3 { margin: 0 0 8px; font-size: 18px; color: #334155; }
.empty-state p { margin: 0 0 20px; font-size: 14px; }

/* Pagination */
.pagination { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 24px; padding-top: 20px; border-top: 1px solid #f1f5f9; }
.pagination a,
.pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-weight: 600; color: #475569; text-decoration: none; transition: .15s; }
.pagination a:hover { background: #f1f5f9; border-color: #cbd5e1; color: #1e293b; }
.pagination .current { background: #2563eb; border-color: #2563eb; color: #fff; }
.pagination .disabled { opacity: 0.5; pointer-events: none; }

/* Results Info */
.results-info { font-size: 14px; color: #64748b; margin-bottom: 16px; }
.results-info strong { color: #1e293b; }

@media (max-width: 900px) {
    .seeker-layout { grid-template-columns: 1fr; }
    .seeker-sidebar { position: fixed; left: -100%; top: 0; width: 280px; height: 100vh; z-index: 50; background: #fff; box-shadow: 4px 0 20px rgba(0,0,0,.1); transition: left .25s; padding-top: 80px; border-right: none; }
    .seeker-sidebar.open { left: 0; }
    .seeker-main { padding: 24px 16px; }
    .applications-table th:nth-child(3),
    .applications-table td:nth-child(3) { display: none; }
    .mobile-menu-btn { display: flex; align-items: center; gap: 8px; padding: 8px 12px; background: #fff; border: 1px solid #d1d5db; border-radius: 10px; font-size: 13px; font-weight: 600; }
}
@media (min-width: 901px) {
    .mobile-menu-btn { display: none; }
}
.overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.3); z-index: 40; }
.overlay.show { display: block; }

/* Responsive table */
@media (max-width: 640px) {
    .applications-table thead { display: none; }
    .applications-table tbody tr { display: block; padding: 16px; border-bottom: 1px solid #f1f5f9; }
    .applications-table td { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border: none; }
    .applications-table td::before { content: attr(data-label); font-weight: 600; color: #64748b; font-size: 12px; text-transform: uppercase; }
    .applications-table td:last-child { justify-content: flex-end; }
}
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
            <h1>Riwayat Lamaran</h1>
            <p>Pantau status semua lamaran Anda di satu tempat.</p>
        </div>

        <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

        <div class="results-info">
            Menampilkan <strong><?= count($applications) ?></strong> dari <strong><?= $total ?></strong> lamaran
        </div>

        <div class="applications-card">
            <?php if (empty($applications)): ?>
                <div class="empty-state">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <h3>Belum ada lamaran</h3>
                    <p>Mulai cari lowongan dan kirim lamaran pertama Anda.</p>
                    <a href="../vacancies.php" class="btn btn-primary" style="padding:12px 24px;font-size:14px;">Cari Lowongan</a>
                </div>
            <?php else: ?>
                <table class="applications-table" role="table">
                    <thead>
                        <tr>
                            <th>Lowongan</th>
                            <th>Perusahaan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                            <th style="width:180px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="vacancy-cell" data-label="Lowongan">
                                    <div class="vacancy-title"><?= e($app['vacancy_title']) ?></div>
                                    <div class="vacancy-company"><?= e($app['company_name']) ?></div>
                                </td>
                                <td data-label="Perusahaan">
                                    <?php if ($app['company_logo']): ?>
                                        <img src="../uploads/logos/<?= e($app['company_logo']) ?>" alt="" style="width:28px;height:28px;border-radius:6px;object-fit:cover;vertical-align:middle;margin-right:8px;">
                                    <?php endif; ?>
                                    <?= e($app['company_name']) ?>
                                </td>
                                <td class="date-cell" data-label="Tanggal"><?= date('d M Y', strtotime($app['created_at'])) ?></td>
                                <td data-label="Status">
                                    <span class="status-badge status-<?= strtolower($app['status']) ?>"><?= e($app['status']) ?></span>
                                </td>
                                <td data-label="Aksi">
                                    <a href="application-detail.php?id=<?= $app['id'] ?>" class="action-btn">Detail</a>
                                    <?php if ($app['status'] === 'Dikirim'): ?>
                                        <form method="post" action="application-cancel.php" style="display:inline;" onsubmit="return confirm('Batalkan lamaran ini? Tindakan tidak dapat diurungkan.')">
                                            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                                            <input type="hidden" name="application_id" value="<?= $app['id'] ?>">
                                            <button type="submit" class="action-btn cancel">Batalkan</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($totalPages > 1): ?>
                    <nav class="pagination" aria-label="Pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?= $page - 1 ?>">&laquo; Sebelumnya</a>
                        <?php else: ?>
                            <span class="disabled">&laquo; Sebelumnya</span>
                        <?php endif; ?>
                        <?php
                        $start = max(1, $page - 2);
                        $end = min($totalPages, $page + 2);
                        for ($i = $start; $i <= $end; $i++):
                        ?>
                            <?php if ($i === $page): ?>
                                <span class="current"><?= $i ?></span>
                            <?php else: ?>
                                <a href="?page=<?= $i ?>"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?= $page + 1 ?>">Selanjutnya &raquo;</a>
                        <?php else: ?>
                            <span class="disabled">Selanjutnya &raquo;</span>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
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