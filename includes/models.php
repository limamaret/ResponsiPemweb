<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

/**
 * Generic query helpers
 */
function db_fetch_one(string $sql, array $params = []): ?array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() ?: null;
}

function db_fetch_all(string $sql, array $params = []): array {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function db_execute(string $sql, array $params = []): bool {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}

function db_last_insert_id(): int {
    global $pdo;
    return (int)$pdo->lastInsertId();
}

function db_transaction(callable $callback) {
    global $pdo;
    $pdo->beginTransaction();
    try {
        $result = $callback($pdo);
        $pdo->commit();
        return $result;
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        throw $e;
    }
}

/**
 * Job Seekers
 */
function seeker_get_by_user_id(int $userId): ?array {
    return db_fetch_one('SELECT * FROM job_seekers WHERE user_id = ?', [$userId]);
}

function seeker_create(int $userId, array $data): int {
    $sql = 'INSERT INTO job_seekers (user_id, phone, birthdate, address, education, experience, skills, disability_type, accommodation_needs, cv_file)
            VALUES (?,?,?,?,?,?,?,?,?,?)';
    db_execute($sql, [
        $userId,
        $data['phone'] ?? null,
        $data['birthdate'] ?? null,
        $data['address'] ?? null,
        $data['education'] ?? null,
        $data['experience'] ?? null,
        $data['skills'] ?? null,
        $data['disability_type'] ?? null,
        $data['accommodation_needs'] ?? null,
        $data['cv_file'] ?? null,
    ]);
    return db_last_insert_id();
}

function seeker_update(int $userId, array $data): bool {
    $fields = [];
    $params = [];
    $allowed = ['phone','birthdate','address','education','experience','skills','disability_type','accommodation_needs','cv_file'];
    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $fields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }
    if (empty($fields)) return false;
    $params[] = $userId;
    $sql = 'UPDATE job_seekers SET ' . implode(', ', $fields) . ' WHERE user_id = ?';
    return db_execute($sql, $params);
}

function seeker_has_minimal_profile(int $userId): bool {
    $seeker = seeker_get_by_user_id($userId);
    if (!$seeker) return false;
    // Minimal: phone OR address OR education OR experience OR skills
    return !empty($seeker['phone']) || !empty($seeker['address']) || !empty($seeker['education']) || !empty($seeker['experience']) || !empty($seeker['skills']);
}

/**
 * Categories
 */
function category_get_all(): array {
    return db_fetch_all('SELECT * FROM categories ORDER BY name');
}

function category_get_by_id(int $id): ?array {
    return db_fetch_one('SELECT * FROM categories WHERE id = ?', [$id]);
}

/**
 * Vacancies (public/browse for seekers)
 */
function vacancy_get_public_list(array $filters = [], int $limit = 10, int $offset = 0): array {
    $where = ['v.status = "open"', 'v.deadline >= CURDATE()'];
    $params = [];

    if (!empty($filters['keyword'])) {
        $where[] = '(v.title LIKE ? OR v.description LIKE ? OR c.name LIKE ?)';
        $kw = '%' . $filters['keyword'] . '%';
        $params = array_merge($params, [$kw, $kw, $kw]);
    }
    if (!empty($filters['category_id'])) {
        $where[] = 'v.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['work_type'])) {
        $where[] = 'v.work_type = ?';
        $params[] = $filters['work_type'];
    }
    if (!empty($filters['accessibility_tags']) && is_array($filters['accessibility_tags'])) {
        // MySQL JSON_CONTAINS for each tag
        foreach ($filters['accessibility_tags'] as $tag) {
            $where[] = 'JSON_CONTAINS(v.accessibility_tags, ?)';
            $params[] = json_encode($tag);
        }
    }

    $sql = 'SELECT v.*, c.name AS company_name, c.logo AS company_logo, c.is_verified, c.is_inclusive,
                   cat.name AS category_name
            FROM vacancies v
            JOIN companies c ON v.company_id = c.id
            JOIN categories cat ON v.category_id = cat.id
            WHERE ' . implode(' AND ', $where) . '
            ORDER BY v.created_at DESC
            LIMIT ? OFFSET ?';
    $params[] = $limit;
    $params[] = $offset;
    return db_fetch_all($sql, $params);
}

