<?php
require_once __DIR__ . '/_layout.php';
$stats = [
 'users'=>(int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn(),
 'seekers'=>(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='job_seeker'")->fetchColumn(),
 'companies'=>(int)$pdo->query("SELECT COUNT(*) FROM users WHERE role='company'")->fetchColumn(),
 'company_profiles'=>(int)$pdo->query("SELECT COUNT(*) FROM companies")->fetchColumn(),
 'pending'=>(int)$pdo->query("SELECT COUNT(*) FROM companies WHERE verification_status='pending'")->fetchColumn(),
 'verified'=>(int)$pdo->query("SELECT COUNT(*) FROM companies WHERE verification_status='verified'")->fetchColumn(),
 'vacancies'=>(int)$pdo->query("SELECT COUNT(*) FROM vacancies")->fetchColumn(),
 'applications'=>(int)$pdo->query("SELECT COUNT(*) FROM applications")->fetchColumn()
];
$companies=$pdo->query("SELECT c.id,c.name,c.verification_status,c.created_at,u.name AS owner_name,u.email FROM companies c JOIN users u ON u.id=c.user_id ORDER BY CASE c.verification_status WHEN 'pending' THEN 0 WHEN 'rejected' THEN 1 ELSE 2 END,c.created_at DESC LIMIT 4")->fetchAll();
$users=$pdo->query("SELECT id,name,email,role,status,created_at FROM users ORDER BY created_at DESC LIMIT 4")->fetchAll();
admin_header('Dashboard','dashboard');
?>
<section class="dashboard-page" aria-label="Dashboard administrasi Rangkul">
  <header class="dashboard-heading">
    <div>
      <h1>Ikhtisar platform</h1>
      <p>Berikut kondisi platform dan hal yang perlu ditindaklanjuti hari ini.</p>
    </div>
    <a class="dashboard-primary-link" href="companies.php?status=pending">Periksa perusahaan</a>
  </header>

  <section class="dashboard-review" aria-label="Antrean verifikasi perusahaan">
    <div class="dashboard-review-main">
      <span class="dashboard-review-kicker">ANTREAN VERIFIKASI</span>
      <div class="dashboard-review-count"><?= number_format($stats['pending']) ?></div>
      <p>perusahaan menunggu pemeriksaan</p>
      <a class="repo-button-link" href="companies.php?status=pending">Buka antrean <span aria-hidden="true">↗</span></a>
    </div>
    <div class="dashboard-review-note">
      <div class="dashboard-review-mark" aria-hidden="true">✓</div>
      <div>
        <h2>Pastikan kesempatan kerja yang inklusif dimulai dari perusahaan yang tepat.</h2>
        <p>Tinjau informasi perusahaan dan komitmen inklusivitas sebelum memberikan verifikasi.</p>
      </div>
    </div>
  </section>

  <div class="dashboard-facts" aria-label="Ringkasan platform">
    <div><span>Akun pengguna</span><strong><?= number_format($stats['users']) ?></strong><small><?= number_format($stats['seekers']) ?> pencari kerja · <?= number_format($stats['companies']) ?> perusahaan</small></div>
    <div><span>Profil perusahaan</span><strong><?= number_format($stats['company_profiles']) ?></strong><small><?= number_format($stats['verified']) ?> sudah terverifikasi</small></div>
    <div><span>Lowongan tersedia</span><strong><?= number_format($stats['vacancies']) ?></strong><small><?= number_format($stats['applications']) ?> lamaran tercatat</small></div>
  </div>

  <div class="repo-workspace" aria-label="Ruang kerja data administrasi">
    <div class="repo-workspace-heading">
      <div class="repo-workspace-title"><h2>Aktivitas terbaru</h2><span>Data terbaru yang masuk ke Rangkul</span></div>
      <div class="repo-workspace-meta"><span class="repo-live-dot"></span> Data langsung</div>
    </div>

  <div class="repo-table-panel active" data-repo-panel="companies">
    <div class="repo-panel-toolbar">
      <div class="repo-subtabs"><span class="repo-subtab-active">Perusahaan</span></div>
      <a class="repo-button-link" href="companies.php">Buka pengelolaan <b>↗</b></a>
    </div>
    <div class="repo-panel-title"><div><h2>Perusahaan terbaru</h2><p>Periksa perusahaan yang baru bergabung dan status verifikasinya.</p></div><span class="repo-context"><?= number_format($stats['companies']) ?> perusahaan</span></div>
    <div class="repo-table-scroll"><table class="repo-table">
      <thead><tr><th class="repo-num-col">No.</th><th>Perusahaan</th><th>Pemilik akun</th><th>Terdaftar</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(!$companies): ?><tr><td colspan="6" class="repo-empty">Belum ada perusahaan terdaftar.</td></tr><?php endif; ?>
      <?php foreach($companies as $i=>$c): $status=$c['verification_status']; ?>
      <tr>
        <td class="repo-number"><?= $i+1 ?></td>
        <td><strong><?= e($c['name']) ?></strong><small><?= e($c['email']) ?></small></td>
        <td><?= e($c['owner_name']) ?></td>
        <td><?= e(date('d M Y · H:i',strtotime($c['created_at']))) ?></td>
        <td><span class="repo-status <?= e($status) ?>"><i></i><?= e(['pending'=>'Menunggu','verified'=>'Terverifikasi','rejected'=>'Ditolak'][$status]??$status) ?></span></td>
        <td><a class="repo-action" href="companies.php?q=<?= rawurlencode($c['name']) ?>">Tinjau <span>↗</span></a></td>
      </tr><?php endforeach; ?>
      </tbody>
    </table></div>
    <div class="repo-table-foot"><span>Menampilkan <?= count($companies) ?> dari <?= number_format($stats['companies']) ?> perusahaan</span><a class="repo-button-link" href="companies.php">Lihat direktori perusahaan <b>→</b></a></div>
  </div>

  <div class="repo-table-panel" data-repo-panel="users" hidden>
    <div class="repo-panel-toolbar">
      <div class="repo-subtabs"><span class="repo-subtab-active">Pengguna</span></div>
      <a class="repo-button-link" href="users.php">Buka pengelolaan <b>↗</b></a>
    </div>
    <div class="repo-panel-title"><div><h2>Pengguna terbaru</h2><p>Daftar akun yang baru bergabung di platform Rangkul.</p></div><span class="repo-context"><?= number_format($stats['users']) ?> akun</span></div>
    <div class="repo-table-scroll"><table class="repo-table">
      <thead><tr><th class="repo-num-col">No.</th><th>Nama pengguna</th><th>Email</th><th>Peran</th><th>Bergabung</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if(!$users): ?><tr><td colspan="7" class="repo-empty">Belum ada pengguna.</td></tr><?php endif; ?>
      <?php foreach($users as $i=>$u): ?>
      <tr>
        <td class="repo-number"><?= $i+1 ?></td><td><strong><?= e($u['name']) ?></strong></td><td><?= e($u['email']) ?></td>
        <td><?= e(['admin'=>'Admin','job_seeker'=>'Job Seeker','company'=>'Perusahaan'][$u['role']]??$u['role']) ?></td>
        <td><?= e(date('d M Y',strtotime($u['created_at']))) ?></td>
        <td><span class="repo-status <?= e($u['status']) ?>"><i></i><?= e(['active'=>'Aktif','pending'=>'Menunggu','inactive'=>'Nonaktif'][$u['status']]??$u['status']) ?></span></td>
        <td><a class="repo-action" href="users.php?q=<?= rawurlencode($u['name']) ?>">Lihat <span>↗</span></a></td>
      </tr><?php endforeach; ?>
      </tbody>
    </table></div>
    <div class="repo-table-foot"><span>Menampilkan <?= count($users) ?> dari <?= number_format($stats['users']) ?> akun</span><a class="repo-button-link" href="users.php">Lihat semua pengguna <b>→</b></a></div>
  </div>

  <div class="repo-table-panel" data-repo-panel="overview" hidden>
    <div class="repo-panel-toolbar"><div class="repo-subtabs"><span class="repo-subtab-active">Ringkasan platform</span><span>Data operasional</span></div></div>
    <div class="repo-panel-title"><div><h2>Ringkasan aktivitas</h2><p>Seluruh angka diambil langsung dari data Rangkul.</p></div><span class="repo-context">DATA LANGSUNG</span></div>
    <div class="repo-overview-grid">
      <div><span>AKUN TERDAFTAR</span><strong><?= number_format($stats['users']) ?></strong><small><?= number_format($stats['seekers']) ?> pencari kerja · <?= number_format($stats['companies']) ?> perusahaan</small></div>
      <div><span>PROFIL PERUSAHAAN</span><strong><?= number_format($stats['company_profiles']) ?></strong><small><?= number_format($stats['verified']) ?> terverifikasi</small></div>
      <div><span>MENUNGGU VERIFIKASI</span><strong><?= number_format($stats['pending']) ?></strong><small>Perlu pemeriksaan admin</small></div>
      <div><span>LOWONGAN</span><strong><?= number_format($stats['vacancies']) ?></strong><small>Lowongan tersimpan</small></div>
      <div><span>LAMARAN</span><strong><?= number_format($stats['applications']) ?></strong><small>Lamaran tercatat</small></div>
    </div>
  </div>

  <div class="repo-lower-workspace">
    <section class="repo-create-panel">
      <div class="repo-lower-heading"><h3>Tambahkan ke Rangkul</h3><p>Akses cepat untuk mengelola data platform.</p></div>
      <div class="repo-create-actions">
        <a href="users.php" class="repo-quick-button"><span class="repo-create-icon blue">◎</span><span><b>Kelola pengguna</b><small>Periksa dan atur akun</small></span><i>↗</i></a>
        <a href="categories.php" class="repo-quick-button"><span class="repo-create-icon pale">▤</span><span><b>Kelola kategori</b><small>Atur kategori pekerjaan</small></span><i>↗</i></a>
      </div>
    </section>
    <section class="repo-priority-panel">
      <div class="repo-lower-heading"><h3>Perlu ditangani</h3><p>Prioritas operasional administrator.</p></div>
      <a class="repo-priority-link" href="companies.php?status=pending" role="button">
        <span class="repo-priority-icon">✓</span>
        <span><b>Verifikasi perusahaan</b><small><?= number_format($stats['pending']) ?> perusahaan menunggu pemeriksaan</small></span>
        <strong><?= number_format($stats['pending']) ?></strong><i>→</i>
      </a>
      <a class="repo-priority-link" href="users.php?status=pending" role="button">
        <span class="repo-priority-icon neutral">◷</span>
        <span><b>Akun berstatus menunggu</b><small>Periksa status akun pengguna</small></span>
        <i>→</i>
      </a>
    </section>
  </div>
  </div>
</section>
<?php admin_footer(); ?>
