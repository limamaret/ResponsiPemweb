<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$cid = (int)$company['id'];
$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$app = $id ? own_application($pdo, $id, $cid) : null;
if (!$app) not_found('applicants.php');

/** Status yang boleh dituju dari status saat ini. */
function allowed_targets(string $current): array {
    if (in_array($current, ['rejected', 'accepted', 'cancelled'], true)) return []; // final
    $all = ['reviewed', 'shortlisted', 'rejected', 'accepted'];
    return array_values(array_diff($all, [$current]));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'status') {
        $target = $_POST['status'] ?? '';
        if (!in_array($target, allowed_targets($app['status']), true)) {
            flash('error', 'Status lamaran ini tidak dapat diubah ke pilihan tersebut. Lamaran berstatus Ditolak, Diterima, atau Dibatalkan bersifat final.');
        } else {
            try {
                $pdo->beginTransaction();
                $pdo->prepare('UPDATE applications SET status = ? WHERE id = ?')->execute([$target, $id]);
                if ($target === 'rejected') { // jadwal aktif ikut dibatalkan
                    $pdo->prepare("UPDATE interviews SET status = 'cancelled' WHERE application_id = ? AND status <> 'cancelled'")->execute([$id]);
                }
                $pdo->commit();
                flash('success', 'Status pelamar diubah menjadi ' . application_statuses()[$target] . '.');
            } catch (PDOException $ex) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                flash('error', 'Status gagal diperbarui. Silakan coba lagi.');
            }
        }
    } elseif ($action === 'note') {
        $note = trim($_POST['recruiter_note'] ?? '');
        $pdo->prepare('UPDATE applications SET recruiter_note = ? WHERE id = ?')->execute([$note === '' ? null : $note, $id]);
        flash('success', 'Catatan rekruter disimpan.');
    }
    redirect('applicant-detail.php?id=' . $id);
}

$iv = $pdo->prepare('SELECT * FROM interviews WHERE application_id = ? LIMIT 1');
$iv->execute([$id]); $interview = $iv->fetch() ?: null;
$activeInterview = $interview && $interview['status'] !== 'cancelled';
$targets = allowed_targets($app['status']);
$hasCv = (bool)($app['cv_file'] ?: $app['profile_cv']);

company_header('Detail Pelamar', 'applicants', $company);
?>
<a class="back-link" href="applicants.php">← Kembali ke daftar pelamar</a>
<div class="page-head"><div><span class="eyebrow">DETAIL PELAMAR</span><h1><?= e($app['seeker_name']) ?></h1>
<p>Melamar: <b><?= e($app['vacancy_title']) ?></b> · <?= e(fmt_date($app['created_at'])) ?> · <?= status_badge($app['status']) ?></p></div>
<?php if ($hasCv): ?><a class="btn btn-primary" href="download-cv.php?id=<?= $id ?>">Unduh CV (PDF)</a><?php else: ?><span class="badge badge-muted">Pelamar belum melampirkan CV</span><?php endif; ?></div>

<div class="grid-2">
<section class="card" aria-labelledby="h-prof"><h2 id="h-prof">Profil pelamar</h2>
<dl class="def-list">
<dt>Email</dt><dd><?= e($app['seeker_email']) ?></dd>
<dt>Telepon</dt><dd><?= e($app['phone'] ?: '-') ?></dd>
<dt>Tanggal lahir</dt><dd><?= e($app['birthdate'] ? fmt_date($app['birthdate']) : '-') ?></dd>
<dt>Alamat</dt><dd><?= e($app['seeker_address'] ?: '-') ?></dd>
<dt>Pendidikan</dt><dd><?= e($app['education'] ?: '-') ?></dd>
<dt>Pengalaman</dt><dd><?= e($app['experience'] ?: '-') ?></dd>
<dt>Keahlian</dt><dd><?= e($app['skills'] ?: '-') ?></dd>
</dl></section>

