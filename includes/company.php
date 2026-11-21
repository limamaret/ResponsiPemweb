<?php
declare(strict_types=1);
/**
 * Helper khusus sisi Company (role 'recruiter' pada sistem autentikasi yang ada).
 * File ini sengaja dipisah dari includes/functions.php agar tidak bentrok
 * dengan pekerjaan sisi Job Seeker.
 */
require_once __DIR__ . '/auth.php';
date_default_timezone_set('Asia/Jakarta');

const UPLOAD_DIR = __DIR__ . '/../uploads';
const MAX_UPLOAD = 2 * 1024 * 1024; // 2 MB

/** Fasilitas aksesibilitas (dipakai untuk profil perusahaan DAN tag lowongan). */
function access_options(): array {
    return [
        'wheelchair'    => 'Akses kursi roda',
        'elevator'      => 'Lift dan jalur ramah difabel',
        'toilet'        => 'Toilet aksesibel',
        'sign_language' => 'Juru bahasa isyarat',
        'braille'       => 'Materi huruf braille',
        'screen_reader' => 'Dukungan pembaca layar (screen reader)',
        'quiet'         => 'Ruang kerja tenang / ramah neurodivergen',
        'flex_hours'    => 'Jam kerja fleksibel',
        'remote'        => 'Opsi kerja jarak jauh',
    ];
}

function application_statuses(): array {
    return [
        'submitted'   => 'Dikirim',
        'reviewed'    => 'Direview',
        'shortlisted' => 'Shortlist',
        'rejected'    => 'Ditolak',
        'accepted'    => 'Diterima',
        'cancelled'   => 'Dibatalkan pelamar',
    ];
}

function work_types(): array {
    return ['onsite' => 'Onsite', 'remote' => 'Remote', 'hybrid' => 'Hybrid'];
}

function interview_status_label(string $s): string {
    return ['scheduled' => 'Menunggu konfirmasi pelamar', 'confirmed' => 'Dikonfirmasi pelamar', 'cancelled' => 'Dibatalkan'][$s] ?? $s;
}

/** Ubah daftar kunci "a,b,c" menjadi array yang sudah divalidasi. */
function csv_keys(?string $csv): array {
    if ($csv === null || $csv === '') return [];
    $valid = array_keys(access_options());
    return array_values(array_intersect(array_map('trim', explode(',', $csv)), $valid));
}

/** Ambil input checkbox (array) dan simpan hanya kunci yang valid sebagai CSV. */
function keys_to_csv($input): string {
    if (!is_array($input)) return '';
    $valid = array_keys(access_options());
    return implode(',', array_values(array_intersect($valid, array_map('strval', $input))));
}

function rupiah(?string $v): string {
    return $v === null || $v === '' ? '-' : 'Rp ' . number_format((float)$v, 0, ',', '.');
}

function salary_range(?string $min, ?string $max): string {
    if (($min === null || $min === '') && ($max === null || $max === '')) return 'Tidak dicantumkan';
    if ($min !== null && $min !== '' && $max !== null && $max !== '') return rupiah($min) . ' – ' . rupiah($max);
    return $min !== null && $min !== '' ? 'Mulai ' . rupiah($min) : 'Hingga ' . rupiah($max);
}

function fmt_date(?string $d): string {
    if (!$d) return '-';
    $t = strtotime($d);
    $bulan = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    return date('j', $t) . ' ' . $bulan[(int)date('n', $t) - 1] . ' ' . date('Y', $t);
}

function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function valid_date(string $d): bool {
    $dt = DateTime::createFromFormat('Y-m-d', $d);
    return $dt !== false && $dt->format('Y-m-d') === $d;
}

function valid_time(string $t): bool {
    return (bool)preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $t);
}

/** Pagination sederhana. Mengembalikan [page, perPage, offset, totalPages]. */
function paginate(int $total, int $perPage = 10): array {
    $pages = max(1, (int)ceil($total / $perPage));
    $page = min(max(1, (int)($_GET['page'] ?? 1)), $pages);
    return [$page, $perPage, ($page - 1) * $perPage, $pages];
}

