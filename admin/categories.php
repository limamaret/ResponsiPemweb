<?php
require_once __DIR__ . '/_layout.php';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';$id=(int)($_POST['id']??0);$name=trim($_POST['name']??'');
 try{
  if($action==='save' && $name!==''){
   if(mb_strlen($name)>100)throw new RuntimeException('Nama kategori maksimal 100 karakter.');
   if($id>0){$pdo->prepare('UPDATE categories SET name=? WHERE id=?')->execute([$name,$id]);flash('success','Kategori diperbarui.');}
   else{$pdo->prepare('INSERT INTO categories(name) VALUES(?)')->execute([$name]);flash('success','Kategori ditambahkan.');}
  }elseif($action==='delete' && $id>0){
   $pdo->prepare('DELETE FROM categories WHERE id=?')->execute([$id]);flash('success','Kategori dihapus.');
  }
 }catch(PDOException $e){flash('error',$e->getCode()==='23000'?'Nama kategori sudah digunakan atau kategori masih dipakai lowongan.':'Kategori tidak dapat diproses.');}
 redirect('categories.php');
}
$editId=(int)($_GET['edit']??0);$edit=null;if($editId){$s=$pdo->prepare('SELECT * FROM categories WHERE id=?');$s->execute([$editId]);$edit=$s->fetch();}
$rows=$pdo->query("SELECT c.id,c.name,c.created_at,COUNT(v.id) AS vacancy_count FROM categories c LEFT JOIN vacancies v ON v.category_id=c.id GROUP BY c.id,c.name,c.created_at ORDER BY c.name")->fetchAll();
$categoryVacancies = array_sum(array_map(fn($r)=>(int)$r['vacancy_count'],$rows));
admin_header('Manajemen Kategori','categories');
?>
<section class="admin-page-intro"><div><h2>Susun bidang pekerjaan agar lowongan mudah ditemukan.</h2><p>Kategori membantu pencari kerja menjelajahi peluang yang sesuai dengan minat dan keahliannya.</p></div></section>
<div class="admin-page-stats category-page-stats"><div><span>Total kategori</span><b><?= number_format(count($rows)) ?></b></div><div><span>Lowongan dalam kategori</span><b><?= number_format($categoryVacancies) ?></b></div><div><span>Kategori tanpa lowongan</span><b><?= number_format(count(array_filter($rows,fn($r)=>(int)$r['vacancy_count']===0))) ?></b></div></div>
<div class="admin-two-col category-layout"><section class="admin-panel category-editor"><div class="admin-panel-head"><h2><?= $edit?'Edit kategori':'Tambah kategori' ?></h2></div>
<form method="post" class="admin-form"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)($edit['id']??0) ?>"><input type="hidden" name="action" value="save"><label>Nama kategori<input name="name" maxlength="100" value="<?= e($edit['name']??'') ?>" placeholder="Contoh: Teknologi" required></label><div class="admin-form-actions"><button class="btn btn-primary"><?= $edit?'Simpan perubahan':'Tambah kategori' ?></button><?php if($edit): ?><a class="btn btn-outline" href="categories.php">Batal</a><?php endif; ?></div></form></section>
<section class="admin-panel admin-directory-panel category-directory"><div class="admin-panel-head"><h2>Daftar kategori</h2><span><?= count($rows) ?> kategori</span></div><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Nama</th><th>Lowongan</th><th>Aksi</th></tr></thead><tbody>
<?php foreach($rows as $c): ?><tr><td><b><?= e($c['name']) ?></b></td><td><?= (int)$c['vacancy_count'] ?></td><td><div class="admin-actions"><a class="admin-action-btn" href="?edit=<?= (int)$c['id'] ?>">Edit</a><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$c['id'] ?>"><button class="admin-action-btn danger" name="action" value="delete" data-confirm="Hapus kategori pekerjaan ini?" data-confirm-title="Hapus kategori?" data-confirm-label="Ya, hapus kategori" data-confirm-tone="danger">Hapus</button></form></div></td></tr><?php endforeach; ?>
<?php if(!$rows): ?><tr><td colspan="3" class="admin-empty">Belum ada kategori.</td></tr><?php endif; ?></tbody></table></div></section></div>
<?php admin_footer(); ?>
