<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
http_response_code(404);
$user = current_user();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Halaman Tidak Ditemukan — Rangkul</title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="landing-body">
<a class="skip-link" href="#main-content">Lewati ke konten utama</a>
<header class="landing-nav"><div class="landing-nav-inner">
<a class="landing-brand" href="index.php" aria-label="Rangkul, halaman utama"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a>
<nav class="landing-links" aria-label="Navigasi utama"><a href="index.php#tentang">Tentang</a><a href="index.php#keunggulan">Keunggulan</a><a href="index.php#cara-kerja">Cara kerja</a><a href="index.php#fitur">Fitur</a><a href="index.php#aksesibilitas">Aksesibilitas</a><a href="index.php#faq">FAQ</a></nav>
<div class="landing-nav-actions">
<?php if ($user): ?>
    <span style="font-size:13px;font-weight:600;color:#536078;"><?= e($user['name']) ?></span>
    <a class="nav-login" href="<?= dashboard_for_role($user['role']) ?>">Dashboard</a>
    <a class="btn btn-outline" href="logout.php" style="padding:10px 16px;font-size:12px;">Logout</a>
<?php else: ?>
    <a class="nav-login" href="login-job-seeker.php">Masuk</a>
    <a class="btn btn-primary" href="register-job-seeker.php" style="padding:10px 16px;font-size:12px;">Daftar</a>
<?php endif; ?>
<button class="mobile-menu" type="button" aria-label="Buka navigasi" aria-expanded="false">☰</button>
</div></div></header>
<main id="main-content" style="max-width:600px;margin:80px auto;padding:0 24px;text-align:center;">
    <div class="not-found" style="padding:60px 20px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:80px;height:80px;margin:0 auto 24px;opacity:0.5;color:#94a3b8;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
        <h2 style="font-size:28px;margin:0 0 12px;color:#1e293b;">Halaman Tidak Ditemukan</h2>
        <p style="color:#64748b;margin:0 0 28px;font-size:16px;">Maaf, halaman yang Anda cari tidak ada atau telah dipindahkan.</p>
        <a href="index.php" class="btn btn-primary" style="padding:14px 28px;font-size:15px;">Kembali ke Beranda</a>
        <a href="vacancies.php" class="btn btn-outline" style="margin-left:12px;padding:14px 28px;font-size:15px;">Cari Lowongan</a>
    </div>
</main>
<footer class="landing-footer"><div class="footer-main"><div class="footer-about"><a class="landing-brand footer-brand" href="index.php"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a><p class="footer-tagline">Merangkul potensi, membuka kesempatan.</p><p class="footer-description">Rangkul adalah ruang digital yang mempertemukan pencari kerja dan perusahaan dalam proses rekrutmen yang lebih terarah, mudah dipahami, dan inklusif. Kami percaya setiap orang berhak mendapatkan kesempatan untuk berkembang.</p></div><div class="footer-column"><b>Jelajahi</b><a href="index.php#tentang">Tentang Rangkul</a><a href="index.php#cara-kerja">Cara kerja</a><a href="index.php#fitur">Fitur platform</a></div><div class="footer-column"><b>Bantuan</b><a href="index.php#aksesibilitas">Aksesibilitas</a><a href="index.php#faq">Pertanyaan umum</a><a href="admin/login.php">Administrator</a></div><div class="footer-column footer-purpose"><b>Komitmen Rangkul</b><p>Mendorong kesempatan kerja yang lebih terbuka dan setara, sejalan dengan Tujuan Pembangunan Berkelanjutan nomor 8.</p><span>SDG 8 · Decent Work and Economic Growth</span></div></div><div class="footer-bottom"><span>© <?= date('Y') ?> Rangkul. Semua hak dilindungi.</span><span>Dibangun untuk kesempatan yang lebih inklusif.</span></div></footer>
</body>
</html>