function vacancy_count_public(array $filters = []): int {
    $where = ['v.status = "open"', 'v.deadline >= CURDATE()'];
    $params = [];

    if (!empty($filters['keyword'])) {
        $where[] = '(v.title LIKE ? OR v.description LIKE ? OR c.name LIKE ?)';
        $kw = '%' . $filters['keyword'] . '%';
        $params = array_merge($params, [$kw, $kw, $kw]);
    }
    if (!empty($filters['category_id'])) {
        $where[] = 'v.category_id = ?';
        $params[] = $filters['category_id'];
    }
    if (!empty($filters['work_type'])) {
        $where[] = 'v.work_type = ?';
        $params[] = $filters['work_type'];
    }
    if (!empty($filters['accessibility_tags']) && is_array($filters['accessibility_tags'])) {
        foreach ($filters['accessibility_tags'] as $tag) {
            $where[] = 'JSON_CONTAINS(v.accessibility_tags, ?)';
            $params[] = json_encode($tag);
        }
    }

    $sql = 'SELECT COUNT(*) AS total FROM vacancies v
            JOIN companies c ON v.company_id = c.id
            WHERE ' . implode(' AND ', $where);
    $row = db_fetch_one($sql, $params);
    return (int)($row['total'] ?? 0);
}

function vacancy_get_public_detail(int $id): ?array {
    $sql = 'SELECT v.*, c.name AS company_name, c.logo AS company_logo, c.is_verified, c.is_inclusive,
                   c.accessibility_facilities AS company_accessibility, c.address AS company_address,
                   cat.name AS category_name
            FROM vacancies v
            JOIN companies c ON v.company_id = c.id
            JOIN categories cat ON v.category_id = cat.id
            WHERE v.id = ? AND v.status = "open" AND v.deadline >= CURDATE()';
    return db_fetch_one($sql, [$id]);
}

/**
 * Applications (Job Seeker side)
 */
function application_create(int $vacancyId, int $seekerId, string $cvFile, ?string $coverLetter): int {
    $sql = 'INSERT INTO applications (vacancy_id, seeker_id, cv_file, cover_letter, status)
            VALUES (?,?,?,?,"Dikirim")';
    db_execute($sql, [$vacancyId, $seekerId, $cvFile, $coverLetter]);
    return db_last_insert_id();
}

function application_exists(int $vacancyId, int $seekerId): bool {
    $row = db_fetch_one('SELECT 1 FROM applications WHERE vacancy_id = ? AND seeker_id = ?', [$vacancyId, $seekerId]);
    return $row !== null;
}

function application_get_by_seeker(int $seekerId, int $limit = 10, int $offset = 0): array {
    $sql = 'SELECT a.*, v.title AS vacancy_title, v.work_type, v.location,
                   c.name AS company_name, c.logo AS company_logo
            FROM applications a
            JOIN vacancies v ON a.vacancy_id = v.id
            JOIN companies c ON v.company_id = c.id
            WHERE a.seeker_id = ?
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?';
    return db_fetch_all($sql, [$seekerId, $limit, $offset]);
}

function application_count_by_seeker(int $seekerId): int {
    $row = db_fetch_one('SELECT COUNT(*) AS total FROM applications WHERE seeker_id = ?', [$seekerId]);
    return (int)($row['total'] ?? 0);
}

function application_get_detail(int $applicationId, int $seekerId): ?array {
    $sql = 'SELECT a.*, v.title AS vacancy_title, v.description, v.requirements, v.work_type, v.location,
                   v.salary_min, v.salary_max, v.deadline, v.accessibility_tags,
                   c.name AS company_name, c.logo AS company_logo, c.is_verified, c.is_inclusive,
                   i.id AS interview_id, i.date AS interview_date, i.time AS interview_time,
                   i.mode AS interview_mode, i.location_or_link, i.accommodation_note, i.status AS interview_status
            FROM applications a
            JOIN vacancies v ON a.vacancy_id = v.id
            JOIN companies c ON v.company_id = c.id
            LEFT JOIN interviews i ON i.application_id = a.id
            WHERE a.id = ? AND a.seeker_id = ?';
    return db_fetch_one($sql, [$applicationId, $seekerId]);
}