<section class="card" aria-labelledby="h-acc"><h2 id="h-acc">Kebutuhan akomodasi</h2>
<?php if ($app['disability_type'] || $app['accommodation_needs']): ?>
<dl class="def-list"><dt>Ragam disabilitas</dt><dd><?= e($app['disability_type'] ?: '-') ?></dd><dt>Kebutuhan akomodasi</dt><dd><?= e($app['accommodation_needs'] ?: '-') ?></dd></dl>
<p class="hint" style="margin:14px 0 0;font-size:12px;color:#475569">Informasi ini bersifat opsional dan hanya dapat dilihat perusahaan yang menerima lamaran. Gunakan hanya untuk menyiapkan akomodasi yang layak.</p>
<?php else: ?><p class="empty" style="padding:10px 0">Pelamar tidak mencantumkan kebutuhan akomodasi.</p><?php endif; ?></section>
</div>

<section class="card" aria-labelledby="h-cl"><h2 id="h-cl">Cover letter</h2>
<p class="prose"><?= $app['cover_letter'] ? e($app['cover_letter']) : 'Pelamar tidak menulis cover letter.' ?></p></section>

<div class="grid-2">
<section class="card" aria-labelledby="h-st"><h2 id="h-st">Ubah status</h2>
<?php if ($targets): ?>
<form method="post" class="co-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="status">
<div class="field"><label for="status">Status saat ini: <?= e(application_statuses()[$app['status']]) ?>. Ubah menjadi</label>
<select id="status" name="status" required><?php foreach ($targets as $t): ?><option value="<?= e($t) ?>"><?= e(application_statuses()[$t]) ?></option><?php endforeach; ?></select>
<span class="hint">Status Ditolak dan Diterima bersifat final dan tidak dapat diubah kembali.</span></div>
<div class="actions"><button class="btn btn-primary" type="submit">Simpan status</button></div></form>
<?php else: ?><p class="note-box">Status <b><?= e(application_statuses()[$app['status']]) ?></b> bersifat final sehingga tidak dapat diubah lagi.</p><?php endif; ?></section>

<section class="card" aria-labelledby="h-nt"><h2 id="h-nt">Catatan rekruter</h2>
<form method="post" class="co-form"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $id ?>"><input type="hidden" name="action" value="note">
<div class="field"><label for="recruiter_note">Catatan internal (tidak terlihat oleh pelamar)</label><textarea id="recruiter_note" name="recruiter_note"><?= e($app['recruiter_note'] ?? '') ?></textarea></div>
<div class="actions"><button class="btn btn-primary" type="submit">Simpan catatan</button></div></form></section>
</div>

<section class="card" aria-labelledby="h-iv"><h2 id="h-iv">Wawancara</h2>
<?php if ($activeInterview): ?>
<dl class="def-list"><dt>Jadwal</dt><dd><?= e(fmt_date($interview['interview_date'])) ?>, <?= e(substr($interview['interview_time'], 0, 5)) ?> WIB</dd>
<dt>Mode</dt><dd><?= $interview['mode'] === 'online' ? 'Online' : 'Offline' ?></dd>
<dt><?= $interview['mode'] === 'online' ? 'Tautan' : 'Lokasi' ?></dt><dd><?= e($interview['location_or_link']) ?></dd>
<dt>Catatan akomodasi</dt><dd><?= e($interview['accommodation_note'] ?: '-') ?></dd>
<dt>Konfirmasi pelamar</dt><dd><span class="badge <?= $interview['status'] === 'confirmed' ? 'badge-ok' : 'badge-wait' ?>"><?= e(interview_status_label($interview['status'])) ?></span></dd></dl>
<div class="actions" style="margin-top:16px"><a class="btn btn-outline" href="interview-form.php?id=<?= (int)$interview['id'] ?>">Edit jadwal</a></div>
<?php elseif ($app['status'] === 'shortlisted'): ?>
<p style="font-size:14px;color:#475569"><?= $interview ? 'Jadwal sebelumnya telah dibatalkan. ' : 'Pelamar ini masuk Shortlist dan belum memiliki jadwal wawancara. ' ?></p>
<a class="btn btn-primary" href="interview-form.php?application_id=<?= $id ?>">Jadwalkan wawancara</a>
<?php else: ?><p class="empty" style="padding:6px 0">Wawancara hanya dapat dijadwalkan untuk pelamar berstatus <b>Shortlist</b>.</p><?php endif; ?></section>
<?php company_footer();
