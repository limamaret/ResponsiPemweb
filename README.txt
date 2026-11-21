RANGKUL AUTHENTICATION — PHP NATIVE + MYSQL

FITUR
- Login terpisah untuk Job Seeker, Recruiter, dan Admin.
- Registrasi publik untuk Job Seeker dan Recruiter.
- Admin tidak dapat mendaftar dari halaman publik.
- Password disimpan menggunakan password_hash().
- Validasi role, session regeneration, CSRF token, prepared statements.
- Pendaftaran Recruiter masuk status pending sampai diverifikasi Admin.

CARA MENJALANKAN DI XAMPP
1. Ekstrak folder jobconnect_auth ke C:\xampp\htdocs\
2. Jalankan Apache dan MySQL dari XAMPP.
3. Buka http://localhost/phpmyadmin
4. Import file database.sql.
5. Periksa config.php:
   DB_HOST = localhost
   DB_NAME = jobconnect
   DB_USER = root
   DB_PASS = '' (default XAMPP; sesuaikan jika punya password)
6. Sebelum membuat Admin, buka setup-admin.php dan ganti:
   CHANGE-THIS-SETUP-KEY
   dengan string acak milikmu.
7. Buka http://localhost/jobconnect_auth/setup-admin.php
8. Buat akun Admin dengan password minimal 12 karakter.
9. Setelah berhasil, HAPUS setup-admin.php dari folder project.
10. Buka http://localhost/jobconnect_auth/

HALAMAN
- Beranda:                 /
- Login Job Seeker:        /login-job-seeker.php
- Register Job Seeker:     /register-job-seeker.php
- Login Recruiter:         /login-recruiter.php
- Register Recruiter:      /register-recruiter.php
- Login Admin:             /admin/login.php
- Dashboard Admin:         /admin/dashboard.php
- Dashboard Job Seeker:    /job-seeker/dashboard.php
- Dashboard Recruiter:     /recruiter/dashboard.php

CATATAN
- Recruiter baru berstatus pending. Login baru dapat dilakukan setelah Admin mengubah status user menjadi active dan verification_status perusahaan menjadi verified. Untuk demo lokal, status dapat diubah melalui phpMyAdmin.
- Fitur lupa password belum diaktifkan; tidak ada email reset yang dikirim.
- Dashboard saat ini adalah placeholder untuk tahap pengembangan modul berikutnya.
- Untuk deployment publik, gunakan HTTPS, ubah kredensial database, hapus setup-admin.php, dan tambahkan rate limiting serta mekanisme reset password.
