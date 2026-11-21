CREATE DATABASE IF NOT EXISTS jobconnect CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE jobconnect;

-- Users table (existing, with photo column added)
CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','job_seeker','company') NOT NULL,
    status ENUM('active','pending','inactive') NOT NULL DEFAULT 'active',
    photo VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Job Seekers profile table
CREATE TABLE IF NOT EXISTS job_seekers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    phone VARCHAR(40) NULL,
    birthdate DATE NULL,
    address TEXT NULL,
    education TEXT NULL,
    experience TEXT NULL,
    skills TEXT NULL,
    disability_type VARCHAR(100) NULL,
    accommodation_needs TEXT NULL,
    cv_file VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_job_seekers_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Companies table (extended per PRD)
CREATE TABLE IF NOT EXISTS companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    name VARCHAR(180) NOT NULL,
    description TEXT NULL,
    address TEXT NULL,
    website VARCHAR(255) NULL,
    logo VARCHAR(255) NULL,
    phone VARCHAR(40) NULL,
    is_verified BOOLEAN NOT NULL DEFAULT FALSE,
    is_inclusive BOOLEAN NOT NULL DEFAULT FALSE,
    accessibility_facilities JSON NULL,
    verification_status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_companies_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Seed categories
INSERT IGNORE INTO categories (name) VALUES
('Teknologi'),
('Pendidikan'),
('Kesehatan'),
('Administrasi'),
('Kreatif'),
('Keuangan'),
('Pemasaran'),
('Manufaktur'),
('Lainnya');