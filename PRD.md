# Product Requirements Document (PRD)

# Rangkul: Platform Lowongan Kerja Inklusif

> **Rangkul Kemampuan, Raih Kesempatan.**

| | |
|---|---|
| **Proyek** | Responsi 2 (Proyek Akhir) Praktikum Pemrograman Web 1 |
| **Jenis aplikasi** | Platform web lowongan kerja (job portal) |
| **SDGs** | SDG 8 (Pekerjaan Layak dan Pertumbuhan Ekonomi), SDG 10 (Berkurangnya Kesenjangan) |
| **Pendekatan** | Hybrid: terbuka untuk semua pencari kerja, dengan fokus pada perusahaan inklusif dan penyandang disabilitas |
| **Teknologi** | Ditentukan tim |

---

## 1. Ringkasan Produk

Rangkul adalah platform web yang mempertemukan pencari kerja dengan perusahaan pemberi lowongan. Pembeda utamanya adalah **informasi inklusivitas**: perusahaan dapat menandai komitmen dan fasilitas aksesibilitasnya, lowongan dapat diberi tag aksesibilitas, dan pencari kerja dapat mencantumkan kebutuhan akomodasinya. Dengan begitu, pencari kerja penyandang disabilitas dapat menemukan lowongan yang relevan dan ramah sejak awal.

## 2. Latar Belakang, Tujuan, dan Manfaat

### 2.1 Latar Belakang
Penyandang disabilitas masih menghadapi hambatan dalam mengakses dunia kerja. Portal lowongan umum jarang menyediakan informasi tentang aksesibilitas tempat kerja, proses seleksi yang ramah, atau komitmen perusahaan terhadap inklusivitas. Akibatnya, pencari kerja harus menebak apakah suatu lowongan sesuai dengan kondisinya, sementara perusahaan yang sebenarnya terbuka tidak punya cara yang jelas untuk menunjukkannya.

### 2.2 Tujuan
1. Menyediakan wadah bagi perusahaan untuk mempublikasikan lowongan beserta informasi aksesibilitasnya.
2. Memudahkan pencari kerja menemukan, melamar, dan memantau lamaran dalam satu platform.
3. Menyediakan proses rekrutmen yang terstruktur, mulai dari lamaran hingga wawancara, termasuk pencatatan kebutuhan akomodasi.

### 2.3 Manfaat
- **Pencari kerja:** informasi lowongan yang transparan, proses lamaran dan wawancara yang terpantau.
- **Perusahaan:** jangkauan ke talenta yang beragam, kemudahan mengelola pelamar, dan label inklusif yang dapat dipercaya.
- **Masyarakat:** mendukung kesempatan kerja yang setara (SDG 8 dan SDG 10).

## 3. Pengguna dan Hak Akses

| Role | Deskripsi | Kemampuan utama |
|---|---|---|
| **Pengunjung** | Belum login | Melihat landing page, daftar dan detail lowongan, detail perusahaan, register, login |
| **Job Seeker** | Pencari kerja | Mengelola profil, melamar, memantau lamaran, mengonfirmasi wawancara |
| **Company** | Pemberi lowongan | Mengelola profil perusahaan dan lowongan, memproses pelamar, menjadwalkan wawancara |
| **Admin** | Pengelola platform (akun dibuat lewat seed, tanpa register) | Mengelola user, kategori, dan verifikasi perusahaan |

## 4. Ruang Lingkup

### 4.1 Termasuk (MVP)
Sembilan modul pada Bagian 5, lengkap dengan alur utama pada Bagian 6.

### 4.2 Di luar cakupan
Reset password via email, notifikasi email, chat antar pengguna, pembayaran, rekomendasi lowongan otomatis, dan dashboard analitik lanjutan.

## 5. Spesifikasi Fitur per Modul

### 5.1 Authentication
**Role:** semua

| Fitur | Keterangan |
|---|---|
| Register | Pengguna memilih role Job Seeker atau Company; Job Seeker otomatis mendapat profil kosong, Company mendapat profil perusahaan kosong |
| Login dan logout | Berbasis session; setelah login diarahkan ke area sesuai role |
| Proteksi halaman | Halaman area seeker, company, dan admin hanya dapat diakses role yang sesuai; akses salah menampilkan halaman 403 |
| Akun nonaktif | Akun yang dinonaktifkan admin tidak dapat login |

**Aturan:** email unik, format email valid, password minimal 8 karakter dan disimpan dalam bentuk hash, pesan error jelas.

### 5.2 User Management
**Role:** Admin

| Fitur | Keterangan |
|---|---|
| Daftar user | Dengan pencarian (nama/email), filter role, dan pagination |
| Tambah, edit, hapus user | CRUD lengkap oleh admin |
| Aktif/nonaktif akun | Toggle status akun |
| Akun saya | Semua role dapat mengubah nama, foto, dan password sendiri |

**Aturan:** admin tidak dapat menghapus atau menonaktifkan akunnya sendiri.

