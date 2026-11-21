<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';
$user = current_user();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/style.css">
<script src="../assets/js/landing-interactions.js" defer></script>
<script src="../assets/js/custom-cursor.js" defer></script>
</head>
<body class="landing-body">
<a class="skip-link" href="#main-content">Lewati ke konten utama</a>
<header class="landing-nav"><div class="landing-nav-inner">
<a class="landing-brand" href="../index.php" aria-label="Rangkul, halaman utama"><img src="../assets/img/rangkul-logo.png" alt="Rangkul"></a>
<nav class="landing-links" aria-label="Navigasi utama"><a href="../index.php#tentang">Tentang</a><a href="../index.php#keunggulan">Keunggulan</a><a href="../index.php#cara-kerja">Cara kerja</a><a href="../index.php#fitur">Fitur</a><a href="../index.php#aksesibilitas">Aksesibilitas</a><a href="../index.php#faq">FAQ</a></nav>
<div class="landing-nav-actions">
<?php if ($user): ?>
    <span style="font-size:13px;font-weight:600;color:#536078;"><?= e($user['name']) ?></span>
    <a class="nav-login" href="<?= dashboard_for_role($user['role']) ?>">Dashboard</a>
    <a class="btn btn-outline" href="../logout.php" style="padding:10px 16px;font-size:12px;">Logout</a>
<?php else: ?>
    <a class="nav-login" href="../login-job-seeker.php">Masuk</a>
    <a class="btn btn-primary" href="../register-job-seeker.php" style="padding:10px 16px;font-size:12px;">Daftar</a>
<?php endif; ?>
<button class="mobile-menu" type="button" aria-label="Buka navigasi" aria-expanded="false">☰</button>
</div></div></header>
<main id="main-content">