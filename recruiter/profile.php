<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];
$errors = [];
$f = [
    'company_name' => $company['company_name'],
    'description'  => $company['description'] ?? '',
    'address'      => $company['address'] ?? '',
    'website'      => $company['website'] ?? '',
    'phone'        => $company['phone'] ?? '',
    'is_inclusive' => (int)$company['is_inclusive'],
    'facilities'   => csv_keys($company['accessibility_facilities'] ?? ''),
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $f['company_name'] = trim($_POST['company_name'] ?? '');
    $f['description']  = trim($_POST['description'] ?? '');
    $f['address']      = trim($_POST['address'] ?? '');
    $f['website']      = trim($_POST['website'] ?? '');
    $f['phone']        = trim($_POST['phone'] ?? '');
    $f['is_inclusive'] = isset($_POST['is_inclusive']) ? 1 : 0;
    $f['facilities']   = csv_keys(keys_to_csv($_POST['facilities'] ?? []));

    if ($f['company_name'] === '' || mb_strlen($f['company_name']) > 180) $errors['company_name'] = 'Nama perusahaan wajib diisi (maksimal 180 karakter).';
    if ($f['description'] === '') $errors['description'] = 'Deskripsi perusahaan wajib diisi.';
    if ($f['address'] === '' || mb_strlen($f['address']) > 255) $errors['address'] = 'Alamat wajib diisi (maksimal 255 karakter).';
    if ($f['website'] !== '' && (!filter_var($f['website'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $f['website']) || mb_strlen($f['website']) > 255)) {
        $errors['website'] = 'Website harus berupa URL lengkap, misalnya https://perusahaan.co.id.';
    }
    if ($f['phone'] !== '' && !preg_match('/^[0-9+\-\s()]{6,40}$/', $f['phone'])) $errors['phone'] = 'Nomor telepon tidak valid.';

    $newLogo = null;
    if (!$errors) {
        try { $newLogo = save_upload($_FILES['logo'] ?? ['error' => UPLOAD_ERR_NO_FILE], 'logos', image_types()); }
        catch (RuntimeException $ex) { $errors['logo'] = $ex->getMessage() . ' Logo harus berformat JPG, PNG, atau WebP, maksimal 2 MB.'; }
    }
    if (!$errors) {
        $logo = $newLogo ?? $company['logo'];
        $pdo->prepare(
            'UPDATE companies SET company_name=?, description=?, address=?, website=?, phone=?, logo=?, is_inclusive=?, accessibility_facilities=? WHERE id=? AND user_id=?'
        )->execute([$f['company_name'], $f['description'], $f['address'], $f['website'] ?: null, $f['phone'] ?: null, $logo, $f['is_inclusive'], implode(',', $f['facilities']), $cid, (int)current_user()['id']]);
        if ($newLogo !== null) delete_upload($company['logo'], 'logos');
        flash('success', 'Profil perusahaan berhasil disimpan.');
        redirect('profile.php');
    }
}

company_header('Profil Perusahaan', 'profile', $company);
?>
<div class="page-head"><div><span class="eyebrow">PROFIL PERUSAHAAN</span><h1>Profil &amp; komitmen inklusif</h1>
<p>Informasi ini tampil di halaman publik perusahaan dan pada setiap lowongan Anda.</p></div>
<a class="btn btn-outline" href="../company.php?id=<?= $cid ?>" target="_blank" rel="noopener">Lihat halaman publik ↗</a></div>

<section class="card" aria-labelledby="h-ver" style="display:flex;gap:18px;align-items:center;flex-wrap:wrap">
<?= company_logo_html($company['logo'], $company['company_name']) ?>
<div><h2 id="h-ver" style="margin-bottom:8px">Status verifikasi</h2><?= verification_badge($company['verification_status']) ?>
<p class="hint" style="margin:8px 0 0;font-size:13px;color:#475569">Badge perusahaan terverifikasi dan label inklusif tampil penuh di halaman publik hanya setelah Admin memverifikasi perusahaan Anda.</p></div></section>

<?php if ($errors): ?><div class="notice error" role="alert">Periksa kembali isian berikut: <?= e(implode(' ', $errors)) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="co-form card" novalidate>
<?= csrf_field() ?>
<h2>Data perusahaan</h2>
<div class="form-grid">
<div class="field"><label for="company_name">Nama perusahaan <span class="req" aria-hidden="true">*</span></label><input id="company_name" name="company_name" value="<?= e($f['company_name']) ?>" required maxlength="180"<?= isset($errors['company_name']) ? ' aria-invalid="true"' : '' ?>><?php if (isset($errors['company_name'])): ?><span class="field-error"><?= e($errors['company_name']) ?></span><?php endif; ?></div>
<div class="field"><label for="phone">Nomor telepon</label><input id="phone" name="phone" type="tel" value="<?= e($f['phone']) ?>" maxlength="40"><?php if (isset($errors['phone'])): ?><span class="field-error"><?= e($errors['phone']) ?></span><?php endif; ?></div>
</div>
<div class="field"><label for="description">Deskripsi perusahaan <span class="req" aria-hidden="true">*</span></label><textarea id="description" name="description" required><?= e($f['description']) ?></textarea><?php if (isset($errors['description'])): ?><span class="field-error"><?= e($errors['description']) ?></span><?php endif; ?></div>
<div class="form-grid">
<div class="field"><label for="address">Alamat <span class="req" aria-hidden="true">*</span></label><input id="address" name="address" value="<?= e($f['address']) ?>" required maxlength="255"><?php if (isset($errors['address'])): ?><span class="field-error"><?= e($errors['address']) ?></span><?php endif; ?></div>
<div class="field"><label for="website">Website</label><input id="website" name="website" type="url" value="<?= e($f['website']) ?>" placeholder="https://perusahaan.co.id" maxlength="255"><?php if (isset($errors['website'])): ?><span class="field-error"><?= e($errors['website']) ?></span><?php endif; ?></div>
</div>
<div class="field"><label for="logo">Logo perusahaan</label><input id="logo" name="logo" type="file" accept="image/jpeg,image/png,image/webp" aria-describedby="logo-hint"><span class="hint" id="logo-hint">JPG, PNG, atau WebP, maksimal 2 MB. Kosongkan bila tidak ingin mengganti logo.</span><?php if (isset($errors['logo'])): ?><span class="field-error"><?= e($errors['logo']) ?></span><?php endif; ?></div>

<h2 style="margin-top:8px">Komitmen inklusif</h2>
<label class="check-item"><input type="checkbox" name="is_inclusive" value="1"<?= $f['is_inclusive'] ? ' checked' : '' ?>> Perusahaan kami berkomitmen membuka kesempatan kerja yang setara, termasuk bagi penyandang disabilitas.</label>
<fieldset class="field-group"><legend>Fasilitas aksesibilitas yang tersedia</legend>
<div class="check-grid">
<?php foreach (access_options() as $key => $label): ?>
<label class="check-item"><input type="checkbox" name="facilities[]" value="<?= e($key) ?>"<?= in_array($key, $f['facilities'], true) ? ' checked' : '' ?>> <?= e($label) ?></label>
<?php endforeach; ?></div></fieldset>
<div class="actions"><button class="btn btn-primary" type="submit">Simpan profil</button></div>
</form>
<?php company_footer();
