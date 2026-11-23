<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];
$today = date('Y-m-d');

// C-22: lowongan aktif, total pelamar, wawancara mendatang
$q = $pdo->prepare("SELECT COUNT(*) FROM vacancies WHERE company_id = ? AND status = 'open' AND deadline >= ?");
$q->execute([$cid, $today]); $activeVacancies = (int)$q->fetchColumn();

$q = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN vacancies v ON v.id = a.vacancy_id WHERE v.company_id = ? AND a.status <> 'Dibatalkan'");
$q->execute([$cid]); $totalApplicants = (int)$q->fetchColumn();

$q = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN vacancies v ON v.id = a.vacancy_id WHERE v.company_id = ? AND a.status = 'Dikirim'");
$q->execute([$cid]); $newApplicants = (int)$q->fetchColumn();

$q = $pdo->prepare(
    "SELECT i.*, a.id AS app_id, u.name AS seeker_name, v.title AS vacancy_title
     FROM interviews i JOIN applications a ON a.id = i.application_id
     JOIN vacancies v ON v.id = a.vacancy_id
     JOIN job_seekers js ON js.id = a.seeker_id JOIN users u ON u.id = js.user_id
     WHERE v.company_id = ? AND i.status <> 'Cancelled' AND CONCAT(i.date,' ',i.time) >= ?
     ORDER BY i.date, i.time LIMIT 5"
);
$q->execute([$cid, date('Y-m-d H:i:s')]); $upcoming = $q->fetchAll();

$q = $pdo->prepare("SELECT COUNT(*) FROM interviews i JOIN applications a ON a.id = i.application_id JOIN vacancies v ON v.id = a.vacancy_id WHERE v.company_id = ? AND i.status <> 'Cancelled' AND CONCAT(i.date,' ',i.time) >= ?");
$q->execute([$cid, date('Y-m-d H:i:s')]); $upcomingCount = (int)$q->fetchColumn();

$q = $pdo->prepare(
    "SELECT a.id, a.status, a.created_at, u.name AS seeker_name, v.title AS vacancy_title
     FROM applications a JOIN vacancies v ON v.id = a.vacancy_id
     JOIN job_seekers js ON js.id = a.seeker_id JOIN users u ON u.id = js.user_id
     WHERE v.company_id = ? ORDER BY a.created_at DESC, a.id DESC LIMIT 5"
);
$q->execute([$cid]); $recent = $q->fetchAll();

company_header('Dashboard', 'dashboard', $company);
?>
<div class="page-head"><div><span class="eyebrow">DASHBOARD PERUSAHAAN</span><h1>Halo, <?= e($company['name']) ?></h1>
<p>Status verifikasi: <?= verification_badge($company['verification_status']) ?></p></div>
<a class="btn btn-primary" href="vacancy-form.php">+ Tambah lowongan</a></div>

<?php if (!company_profile_complete($company)): ?>
<div class="progress-note" role="status"><b>Lengkapi profil perusahaan Anda.</b> Nama, deskripsi, dan alamat wajib diisi sebelum Anda dapat memasang lowongan. <a href="profile.php">Isi profil sekarang →</a></div>
<?php endif; ?>
<?php if ($company['verification_status'] === 'pending'): ?>
<div class="note-box" style="margin-bottom:22px">Perusahaan Anda sedang menunggu verifikasi Admin. Badge perusahaan terverifikasi dan label inklusif akan tampil penuh di halaman publik setelah disetujui.</div>
<?php elseif ($company['verification_status'] === 'rejected'): ?>
<div class="notice error" role="alert">Verifikasi perusahaan ditolak oleh Admin. Perbarui profil perusahaan Anda lalu hubungi Admin untuk pengajuan ulang.</div>
<?php endif; ?>

<section class="stats" aria-label="Ringkasan">
<div class="stat"><b><?= $activeVacancies ?></b><span>Lowongan aktif</span></div>
<div class="stat"><b><?= $totalApplicants ?></b><span>Total pelamar<?= $newApplicants ? ' · ' . $newApplicants . ' baru' : '' ?></span></div>
<div class="stat"><b><?= $upcomingCount ?></b><span>Wawancara mendatang</span></div>
</section>

<div class="grid-2">
<section class="card" aria-labelledby="h-up"><h2 id="h-up">Wawancara mendatang</h2>
<?php if (!$upcoming): ?><p class="empty">Belum ada wawancara terjadwal.</p><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Pelamar</th><th scope="col">Jadwal</th><th scope="col">Status</th></tr></thead><tbody>
<?php foreach ($upcoming as $i): ?><tr>
<td><a href="applicant-detail.php?id=<?= (int)$i['app_id'] ?>"><?= e($i['seeker_name']) ?></a><span class="sub"><?= e($i['vacancy_title']) ?></span></td>
<td><?= e(fmt_date($i['date'])) ?><span class="sub"><?= e(substr($i['time'], 0, 5)) ?> WIB · <?= $i['mode'] === 'online' ? 'Online' : 'Offline' ?></span></td>
<td><span class="badge <?= $i['status'] === 'Confirmed' ? 'badge-ok' : 'badge-wait' ?>"><?= e(interview_status_label($i['status'])) ?></span></td></tr>
<?php endforeach; ?></tbody></table></div>
<p style="margin:14px 0 0"><a href="interviews.php">Lihat semua jadwal →</a></p><?php endif; ?></section>

<section class="card" aria-labelledby="h-rec"><h2 id="h-rec">Pelamar terbaru</h2>
<?php if (!$recent): ?><p class="empty">Belum ada pelamar. Pastikan lowongan Anda berstatus terbuka.</p><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Pelamar</th><th scope="col">Tanggal</th><th scope="col">Status</th></tr></thead><tbody>
<?php foreach ($recent as $r): ?><tr>
<td><a href="applicant-detail.php?id=<?= (int)$r['id'] ?>"><?= e($r['seeker_name']) ?></a><span class="sub"><?= e($r['vacancy_title']) ?></span></td>
<td><?= e(fmt_date($r['created_at'])) ?></td><td><?= status_badge($r['status']) ?></td></tr>
<?php endforeach; ?></tbody></table></div>
<p style="margin:14px 0 0"><a href="applicants.php">Lihat semua pelamar →</a></p><?php endif; ?></section>
</div>
<?php company_footer();
