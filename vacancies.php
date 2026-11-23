<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/models.php';
require_once __DIR__ . '/includes/functions.php';

$keyword = trim($_GET['q'] ?? '');
$categoryId = isset($_GET['category']) && $_GET['category'] !== '' ? (int)$_GET['category'] : null;
$workType = $_GET['work_type'] ?? '';
$accessibilityTags = isset($_GET['accessibility']) ? array_filter(explode(',', $_GET['accessibility'])) : [];
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

$filters = [
    'keyword' => $keyword ?: null,
    'category_id' => $categoryId,
    'work_type' => $workType ?: null,
    'accessibility_tags' => $accessibilityTags ?: null,
];

$vacancies = vacancy_get_public_list($filters, $perPage, $offset);
$total = vacancy_count_public($filters);
$totalPages = max(1, (int)ceil($total / $perPage));
$categories = category_get_all();

$success = flash('success');
$error = flash('error');
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Lowongan Kerja — Rangkul</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
/* Layout */
.vacancies-layout { max-width: 1200px; margin: 0 auto; padding: 40px 24px; }
.vacancies-header { margin-bottom: 32px; }
.vacancies-header .eyebrow { font-size: 11px; font-weight: 800; letter-spacing: 1.3px; color: #2563eb; margin-bottom: 8px; }
.vacancies-header h1 { font-size: 32px; letter-spacing: -1px; margin: 0 0 8px; }
.vacancies-header p { color: #64748b; margin: 0; }

/* Filter Bar */
.filter-bar { background: #fff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; }
.filter-row { display: grid; grid-template-columns: 1fr auto auto; gap: 16px; align-items: end; }
.filter-group { display: flex; flex-direction: column; gap: 6px; }
.filter-group label { font-size: 12px; font-weight: 600; color: #334155; }
.filter-group input,
.filter-group select { height: 42px; border: 1px solid #d1d5db; border-radius: 10px; padding: 0 12px; font: 14px Inter; background: #fff; }
.filter-group input:focus,
.filter-group select:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.15); }
.filter-actions { display: flex; gap: 10px; }
.btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; border: 1px solid transparent; border-radius: 10px; padding: 11px 17px; font: 600 14px Inter; cursor: pointer; text-decoration: none !important; transition: .2s; }
.btn-primary { background: #2563eb; color: #fff; }
.btn-primary:hover { background: #1d4ed8; }
.btn-outline { border-color: #d1d5db; color: #334155; background: #fff; }
.btn-outline:hover { border-color: #2563eb; color: #2563eb; }

/* Accessibility Tags Filter */
.accessibility-filter { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; }
.accessibility-filter summary { font-size: 13px; font-weight: 600; color: #334155; cursor: pointer; }
.accessibility-tags { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
.accessibility-tag { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 99px; font-size: 12px; color: #475569; cursor: pointer; transition: .15s; }
.accessibility-tag:hover { background: #e2e8f0; }
.accessibility-tag.active { background: #2563eb; border-color: #2563eb; color: #fff; }
.accessibility-tag input { display: none; }

/* Vacancy Grid */
.vacancies-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 20px; }
.vacancy-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; display: flex; flex-direction: column; transition: .2s; }
.vacancy-card:hover { box-shadow: 0 12px 32px rgba(20,43,85,.1); border-color: #cbd5e1; }
.vacancy-card-header { display: flex; align-items: flex-start; gap: 12px; margin-bottom: 16px; }
.company-logo { width: 48px; height: 48px; border-radius: 12px; object-fit: cover; background: #f1f5f9; flex-shrink: 0; }
.company-info { flex: 1; min-width: 0; }
.company-name { font-weight: 600; color: #1e293b; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.company-badges { display: flex; gap: 6px; margin-top: 4px; }
.badge { font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 99px; text-transform: uppercase; letter-spacing: 0.3px; }
.badge-verified { background: #eff6ff; color: #2563eb; }
.badge-inclusive { background: #ecfdf5; color: #059669; }
.vacancy-title { font-size: 18px; font-weight: 700; color: #1e293b; margin: 0 0 12px; line-height: 1.3; }
.vacancy-meta { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px; font-size: 13px; color: #64748b; }
.vacancy-meta span { display: flex; align-items: center; gap: 6px; }
.vacancy-tags { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 20px; }
.tag { font-size: 11px; font-weight: 600; padding: 4px 10px; border-radius: 99px; background: #f1f5f9; color: #475569; }
.tag.accessibility { background: #f0fdf4; color: #166534; }
.vacancy-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 16px; border-top: 1px solid #f1f5f9; margin-top: auto; }
.vacancy-deadline { font-size: 13px; color: #64748b; }
.vacancy-deadline.urgent { color: #dc2626; font-weight: 600; }

/* Empty State */
.empty-state { text-align: center; padding: 60px 20px; color: #64748b; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; }
.empty-state svg { width: 64px; height: 64px; margin-bottom: 16px; opacity: 0.5; }
.empty-state h3 { margin: 0 0 8px; font-size: 18px; color: #334155; }
.empty-state p { margin: 0 0 20px; font-size: 14px; }

/* Pagination */
.pagination { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 32px; }
.pagination a,
.pagination span { display: inline-flex; align-items: center; justify-content: center; min-width: 40px; height: 40px; padding: 0 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-size: 14px; font-weight: 600; color: #475569; text-decoration: none; transition: .15s; }
.pagination a:hover { background: #f1f5f9; border-color: #cbd5e1; color: #1e293b; }
.pagination .current { background: #2563eb; border-color: #2563eb; color: #fff; }
.pagination .disabled { opacity: 0.5; pointer-events: none; }

/* Results Info */
.results-info { font-size: 14px; color: #64748b; margin-bottom: 16px; }
.results-info strong { color: #1e293b; }

@media (max-width: 760px) {
    .filter-row { grid-template-columns: 1fr; }
    .filter-actions { width: 100%; justify-content: stretch; }
    .filter-actions .btn { flex: 1; }
    .vacancies-grid { grid-template-columns: 1fr; }
}
</style>
</head>
<body>
<?php require_once __DIR__ . '/includes/header.php'; ?>

<main class="vacancies-layout">
    <header class="vacancies-header">
        <span class="eyebrow">RANGKUL / LOWONGAN</span>
        <h1>Temukan Lowongan Kerja</h1>
        <p>Cari peluang karier yang sesuai dengan keahlian dan kebutuhan Anda. Gunakan filter untuk menyaring hasil.</p>
    </header>

    <?php if ($success): ?><div class="notice success" style="margin-bottom:20px;padding:14px 18px;background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;border-radius:10px;font-size:14px;"><?= e($success) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="notice error" style="margin-bottom:20px;padding:14px 18px;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:10px;font-size:14px;"><?= e($error) ?></div><?php endif; ?>

    <form method="get" class="filter-bar" id="filterForm">
        <div class="filter-row">
            <div class="filter-group" style="flex:1;">
                <label>Kata Kunci</label>
                <input type="search" name="q" placeholder="Judul, deskripsi, nama perusahaan..." value="<?= e($keyword) ?>" aria-label="Cari lowongan">
            </div>
            <div class="filter-group">
                <label>Kategori</label>
                <select name="category">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId === $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label>Tipe Kerja</label>
                <select name="work_type">
                    <option value="">Semua Tipe</option>
                    <option value="onsite" <?= $workType === 'onsite' ? 'selected' : '' ?>>On-site</option>
                    <option value="remote" <?= $workType === 'remote' ? 'selected' : '' ?>>Remote</option>
                    <option value="hybrid" <?= $workType === 'hybrid' ? 'selected' : '' ?>>Hybrid</option>
                </select>
            </div>
        </div>

        <details class="accessibility-filter">
            <summary>Tag Aksesibilitas</summary>
            <div class="accessibility-tags">
                <?php
                $allTags = ['akses_kursi_roda', 'juru_bahasa_isyarat', 'huruf_braille', 'screen_reader', 'waktu_tambahan', 'ruang_tenang', 'akses_lift', 'toilet_inklusif'];
                $tagLabels = [
                    'akses_kursi_roda' => '♿ Akses Kursi Roda',
                    'juru_bahasa_isyarat' => '🤟 Juru Bahasa Isyarat',
                    'huruf_braille' => '⠃ Braille',
                    'screen_reader' => '🔊 Screen Reader',
                    'waktu_tambahan' => '⏱ Waktu Tambahan',
                    'ruang_tenang' => '🤫 Ruang Tenang',
                    'akses_lift' => '🛗 Akses Lift',
                    'toilet_inklusif' => '🚻 Toilet Inklusif',
                ];
                foreach ($allTags as $tag):
                    $checked = in_array($tag, $accessibilityTags) ? 'active' : '';
                ?>
                    <label class="accessibility-tag <?= $checked ?>">
                        <input type="checkbox" name="accessibility[]" value="<?= $tag ?>" <?= $checked ? 'checked' : '' ?> onchange="this.closest('form').submit()">
                        <?= $tagLabels[$tag] ?>
                    </label>
                <?php endforeach; ?>
            </div>
        </details>
    </form>

    <div class="results-info">
        Menampilkan <strong><?= count($vacancies) ?></strong> dari <strong><?= $total ?></strong> lowongan
    </div>

    <?php if (empty($vacancies)): ?>
        <div class="empty-state">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0zM10 7v3m0 0v3m0-3h3m-3 0H7"/></svg>
            <h3>Tidak ada lowongan ditemukan</h3>
            <p>Coba ubah kata kunci atau filter pencarian Anda.</p>
            <a href="vacancies.php" class="btn btn-primary">Reset Filter</a>
        </div>
    <?php else: ?>
        <div class="vacancies-grid">
            <?php foreach ($vacancies as $vacancy): ?>
                <article class="vacancy-card">
                    <div class="vacancy-card-header">
                        <?php if ($vacancy['company_logo']): ?>
                            <img class="company-logo" src="../uploads/logos/<?= e($vacancy['company_logo']) ?>" alt="<?= e($vacancy['company_name']) ?>" onerror="this.src='https://ui-avatars.com/api/?name=' + encodeURIComponent('<?= e($vacancy['company_name']) ?>') + '&background=2563eb&color=fff&size=48'">
                        <?php else: ?>
                            <div class="company-logo" style="display:grid;place-items:center;font-weight:800;color:#fff;background:#2563eb;"><?= strtoupper(mb_substr($vacancy['company_name'], 0, 1)) ?></div>
                        <?php endif; ?>
                        <div class="company-info">
                            <div class="company-name"><?= e($vacancy['company_name']) ?></div>
                            <div class="company-badges">
                                <?php if ($vacancy['is_verified']): ?>
                                    <span class="badge badge-verified">Terverifikasi</span>
                                <?php endif; ?>
                                <?php if ($vacancy['is_inclusive']): ?>
                                    <span class="badge badge-inclusive">Inklusif</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <h2 class="vacancy-title"><?= e($vacancy['title']) ?></h2>
                    <div class="vacancy-meta">
                        <span>📍 <?= e($vacancy['location']) ?></span>
                        <span><?= e(match($vacancy['work_type']) { 'onsite' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid' }) ?></span>
                        <span>🏷 <?= e($vacancy['category_name']) ?></span>
                    </div>
                    <div class="vacancy-tags">
                        <span class="tag"><?= e($vacancy['category_name']) ?></span>
                        <span class="tag"><?= e(match($vacancy['work_type']) { 'onsite' => 'On-site', 'remote' => 'Remote', 'hybrid' => 'Hybrid' }) ?></span>
                        <?php if (!empty($vacancy['accessibility_tags']) && is_array($vacancy['accessibility_tags'])): ?>
                            <?php foreach (array_slice($vacancy['accessibility_tags'], 0, 3) as $tag): ?>
                                <span class="tag accessibility"><?= e(match($tag) {
                                    'akses_kursi_roda' => 'Akses Kursi Roda',
                                    'juru_bahasa_isyarat' => 'Juru Bahasa Isyarat',
                                    'huruf_braille' => 'Braille',
                                    'screen_reader' => 'Screen Reader',
                                    'waktu_tambahan' => 'Waktu Tambahan',
                                    'ruang_tenang' => 'Ruang Tenang',
                                    'akses_lift' => 'Akses Lift',
                                    'toilet_inklusif' => 'Toilet Inklusif',
                                    default => $tag
                                }) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($vacancy['accessibility_tags']) > 3): ?>
                                <span class="tag">+<?= count($vacancy['accessibility_tags']) - 3 ?></span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="vacancy-footer">
                        <?php
                        $daysLeft = (int)ceil((strtotime($vacancy['deadline']) - time()) / 86400);
                        $urgent = $daysLeft <= 7;
                        ?>
                        <span class="vacancy-deadline <?= $urgent ? 'urgent' : '' ?>">
                            <?= $urgent ? '⚠ ' : '' ?>Batas: <?= date('d M Y', strtotime($vacancy['deadline'])) ?>
                            <?= $urgent ? " ($daysLeft hari)" : '' ?>
                        </span>
                        <a href="vacancy-detail.php?id=<?= $vacancy['id'] ?>" class="btn btn-primary" style="padding:10px 16px;font-size:13px;">Detail & Lamar</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($totalPages > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo; Sebelumnya</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Sebelumnya</span>
                <?php endif; ?>
                <?php
                $start = max(1, $page - 2);
                $end = min($totalPages, $page + 2);
                for ($i = $start; $i <= $end; $i++):
                ?>
                    <?php if ($i === $page): ?>
                        <span class="current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($page < $totalPages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Selanjutnya &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Selanjutnya &raquo;</span>
                <?php endif; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

<script>
function buildUrl(params) {
    const url = new URL(window.location.href);
    Object.entries(params).forEach(([key, value]) => {
        if (value !== null && value !== undefined && value !== '') {
            url.searchParams.set(key, value);
        } else {
            url.searchParams.delete(key);
        }
    });
    return url.toString();
}

// Auto-submit on filter change (except checkboxes which have onchange)
document.querySelectorAll('#filterForm select, #filterForm input[name="q"]').forEach(el => {
    el.addEventListener('change', () => document.getElementById('filterForm').submit());
});

// Debounced search for keyword
let searchTimeout;
document.querySelector('#filterForm input[name="q"]').addEventListener('input', (e) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => document.getElementById('filterForm').submit(), 500);
});
</script>
</body>
</html>