<?php
require_once __DIR__ . '/_layout.php';
admin_post_only_if_needed();
function admin_post_only_if_needed(): void { if ($_SERVER['REQUEST_METHOD']==='POST') verify_csrf(); }
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)($_POST['id']??0); $action=$_POST['action']??'';
    if ($id === (int)current_user()['id'] && in_array($action,['toggle','delete'],true)) {
        flash('error','Admin tidak dapat menonaktifkan atau menghapus akunnya sendiri.');
    } elseif ($id>0 && $action==='toggle') {
        $q=$pdo->prepare("UPDATE users SET status=IF(status='active','inactive','active') WHERE id=?");
        $q->execute([$id]); flash('success','Status akun berhasil diperbarui.');
    } elseif ($id>0 && $action==='delete') {
        $q=$pdo->prepare('DELETE FROM users WHERE id=?'); $q->execute([$id]); flash('success','Pengguna berhasil dihapus beserta data turunannya.');
    }
    redirect('users.php');
}
$search=trim($_GET['q']??''); $role=$_GET['role']??''; $status=$_GET['status']??'';
$page=max(1,(int)($_GET['page']??1)); $perPage=10; $where=[]; $params=[];
if($search!==''){ $where[]='(u.name LIKE ? OR u.email LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; }
if(in_array($role,['admin','job_seeker','company'],true)){ $where[]='u.role=?'; $params[]=$role; }
if(in_array($status,['active','pending','inactive'],true)){ $where[]='u.status=?'; $params[]=$status; }
$clause=$where?' WHERE '.implode(' AND ',$where):'';
$count=$pdo->prepare("SELECT COUNT(*) FROM users u$clause"); $count->execute($params); $total=(int)$count->fetchColumn();
$pages=max(1,(int)ceil($total/$perPage)); $page=min($page,$pages); $offset=($page-1)*$perPage;
$sql="SELECT u.id,u.name,u.email,u.role,u.status,u.created_at,c.name,c.verification_status
 FROM users u LEFT JOIN companies c ON c.user_id=u.id $clause ORDER BY u.created_at DESC LIMIT $perPage OFFSET $offset";
$q=$pdo->prepare($sql); $q->execute($params); $rows=$q->fetchAll();
$userSummary = $pdo->query("SELECT COUNT(*) total, SUM(role='job_seeker') seekers, SUM(role='company') companies, SUM(status='pending') pending FROM users")->fetch();
admin_header('Manajemen Pengguna','users');
?>
<section class="admin-page-intro"><div><h2>Kelola akun yang menggunakan Rangkul.</h2><p>Kelola informasi akun, blokir akses sementara, atau hapus akun secara permanen.</p></div><a class="btn btn-primary" href="user-form.php">+ Tambah pengguna</a></section>
<div class="admin-page-stats">
 <div><span>Total akun</span><b><?= number_format((int)$userSummary['total']) ?></b></div>
 <div><span>Pencari kerja</span><b><?= number_format((int)$userSummary['seekers']) ?></b></div>
 <div><span>Perusahaan</span><b><?= number_format((int)$userSummary['companies']) ?></b></div>
 <div><span>Menunggu</span><b><?= number_format((int)$userSummary['pending']) ?></b></div>
</div>
<div class="admin-toolbar admin-toolbar-refined"><form method="get" class="admin-filter-form"><input name="q" value="<?= e($search) ?>" placeholder="Cari nama atau email"><select name="role"><option value="">Semua role</option><?php foreach(['admin'=>'Admin','job_seeker'=>'Job Seeker','company'=>'Perusahaan'] as $k=>$v): ?><option value="<?= e($k) ?>"<?= $role===$k?' selected':'' ?>><?= e($v) ?></option><?php endforeach; ?></select><select name="status"><option value="">Semua status</option><?php foreach(['active','pending','inactive'] as $v): ?><option value="<?= e($v) ?>"<?= $status===$v?' selected':'' ?>><?= e(['active'=>'Aktif','pending'=>'Menunggu','inactive'=>'Diblokir'][$v] ?? $v) ?></option><?php endforeach; ?></select><button class="btn btn-primary">Terapkan</button></form></div>
<section class="admin-panel admin-directory-panel"><div class="admin-panel-head"><h2>Daftar pengguna</h2><span><?= number_format($total) ?> akun</span></div>
<div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Pengguna</th><th>Role</th><th>Perusahaan</th><th>Status</th><th>Bergabung</th><th>Aksi</th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="6" class="admin-empty">Tidak ada pengguna yang cocok.</td></tr><?php endif; ?>
<?php foreach($rows as $u): ?><tr><td><b><?= e($u['name']) ?></b><small><?= e($u['email']) ?></small></td><td><?= e(['admin'=>'Admin','job_seeker'=>'Job Seeker','company'=>'Perusahaan'][$u['role']] ?? $u['role']) ?></td><td><?= e($u['name']??'—') ?><?php if($u['verification_status']): ?><small>Verifikasi: <?= e($u['verification_status']) ?></small><?php endif; ?></td><td><span class="admin-pill <?= e(['active'=>'Aktif','pending'=>'Menunggu','inactive'=>'Diblokir'][$u['status']] ?? $u['status']) ?>"><?= e(['active'=>'Aktif','pending'=>'Menunggu','inactive'=>'Diblokir'][$u['status']] ?? $u['status']) ?></span></td><td><?= e(date('d M Y',strtotime($u['created_at']))) ?></td><td><div class="admin-actions"><a class="admin-action-btn" href="user-form.php?id=<?= (int)$u['id'] ?>">Edit</a>
<?php if((int)$u['id']!==(int)current_user()['id']): ?><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$u['id'] ?>"><?php if($u['status']==='active'): ?><button name="action" value="toggle" class="admin-action-btn" data-confirm="Blokir akses akun <?= e($u['name']) ?>? Akun tidak dapat login sampai diaktifkan kembali." data-confirm-title="Blokir akun?" data-confirm-label="Blokir akses" data-confirm-tone="danger">Blokir akses</button><?php else: ?><button name="action" value="toggle" class="admin-action-btn" data-confirm="Aktifkan kembali akun <?= e($u['name']) ?>?" data-confirm-title="Aktifkan akun?" data-confirm-label="Aktifkan akun">Aktifkan</button><?php endif; ?><button name="action" value="delete" class="admin-action-btn danger" data-confirm="Hapus akun <?= e($u['name']) ?> secara permanen? Tindakan ini tidak dapat dibatalkan." data-confirm-title="Hapus akun permanen?" data-confirm-label="Ya, hapus akun" data-confirm-tone="danger">Hapus</button></form><?php endif; ?></div></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php if($pages>1): ?><div class="admin-pagination"><?php for($i=1;$i<=$pages;$i++): ?><a class="<?= $i===$page?'active':'' ?>" href="?<?= e(http_build_query(['q'=>$search,'role'=>$role,'status'=>$status,'page'=>$i])) ?>"><?= $i ?></a><?php endfor; ?></div><?php endif; ?>
</section>
<?php admin_footer(); ?>
