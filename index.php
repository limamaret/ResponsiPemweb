<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
if (current_user()) { redirect(dashboard_for_role(current_user()['role'])); }
$success = flash('success'); $error = flash('error');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Rangkul — Ruang karier yang terbuka untuk semua</title>
<meta name="description" content="Kenali Rangkul, platform yang menghubungkan pencari kerja dan perusahaan melalui proses rekrutmen yang terarah dan pengalaman yang inklusif.">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<script src="assets/js/landing-interactions.js" defer></script>
<script src="assets/js/custom-cursor.js" defer></script>
</head>
<body class="landing-body">
<a class="skip-link" href="#konten">Lewati ke konten utama</a>
<header class="landing-nav"><div class="landing-nav-inner">
<a class="landing-brand" href="index.php" aria-label="Rangkul, halaman utama"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a>
<nav class="landing-links" aria-label="Navigasi utama"><a href="#tentang">Tentang</a><a href="#keunggulan">Keunggulan</a><a href="#cara-kerja">Cara kerja</a><a href="#fitur">Fitur</a><a href="#aksesibilitas">Aksesibilitas</a><a href="#faq">FAQ</a></nav>
<div class="landing-nav-actions"><a class="nav-login" href="login-job-seeker.php">Masuk</a><button class="mobile-menu" type="button" aria-label="Buka navigasi" aria-expanded="false">☰</button></div>
</div></header>
<main id="konten">
<?php if ($success): ?><div class="notice success landing-notice"><?= e($success) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error landing-notice"><?= e($error) ?></div><?php endif; ?>
<section class="landing-hero">
<div class="hero-glow glow-one"></div><div class="hero-glow glow-two"></div>
<div class="landing-hero-inner">
<div class="hero-text"><div class="hero-pill"><span class="pill-spark" aria-hidden="true">✦</span> KARIER YANG LEBIH TERBUKA UNTUK SEMUA</div>
<h1>Setiap potensi berhak mendapat <span>kesempatan.</span></h1>
<p>Rangkul adalah ruang digital yang mempertemukan pencari kerja dan perusahaan. Kenali kemampuan, jelajahi proses rekrutmen, dan bangun langkah karier dengan pengalaman yang dirancang agar semakin inklusif.</p>
<div class="hero-cta"><a class="btn hero-primary" href="#tentang">Jelajahi Rangkul <span aria-hidden="true">↓</span></a><a class="hero-secondary" href="#cara-kerja">Lihat cara kerjanya <span aria-hidden="true">↗</span></a></div>
<div class="hero-proof"><div class="proof-avatars" aria-hidden="true"><i>R</i><i>+</i><i>∞</i></div><div><strong>Potensi yang beragam. Kesempatan yang setara.</strong><small>Platform untuk pencari kerja dan tim perekrutan.</small></div></div>
</div>
<div class="hero-visual" aria-label="Ilustrasi hubungan pencari kerja dan perusahaan"><div class="visual-orbit orbit-a"></div><div class="visual-orbit orbit-b"></div><div class="brand-promise-card"><img src="assets/img/rangkul-logo-white.png" alt="" class="promise-logo"><span class="promise-kicker">SATU RUANG, BANYAK KEMUNGKINAN</span><h3>Temukan arah.<br>Bangun koneksi.<br>Bertumbuh bersama.</h3><div class="promise-divider"></div><p>Merangkul potensi, membuka kesempatan.</p><div class="promise-tags"><span>Pencari kerja</span><span>Perusahaan</span></div></div><div class="visual-float float-bottom"><span aria-hidden="true">✦</span><div><b>Kesempatan dimulai dari akses</b><small>Pengalaman yang terus dikembangkan</small></div></div></div>
</div>
<div class="hero-bottom"><span>RUANG UNTUK BERTUMBUH</span><span class="bottom-line"></span><span>RANGKUL MASA DEPANMU</span></div>
</section>

