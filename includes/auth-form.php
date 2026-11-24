<?php
// Shared visual form helpers. The calling page supplies $title, $subtitle, $actionLabel, $roleLabel.
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Rangkul</title><link rel="stylesheet" href="assets/css/style.css"><script src="assets/js/password-toggle.js" defer></script></head><body class="auth-body">
<div class="auth-shell"><aside class="auth-aside"><a class="brand brand-light" href="index.php" aria-label="Rangkul - Beranda"><img class="brand-logo" src="assets/img/rangkul-logo-white.png" alt="Rangkul"></a>
<div class="aside-content"><span class="eyebrow light">YOUR NEXT CHAPTER STARTS HERE</span><h1>Bangun masa depan kariermu bersama kami.</h1><p>Temukan peluang, kembangkan potensi, dan wujudkan langkah berikutnya.</p><div class="aside-points"><span>✓ Akses peluang karier</span><span>✓ Proses rekrutmen terarah</span><span>✓ Satu platform untuk berkembang</span></div></div></aside>
<main class="auth-main"><div class="auth-form-wrap"><a class="back-link" href="index.php">← Kembali</a><span class="eyebrow auth-eyebrow"><?= e($roleLabel) ?></span><h2><?= e($title) ?></h2><p class="muted"><?= e($subtitle) ?></p>
<?php if (!empty($error)): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
