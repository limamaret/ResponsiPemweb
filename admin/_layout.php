<?php
require_once __DIR__ . '/../includes/auth.php';
require_role('admin');

function admin_header(string $title, string $active): void {
    global $pdo;
    $user = current_user();
    $items = [
        'dashboard' => ['dashboard.php', 'Dashboard', 'grid'],
        'users' => ['users.php', 'Pengguna', 'users'],
        'companies' => ['companies.php', 'Perusahaan', 'building'],
        'categories' => ['categories.php', 'Kategori', 'folder'],
        'preferences' => ['preferences.php', 'Preferensi', 'sliders'],
    ];
    $pendingCompanies = (int)$pdo->query("SELECT COUNT(*) FROM companies WHERE verification_status='pending'")->fetchColumn();
    $searchTarget = $active === 'companies' ? 'companies.php' : 'users.php';
    $searchPlaceholder = $active === 'companies' ? 'Cari perusahaan...' : 'Cari pengguna...';
    $icons = [
      'grid'=>'<rect x="3" y="3" width="7" height="7" rx="1.3"/><rect x="14" y="3" width="7" height="7" rx="1.3"/><rect x="3" y="14" width="7" height="7" rx="1.3"/><rect x="14" y="14" width="7" height="7" rx="1.3"/>',
      'users'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',
      'building'=>'<rect x="4" y="3" width="16" height="18" rx="2"/><path d="M9 21v-4h6v4M8 7h2m4 0h2M8 11h2m4 0h2"/>',
      'folder'=>'<path d="M3 7a2 2 0 0 1 2-2h5l2 2h7a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>',
      'sliders'=>'<path d="M4 21v-7m0-4V3m8 18v-9m0-4V3m8 18v-5m0-4V3M2 14h4m4-6h4m4 6h4"/>'
    ];
    ?>
<!doctype html><html lang="id"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Rangkul Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="admin.css">
<script src="admin.js" defer></script>
<script src="../assets/js/custom-cursor.js" defer></script>
</head><body class="admin-body page-<?= e($active) ?>">
<a class="admin-skip-link" href="#admin-content">Lewati ke konten utama</a>
<aside class="admin-sidebar" id="admin-sidebar" aria-label="Navigasi utama">
  <a class="admin-rail-brand" href="dashboard.php" aria-label="Rangkul"><img src="../assets/img/rangkul-logo.png" alt="Rangkul"></a>
  <nav aria-label="Navigasi admin">
    <?php foreach ($items as $key => [$href,$label,$icon]): ?>
      <a href="<?= e($href) ?>" class="admin-nav-link<?= $active === $key ? ' active' : '' ?>" title="<?= e($label) ?>"<?= $active === $key ? ' aria-current="page"' : '' ?>>
        <svg class="admin-nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><?= $icons[$icon] ?></svg>
        <span class="admin-rail-tooltip"><?= e($label) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="admin-sidebar-bottom">
    <button class="admin-rail-profile" type="button" aria-label="Buka menu profil" aria-expanded="false" aria-controls="admin-profile-menu">
      <?php if (!empty($user['photo'])): ?><img src="../uploads/photos/<?= e(rawurlencode(basename($user['photo']))) ?>" alt=""><?php else: ?><span><?= e(mb_strtoupper(mb_substr($user['name'] ?? 'A',0,1))) ?></span><?php endif; ?>
    </button>
    <div class="admin-profile-menu" id="admin-profile-menu" hidden>
      <div class="admin-profile-menu-head"><b><?= e($user['name'] ?? 'Administrator') ?></b><small><?= e($user['email'] ?? '') ?></small></div>
      <a class="profile-menu-action" href="account.php"><span>♙</span> Profil & keamanan akun</a>
      <form method="post" action="../logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button type="submit"><span>↪</span> Keluar dari Rangkul</button></form>
    </div>
  </div>
</aside>
<div class="admin-sidebar-backdrop" aria-hidden="true"></div>
<div class="admin-app">
<header class="admin-topbar">
  <div class="admin-top-left">
    <button class="admin-menu-toggle" type="button" aria-label="Buka navigasi" aria-expanded="false" aria-controls="admin-sidebar">☰</button>
    <?php if ($active === 'dashboard'): ?>
      <div class="repo-tabs repo-tabs-global" role="tablist" aria-label="Konten dashboard">
        <button class="repo-tab active" type="button" data-repo-tab="companies" role="tab" aria-selected="true">Perusahaan</button>
        <button class="repo-tab" type="button" data-repo-tab="users" role="tab" aria-selected="false">Pengguna</button>
        <button class="repo-tab" type="button" data-repo-tab="overview" role="tab" aria-selected="false">Ringkasan platform</button>
      </div>
    <?php else: ?>
      <div class="admin-top-context"><span>RANGKUL</span><i>/</i><b><?= e($title) ?></b></div>
    <?php endif; ?>
  </div>
  <div class="admin-top-actions">
    <form class="admin-global-search" method="get" action="<?= e($searchTarget) ?>">
      <input type="search" name="q" placeholder="<?= e($searchPlaceholder) ?>" aria-label="<?= e($searchPlaceholder) ?>">
      <button type="submit" aria-label="Cari"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.4"/><path d="m16 16 4.2 4.2"/></svg></button>
    </form>
    <a class="admin-notification" href="companies.php?status=pending" aria-label="<?= $pendingCompanies ?> perusahaan menunggu verifikasi" title="Antrean verifikasi">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4"/></svg>
      <?php if($pendingCompanies>0): ?><i><?= $pendingCompanies>9?'9+':$pendingCompanies ?></i><?php endif; ?>
    </a>
  </div>
</header>
<main id="admin-content" class="admin-main">
  <?php if ($m = flash('success')): ?><div class="notice success admin-toast" role="status"><?= e($m) ?></div><?php endif; ?>
  <?php if ($m = flash('error')): ?><div class="notice error admin-toast" role="alert"><?= e($m) ?></div><?php endif; ?>
  <div class="admin-page-heading"><div><h1><?= e($title) ?></h1><p>Rangkul · Ruang administrasi platform inklusif</p></div></div>
<?php
}
function admin_footer(): void {
    ?></main>
<footer class="admin-footer">
  <div class="admin-footer-grid admin-footer-editorial">
    <div class="admin-footer-brand"><img src="../assets/img/rangkul-logo.png" alt="Rangkul"><div><strong>Rangkul</strong><p>Merangkul potensi,<br>membuka kesempatan.</p><small>Platform kesempatan kerja inklusif</small></div></div>
    <div class="admin-footer-social"><span class="admin-footer-kicker">TEMUKAN RANGKUL</span><h2>Tetap terhubung.</h2><p>Ikuti kabar, cerita, dan kesempatan terbaru dari Rangkul.</p><div class="admin-social-list"><span><b>ig</b><em>Instagram</em><strong>@rangkul.id</strong></span><span><b>in</b><em>LinkedIn</em><strong>Rangkul Indonesia</strong></span><span><b>x</b><em>X / Twitter</em><strong>@rangkul_id</strong></span></div><small class="admin-social-note">Identitas media sosial contoh untuk kebutuhan tampilan.</small></div>
    <div class="admin-footer-commitment"><span class="admin-footer-kicker">KOMITMEN KAMI</span><strong>SDG 08</strong><b>Decent Work and Economic Growth</b><p>Mendorong kesempatan kerja yang layak, setara, dan inklusif bagi setiap potensi.</p><div class="admin-footer-tags"><span>Kesetaraan</span><span>Aksesibilitas</span><span>Inklusi</span></div></div>
  </div>
  <div class="admin-footer-meta"><span>© <?= date('Y') ?> Rangkul Indonesia</span><span>Dibangun untuk kesempatan yang lebih setara.</span><span>Ruang administrator · Internal</span></div>
</footer></div></body></html><?php
}
function admin_post_only(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Metode tidak diizinkan.'); }
    verify_csrf();
}
