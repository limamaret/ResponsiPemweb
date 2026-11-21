<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('job_seeker');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('applications.php');
}

verify_csrf();

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);

$applicationId = (int)($_POST['application_id'] ?? 0);
if (!$applicationId) {
    flash('error', 'Lamaran tidak valid.');
    redirect('applications.php');
}

if (application_cancel($applicationId, $seeker['id'])) {
    flash('success', 'Lamaran berhasil dibatalkan.');
} else {
    flash('error', 'Gagal membatalkan lamaran. Hanya lamaran dengan status "Dikirim" yang dapat dibatalkan.');
}

redirect('applications.php');