<section class="overview-section" id="tentang"><div class="overview-wrap">
<div class="section-eyebrow">MENGENAL RANGKUL</div><div class="overview-grid"><div><h2>Bukan hanya tempat mencari lowongan. <span>Ini ruang untuk menghubungkan potensi.</span></h2></div><div><p>Rangkul dirancang sebagai platform rekrutmen yang menghubungkan dua kebutuhan dalam satu alur: pencari kerja yang ingin menemukan kesempatan dan perusahaan yang ingin menemukan talenta.</p><p>Di dalamnya, pencari kerja dapat membangun profil dan mengelola perjalanan lamaran. Perusahaan dapat memperkenalkan organisasinya, mengelola lowongan, meninjau kandidat, serta mengatur tahapan seleksi.</p></div></div>
<div class="overview-stats"><article><strong>01</strong><span>Profil dan identitas profesional</span></article><article><strong>02</strong><span>Informasi dan pengelolaan lowongan</span></article><article><strong>03</strong><span>Lamaran dan tahapan rekrutmen</span></article><article><strong>04</strong><span>Komunikasi dan jadwal wawancara</span></article></div>
</div></section>

<section class="highlights-section" id="keunggulan"><div class="highlights-wrap">
<div class="section-heading"><span class="section-eyebrow">KENAPA RANGKUL?</span><h2>Kesempatan yang lebih terbuka,<br>dengan pengalaman yang lebih terarah.</h2><p>Rangkul dibangun dengan perhatian pada kebutuhan pencari kerja maupun perusahaan.</p></div>
<div class="highlights-grid">
<article class="highlight-card highlight-featured"><div class="highlight-icon" aria-hidden="true">♡</div><span class="highlight-label">KESEMPATAN KERJA INKLUSIF</span><h3>Membuka peluang bagi penyandang disabilitas</h3><p>Rangkul ingin menjadi ruang bagi penyandang disabilitas untuk menemukan dan melamar pekerjaan berdasarkan kemampuan, keterampilan, serta minat mereka. Kami juga mendorong perusahaan agar semakin terbuka menyediakan kesempatan kerja yang setara dan lingkungan kerja yang inklusif.</p><a href="#komitmen-inklusif" class="highlight-link">Kenali komitmen Rangkul <span aria-hidden="true">↗</span></a></article>
<article class="highlight-card"><div class="highlight-icon" aria-hidden="true">◎</div><span class="highlight-label">AKSESIBILITAS DIGITAL</span><h3>Pengalaman yang lebih nyaman diakses</h3><p>Pengaturan tampilan seperti ukuran teks, pilihan font, kontras, dan tema membantu pengunjung menyesuaikan pengalaman menggunakan website. Aksesibilitas juga perlu diuji dan dikembangkan bersama pengguna dengan kebutuhan yang beragam.</p><a href="#aksesibilitas" class="highlight-link">Lihat fitur aksesibilitas <span aria-hidden="true">↗</span></a></article>
<article class="highlight-card"><div class="highlight-icon" aria-hidden="true">◎</div><span class="highlight-label">PROFIL TERSTRUKTUR</span><h3>Kenali potensi secara utuh</h3><p>Informasi biodata, pendidikan, pengalaman, keterampilan, dan CV dapat dikelola dalam profil profesional.</p></article>
<article class="highlight-card"><div class="highlight-icon" aria-hidden="true">↗</div><span class="highlight-label">PROSES LEBIH JELAS</span><h3>Perjalanan lamaran lebih terpantau</h3><p>Informasi lamaran dan tahapan seleksi disusun agar pencari kerja maupun perusahaan lebih mudah mengikuti perkembangannya.</p></article>
<article class="highlight-card"><div class="highlight-icon" aria-hidden="true">⇄</div><span class="highlight-label">DUA SISI, SATU RUANG</span><h3>Menghubungkan talenta dan perusahaan</h3><p>Pencari kerja dan perusahaan memiliki ruang sesuai perannya, dari pengelolaan profil hingga proses rekrutmen dan wawancara.</p></article>
</div></div></section>