### 5.3 Job Seeker Profile Management
**Role:** Job Seeker

| Fitur | Keterangan |
|---|---|
| Biodata | Nama, telepon, tanggal lahir, alamat |
| Pendidikan dan pengalaman | Riwayat singkat dalam teks |
| Skill | Daftar keahlian |
| Informasi disabilitas | Ragam disabilitas dan kebutuhan akomodasi (opsional) |
| CV | Upload dan ganti berkas CV |
| Lihat profil | Halaman ringkasan profil |

**Aturan:** upload CV hanya PDF dengan ukuran maksimal yang ditetapkan (misalnya 2 MB); informasi disabilitas bersifat opsional dan hanya tampil kepada perusahaan yang menerima lamarannya.

### 5.4 Company Management
**Role:** Company, Admin

| Fitur | Keterangan |
|---|---|
| Profil perusahaan | Nama, deskripsi, alamat, website, logo |
| Label inklusif | Checkbox komitmen inklusif dan daftar fasilitas aksesibilitas (misalnya akses kursi roda, juru bahasa isyarat, huruf braille) |
| Halaman publik perusahaan | Profil perusahaan beserta lowongan aktifnya |
| Verifikasi (Admin) | Admin memverifikasi atau mencabut verifikasi perusahaan; perusahaan terverifikasi mendapat badge |
| Hapus perusahaan (Admin) | Beserta data terkait |

**Aturan:** badge inklusif hanya tampil penuh pada perusahaan yang sudah terverifikasi.

### 5.5 Job Category Management
**Role:** Admin (baca oleh Company dan publik)

| Fitur | Keterangan |
|---|---|
| CRUD kategori | Daftar, tambah, edit, hapus |
| Seed awal | Kategori bawaan (misalnya Teknologi, Pendidikan, Kesehatan, Administrasi, Kreatif) |

**Aturan:** kategori yang masih dipakai lowongan tidak dapat dihapus; nama kategori unik.

### 5.6 Job Vacancy Management
**Role:** Company (kelola), semua (lihat)

| Fitur | Keterangan |
|---|---|
| CRUD lowongan | Judul, deskripsi, persyaratan, kategori, tipe kerja (onsite/remote/hybrid), lokasi, rentang gaji, deadline |
| Tag aksesibilitas | Checkbox fasilitas yang tersedia untuk lowongan tersebut |
| Buka/tutup lowongan | Status open atau closed |
| Daftar lowongan publik | Pencarian kata kunci, filter kategori, tipe kerja, dan tag aksesibilitas, serta pagination |
| Detail lowongan | Informasi lengkap, badge perusahaan, tombol **Lamar** |

**Aturan:** lowongan yang melewati deadline atau berstatus closed tidak muncul di daftar publik dan tidak dapat dilamar; Company hanya dapat mengelola lowongannya sendiri.

### 5.7 Job Application Management
**Role:** Job Seeker

| Fitur | Keterangan |
|---|---|
| Form lamar | Memakai CV dari profil atau upload CV baru, plus cover letter |
| Riwayat lamaran | Daftar lamaran beserta status dan tanggal |
| Detail lamaran | Informasi lowongan, status, dan jadwal wawancara bila ada |
| Batalkan lamaran | Hanya selama status masih "Dikirim" |

**Aturan:** wajib login sebagai Job Seeker, satu pengguna hanya boleh melamar satu kali per lowongan, profil minimal harus terisi sebelum melamar.

### 5.8 Recruitment Management
**Role:** Company

| Fitur | Keterangan |
|---|---|
| Daftar pelamar per lowongan | Filter status dan pagination |
| Detail pelamar | Profil seeker, kebutuhan akomodasi, unduh CV, cover letter |
| Ubah status | Dikirim, Direview, Shortlist, Ditolak, Diterima |
| Catatan rekruter | Catatan internal per pelamar |

**Aturan:** Company hanya dapat mengelola pelamar dari lowongannya sendiri; status yang sudah Ditolak atau Diterima tidak dapat diubah kembali.

### 5.9 Interview Management
**Role:** Company (jadwalkan), Job Seeker (konfirmasi)

| Fitur | Keterangan |
|---|---|
| Jadwalkan wawancara | Dari pelamar berstatus Shortlist: tanggal, jam, mode (online/offline), tautan atau lokasi, catatan akomodasi |
| Edit dan batalkan jadwal | Oleh Company |
| Lihat jadwal | Daftar jadwal wawancara untuk Company dan Job Seeker |
| Konfirmasi kehadiran | Job Seeker mengonfirmasi jadwal |

**Aturan:** tanggal wawancara tidak boleh di masa lalu; satu lamaran hanya memiliki satu jadwal aktif.

## 6. Alur Utama

