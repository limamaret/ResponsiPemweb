<?php
require_once __DIR__ . '/../includes/company.php';
$company = company_guard($pdo);
$app = own_application($pdo, (int)($_GET['id'] ?? 0), (int)$company['id']); // cek kepemilikan (anti-IDOR)
if (!$app) not_found('applicants.php');

$name = basename((string)($app['cv_file'] ?: $app['profile_cv']));
$path = realpath(UPLOAD_DIR . '/cv/' . $name);
$base = realpath(UPLOAD_DIR . '/cv');
if ($name === '' || !$path || !$base || strpos($path, $base . DIRECTORY_SEPARATOR) !== 0 || !is_file($path)) {
    flash('error', 'Berkas CV pelamar tidak ditemukan di server.');
    redirect('applicant-detail.php?id=' . (int)$app['id']);
}
$download = preg_replace('/[^A-Za-z0-9_-]+/', '_', $app['seeker_name']) . '_CV.pdf';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $download . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
