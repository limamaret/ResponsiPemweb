<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];

// Aksi POST: buka/tutup dan hapus (selalu cek kepemilikan + CSRF)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $v = own_vacancy($pdo, $id, $cid);
    if (!$v) { flash('error', 'Lowongan tidak ditemukan.'); redirect('vacancies.php'); }
    if ($action === 'toggle') {
        $new = $v['status'] === 'open' ? 'closed' : 'open';
        $pdo->prepare('UPDATE vacancies SET status = ? WHERE id = ? AND company_id = ?')->execute([$new, $id, $cid]);
        flash('success', 'Lowongan "' . $v['title'] . '" ' . ($new === 'open' ? 'dibuka kembali.' : 'ditutup.'));
        if ($new === 'open' && $v['deadline'] < date('Y-m-d')) flash('error', 'Perhatian: deadline lowongan sudah lewat, sehingga belum tampil di daftar publik. Perbarui deadline melalui menu Edit.');
    } elseif ($action === 'delete') {
        // Lamaran & wawancara terkait ikut terhapus (ON DELETE CASCADE)
        $pdo->prepare('DELETE FROM vacancies WHERE id = ? AND company_id = ?')->execute([$id, $cid]);
        flash('success', 'Lowongan "' . $v['title'] . '" dihapus beserta data lamarannya.');
    }
    redirect('vacancies.php?' . http_build_query(array_filter(['page' => $_POST['page'] ?? ''])));
}

$status = $_GET['status'] ?? '';
$where = 'v.company_id = :cid'; $params = ['cid' => $cid];
if ($status === 'open') { $where .= " AND v.status = 'open' AND v.deadline >= CURDATE()"; }
elseif ($status === 'closed') { $where .= " AND (v.status = 'closed' OR v.deadline < CURDATE())"; }

$c = $pdo->prepare("SELECT COUNT(*) FROM vacancies v WHERE $where"); $c->execute($params);
[$page, $per, $offset, $pages] = paginate((int)$c->fetchColumn(), 10);
$q = $pdo->prepare(
    "SELECT v.*, cat.name AS category_name,
            (SELECT COUNT(*) FROM applications a WHERE a.vacancy_id = v.id AND a.status <> 'Dibatalkan') AS applicant_count
     FROM vacancies v JOIN categories cat ON cat.id = v.category_id
     WHERE $where ORDER BY v.created_at DESC, v.id DESC LIMIT :per OFFSET :offset"
);
$params[':per'] = $per;
$params[':offset'] = $offset;
$q->execute($params); $rows = $q->fetchAll();

company_header('Kelola Lowongan', 'vacancies', $company);
?>
<div class="page-head"><div><span class="eyebrow">LOWONGAN</span><h1>Lowongan saya</h1><p>Kelola lowongan milik perusahaan Anda.</p></div>
<a class="btn btn-primary" href="vacancy-form.php">+ Tambah lowongan</a></div>

<form method="get" class="filters"><div class="field"><label for="status">Tampilkan</label>
<select id="status" name="status"><option value="">Semua lowongan</option><option value="open"<?= $status === 'open' ? ' selected' : '' ?>>Aktif (terbuka &amp; belum lewat deadline)</option><option value="closed"<?= $status === 'closed' ? ' selected' : '' ?>>Ditutup / lewat deadline</option></select></div>
<button class="btn btn-outline" type="submit">Terapkan</button></form>

<?php if (!$rows): ?><div class="card empty">Belum ada lowongan. <a href="vacancy-form.php">Buat lowongan pertama Anda →</a></div><?php else: ?>
<div class="table-wrap"><table class="table"><thead><tr><th scope="col">Lowongan</th><th scope="col">Deadline</th><th scope="col">Pelamar</th><th scope="col">Status</th><th scope="col">Aksi</th></tr></thead><tbody>
<?php foreach ($rows as $v): $expired = $v['deadline'] < date('Y-m-d'); ?>
<tr><td><b><?= e($v['title']) ?></b><span class="sub"><?= e($v['category_name']) ?> · <?= e(work_types()[$v['work_type']] ?? $v['work_type']) ?> · <?= e($v['location']) ?></span>
<?php foreach (csv_keys(json_decode($v['accessibility_tags'] ?? '[]', true) ?: []) as $k): ?><span class="tag tag-access"><?= e(access_options()[$k]) ?></span><?php endforeach; ?></td>
<td><?= e(fmt_date($v['deadline'])) ?><?= $expired ? '<span class="sub">Sudah lewat</span>' : '' ?></td>
<td><a href="applicants.php?vacancy_id=<?= (int)$v['id'] ?>"><?= (int)$v['applicant_count'] ?> pelamar</a></td>
<td><?php if ($v['status'] === 'open' && !$expired): ?><span class="badge badge-ok">Terbuka</span><?php elseif ($v['status'] === 'open'): ?><span class="badge badge-wait">Lewat deadline</span><?php else: ?><span class="badge badge-muted">Ditutup</span><?php endif; ?></td>
<td><div class="actions">
<a class="btn btn-outline btn-sm" href="vacancy-form.php?id=<?= (int)$v['id'] ?>">Edit</a>
<form method="post" class="inline-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><input type="hidden" name="page" value="<?= $page ?>"><input type="hidden" name="action" value="toggle"><button class="btn btn-outline btn-sm" type="submit"><?= $v['status'] === 'open' ? 'Tutup' : 'Buka' ?></button></form>
<form method="post" class="inline-form" data-confirm="Hapus lowongan ini beserta seluruh lamaran dan jadwal wawancaranya? Tindakan ini tidak dapat dibatalkan."><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$v['id'] ?>"><input type="hidden" name="page" value="<?= $page ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-danger btn-sm" type="submit">Hapus</button></form>
</div></td></tr>
<?php endforeach; ?></tbody></table></div>
<?= pagination_html($page, $pages, ['status' => $status]) ?>
<?php endif; ?>
<?php company_footer();