1. **Company** register, melengkapi profil perusahaan, lalu diverifikasi **Admin**.
2. **Company** membuat lowongan lengkap dengan kategori dan tag aksesibilitas.
3. **Job Seeker** register, melengkapi profil dan CV, lalu mencari lowongan dengan filter.
4. **Job Seeker** melamar pada lowongan yang sesuai.
5. **Company** meninjau pelamar dan mengubah status (misalnya ke Shortlist).
6. **Company** menjadwalkan wawancara dan mencatat kebutuhan akomodasi.
7. **Job Seeker** melihat jadwal dan mengonfirmasi kehadiran.
8. **Company** menetapkan hasil akhir (Diterima atau Ditolak); **Job Seeker** melihat hasilnya pada riwayat lamaran.

## 7. Struktur Halaman dan Navigasi

| Area | Pengguna | Halaman utama |
|---|---|---|
| **Publik** | Semua | Landing page, daftar lowongan, detail lowongan, detail perusahaan, login, register |
| **Area Job Seeker** | Job Seeker | Dashboard, profil saya, riwayat lamaran, jadwal wawancara, akun saya |
| **Area Company** | Company | Dashboard, profil perusahaan, kelola lowongan, daftar pelamar, jadwal wawancara, akun saya |
| **Area Admin** | Admin | Dashboard, kelola user, kelola kategori, verifikasi perusahaan |

Seluruh area berada dalam satu aplikasi dengan satu database. Menu navigasi menyesuaikan role, dan setiap halaman area dilindungi pengecekan role.

**Dashboard ringkas:**
- Admin: total user, perusahaan, lowongan, dan lamaran.
- Company: lowongan aktif, total pelamar, wawancara mendatang.
- Job Seeker: jumlah lamaran per status, wawancara mendatang.

**Landing page:** hero dengan nama dan tagline, lowongan terbaru, kategori populer, dan bagian perusahaan inklusif.

## 8. Desain Data

| Tabel | Kolom utama |
|---|---|
| `users` | id, name, email, password, role (admin/seeker/company), is_active, photo |
| `job_seekers` | id, user_id, phone, birthdate, address, education, experience, skills, disability_type, accommodation_needs, cv_file |
| `companies` | id, user_id, name, description, address, website, logo, is_verified, is_inclusive, accessibility_facilities |
| `categories` | id, name |
| `vacancies` | id, company_id, category_id, title, description, requirements, work_type, location, salary_min, salary_max, deadline, accessibility_tags, status (open/closed) |
| `applications` | id, vacancy_id, seeker_id, cv_file, cover_letter, status, recruiter_note, created_at |
| `interviews` | id, application_id, date, time, mode, location_or_link, accommodation_note, status |

**Relasi:**
- `users` 1 : 1 `job_seekers` dan 1 : 1 `companies`
- `companies` 1 : N `vacancies`
- `categories` 1 : N `vacancies`
- `vacancies` 1 : N `applications`
- `job_seekers` 1 : N `applications`
- `applications` 1 : 1 `interviews` (satu jadwal aktif)

## 9. Kebutuhan Non-Fungsional

| Aspek | Ketentuan |
|---|---|
| **Keamanan** | Password di-hash, validasi dan sanitasi seluruh input, perlindungan terhadap SQL injection dan XSS, pengecekan role di setiap halaman terproteksi |
| **Upload file** | Validasi tipe dan ukuran, nama berkas diacak, folder upload tidak dapat dieksekusi |
| **Aksesibilitas** | Label pada setiap form, kontras warna memadai, teks alternatif pada gambar, dapat dinavigasi dengan keyboard |
| **Responsif** | Tampilan dapat digunakan di desktop dan ponsel |
| **Tampilan** | Menggunakan template atau framework CSS standar; desain khusus bukan prioritas |
| **Kinerja** | Daftar panjang memakai pagination |
| **Data demo** | Tersedia data contoh (seed) agar seluruh fitur dapat didemonstrasikan |

## 10. Kesesuaian dengan Ketentuan Responsi

| Ketentuan | Pemenuhan |
|---|---|
| Relevan dengan SDGs | SDG 8 dan SDG 10 |
| Latar belakang, tujuan, manfaat jelas | Bagian 2 |
| Minimal 9 modul terintegrasi | Sembilan modul pada Bagian 5, terhubung lewat alur pada Bagian 6 |
| Seluruh fitur berjalan dan didemonstrasikan | Data seed dan alur end-to-end |

### Deliverable
- Repository GitHub **public** dengan `README.md` (nama dan deskripsi, latar belakang dan tujuan, daftar modul, teknologi, panduan instalasi dan konfigurasi, panduan menjalankan)
- Video presentasi di YouTube, minimal 7 menit, seluruh anggota tampil (profil tim, latar belakang, tujuan dan manfaat, modul dan fitur, demonstrasi, keterkaitan SDGs, kesimpulan)
- Project Showcase (PDF atau tautan desain) yang diunggah publik oleh masing-masing anggota di Instagram atau LinkedIn
- Batas pengumpulan: dua minggu setelah hari asistensi, pukul 23.49 WIB (target internal tim: selesai dalam 2 hari)