function application_cancel(int $applicationId, int $seekerId): bool {
    // Only allow cancel if status = 'Dikirim'
    $sql = 'UPDATE applications SET status = "Dibatalkan" WHERE id = ? AND seeker_id = ? AND status = "Dikirim"';
    return db_execute($sql, [$applicationId, $seekerId]);
}

function application_count_by_status(int $seekerId): array {
    $result = ['Dikirim'=>0,'Direview'=>0,'Shortlist'=>0,'Diterima'=>0,'Ditolak'=>0,'Dibatalkan'=>0];
    $all = db_fetch_all('
        SELECT status, COUNT(*) AS cnt
        FROM applications
        WHERE seeker_id = ?
        GROUP BY status
    ', [$seekerId]);
    foreach ($all as $row) {
        $result[$row['status']] = (int)$row['cnt'];
    }
    return $result;
}

/**
 * Interviews (Job Seeker side)
 */
function interview_get_by_seeker(int $seekerId, ?string $filter = null): array {
    $sql = 'SELECT i.*, a.id AS application_id, v.title AS vacancy_title, c.name AS company_name, c.logo AS company_logo
            FROM interviews i
            JOIN applications a ON i.application_id = a.id
            JOIN vacancies v ON a.vacancy_id = v.id
            JOIN companies c ON v.company_id = c.id
            WHERE a.seeker_id = ?';
    $params = [$seekerId];
    if ($filter === 'upcoming') {
        $sql .= ' AND i.date >= CURDATE() AND i.status IN ("Scheduled","Confirmed")';
    } elseif ($filter === 'past') {
        $sql .= ' AND (i.date < CURDATE() OR i.status IN ("Cancelled","Completed"))';
    }
    $sql .= ' ORDER BY i.date ASC, i.time ASC';
    return db_fetch_all($sql, $params);
}

function interview_get_detail(int $interviewId, int $seekerId): ?array {
    $sql = 'SELECT i.*, a.id AS application_id, v.title AS vacancy_title, c.name AS company_name
            FROM interviews i
            JOIN applications a ON i.application_id = a.id
            JOIN vacancies v ON a.vacancy_id = v.id
            JOIN companies c ON v.company_id = c.id
            WHERE i.id = ? AND a.seeker_id = ?';
    return db_fetch_one($sql, [$interviewId, $seekerId]);
}

function interview_confirm(int $interviewId, int $seekerId): bool {
    $sql = 'UPDATE interviews i
            JOIN applications a ON i.application_id = a.id
            SET i.status = "Confirmed"
            WHERE i.id = ? AND a.seeker_id = ? AND i.status = "Scheduled"';
    return db_execute($sql, [$interviewId, $seekerId]);
}

/**
 * Dashboard stats for Job Seeker
 */
function seeker_dashboard_stats(int $seekerId): array {
    $appStats = application_count_by_status($seekerId);
    $upcomingInterviews = interview_get_by_seeker($seekerId, 'upcoming');
    return [
        'applications' => $appStats,
        'upcoming_interviews' => array_slice($upcomingInterviews, 0, 3),
        'total_applications' => array_sum($appStats),
    ];
}

/**
 * File upload helper
 */
function upload_cv(array $file, int $userId): array {
    $allowedTypes = ['application/pdf'];
    $maxSize = 2 * 1024 * 1024; // 2MB

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'File tidak valid.'];
    }
    if (!in_array($file['type'], $allowedTypes, true)) {
        return ['success' => false, 'error' => 'Hanya file PDF yang diizinkan.'];
    }
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Ukuran file maksimal 2 MB.'];
    }

    $ext = 'pdf';
    $filename = 'cv_' . $userId . '_' . time() . '_' . bin2hex(random_bytes(8)) . '.' . $ext;
    $uploadDir = __DIR__ . '/../../uploads/cv/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    $dest = $uploadDir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'error' => 'Gagal menyimpan file.'];
    }

    return ['success' => true, 'filename' => $filename];
}

function delete_cv_file(?string $filename): void {
    if ($filename) {
        $path = __DIR__ . '/../../uploads/cv/' . $filename;
        if (is_file($path)) @unlink($path);
    }
}