USE jobconnect;

-- Vacancies table
CREATE TABLE IF NOT EXISTS vacancies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    company_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    requirements TEXT NOT NULL,
    work_type ENUM('onsite','remote','hybrid') NOT NULL,
    location VARCHAR(255) NOT NULL,
    salary_min BIGINT UNSIGNED NULL,
    salary_max BIGINT UNSIGNED NULL,
    deadline DATE NOT NULL,
    accessibility_tags JSON NULL,
    status ENUM('open','closed') NOT NULL DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_vacancies_company FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE,
    CONSTRAINT fk_vacancies_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_vacancies_status_deadline (status, deadline),
    INDEX idx_vacancies_category (category_id),
    INDEX idx_vacancies_company (company_id)
) ENGINE=InnoDB;

-- Applications table
CREATE TABLE IF NOT EXISTS applications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    vacancy_id BIGINT UNSIGNED NOT NULL,
    seeker_id BIGINT UNSIGNED NOT NULL,
    cv_file VARCHAR(255) NOT NULL,
    cover_letter TEXT NULL,
    status ENUM('Dikirim','Direview','Shortlist','Diterima','Ditolak','Dibatalkan') NOT NULL DEFAULT 'Dikirim',
    recruiter_note TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_applications_vacancy FOREIGN KEY (vacancy_id) REFERENCES vacancies(id) ON DELETE CASCADE,
    CONSTRAINT fk_applications_seeker FOREIGN KEY (seeker_id) REFERENCES job_seekers(id) ON DELETE CASCADE,
    UNIQUE KEY uk_application_vacancy_seeker (vacancy_id, seeker_id),
    INDEX idx_applications_seeker (seeker_id),
    INDEX idx_applications_vacancy (vacancy_id),
    INDEX idx_applications_status (status)
) ENGINE=InnoDB;

-- Interviews table
CREATE TABLE IF NOT EXISTS interviews (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    application_id BIGINT UNSIGNED NOT NULL UNIQUE,
    date DATE NOT NULL,
    time TIME NOT NULL,
    mode ENUM('online','offline') NOT NULL,
    location_or_link VARCHAR(500) NOT NULL,
    accommodation_note TEXT NULL,
    status ENUM('Scheduled','Confirmed','Cancelled','Completed') NOT NULL DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_interviews_application FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    INDEX idx_interviews_date (date),
    INDEX idx_interviews_status (status)
) ENGINE=InnoDB;