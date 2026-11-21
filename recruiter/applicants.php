<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];

$vacancies = $pdo->prepare('SELECT id, title FROM vacancies WHERE company_id = ? ORDER BY title');
$vacancies->execute([$cid]); $vacancies = $vacancies->fetchAll();
$vacIds = array_map('intval', array_column($vacancies, 'id'));

$vacId = (int)($_GET['vacancy_id'] ?? 0);
$status = $_GET['status'] ?? '';
$where = 'v.company_id = ?'; $params = [$cid];
if ($vacId && in_array($vacId, $vacIds, true)) { $where .= ' AND v.id = ?'; $params[] = $vacId; } else { $vacId = 0; }
if (isset(application_statuses()[$status])) { $where .= ' AND a.status = ?'; $params[] = $status; } else { $status = ''; }

$c = $pdo->prepare("SELECT COUNT(*) FROM applications a JOIN vacancies v ON v.id = a.vacancy_id WHERE $where");
$c->execute($params);
[$page, $per, $offset, $pages] = paginate((int)$c->fetchColumn(), 10);
$q = $pdo->prepare(
    "SELECT a.id, a.status, a.created_at, u.name AS seeker_name, v.title AS vacancy_title, js.disability_type
     FROM applications a JOIN vacancies v ON v.id = a.vacancy_id
     JOIN job_seekers js ON js.id = a.seeker_id JOIN users u ON u.id = js.user_id
     WHERE $where ORDER BY a.created_at DESC, a.id DESC LIMIT $per OFFSET $offset"
);
$q->execute($params); $rows = $q->fetchAll();

company_header('Daftar Pelamar', 'applicants', $company);
?>
<div class="page-head"><div><span class="eyebrow">REKRUTMEN</span><h1>Daftar pelamar</h1><p>Tinjau pelamar pada lowongan perusahaan Anda.</p></div></div>
<form method="get" class="filters">
<div class="field"><label for="vacancy_id">Lowongan</label><select id="vacancy_id" name="vacancy_id"><option value="">Semua lowongan</option>
<?php foreach ($vacancies as $v): ?><option value="<?= (int)$v['id'] ?>"<?= $vacId === (int)$v['id'] ? ' selected' : '' ?>><?= e($v['title']) ?></option><?php endforeach; ?></select></div>
<div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">Semua status</option>
<?php foreach (application_statuses() as $k => $l): ?><option value="<?= e($k) ?>"<?= $status === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select></div>
<button class="btn btn-outline" type="submit">Terapkan filter</button></form>

<?php if (!$rows): ?><div class="card empty">Belum ada pelamar yang sesuai dengan filter.</div><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Pelamar</th><th scope="col">Lowongan</th><th scope="col">Tanggal lamar</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $r): ?><tr>
<td><b><?= e($r['seeker_name']) ?></b></td><td><?= e($r['vacancy_title']) ?></td><td><?= e(fmt_date($r['created_at'])) ?></td><td><?= status_badge($r['status']) ?></td>
<td><a class="btn btn-outline btn-sm" href="applicant-detail.php?id=<?= (int)$r['id'] ?>">Lihat detail</a></td></tr>
<?php endforeach; ?></tbody></table></div>
<?= pagination_html($page, $pages, ['vacancy_id' => $vacId ?: '', 'status' => $status]) ?>
<?php endif; ?>
<?php company_footer();
