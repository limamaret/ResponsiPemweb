<?php
// C-08: halaman publik perusahaan beserta lowongan aktifnya. Dapat diakses siapa saja.
require_once __DIR__ . '/includes/company.php';
require_once __DIR__ . '/includes/public-layout.php';

$id = (int)($_GET['id'] ?? 0);
$s = $pdo->prepare('SELECT * FROM companies WHERE id = ? LIMIT 1');
$s->execute([$id]); $co = $s->fetch();
if (!$co) {
    http_response_code(404);
    public_header('Perusahaan tidak ditemukan');
    echo '<div class="pub-wrap"><h1>Perusahaan tidak ditemukan</h1><p><a href="index.php">← Kembali ke beranda</a></p></div>';
    public_footer(); exit;
}
$verified = $co['verification_status'] === 'verified';

$q = $pdo->prepare(
    "SELECT v.*, cat.name AS category_name, c.company_name, c.logo, c.verification_status
     FROM vacancies v JOIN categories cat ON cat.id = v.category_id JOIN companies c ON c.id = v.company_id
     WHERE v.company_id = ? AND v.status = 'open' AND v.deadline >= ? ORDER BY v.created_at DESC, v.id DESC"
);
$q->execute([$id, date('Y-m-d')]); 
$jobs = $q->fetchAll();

public_header($co['company_name'], mb_substr(strip_tags((string)$co['description']), 0, 150));
?>
<div class="pub-wrap">
<div class="pub-hero">
<?= company_logo_html($co['logo'], $co['company_name'], '') ?>
<div><h1><?= e($co['company_name']) ?></h1>
<?php if ($verified): ?><span class="badge badge-ok">✓ Perusahaan terverifikasi</span> <?php if ($co['is_inclusive']): ?><span class="badge badge-inclusive">♡ Perusahaan inklusif</span><?php endif; ?>
<?php else: ?><span class="badge badge-wait">Belum diverifikasi</span><?php endif; ?></div>
</div>

<div class="job-layout">
<div>
<section class="card" aria-labelledby="h-about"><h2 id="h-about">Tentang perusahaan</h2>
<p class="prose"><?= $co['description'] ? e($co['description']) : 'Perusahaan belum menambahkan deskripsi.' ?></p></section>

<section aria-labelledby="h-jobs"><h2 id="h-jobs" style="margin:8px 0 14px">Lowongan aktif (<?= count($jobs) ?>)</h2>
<?php if (!$jobs): ?><div class="card empty">Belum ada lowongan aktif saat ini.</div>
<?php else: ?><div class="job-cards"><?php foreach ($jobs as $v) public_job_card($v); ?></div><?php endif; ?></section>
</div>

<aside class="apply-box">
<div class="card"><h2>Informasi</h2>
<dl class="def-list" style="grid-template-columns:90px 1fr">
<dt>Alamat</dt><dd><?= e($co['address'] ?: '-') ?></dd>
<dt>Website</dt><dd><?php if ($co['website'] && preg_match('#^https?://#i', $co['website'])): ?><a href="<?= e($co['website']) ?>" target="_blank" rel="noopener noreferrer"><?= e($co['website']) ?></a><?php else: ?>-<?php endif; ?></dd></dl></div>
<?php if ($verified && ($co['is_inclusive'] || $co['accessibility_facilities'])): ?>
<div class="card"><h2>Komitmen inklusif</h2>
<?php if ($co['is_inclusive']): ?><p style="font-size:14px;line-height:1.7;color:#1e293b">Perusahaan ini berkomitmen membuka kesempatan kerja yang setara, termasuk bagi penyandang disabilitas.</p><?php endif; ?>
<?php $fac = csv_keys($co['accessibility_facilities']); if ($fac): ?><p style="font-size:13px;font-weight:700;margin:12px 0 8px">Fasilitas aksesibilitas</p>
<?php foreach ($fac as $k): ?><span class="tag tag-access"><?= e(access_options()[$k]) ?></span><?php endforeach; endif; ?></div>
<?php endif; ?>
</aside>
</div></div>
<?php public_footer();
