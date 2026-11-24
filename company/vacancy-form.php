<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$existing = null;
if ($id) {
    $existing = own_vacancy($pdo, $id, $cid);
    if (!$existing) not_found('vacancies.php');
}
// Profil wajib lengkap sebelum membuat lowongan baru
if (!$existing && !company_profile_complete($company)) {
    flash('error', 'Lengkapi profil perusahaan (nama, deskripsi, alamat) sebelum membuat lowongan.');
    redirect('profile.php');
}

$cats = $pdo->query('SELECT id, name FROM categories ORDER BY name')->fetchAll();
$catIds = array_map('intval', array_column($cats, 'id'));
$errors = [];
$f = $existing ? [
    'title' => $existing['title'], 'description' => $existing['description'], 'requirements' => $existing['requirements'],
    'category_id' => (int)$existing['category_id'], 'work_type' => $existing['work_type'], 'location' => $existing['location'],
    'salary_min' => $existing['salary_min'] !== null ? (string)(int)$existing['salary_min'] : '',
    'salary_max' => $existing['salary_max'] !== null ? (string)(int)$existing['salary_max'] : '',
    'deadline' => $existing['deadline'], 'tags' => csv_keys_any($existing['accessibility_tags'] ?? []), 'status' => $existing['status'],
] : ['title' => '', 'description' => '', 'requirements' => '', 'category_id' => 0, 'work_type' => 'onsite', 'location' => '',
     'salary_min' => '', 'salary_max' => '', 'deadline' => '', 'tags' => [], 'status' => 'open'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $f['title'] = trim($_POST['title'] ?? '');
    $f['description'] = trim($_POST['description'] ?? '');
    $f['requirements'] = trim($_POST['requirements'] ?? '');
    $f['category_id'] = (int)($_POST['category_id'] ?? 0);
    $f['work_type'] = $_POST['work_type'] ?? '';
    $f['location'] = trim($_POST['location'] ?? '');
    $f['salary_min'] = preg_replace('/[^0-9]/', '', (string)($_POST['salary_min'] ?? ''));
    $f['salary_max'] = preg_replace('/[^0-9]/', '', (string)($_POST['salary_max'] ?? ''));
    $f['deadline'] = trim($_POST['deadline'] ?? '');
    $f['tags'] = csv_keys(keys_to_csv($_POST['tags'] ?? []));
    $f['status'] = ($_POST['status'] ?? 'open') === 'closed' ? 'closed' : 'open';

    if ($f['title'] === '' || mb_strlen($f['title']) > 150) $errors['title'] = 'Judul wajib diisi (maksimal 150 karakter).';
    if ($f['description'] === '') $errors['description'] = 'Deskripsi wajib diisi.';
    if ($f['requirements'] === '') $errors['requirements'] = 'Persyaratan wajib diisi.';
    if (!in_array($f['category_id'], $catIds, true)) $errors['category_id'] = 'Pilih kategori yang tersedia.';
    if (!isset(work_types()[$f['work_type']])) $errors['work_type'] = 'Pilih tipe kerja.';
    if ($f['location'] === '' || mb_strlen($f['location']) > 150) $errors['location'] = 'Lokasi wajib diisi (maksimal 150 karakter).';
    if ($f['salary_min'] !== '' && $f['salary_max'] !== '' && (float)$f['salary_max'] < (float)$f['salary_min']) $errors['salary'] = 'Gaji maksimum tidak boleh lebih kecil dari gaji minimum.';
    if (strlen($f['salary_min']) > 12 || strlen($f['salary_max']) > 12) $errors['salary'] = 'Nilai gaji terlalu besar.';
    if (!valid_date($f['deadline'])) $errors['deadline'] = 'Deadline wajib diisi dengan tanggal yang valid.';
    elseif ($f['deadline'] < date('Y-m-d') && (!$existing || $f['deadline'] !== $existing['deadline'])) $errors['deadline'] = 'Deadline tidak boleh di masa lalu.';

    if (!$errors) {
        $min = $f['salary_min'] === '' ? null : $f['salary_min'];
        $max = $f['salary_max'] === '' ? null : $f['salary_max'];
        $tags = json_encode($f['tags']);
        if ($existing) {
            $pdo->prepare('UPDATE vacancies SET category_id=?, title=?, description=?, requirements=?, work_type=?, location=?, salary_min=?, salary_max=?, deadline=?, accessibility_tags=?, status=? WHERE id=? AND company_id=?')
                ->execute([$f['category_id'], $f['title'], $f['description'], $f['requirements'], $f['work_type'], $f['location'], $min, $max, $f['deadline'], $tags, $f['status'], $id, $cid]);
            flash('success', 'Lowongan berhasil diperbarui.');
        } else {
            $pdo->prepare('INSERT INTO vacancies (company_id, category_id, title, description, requirements, work_type, location, salary_min, salary_max, deadline, accessibility_tags, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$cid, $f['category_id'], $f['title'], $f['description'], $f['requirements'], $f['work_type'], $f['location'], $min, $max, $f['deadline'], $tags, $f['status']]);
            flash('success', 'Lowongan berhasil ditambahkan.');
        }
        redirect('vacancies.php');
    }
}

$err = fn(string $k) => isset($errors[$k]) ? '<span class="field-error">' . e($errors[$k]) . '</span>' : '';
company_header($existing ? 'Edit Lowongan' : 'Tambah Lowongan', 'vacancies', $company);
?>
<a class="back-link" href="vacancies.php">← Kembali ke daftar lowongan</a>
<div class="page-head"><div><span class="eyebrow">LOWONGAN</span><h1><?= $existing ? 'Edit lowongan' : 'Tambah lowongan' ?></h1></div></div>
<?php if ($errors): ?><div class="notice error" role="alert">Masih ada isian yang perlu diperbaiki. Periksa pesan di bawah setiap kolom.</div><?php endif; ?>
<form method="post" class="co-form card" novalidate>
<?= csrf_field() ?><?php if ($existing): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
<div class="field"><label for="title">Judul lowongan <span class="req" aria-hidden="true">*</span></label><input id="title" name="title" value="<?= e($f['title']) ?>" required maxlength="150"><?= $err('title') ?></div>
<div class="form-grid">
<div class="field"><label for="category_id">Kategori <span class="req" aria-hidden="true">*</span></label><select id="category_id" name="category_id" required><option value="">— Pilih kategori —</option>
<?php foreach ($cats as $c): ?><option value="<?= (int)$c['id'] ?>"<?= $f['category_id'] === (int)$c['id'] ? ' selected' : '' ?>><?= e($c['name']) ?></option><?php endforeach; ?></select><?= $err('category_id') ?></div>
<div class="field"><label for="work_type">Tipe kerja <span class="req" aria-hidden="true">*</span></label><select id="work_type" name="work_type" required>
<?php foreach (work_types() as $k => $l): ?><option value="<?= e($k) ?>"<?= $f['work_type'] === $k ? ' selected' : '' ?>><?= e($l) ?></option><?php endforeach; ?></select><?= $err('work_type') ?></div>
</div>
<div class="field"><label for="description">Deskripsi pekerjaan <span class="req" aria-hidden="true">*</span></label><textarea id="description" name="description" required><?= e($f['description']) ?></textarea><?= $err('description') ?></div>
<div class="field"><label for="requirements">Persyaratan <span class="req" aria-hidden="true">*</span></label><textarea id="requirements" name="requirements" required><?= e($f['requirements']) ?></textarea><?= $err('requirements') ?></div>
<div class="form-grid">
<div class="field"><label for="location">Lokasi <span class="req" aria-hidden="true">*</span></label><input id="location" name="location" value="<?= e($f['location']) ?>" required maxlength="150"><?= $err('location') ?></div>
<div class="field"><label for="deadline">Deadline lamaran <span class="req" aria-hidden="true">*</span></label><input id="deadline" name="deadline" type="date" value="<?= e($f['deadline']) ?>" required><?= $err('deadline') ?></div>
</div>
<div class="form-grid">
<div class="field"><label for="salary_min">Gaji minimum (Rp)</label><input id="salary_min" name="salary_min" inputmode="numeric" value="<?= e($f['salary_min']) ?>" placeholder="5000000"></div>
<div class="field"><label for="salary_max">Gaji maksimum (Rp)</label><input id="salary_max" name="salary_max" inputmode="numeric" value="<?= e($f['salary_max']) ?>" placeholder="8000000"></div>
</div><?= $err('salary') ?>
<fieldset class="field-group"><legend>Tag aksesibilitas lowongan</legend><span class="hint">Pilih fasilitas yang benar-benar tersedia untuk posisi ini.</span>
<div class="check-grid"><?php foreach (access_options() as $k => $l): ?><label class="check-item"><input type="checkbox" name="tags[]" value="<?= e($k) ?>"<?= in_array($k, $f['tags'], true) ? ' checked' : '' ?>> <?= e($l) ?></label><?php endforeach; ?></div></fieldset>
<div class="field"><label for="status">Status lowongan</label><select id="status" name="status"><option value="open"<?= $f['status'] === 'open' ? ' selected' : '' ?>>Terbuka (tampil di daftar publik)</option><option value="closed"<?= $f['status'] === 'closed' ? ' selected' : '' ?>>Ditutup</option></select></div>
<div class="actions"><button class="btn btn-primary" type="submit"><?= $existing ? 'Simpan perubahan' : 'Tambah lowongan' ?></button><a class="btn btn-outline" href="vacancies.php">Batal</a></div>
</form>
<?php company_footer();
