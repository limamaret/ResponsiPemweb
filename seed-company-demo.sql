-- =====================================================================
-- DATA DEMO SISI COMPANY (opsional). Jalankan setelah database-company.sql
-- Semua akun demo memakai password:  Demo12345
--   Company terverifikasi : hr@inklusi-digital.test
--   Company belum verifikasi : hr@kopi-ramah.test
--   Job Seeker demo        : andi@demo.test, sinta@demo.test
-- Hapus data demo ini sebelum pengumpulan jika tidak diperlukan.
-- =====================================================================
USE jobconnect;
SET @pw = '$2b$12$jFZnpWVD7BmwPz0pYYhDUOzp7oItCVEzb5n438c7N/RGvWv0Rs3Di';

INSERT IGNORE INTO users (name,email,password,role,status) VALUES
('Rani Kusuma','hr@inklusi-digital.test',@pw,'recruiter','active'),
('Budi Santoso','hr@kopi-ramah.test',@pw,'recruiter','active'),
('Andi Pratama','andi@demo.test',@pw,'job_seeker','active'),
('Sinta Wulandari','sinta@demo.test',@pw,'job_seeker','active');

INSERT IGNORE INTO companies (user_id,company_name,phone,verification_status,description,address,website,is_inclusive,accessibility_facilities)
SELECT id,'PT Inklusi Digital Nusantara','081234567890','verified',
 'Perusahaan pengembang perangkat lunak yang percaya bahwa talenta hadir dalam banyak bentuk. Kami membuka kesempatan setara bagi semua kemampuan, termasuk penyandang disabilitas.',
 'Jl. Pemuda No. 12, Semarang, Jawa Tengah','https://inklusi-digital.example',1,'wheelchair,screen_reader,flex_hours,elevator'
FROM users WHERE email='hr@inklusi-digital.test';
INSERT IGNORE INTO companies (user_id,company_name,phone,verification_status,description,address,website,is_inclusive,accessibility_facilities)
SELECT id,'Kopi Ramah Bersama','082233445566','pending',
 'Kedai kopi komunitas yang mempekerjakan barista tuli dan teman netra.',
 'Jl. Gajah Mada No. 5, Purwokerto','',1,'sign_language,quiet'
FROM users WHERE email='hr@kopi-ramah.test';

INSERT IGNORE INTO job_seekers (user_id,phone,birthdate,address,education,experience,skills,disability_type,accommodation_needs)
SELECT id,'081300000001','2001-04-12','Purwokerto, Jawa Tengah','S1 Informatika, Universitas Jenderal Soedirman','Magang web developer 6 bulan','PHP, MySQL, HTML, CSS','Disabilitas daksa (pengguna kursi roda)','Akses kursi roda dan meja kerja yang dapat disesuaikan'
FROM users WHERE email='andi@demo.test';
INSERT IGNORE INTO job_seekers (user_id,phone,birthdate,address,education,experience,skills,disability_type,accommodation_needs)
SELECT id,'081300000002','1999-09-03','Semarang, Jawa Tengah','D3 Desain Komunikasi Visual','Desainer lepas 2 tahun','Figma, Illustrator, Ilustrasi','Disabilitas rungu','Juru bahasa isyarat atau komunikasi tertulis saat wawancara'
FROM users WHERE email='sinta@demo.test';

INSERT INTO vacancies (company_id,category_id,title,description,requirements,work_type,location,salary_min,salary_max,deadline,accessibility_tags,status)
SELECT c.id,(SELECT id FROM categories WHERE name='Teknologi'),'Junior Web Developer',
 'Mengembangkan dan memelihara aplikasi web internal memakai PHP dan MySQL bersama tim kecil yang suportif.',
 'Menguasai dasar PHP, MySQL, HTML dan CSS. Mau belajar. Portofolio proyek kuliah diterima.',
 'hybrid','Semarang',5000000,7500000,DATE_ADD(CURDATE(),INTERVAL 30 DAY),'wheelchair,screen_reader,flex_hours','open'
FROM companies c JOIN users u ON u.id=c.user_id WHERE u.email='hr@inklusi-digital.test';
INSERT INTO vacancies (company_id,category_id,title,description,requirements,work_type,location,salary_min,salary_max,deadline,accessibility_tags,status)
SELECT c.id,(SELECT id FROM categories WHERE name='Kreatif'),'UI/UX Designer',
 'Merancang antarmuka aplikasi yang ramah aksesibilitas dan menguji bersama pengguna difabel.',
 'Menguasai Figma, memahami dasar WCAG, mampu berkomunikasi tertulis dengan baik.',
 'remote','Remote (Indonesia)',6000000,9000000,DATE_ADD(CURDATE(),INTERVAL 21 DAY),'screen_reader,flex_hours,remote','open'
FROM companies c JOIN users u ON u.id=c.user_id WHERE u.email='hr@inklusi-digital.test';
INSERT INTO vacancies (company_id,category_id,title,description,requirements,work_type,location,salary_min,salary_max,deadline,accessibility_tags,status)
SELECT c.id,(SELECT id FROM categories WHERE name='Administrasi'),'Staf Administrasi',
 'Mengelola dokumen dan korespondensi kantor.',
 'Teliti, menguasai aplikasi perkantoran.',
 'onsite','Semarang',4000000,5000000,DATE_ADD(CURDATE(),INTERVAL 14 DAY),'wheelchair,elevator','closed'
FROM companies c JOIN users u ON u.id=c.user_id WHERE u.email='hr@inklusi-digital.test';

-- Lamaran contoh (tanpa berkas CV: unduh CV akan menampilkan pesan "belum ada CV")
INSERT IGNORE INTO applications (vacancy_id,seeker_id,cover_letter,status)
SELECT v.id,s.id,'Saya tertarik pada posisi ini dan siap belajar. Terima kasih atas kesempatannya.','shortlisted'
FROM vacancies v, job_seekers s JOIN users u ON u.id=s.user_id WHERE v.title='Junior Web Developer' AND u.email='andi@demo.test';
INSERT IGNORE INTO applications (vacancy_id,seeker_id,cover_letter,status)
SELECT v.id,s.id,'Saya desainer dengan pengalaman membuat antarmuka yang mudah dipahami.','submitted'
FROM vacancies v, job_seekers s JOIN users u ON u.id=s.user_id WHERE v.title='UI/UX Designer' AND u.email='sinta@demo.test';
INSERT IGNORE INTO applications (vacancy_id,seeker_id,cover_letter,status)
SELECT v.id,s.id,'Saya ingin berkontribusi sebagai developer.','submitted'
FROM vacancies v, job_seekers s JOIN users u ON u.id=s.user_id WHERE v.title='Junior Web Developer' AND u.email='sinta@demo.test';

INSERT IGNORE INTO interviews (application_id,interview_date,interview_time,mode,location_or_link,accommodation_note,status)
SELECT a.id,DATE_ADD(CURDATE(),INTERVAL 5 DAY),'10:00:00','offline','Kantor PT Inklusi Digital Nusantara, Jl. Pemuda No. 12, Semarang (lantai 1, tersedia ramp)','Ruang wawancara di lantai dasar, akses kursi roda tersedia','scheduled'
FROM applications a JOIN job_seekers s ON s.id=a.seeker_id JOIN users u ON u.id=s.user_id JOIN vacancies v ON v.id=a.vacancy_id
WHERE u.email='andi@demo.test' AND v.title='Junior Web Developer';