<section class="inclusive-commitment-section" id="komitmen-inklusif">
  <div class="inclusive-commitment-wrap">
    <div class="commitment-copy">
      <span class="section-eyebrow">BUKAN SEKADAR AKSES WEBSITE</span>
      <h2>Kesempatan kerja juga harus <span>terbuka untuk semua.</span></h2>
      <p>Inklusivitas Rangkul bukan hanya tentang membuat website lebih mudah digunakan. Kami ingin penyandang disabilitas juga mendapat ruang yang nyata dalam dunia kerja—untuk menemukan lowongan, menunjukkan kompetensi, dan mengikuti proses rekrutmen secara setara.</p>
    </div>
    <div class="commitment-points">
      <article><span>01</span><div><b>Penyandang disabilitas sebagai pencari kerja</b><p>Kesempatan untuk mencari dan melamar pekerjaan perlu terbuka berdasarkan kompetensi, bukan prasangka terhadap disabilitas.</p></div></article>
      <article><span>02</span><div><b>Perusahaan didorong lebih inklusif</b><p>Rangkul mendorong perusahaan mempertimbangkan kandidat penyandang disabilitas serta memperhatikan kebutuhan akomodasi yang relevan pada proses kerja.</p></div></article>
      <article><span>03</span><div><b>Informasi lowongan yang lebih transparan</b><p>Informasi tentang tugas, persyaratan, lingkungan kerja, dan dukungan yang tersedia membantu kandidat menilai kecocokan pekerjaan.</p></div></article>
    </div>
    <p class="commitment-note">Ini merupakan komitmen dan arah pengembangan Rangkul. Ketersediaan lowongan inklusif dan akomodasi tetap bergantung pada perusahaan yang memasang lowongan.</p>
  </div>
</section>

<section class="workflow-section" id="cara-kerja"><div class="workflow-wrap"><div class="section-heading"><span class="section-eyebrow">ALUR YANG LEBIH JELAS</span><h2>Dari langkah pertama<br>hingga proses seleksi.</h2><p>Setiap peran memiliki alur dan kebutuhan yang berbeda. Rangkul menyatukannya dalam satu perjalanan.</p></div>
<div class="workflow-grid"><article><span class="step-no">01</span><div class="step-icon">◎</div><h3>Kenali dan buat profil</h3><p>Pencari kerja mengisi informasi diri, pendidikan, pengalaman, keterampilan, dan CV. Perusahaan melengkapi identitas organisasinya.</p></article><article><span class="step-no">02</span><div class="step-icon">⌕</div><h3>Temukan kecocokan</h3><p>Perusahaan mengelola informasi lowongan. Pencari kerja meninjau kesempatan dan memilih posisi yang sesuai.</p></article><article><span class="step-no">03</span><div class="step-icon">↗</div><h3>Kelola proses lamaran</h3><p>Lamaran tercatat dan dapat dipantau. Recruiter meninjau kandidat serta memperbarui status seleksi.</p></article><article><span class="step-no">04</span><div class="step-icon">◷</div><h3>Lanjut ke wawancara</h3><p>Jadwal wawancara dapat diatur dan hasil tahapan seleksi dicatat agar proses lebih terstruktur.</p></article></div>
</div></section>

<section class="capability-section" id="fitur"><div class="capability-wrap"><div class="section-heading"><span class="section-eyebrow">APA YANG ADA DI DALAM RANGKUL?</span><h2>Fitur yang mengikuti<br>kebutuhan penggunanya.</h2><p>Pilih peran untuk melihat gambaran fitur utama yang disediakan.</p></div>
<div class="role-tabs" role="tablist" aria-label="Pilih peran pengguna"><button type="button" class="role-tab is-active" id="tab-seeker" role="tab" aria-selected="true" aria-controls="panel-seeker">Pencari kerja</button><button type="button" class="role-tab" id="tab-company" role="tab" aria-selected="false" aria-controls="panel-company">Perusahaan</button></div>
<div class="role-panel is-active" id="panel-seeker" role="tabpanel" aria-labelledby="tab-seeker"><div class="panel-copy"><span class="panel-kicker">UNTUK JOB SEEKER</span><h3>Kelola perjalanan kariermu dalam satu tempat.</h3><p>Mulai dari membangun profil hingga memantau proses lamaran, informasi penting tersusun agar lebih mudah ditemukan.</p></div><div class="feature-list"><article><b>Profil profesional</b><span>Biodata, pendidikan, pengalaman, keterampilan, dan CV.</span></article><article><b>Eksplorasi lowongan</b><span>Informasi posisi, perusahaan, lokasi, dan kualifikasi.</span></article><article><b>Pengelolaan lamaran</b><span>Kirim lamaran, lihat status, dan ikuti perkembangan seleksi.</span></article><article><b>Informasi wawancara</b><span>Lihat jadwal dan informasi tahapan wawancara.</span></article></div></div>
<div class="role-panel" id="panel-company" role="tabpanel" aria-labelledby="tab-company" hidden><div class="panel-copy"><span class="panel-kicker">UNTUK PERUSAHAAN</span><h3>Atur kebutuhan rekrutmen dengan lebih terstruktur.</h3><p>Kelola informasi perusahaan, publikasikan kebutuhan talenta, dan ikuti proses kandidat dari satu alur.</p></div><div class="feature-list"><article><b>Profil perusahaan</b><span>Informasi organisasi dan pengelolaan data perusahaan.</span></article><article><b>Manajemen lowongan</b><span>Buat, perbarui, dan kelola status lowongan pekerjaan.</span></article><article><b>Peninjauan kandidat</b><span>Lihat data pelamar, lakukan screening, dan perbarui status.</span></article><article><b>Seleksi dan wawancara</b><span>Atur jadwal wawancara dan catat hasil proses seleksi.</span></article></div></div>
</div></section>

