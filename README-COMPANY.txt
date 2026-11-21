RANGKUL — SISI COMPANY (PEMBERI KERJA)
=======================================
Stack: Native PHP 8.2+ (PDO), MySQL/MariaDB, HTML5, CSS3, Vanilla JS (sesuai PRD docx).

CARA MENJALANKAN (XAMPP)
1. Import database.sql (sudah ada dari tim), LALU import database-company.sql.
   File migrasi aman dijalankan berulang (kolom hanya ditambah jika belum ada).
2. (Opsional) Import seed-company-demo.sql untuk data demo — jalankan SEKALI.
   Password semua akun demo: Demo12345
     Company terverifikasi : hr@inklusi-digital.test  (login di /login-recruiter.php)
     Company belum verifikasi: hr@kopi-ramah.test
     Job Seeker demo        : andi@demo.test, sinta@demo.test
3. Pastikan folder uploads/logos, uploads/cv, uploads/photos dapat ditulis oleh Apache.
4. Buka http://localhost/<folder-project>/

HALAMAN COMPANY (area /recruiter/)
  dashboard.php         C-22, C-07  ringkasan + status verifikasi
  profile.php           C-04..C-07  profil, logo, komitmen inklusif, fasilitas
  vacancies.php         C-11..C-13  daftar lowongan, buka/tutup, hapus
  vacancy-form.php      C-09..C-11  tambah/edit lowongan + tag aksesibilitas
  applicants.php        C-14        daftar pelamar (filter lowongan/status, pagination)
  applicant-detail.php  C-15,C-17,C-18  detail, ubah status, catatan rekruter
  download-cv.php       C-16        unduh CV pelamar
  interviews.php        C-20,C-21   daftar jadwal, status konfirmasi, batalkan
  interview-form.php    C-19,C-20   jadwalkan / edit wawancara
  account.php           C-03        nama, foto, password
HALAMAN PUBLIK
  company.php?id=       C-08        profil publik perusahaan + lowongan aktif
  job-detail.php?id=                detail lowongan (versi minimal)
  index.php                         landing page + bagian "Lowongan terbaru" & "Perusahaan inklusif"
C-01 / C-02 memakai register-recruiter.php, login-recruiter.php, logout.php yang sudah ada.

FILE BARU (tidak menyentuh area Job Seeker)
  recruiter/*.php (kecuali dashboard.php, yang sebelumnya placeholder), company.php, job-detail.php,
  includes/company.php, includes/public-layout.php, assets/css/company.css, assets/js/company.js,
  database-company.sql, seed-company-demo.sql, uploads/*/.htaccess
FILE LAMA YANG DIUBAH (kecil, mudah di-merge)
  index.php             +2 require di atas, +query data dinamis, +link CSS company.css, +link nav,
                        +1 section #lowongan sebelum FAQ, +jawaban FAQ "lowongan aktif" diperbarui
  register-recruiter.php akun recruiter langsung 'active' (status verifikasi dipegang
                        companies.verification_status, bukan users.status); pesan flash diperbarui
TIDAK DIUBAH: database.sql, config.php, includes/functions.php, includes/auth.php, style.css,
              semua file job-seeker/ dan login/register Job Seeker.

KONTRAK DENGAN SISI JOB SEEKER (perlu disepakati dengan teman Anda)
  * Tabel job_seekers (user_id, phone, birthdate, address, education, experience, skills,
    disability_type, accommodation_needs, cv_file) — sisi Company hanya MEMBACA.
  * Tabel applications: status = submitted|reviewed|shortlisted|rejected|accepted|cancelled.
    Job Seeker mengisi vacancy_id, seeker_id (= job_seekers.id), cv_file, cover_letter.
    Berkas CV disimpan di uploads/cv/ (nama acak); kolom hanya berisi nama berkas.
  * Tabel interviews: Job Seeker mengonfirmasi dengan UPDATE interviews SET status='confirmed'.
  * Tombol "Lamar" di job-detail.php mengarah ke job-seeker/apply.php?vacancy_id=ID.
    Ubah konstanta APPLY_URL di job-detail.php bila nama halamannya berbeda.
  * Kolom users.photo ditambahkan oleh database-company.sql (bila sudah dibuat teman, dilewati otomatis).
  * Role Company di database/auth yang sudah ada bernama 'recruiter' (tidak diganti agar login tetap jalan).

ATURAN PRD YANG DITERAPKAN
  Hanya mengelola data milik sendiri (cek kepemilikan di setiap aksi), CSRF di semua form POST,
  prepared statements, htmlspecialchars pada semua output, upload divalidasi finfo + nama diacak,
  status Ditolak/Diterima final, wawancara hanya untuk Shortlist dan tidak boleh di masa lalu,
  satu lamaran satu jadwal, lowongan lewat deadline/ditutup tidak tampil publik, profil wajib lengkap
  sebelum membuat lowongan, badge inklusif hanya tampil penuh pada perusahaan terverifikasi.
CATATAN: verifikasi perusahaan oleh Admin belum ada UI-nya (modul Admin). Untuk demo ubah
  companies.verification_status menjadi 'verified' lewat phpMyAdmin.
