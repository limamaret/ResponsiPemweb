<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];

// Mode edit (?id=) atau buat baru (?application_id=)
$interview = null;
if (isset($_GET['id']) || isset($_POST['interview_id'])) {
    $iid = (int)($_GET['id'] ?? $_POST['interview_id']);
    $s = $pdo->prepare('SELECT i.* FROM interviews i JOIN applications a ON a.id = i.application_id JOIN vacancies v ON v.id = a.vacancy_id WHERE i.id = ? AND v.company_id = ? LIMIT 1');
    $s->execute([$iid, $cid]); $interview = $s->fetch() ?: null;
    if (!$interview) not_found('interviews.php');
    $appId = (int)$interview['application_id'];
} else {
    $appId = (int)($_GET['application_id'] ?? $_POST['application_id'] ?? 0);
}
$app = $appId ? own_application($pdo, $appId, $cid) : null;
if (!$app) not_found('applicants.php');

if (!$interview) { // kemungkinan ada jadwal lama untuk lamaran ini
    $s = $pdo->prepare('SELECT * FROM interviews WHERE application_id = ? LIMIT 1');
    $s->execute([$appId]); $old = $s->fetch() ?: null;
    if ($old && $old['status'] !== 'Cancelled') redirect('interview-form.php?id=' . (int)$old['id']);
    $interview = $old; // jadwal batal -> dipakai ulang (satu lamaran = satu jadwal)
    $reactivating = (bool)$old;
} else { $reactivating = false; }

$isNew = !$interview || $reactivating;
if ($isNew && $app['status'] !== 'Shortlist') {
    flash('error', 'Wawancara hanya dapat dijadwalkan untuk pelamar berstatus Shortlist.');
    redirect('applicant-detail.php?id=' . $appId);
}
if (!$isNew && in_array($app['status'], ['Ditolak', 'Dibatalkan'], true)) {
    flash('error', 'Jadwal tidak dapat diubah karena lamaran sudah ' . strtolower(application_statuses()[$app['status']]) . '.');
    redirect('applicant-detail.php?id=' . $appId);
}

$errors = [];
$f = $interview && !$reactivating ? [
    'date' => $interview['date'], 'time' => substr($interview['time'], 0, 5), 'mode' => $interview['mode'],
    'loc' => $interview['location_or_link'], 'note' => $interview['accommodation_note'] ?? '',
] : ['date' => '', 'time' => '', 'mode' => 'online', 'loc' => '', 'note' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $f['date'] = trim($_POST['interview_date'] ?? '');
    $f['time'] = trim($_POST['interview_time'] ?? '');
    $f['mode'] = ($_POST['mode'] ?? '') === 'offline' ? 'offline' : 'online';
    $f['loc']  = trim($_POST['location_or_link'] ?? '');
    $f['note'] = trim($_POST['accommodation_note'] ?? '');

    $dateOk = valid_date($f['date']); $timeOk = valid_time($f['time']);
    if (!$dateOk) $errors['date'] = 'Tanggal wawancara wajib diisi dengan format yang valid.';
    if (!$timeOk) $errors['time'] = 'Jam wawancara wajib diisi (format JJ:MM).';
    $changedWhen = $isNew || !$interview || $f['date'] !== $interview['date'] || $f['time'] !== substr($interview['time'], 0, 5);
    if ($dateOk && $timeOk && $changedWhen && strtotime($f['date'] . ' ' . $f['time']) < time()) $errors['date'] = 'Jadwal wawancara tidak boleh di masa lalu.';
    if ($f['loc'] === '' || mb_strlen($f['loc']) > 255) $errors['loc'] = ($f['mode'] === 'online' ? 'Tautan' : 'Lokasi') . ' wawancara wajib diisi (maksimal 255 karakter).';
    elseif ($f['mode'] === 'online' && (!filter_var($f['loc'], FILTER_VALIDATE_URL) || !preg_match('#^https?://#i', $f['loc']))) $errors['loc'] = 'Tautan wawancara online harus berupa URL lengkap (https://...).';

    if (!$errors) {
        try {
            $pdo->beginTransaction();
            if ($isNew && !$interview) {
                $pdo->prepare("INSERT INTO interviews (application_id, date, time, mode, location_or_link, accommodation_note, status) VALUES (?,?,?,?,?,?, 'Scheduled')")
                    ->execute([$appId, $f['date'], $f['time'] . ':00', $f['mode'], $f['loc'], $f['note'] ?: null]);
                $msg = 'Jadwal wawancara berhasil dibuat.';
            } else {
                // Bila waktu/mode/lokasi berubah, pelamar perlu konfirmasi ulang.
                $reset = $reactivating || $changedWhen || $f['mode'] !== $interview['mode'] || $f['loc'] !== $interview['location_or_link'];
                $status = $reset ? 'Scheduled' : $interview['status'];
                $pdo->prepare('UPDATE interviews SET date=?, time=?, mode=?, location_or_link=?, accommodation_note=?, status=? WHERE id=?')
                    ->execute([$f['date'], $f['time'] . ':00', $f['mode'], $f['loc'], $f['note'] ?: null, $status, (int)$interview['id']]);
                $msg = 'Jadwal wawancara berhasil diperbarui.' . ($reset ? ' Pelamar perlu mengonfirmasi ulang.' : '');
            }
            $pdo->commit();
            flash('success', $msg);
            redirect('interviews.php');
        } catch (PDOException $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $errors['db'] = 'Jadwal gagal disimpan. Silakan coba lagi.';
        }
    }
}