<section class="inclusive-section" id="aksesibilitas"><div class="inclusive-wrap"><div class="inclusive-copy"><span class="section-eyebrow">AKSES YANG BISA DISESUAIKAN</span><h2>Pengalaman digital yang bisa <span>menyesuaikan kebutuhanmu.</span></h2><p>Setiap orang punya cara membaca dan berinteraksi yang berbeda. Karena itu, Rangkul menyediakan beberapa pengaturan tampilan yang bisa diubah langsung oleh pengunjung.</p><p>Pengaturan ini merupakan langkah awal. Aksesibilitas juga perlu diterapkan pada seluruh halaman aplikasi, diuji dengan keyboard, pembaca layar, dan pengguna dengan kebutuhan yang beragam.</p><div class="inclusive-note"><b>Pengaturan tersimpan</b><span>Pilihan tampilan akan diingat di browser yang sama dan bisa dikembalikan kapan saja.</span></div></div>
<div class="access-demo"><div class="access-demo-head"><div><span class="panel-kicker">PREFERENSI TAMPILAN</span><h3>Atur kenyamananmu</h3></div><span aria-hidden="true">Aa</span></div><p class="access-demo-desc">Coba pengaturan berikut dan lihat perubahan pada halaman ini.</p><div class="access-controls">
<div class="access-control-row"><div><b>Mode tampilan</b><small>Pilih tema terang atau gelap</small></div><div class="segmented-theme" role="group" aria-label="Mode tampilan"><button type="button" id="theme-light" aria-pressed="true">☀ Terang</button><button type="button" id="theme-dark" aria-pressed="false">☾ Gelap</button></div></div>
<div class="access-control-row"><div><b>Ukuran teks</b><small>Sesuaikan besar tulisan</small></div><div class="control-buttons"><button type="button" id="font-decrease" aria-label="Perkecil teks">A−</button><button type="button" id="font-increase" aria-label="Perbesar teks">A+</button></div></div>
<div class="access-control-row"><div><b>Kontras tinggi</b><small>Pertegas perbedaan warna</small></div><button type="button" class="toggle-switch" id="contrast-toggle" aria-pressed="false"><span></span><em>Tidak aktif</em></button></div>
<div class="access-control-row"><div><b>Bahasa halaman</b><small>Pilih bahasa yang nyaman dibaca</small></div><select id="language-select" aria-label="Bahasa halaman"><option value="id">Bahasa Indonesia</option><option value="en">English</option></select></div>
<div class="access-control-row"><div><b>Jenis huruf</b><small>Pilih bentuk huruf yang mudah dibaca</small></div><select id="font-select" aria-label="Jenis huruf"><option value="jakarta">Plus Jakarta Sans</option><option value="atkinson">Atkinson Hyperlegible</option><option value="arial">Arial</option></select></div>
</div><button type="button" class="reset-access" id="reset-access">Kembalikan pengaturan awal</button></div>
</div></section>

