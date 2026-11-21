<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('recruiter');
$user = current_user();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Dashboard — Rangkul</title><link rel="stylesheet" href="../assets/css/style.css"></head><body>
<header class="topbar"><a class="brand" href="../index.php"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a><div class="user-nav"><span><?= e($user['name']) ?> · <?= e(ucwords(str_replace('_',' ',$user['role']))) ?></span><a class="btn btn-outline" href="../logout.php">Logout</a></div></header>
<main class="dashboard-wrap"><span class="eyebrow">Rangkul / WORKSPACE</span><h1>Halo, <?= e($user['name']) ?> 👋</h1><p class="muted">Selamat datang di dashboard <?= e(ucwords(str_replace('_',' ',$user['role']))) ?>.</p>
<div class="dashboard-card"><div class="role-icon green">✦</div><h2>Autentikasi berhasil</h2><p>Anda berhasil masuk dengan role <b><?= e($user['role']) ?></b>. Halaman ini adalah placeholder awal untuk pengembangan modul berikutnya.</p>
<a class="btn btn-primary" href="../index.php">Kembali ke beranda</a></div></main><script src="../assets/js/custom-cursor.js" defer></script></body></html>
