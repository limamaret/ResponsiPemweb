<?php
// Detail lowongan publik (versi minimal dari sisi Company).
// Tombol "Lamar" mengarah ke job-seeker/apply.php?vacancy_id=ID — halaman itu milik sisi Job Seeker.
// Jika tim Job Seeker memiliki halaman detail sendiri, file ini boleh diganti.
require_once __DIR__ . '/includes/company.php';
require_once __DIR__ . '/includes/public-layout.php';

const APPLY_URL = 'job-seeker/apply.php';
$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare(
    "SELECT v.*, cat.name AS category_name, c.id AS cid, c.company_name, c.logo, c.verification_status, c.is_inclusive, c.user_id AS company_user_id
     FROM vacancies v JOIN categories cat ON cat.id = v.category_id JOIN companies c ON c.id = v.company_id WHERE v.id = ? LIMIT 1"
);
$s->execute([$id]); $v = $s->fetch();
$user = current_user();
$isOwner = $v && $user && $user['role'] === 'recruiter' && (int)$user['id'] === (int)$v['company_user_id'];
$visible = $v && $v['status'] === 'open' && $v['deadline'] >= date('Y-m-d');
if (!$v || (!$visible && !$isOwner)) {
    http_response_code(404);
    public_header('Lowongan tidak ditemukan');
    echo '<div class="pub-wrap"><h1>Lowongan tidak ditemukan atau sudah ditutup</h1><p><a href="index.php">← Kembali ke beranda</a></p></div>';
    public_footer(); exit;
}
public_header($v['title'] . ' — ' . $v['company_name'], mb_substr(strip_tags((string)$v['description']), 0, 150));
?>
<div class="pub-wrap">
<?php if ($isOwner && !$visible): ?><div class="note-box" style="margin-bottom:18px">Pratinjau pemilik: lowongan ini <b>tidak tampil di daftar publik</b> karena ditutup atau sudah lewat deadline.</div><?php endif; ?>
<a class="back-link" href="company.php?id=<?= (int)$v['cid'] ?>">← Profil <?= e($v['company_name']) ?></a>
<div class="job-layout">
<div>
<div class="pub-hero"><?= company_logo_html($v['logo'], $v['company_name'], '') ?>
<div><h1><?= e($v['title']) ?></h1>
<p style="margin:0"><a href="company.php?id=<?= (int)$v['cid'] ?>"><?= e($v['company_name']) ?></a> <?= $v['verification_status'] === 'verified' ? '<span class="badge badge-ok">✓ Terverifikasi</span>' : '' ?> <?= ($v['verification_status'] === 'verified' && $v['is_inclusive']) ? '<span class="badge badge-inclusive">♡ Perusahaan inklusif</span>' : '' ?></p></div></div>
<section class="card"><h2>Deskripsi pekerjaan</h2><p class="prose"><?= e($v['description']) ?></p></section>
<section class="card"><h2>Persyaratan</h2><p class="prose"><?= e($v['requirements']) ?></p></section>
<?php $tags = csv_keys(json_decode($v['accessibility_tags'] ?? '[]', true) ?: []); ?>
<section class="card"><h2>Aksesibilitas lowongan ini</h2>
<?php if ($tags): foreach ($tags as $k): ?><span class="tag tag-access"><?= e(access_options()[$k]) ?></span><?php endforeach; else: ?><p class="muted" style="margin:0">Perusahaan belum mencantumkan fasilitas aksesibilitas khusus untuk posisi ini.</p><?php endif; ?></section>
</div>
<aside class="apply-box"><div class="card"><h2>Ringkasan</h2>
<dl class="def-list" style="grid-template-columns:95px 1fr">
<dt>Kategori</dt><dd><?= e($v['category_name']) ?></dd><dt>Tipe kerja</dt><dd><?= e(work_types()[$v['work_type']] ?? $v['work_type']) ?></dd>
<dt>Lokasi</dt><dd><?= e($v['location']) ?></dd><dt>Gaji</dt><dd><?= e(salary_range($v['salary_min'], $v['salary_max'])) ?></dd>
<dt>Deadline</dt><dd><?= e(fmt_date($v['deadline'])) ?></dd></dl>
<div style="margin-top:18px">
<?php if (!$visible): ?><span class="badge badge-muted">Tidak menerima lamaran</span>
<?php elseif (!$user): ?><a class="btn btn-primary btn-block" href="login-job-seeker.php">Masuk untuk melamar</a>
<?php elseif ($user['role'] === 'job_seeker'): ?><a class="btn btn-primary btn-block" href="<?= APPLY_URL ?>?vacancy_id=<?= (int)$v['id'] ?>">Lamar</a>
<?php else: ?><p class="muted" style="font-size:13px;margin:0">Hanya akun Job Seeker yang dapat melamar.</p><?php endif; ?>
</div></div></aside>
</div></div>
<?php public_footer();
