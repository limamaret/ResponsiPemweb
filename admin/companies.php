<?php
require_once __DIR__ . '/_layout.php';
require_once __DIR__ . '/../includes/company.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=(int)($_POST['id']??0);$action=$_POST['action']??'';
 if($id>0 && in_array($action,['verified','rejected','pending'],true)){
  $pdo->prepare('UPDATE companies SET verification_status=? WHERE id=?')->execute([$action,$id]);
  flash('success','Status verifikasi perusahaan diperbarui.');
 }elseif($id>0 && $action==='delete'){
  $pdo->beginTransaction();
  try {
   $owner=$pdo->prepare('SELECT user_id FROM companies WHERE id=?');$owner->execute([$id]);$ownerId=(int)$owner->fetchColumn();
   if($ownerId>0){$pdo->prepare("DELETE FROM users WHERE id=? AND role='company'")->execute([$ownerId]);}
   else{$pdo->prepare('DELETE FROM companies WHERE id=?')->execute([$id]);}
   $pdo->commit();flash('success','Perusahaan dan akun company terkait berhasil dihapus beserta data turunannya.');
  } catch(Throwable $e) {
   if($pdo->inTransaction())$pdo->rollBack();flash('error','Perusahaan gagal dihapus. Silakan periksa relasi datanya.');
  }
 }
 redirect('companies.php');
}
$status=$_GET['status']??'';$qtext=trim($_GET['q']??'');$where=[];$params=[];
if(in_array($status,['pending','verified','rejected'],true)){$where[]='c.verification_status=?';$params[]=$status;}
if($qtext!==''){$where[]='(c.name LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';array_push($params,"%$qtext%","%$qtext%","%$qtext%");}
$clause=$where?' WHERE '.implode(' AND ',$where):'';
$q=$pdo->prepare("SELECT c.*,u.name AS owner_name,u.email,u.status AS owner_status FROM companies c JOIN users u ON u.id=c.user_id $clause ORDER BY FIELD(c.verification_status,'pending','rejected','verified'),c.created_at DESC");$q->execute($params);$rows=$q->fetchAll();
$companySummary = $pdo->query("SELECT COUNT(*) total, SUM(verification_status='pending') pending, SUM(verification_status='verified') verified, SUM(verification_status='rejected') rejected FROM companies")->fetch();
admin_header('Verifikasi Perusahaan','companies');
?>
<section class="admin-page-intro"><div><h2>Pastikan perusahaan siap membuka kesempatan kerja inklusif.</h2><p>Tinjau identitas, informasi aksesibilitas, dan komitmen inklusivitas sebelum mengubah status verifikasi.</p></div></section>
<div class="admin-page-stats company-page-stats">
 <div><span>Semua perusahaan</span><b><?= number_format((int)$companySummary['total']) ?></b></div>
 <div class="is-pending"><span>Menunggu pemeriksaan</span><b><?= number_format((int)$companySummary['pending']) ?></b></div>
 <div><span>Terverifikasi</span><b><?= number_format((int)$companySummary['verified']) ?></b></div>
 <div><span>Ditolak</span><b><?= number_format((int)$companySummary['rejected']) ?></b></div>
</div>
<div class="admin-toolbar admin-toolbar-refined"><form method="get" class="admin-filter-form"><input name="q" value="<?= e($qtext) ?>" placeholder="Cari perusahaan atau pemilik"><select name="status"><option value="">Semua status</option><?php foreach(['pending','verified','rejected'] as $v): ?><option value="<?= e($v) ?>"<?= $status===$v?' selected':'' ?>><?= e(ucfirst($v)) ?></option><?php endforeach; ?></select><button class="btn btn-primary">Filter</button></form></div>
<section class="admin-panel admin-directory-panel"><div class="admin-panel-head"><h2>Daftar perusahaan</h2><span><?= count($rows) ?> perusahaan</span></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Perusahaan</th><th>Pemilik akun</th><th>Informasi inklusif</th><th>Verifikasi</th><th>Aksi</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="5" class="admin-empty">Tidak ada data perusahaan.</td></tr><?php endif; ?>
<?php foreach($rows as $c):
    $fac = json_decode($c['accessibility_facilities'] ?? '[]', true) ?: [];
    $facLabels = $fac ? implode(', ', array_map(fn($k) => access_options()[$k] ?? $k, $fac)) : 'Belum ada fasilitas dicantumkan';
?>
<tr><td><b><?= e($c['name']) ?></b><small><?= e($c['address']??'Alamat belum diisi') ?></small></td><td><?= e($c['owner_name']) ?><small><?= e($c['email']) ?></small><small>Akun: <?= e($c['owner_status']) ?></small></td><td><?= !empty($c['is_inclusive'])?'<span class="admin-pill verified">Komitmen inklusif</span>':'—' ?><small><?= e($facLabels) ?></small></td><td><span class="admin-pill <?= e($c['verification_status']) ?>"><?= e(['pending'=>'Menunggu','verified'=>'Terverifikasi','rejected'=>'Ditolak'][$c['verification_status']] ?? $c['verification_status']) ?></span></td><td><form method="post" class="admin-inline-actions"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button name="action" value="verified" data-confirm="Verifikasi perusahaan ini dan setujui profilnya?" data-confirm-title="Verifikasi perusahaan?" data-confirm-label="Ya, verifikasi">Verifikasi</button><button name="action" value="rejected" data-confirm="Tolak verifikasi perusahaan ini?" data-confirm-title="Tolak perusahaan?" data-confirm-label="Ya, tolak" data-confirm-tone="danger">Tolak</button><button name="action" value="pending" data-confirm="Kembalikan status perusahaan menjadi menunggu peninjauan?" data-confirm-title="Ubah ke status pending?" data-confirm-label="Ya, ubah status">Pending</button><button name="action" value="delete" class="danger" data-confirm="Hapus data perusahaan ini secara permanen?" data-confirm-title="Hapus perusahaan?" data-confirm-label="Ya, hapus perusahaan" data-confirm-tone="danger">Hapus</button></form></td></tr><?php endforeach; ?>
</tbody></table></div></section>
<?php admin_footer(); ?>
