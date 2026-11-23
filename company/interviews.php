<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $iid = (int)($_POST['id'] ?? 0);
    // cek kepemilikan lewat join ke lowongan milik perusahaan ini
    $s = $pdo->prepare('SELECT i.id, i.status FROM interviews i JOIN applications a ON a.id = i.application_id JOIN vacancies v ON v.id = a.vacancy_id WHERE i.id = ? AND v.company_id = ? LIMIT 1');
    $s->execute([$iid, $cid]); $row = $s->fetch();
    if (!$row) { flash('error', 'Jadwal tidak ditemukan.'); }
    elseif ($row['status'] === 'Cancelled') { flash('error', 'Jadwal ini sudah dibatalkan.'); }
    else {
        $pdo->prepare("UPDATE interviews SET status = 'Cancelled' WHERE id = ?")->execute([$iid]);
        flash('success', 'Jadwal wawancara dibatalkan. Anda dapat menjadwalkan ulang dari halaman detail pelamar.');
    }
    redirect('interviews.php');
}

$when = $_GET['when'] ?? 'upcoming';
if (!in_array($when, ['upcoming', 'past', 'all'], true)) $when = 'upcoming';
$now = date('Y-m-d H:i:s');
$where = 'v.company_id = ?'; $params = [$cid];
if ($when === 'upcoming') { $where .= " AND i.status <> 'Cancelled' AND CONCAT(i.date,' ',i.time) >= ?"; $params[] = $now; }
elseif ($when === 'past') { $where .= " AND (i.status = 'Cancelled' OR CONCAT(i.date,' ',i.time) < ?)"; $params[] = $now; }

$base = 'FROM interviews i JOIN applications a ON a.id = i.application_id JOIN vacancies v ON v.id = a.vacancy_id JOIN job_seekers js ON js.id = a.seeker_id JOIN users u ON u.id = js.user_id';
$c = $pdo->prepare("SELECT COUNT(*) $base WHERE $where"); $c->execute($params);
[$page, $per, $offset, $pages] = paginate((int)$c->fetchColumn(), 10);
$q = $pdo->prepare("SELECT i.*, a.id AS app_id, u.name AS seeker_name, v.title AS vacancy_title $base WHERE $where ORDER BY i.date " . ($when === 'upcoming' ? 'ASC' : 'DESC') . ", i.time LIMIT :per OFFSET :offset");
$params[':per'] = $per;
$params[':offset'] = $offset;
$q->execute($params); $rows = $q->fetchAll();

company_header('Jadwal Wawancara', 'interviews', $company);
?>
<div class="page-head"><div><span class="eyebrow">WAWANCARA</span><h1>Jadwal wawancara</h1><p>Jadwal baru dibuat dari halaman detail pelamar berstatus Shortlist.</p></div>
<a class="btn btn-outline" href="applicants.php?status=shortlisted">Lihat pelamar Shortlist</a></div>
<form method="get" class="filters"><div class="field"><label for="when">Tampilkan</label><select id="when" name="when">
<option value="upcoming"<?= $when === 'upcoming' ? ' selected' : '' ?>>Mendatang</option><option value="past"<?= $when === 'past' ? ' selected' : '' ?>>Sudah lewat / dibatalkan</option><option value="all"<?= $when === 'all' ? ' selected' : '' ?>>Semua</option></select></div>
<button class="btn btn-outline" type="submit">Terapkan</button></form>

<?php if (!$rows): ?><div class="card empty">Tidak ada jadwal wawancara pada filter ini.</div><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Pelamar</th><th scope="col">Jadwal</th><th scope="col">Mode &amp; lokasi</th><th scope="col">Konfirmasi pelamar</th><th scope="col">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $i): $cancelled = $i['status'] === 'Cancelled'; ?><tr>
<td><a href="applicant-detail.php?id=<?= (int)$i['app_id'] ?>"><?= e($i['seeker_name']) ?></a><span class="sub"><?= e($i['vacancy_title']) ?></span></td>
<td><?= e(fmt_date($i['date'])) ?><span class="sub"><?= e(substr($i['time'], 0, 5)) ?> WIB</span></td>
<td><?= $i['mode'] === 'online' ? 'Online' : 'Offline' ?><span class="sub"><?= e($i['location_or_link']) ?></span><?php if ($i['accommodation_note']): ?><span class="sub">Akomodasi: <?= e($i['accommodation_note']) ?></span><?php endif; ?></td>
<td><span class="badge <?= $i['status'] === 'Confirmed' ? 'badge-ok' : ($cancelled ? 'badge-muted' : 'badge-wait') ?>"><?= e(interview_status_label($i['status'])) ?></span></td>
<td><div class="actions"><?php if (!$cancelled): ?>
<a class="btn btn-outline btn-sm" href="interview-form.php?id=<?= (int)$i['id'] ?>">Edit</a>
<form method="post" class="inline-form" data-confirm="Batalkan jadwal wawancara ini?"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$i['id'] ?>"><button class="btn btn-danger btn-sm" type="submit">Batalkan</button></form>
<?php else: ?><a class="btn btn-outline btn-sm" href="applicant-detail.php?id=<?= (int)$i['app_id'] ?>">Detail pelamar</a><?php endif; ?></div></td></tr>
<?php endforeach; ?></tbody></table></div>
<?= pagination_html($page, $pages, ['when' => $when]) ?>
<?php endif; ?>
<?php company_footer();