$err = fn(string $k) => isset($errors[$k]) ? '<span class="field-error">' . e($errors[$k]) . '</span>' : '';
company_header($isNew ? 'Jadwalkan Wawancara' : 'Edit Jadwal', 'interviews', $company);
?>
<a class="back-link" href="applicant-detail.php?id=<?= $appId ?>">← Kembali ke detail pelamar</a>
<div class="page-head"><div><span class="eyebrow">WAWANCARA</span><h1><?= $isNew ? 'Jadwalkan wawancara' : 'Edit jadwal wawancara' ?></h1>
<p>Pelamar: <b><?= e($app['seeker_name']) ?></b> · <?= e($app['vacancy_title']) ?></p></div></div>
<?php if ($app['accommodation_needs'] || $app['disability_type']): ?><div class="note-box" style="margin-bottom:22px"><b>Kebutuhan akomodasi pelamar:</b> <?= e(trim(($app['disability_type'] ?: '') . ($app['disability_type'] && $app['accommodation_needs'] ? ' — ' : '') . ($app['accommodation_needs'] ?: ''))) ?>. Catat dukungan yang Anda siapkan pada kolom catatan akomodasi di bawah.</div><?php endif; ?>
<?php if ($errors): ?><div class="notice error" role="alert"><?= e($errors['db'] ?? 'Masih ada isian yang perlu diperbaiki. Periksa pesan di bawah setiap kolom.') ?></div><?php endif; ?>
<form method="post" class="co-form card" novalidate><?= csrf_field() ?>
<?php if ($interview && !$reactivating): ?><input type="hidden" name="interview_id" value="<?= (int)$interview['id'] ?>"><?php else: ?><input type="hidden" name="application_id" value="<?= $appId ?>"><?php endif; ?>
<div class="form-grid">
<div class="field"><label for="interview_date">Tanggal <span class="req" aria-hidden="true">*</span></label><input id="interview_date" name="interview_date" type="date" min="<?= date('Y-m-d') ?>" value="<?= e($f['date']) ?>" required><?= $err('date') ?></div>
<div class="field"><label for="interview_time">Jam (WIB) <span class="req" aria-hidden="true">*</span></label><input id="interview_time" name="interview_time" type="time" value="<?= e($f['time']) ?>" required><?= $err('time') ?></div>
</div>
<div class="field"><label for="mode">Mode wawancara</label><select id="mode" name="mode"><option value="online"<?= $f['mode'] === 'online' ? ' selected' : '' ?>>Online</option><option value="offline"<?= $f['mode'] === 'offline' ? ' selected' : '' ?>>Offline (tatap muka)</option></select></div>
<div class="field"><label for="location_or_link"><span id="loc-label"><?= $f['mode'] === 'online' ? 'Tautan wawancara (Zoom / Google Meet) ' : 'Lokasi wawancara ' ?></span><span class="req" aria-hidden="true">*</span></label><input id="location_or_link" name="location_or_link" value="<?= e($f['loc']) ?>" required maxlength="255"><?= $err('loc') ?></div>
<div class="field"><label for="accommodation_note">Catatan akomodasi</label><textarea id="accommodation_note" name="accommodation_note" placeholder="Contoh: tersedia juru bahasa isyarat, ruang wawancara di lantai dasar dengan akses kursi roda."><?= e($f['note']) ?></textarea></div>
<div class="actions"><button class="btn btn-primary" type="submit"><?= $isNew ? 'Jadwalkan' : 'Simpan perubahan' ?></button><a class="btn btn-outline" href="applicant-detail.php?id=<?= $appId ?>">Batal</a></div>
</form>
<?php company_footer();