<section class="faq-section" id="faq"><div class="faq-wrap"><div class="section-heading"><span class="section-eyebrow">YANG SERING DITANYAKAN</span><h2>Masih ingin tahu lebih jauh?</h2><p>Temukan jawaban singkat tentang Rangkul dan cara penggunaannya.</p></div><div class="faq-list">
<article class="faq-item"><h3><button type="button" aria-expanded="false">Apa itu Rangkul?<span aria-hidden="true">＋</span></button></h3><div class="faq-answer" hidden><p>Rangkul adalah platform rekrutmen yang menghubungkan pencari kerja dengan perusahaan, sekaligus membantu mengelola informasi lowongan, lamaran, seleksi, dan wawancara.</p></div></article>
<article class="faq-item"><h3><button type="button" aria-expanded="false">Siapa yang dapat menggunakan Rangkul?<span aria-hidden="true">＋</span></button></h3><div class="faq-answer" hidden><p>Rangkul ditujukan bagi pencari kerja dan perwakilan perusahaan. Pengelolaan platform dilakukan oleh administrator.</p></div></article>
<article class="faq-item"><h3><button type="button" aria-expanded="false">Apakah Rangkul ditujukan untuk penyandang disabilitas juga?<span aria-hidden="true">＋</span></button></h3><div class="faq-answer" hidden><p>Ya. Rangkul memiliki arah pengembangan inklusif. Kami ingin mengurangi hambatan penggunaan melalui desain yang jelas, navigasi yang dapat diakses, dan dukungan teknologi bantu. Implementasinya perlu diuji secara berkala bersama pengguna dengan kebutuhan akses yang beragam.</p></div></article>
<article class="faq-item"><h3><button type="button" aria-expanded="false">Apakah lowongan di halaman ini merupakan lowongan aktif?<span aria-hidden="true">＋</span></button></h3><div class="faq-answer" hidden><p>Tidak. Halaman ini merupakan pengenalan platform, bukan daftar lowongan aktif. Informasi lowongan akan tersedia melalui fitur di dalam aplikasi setelah modul tersebut digunakan.</p></div></article>
</div></div></section>

<section class="closing-cta"><div class="cta-decoration" aria-hidden="true">R</div><span class="section-eyebrow">LANGKAH BERIKUTNYA DIMULAI DARI SINI</span><h2>Siap membuka<br>kesempatan bersama?</h2><p>Pilih jalur yang sesuai dengan tujuanmu dan mulai gunakan Rangkul.</p><div class="final-actions"><a href="register-job-seeker.php" class="btn cta-white">Daftar sebagai pencari kerja <span>→</span></a><a href="register-recruiter.php" class="btn final-outline">Daftar sebagai perusahaan <span>↗</span></a></div><small>Sudah memiliki akun? <a href="login-job-seeker.php">Masuk ke Rangkul</a></small></section>
</main>
<footer class="landing-footer"><div class="footer-main"><div class="footer-about"><a class="landing-brand footer-brand" href="index.php"><img src="assets/img/rangkul-logo.png" alt="Rangkul"></a><p class="footer-tagline">Merangkul potensi, membuka kesempatan.</p><p class="footer-description">Rangkul adalah ruang digital yang mempertemukan pencari kerja dan perusahaan dalam proses rekrutmen yang lebih terarah, mudah dipahami, dan inklusif. Kami percaya setiap orang berhak mendapatkan kesempatan untuk berkembang.</p></div><div class="footer-column"><b>Jelajahi</b><a href="#tentang">Tentang Rangkul</a><a href="#cara-kerja">Cara kerja</a><a href="#fitur">Fitur platform</a></div><div class="footer-column"><b>Bantuan</b><a href="#aksesibilitas">Aksesibilitas</a><a href="#faq">Pertanyaan umum</a><a href="admin/login.php">Administrator</a></div><div class="footer-column footer-purpose"><b>Komitmen Rangkul</b><p>Mendorong kesempatan kerja yang lebih terbuka dan setara, sejalan dengan Tujuan Pembangunan Berkelanjutan nomor 8.</p><span>SDG 8 · Decent Work and Economic Growth</span></div></div><div class="footer-bottom"><span>© <?= date('Y') ?> Rangkul. Semua hak dilindungi.</span><span>Dibangun untuk kesempatan yang lebih inklusif.</span></div></footer>
</body></html>