function pagination_html(int $page, int $pages, array $query = []): string {
    if ($pages <= 1) return '';
    unset($query['page']);
    $html = '<nav class="pager" aria-label="Navigasi halaman"><ul>';
    for ($i = 1; $i <= $pages; $i++) {
        $href = '?' . http_build_query($query + ['page' => $i]);
        $html .= '<li>' . ($i === $page
            ? '<span aria-current="page" class="is-current">' . $i . '</span>'
            : '<a href="' . e($href) . '">' . $i . '</a>') . '</li>';
    }
    return $html . '</ul></nav>';
}

/**
 * Validasi + simpan berkas unggahan dengan aman.
 * MIME dicek lewat finfo (bukan $_FILES['type']), nama berkas diacak.
 * Mengembalikan nama berkas baru, null bila tidak ada unggahan, atau
 * melempar RuntimeException berisi pesan error yang ramah pengguna.
 */
function save_upload(array $file, string $subdir, array $allowed): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Unggah berkas gagal. Silakan coba lagi.');
    if ($file['size'] > MAX_UPLOAD) throw new RuntimeException('Ukuran berkas maksimal 2 MB.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    if (!isset($allowed[$mime])) throw new RuntimeException('Tipe berkas tidak diizinkan.');
    $dir = UPLOAD_DIR . '/' . $subdir;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) throw new RuntimeException('Folder unggahan tidak dapat dibuat.');
    $name = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Berkas tidak dapat disimpan.');
    return $name;
}

function image_types(): array {
    return ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
}

function delete_upload(?string $name, string $subdir): void {
    if (!$name) return;
    $path = UPLOAD_DIR . '/' . $subdir . '/' . basename($name);
    if (is_file($path)) @unlink($path);
}

/**
 * Guard halaman area Company: wajib login sebagai recruiter, akun harus
 * masih aktif, dan profil perusahaan dimuat. Mengembalikan baris companies
 * (+ kolom user: user_photo).
 */
function company_guard(PDO $pdo): array {
    require_role('recruiter');
    $uid = (int)current_user()['id'];
    $stmt = $pdo->prepare(
        "SELECT c.*, u.status AS user_status, u.photo AS user_photo, u.name AS user_name
         FROM users u LEFT JOIN companies c ON c.user_id = u.id
         WHERE u.id = ? AND u.role = 'recruiter' LIMIT 1"
    );
    $stmt->execute([$uid]);
    $row = $stmt->fetch();
    if (!$row || $row['user_status'] !== 'active') {
        $_SESSION = [];
        session_regenerate_id(true);
        flash('error', 'Akun Anda tidak aktif atau tidak ditemukan.');
        redirect('../index.php');
    }
    if ($row['id'] === null) { // jaga-jaga: akun recruiter tanpa baris perusahaan
        $pdo->prepare("INSERT INTO companies (user_id, company_name, verification_status) VALUES (?, ?, 'pending')")
            ->execute([$uid, current_user()['name']]);
        redirect('dashboard.php');
    }
    return $row;
}

/** Profil dianggap lengkap bila nama, deskripsi, dan alamat terisi. */
function company_profile_complete(array $c): bool {
    return trim((string)$c['company_name']) !== '' && trim((string)($c['description'] ?? '')) !== '' && trim((string)($c['address'] ?? '')) !== '';
}

function verification_badge(string $status): string {
    $map = [
        'verified' => ['badge-ok', '✓ Terverifikasi'],
        'pending'  => ['badge-wait', 'Menunggu verifikasi'],
        'rejected' => ['badge-bad', 'Verifikasi ditolak'],
    ];
    [$cls, $txt] = $map[$status] ?? ['badge-wait', $status];
    return '<span class="badge ' . $cls . '">' . e($txt) . '</span>';
}

function status_badge(string $status): string {
    $cls = ['submitted' => 'badge-info', 'reviewed' => 'badge-wait', 'shortlisted' => 'badge-purple', 'rejected' => 'badge-bad', 'accepted' => 'badge-ok', 'cancelled' => 'badge-muted'][$status] ?? 'badge-muted';
    return '<span class="badge ' . $cls . '">' . e(application_statuses()[$status] ?? $status) . '</span>';
}

function company_initial(string $name): string {
    return mb_strtoupper(mb_substr(trim($name) ?: 'R', 0, 1));
}

function company_logo_html(?string $logo, string $name, string $base = '../', string $cls = 'co-logo'): string {
    if ($logo) {
        return '<img class="' . $cls . '" src="' . e($base . 'uploads/logos/' . rawurlencode(basename($logo))) . '" alt="Logo ' . e($name) . '">';
    }
    return '<span class="' . $cls . ' co-logo-fallback" aria-hidden="true">' . e(company_initial($name)) . '</span>';
}

