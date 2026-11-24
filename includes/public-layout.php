<?php
declare(strict_types=1);
/** Kerangka halaman publik (company.php, job-detail.php) memakai gaya navbar landing page. */
function public_header(string $title, string $description = ''): void {
    $user = current_user();
    ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Rangkul</title><?php if ($description): ?><meta name="description" content="<?= e($description) ?>"><?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css"><link rel="stylesheet" href="assets/css/company.css"></head>
<body class="landing-body">
<a class="skip-link" href="#konten">Lewati ke konten utama</a>
<header class="landing-nav"><div class="landing-nav-inner">
<a class="landing-brand" href="index.php" aria-label="Rangkul, halaman utama"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a>
<nav class="landing-links" aria-label="Navigasi utama"><a href="index.php#lowongan">Lowongan</a><a href="index.php#komitmen-inklusif">Komitmen inklusif</a><a href="index.php#cara-kerja">Cara kerja</a></nav>
<div class="landing-nav-actions">
<?php if ($user): ?><a class="nav-login" href="<?= e(dashboard_for_role($user['role'])) ?>">Dashboard</a>
<?php else: ?><a class="nav-login" href="login-recruiter.php">Untuk Perusahaan</a><a class="nav-login" href="login-job-seeker.php">Masuk</a><?php endif; ?>
</div></div></header>
<main id="konten">
<?php
}

function public_footer(): void {
    ?></main>
<footer class="landing-footer"><div class="footer-bottom"><span>© <?= date('Y') ?> Rangkul. Semua hak dilindungi.</span><span>Merangkul potensi, membuka kesempatan.</span></div></footer></body></html><?php
}

/** Kartu lowongan publik. $v wajib memuat: id,title,location,work_type,category_name,company_name,logo,verification_status. */
function public_job_card(array $v): void {
    ?>
<a class="job-card" href="job-detail.php?id=<?= (int)$v['id'] ?>">
<span class="co-name"><?= company_logo_html($v['logo'] ?? null, $v['company_name'], '', 'co-logo co-logo-sm') ?><span><?= e($v['company_name']) ?><?= ($v['verification_status'] ?? '') === 'verified' ? ' <span class="badge badge-ok" style="margin-left:4px">✓</span>' : '' ?></span></span>
<h3><?= e($v['title']) ?></h3>
<p class="meta"><?= e($v['category_name']) ?> · <?= e(work_types()[$v['work_type']] ?? $v['work_type']) ?> · <?= e($v['location']) ?></p>
<?php foreach (array_slice(csv_keys(json_decode($v['accessibility_tags'] ?? '[]', true) ?: []), 0, 3) as $k): ?><span class="tag tag-access"><?= e(access_options()[$k]) ?></span><?php endforeach; ?>
</a>
<?php
}
