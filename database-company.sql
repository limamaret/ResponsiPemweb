-- =====================================================================
-- RANGKUL — MIGRASI SISI COMPANY (jalankan SETELAH database.sql)
-- Aman dijalankan berulang: kolom hanya ditambah bila belum ada, dan
-- tabel memakai CREATE TABLE IF NOT EXISTS.
-- Role company di kode autentikasi yang sudah ada bernama 'recruiter'.
-- =====================================================================
USE jobconnect;

-- ---------- helper: tambah kolom hanya jika belum ada ----------
-- users.photo (Akun saya: foto)
SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE users ADD COLUMN photo VARCHAR(255) NULL', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='users' AND COLUMN_NAME='photo');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- companies: profil perusahaan + label inklusif
SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE companies ADD COLUMN description TEXT NULL', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='companies' AND COLUMN_NAME='description');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE companies ADD COLUMN address VARCHAR(255) NULL', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='companies' AND COLUMN_NAME='address');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE companies ADD COLUMN website VARCHAR(255) NULL', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='companies' AND COLUMN_NAME='website');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE companies ADD COLUMN logo VARCHAR(255) NULL', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='companies' AND COLUMN_NAME='logo');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE companies ADD COLUMN is_inclusive TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='companies' AND COLUMN_NAME='is_inclusive');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s = (SELECT IF(COUNT(*)=0, 'ALTER TABLE companies ADD COLUMN accessibility_facilities VARCHAR(500) NULL', 'SELECT 1')
          FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='companies' AND COLUMN_NAME='accessibility_facilities');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- ---------- kategori lowongan (dikelola Admin, dibaca Company & publik) ----------
CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT IGNORE INTO categories (name) VALUES
('Teknologi'), ('Pendidikan'), ('Kesehatan'), ('Administrasi'), ('Kreatif');

-- ---------- profil Job Seeker (KONTRAK dengan sisi Job Seeker) ----------
-- Sisi Company hanya MEMBACA tabel ini. Bila tim Job Seeker sudah punya
-- versi sendiri, pastikan nama kolom di bawah tetap tersedia.
CREATE TABLE IF NOT EXISTS job_seekers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    phone VARCHAR(40) NULL,
    birthdate DATE NULL,
    address VARCHAR(255) NULL,
    education TEXT NULL,
    experience TEXT NULL,
    skills TEXT NULL,
    disability_type VARCHAR(255) NULL,
    accommodation_needs TEXT NULL,
    cv_file VARCHAR(255) NULL,
    CONSTRAINT fk_seekers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- lowongan ----------
CREATE TABLE IF NOT EXISTS vacancies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    work_type ENUM('onsite','remote','hybrid') NOT NULL DEFAULT 'onsite',
    location VARCHAR(150) NOT NULL,
    salary_min DECIMAL(15,2) NULL,
    salary_max DECIMAL(15,2) NULL,
    deadline DATE NOT NULL,
    accessibility_tags VARCHAR(500) NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vacancies_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_vacancies_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ---------- lamaran (KONTRAK dengan sisi Job Seeker) ----------
-- status: submitted=Dikirim, reviewed=Direview, shortlisted=Shortlist,
--         rejected=Ditolak, accepted=Diterima, cancelled=Dibatalkan pelamar
-- cv_file: nama berkas di folder uploads/cv/
CREATE TABLE IF NOT EXISTS applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vacancy_id BIGINT UNSIGNED NOT NULL,
    seeker_id BIGINT UNSIGNED NOT NULL,
    cv_file VARCHAR(255) NULL,
    cover_letter TEXT NULL,
    status ENUM('submitted','reviewed','shortlisted','rejected','accepted','cancelled') NOT NULL DEFAULT 'submitted',
    recruiter_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_vacancy_seeker (vacancy_id, seeker_id),
    CONSTRAINT fk_app_vacancy FOREIGN KEY (vacancy_id) REFERENCES vacancies(id) ON DELETE CASCADE,
    CONSTRAINT fk_app_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------- wawancara ----------
-- status: scheduled=menunggu konfirmasi pelamar, confirmed=dikonfirmasi pelamar,
--         cancelled=dibatalkan perusahaan. Satu lamaran = satu jadwal (UNIQUE).
-- Job Seeker cukup UPDATE interviews SET status='confirmed' pada jadwalnya.
CREATE TABLE IF NOT EXISTS interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL UNIQUE,
    interview_date DATE NOT NULL,
    interview_time TIME NOT NULL,
    mode ENUM('online','offline') NOT NULL DEFAULT 'online',
    location_or_link VARCHAR(255) NOT NULL,
    accommodation_note TEXT NULL,
    status ENUM('scheduled','confirmed','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_interviews_app FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE
) ENGINE=InnoDB;