/** Kepala halaman area Company (topbar + navigasi). */
function company_header(string $title, string $active, array $company): void {
    $user = current_user();
    $nav = [
        'dashboard'  => ['dashboard.php', 'Dashboard'],
        'profile'    => ['profile.php', 'Profil Perusahaan'],
        'vacancies'  => ['vacancies.php', 'Lowongan'],
        'applicants' => ['applicants.php', 'Pelamar'],
        'interviews' => ['interviews.php', 'Wawancara'],
        'account'    => ['account.php', 'Akun Saya'],
    ];
    $photo = $company['user_photo'] ?? null;
    ?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> — Rangkul Perusahaan</title>
<link rel="stylesheet" href="../assets/css/style.css"><link rel="stylesheet" href="../assets/css/company.css">
<script src="../assets/js/company.js" defer></script></head><body class="co-body">
<a class="skip-link" href="#konten">Lewati ke konten utama</a>
<header class="topbar co-topbar"><a class="brand" href="../index.php" aria-label="Rangkul, beranda"><img class="brand-logo" src="../assets/img/rangkul-logo.png" alt="Rangkul"></a>
<div class="user-nav">
<?php if ($photo): ?><img class="co-avatar" src="../uploads/photos/<?= e(rawurlencode(basename($photo))) ?>" alt="Foto profil <?= e($user['name']) ?>"><?php else: ?><span class="co-avatar co-avatar-fallback" aria-hidden="true"><?= e(company_initial($user['name'])) ?></span><?php endif; ?>
<span class="co-username"><?= e($user['name']) ?> · <?= e($company['company_name']) ?></span>
<form method="post" action="../logout.php" class="inline-form"><button class="btn btn-outline" type="submit">Logout</button></form></div></header>
<nav class="co-nav" aria-label="Menu perusahaan"><ul>
<?php foreach ($nav as $key => [$href, $label]): ?>
<li><a href="<?= e($href) ?>"<?= $key === $active ? ' aria-current="page" class="is-active"' : '' ?>><?= e($label) ?></a></li>
<?php endforeach; ?>
</ul></nav>
<main id="konten" class="co-main" tabindex="-1">
<?php
    if ($m = flash('success')) echo '<div class="notice success" role="status">' . e($m) . '</div>';
    if ($m = flash('error'))   echo '<div class="notice error" role="alert">' . e($m) . '</div>';
}

function company_footer(): void {
    ?></main><footer class="co-footer">© <?= date('Y') ?> Rangkul · Area Perusahaan</footer></body></html><?php
}

/** Pastikan lowongan milik perusahaan ini (cegah IDOR). */
function own_vacancy(PDO $pdo, int $id, int $companyId): ?array {
    $s = $pdo->prepare('SELECT * FROM vacancies WHERE id = ? AND company_id = ? LIMIT 1');
    $s->execute([$id, $companyId]);
    return $s->fetch() ?: null;
}

/** Ambil lamaran beserta data lowongan/pelamar, hanya bila milik perusahaan ini. */
function own_application(PDO $pdo, int $id, int $companyId): ?array {
    $s = $pdo->prepare(
        "SELECT a.*, v.title AS vacancy_title, v.company_id, u.name AS seeker_name, u.email AS seeker_email,
                js.phone, js.birthdate, js.address AS seeker_address, js.education, js.experience, js.skills,
                js.disability_type, js.accommodation_needs, js.cv_file AS profile_cv
         FROM applications a
         JOIN vacancies v ON v.id = a.vacancy_id
         JOIN job_seekers js ON js.id = a.seeker_id
         JOIN users u ON u.id = js.user_id
         WHERE a.id = ? AND v.company_id = ? LIMIT 1"
    );
    $s->execute([$id, $companyId]);
    return $s->fetch() ?: null;
}

/** Hentikan request dengan halaman 404 sederhana. */
function not_found(string $back = 'dashboard.php'): never {
    http_response_code(404);
    exit('<!doctype html><meta charset="utf-8"><title>Tidak ditemukan</title><p style="font-family:sans-serif;padding:40px">Data tidak ditemukan atau bukan milik perusahaan Anda. <a href="' . e($back) . '">Kembali</a></p>');
}
