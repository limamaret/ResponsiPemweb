<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/models.php';
require_once __DIR__ . '/../includes/functions.php';
require_role('job_seeker');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('interviews.php');
}

verify_csrf();

$user = current_user();
$seeker = seeker_get_by_user_id($user['id']);

$interviewId = (int)($_POST['interview_id'] ?? 0);
if (!$interviewId) {
    flash('error', 'Jadwal wawancara tidak valid.');
    redirect('interviews.php');
}

if (interview_confirm($interviewId, $seeker['id'])) {
    flash('success', 'Konfirmasi kehadiran berhasil dikirim.');
} else {
    flash('error', 'Gagal mengonfirmasi. Hanya wawancara dengan status "Scheduled" yang dapat dikonfirmasi.');
}

redirect('interviews.php');