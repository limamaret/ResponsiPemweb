<?php
/**
 * Seed Data Script for Rangkul
 * Run once after database.sql import: php seed.php
 */
require_once __DIR__ . '/config.php';

echo "🌱 Seeding database...\n\n";

try {
    // 1. Categories (already in database.sql, but ensure they exist)
    $categories = [
        'Teknologi', 'Pendidikan', 'Kesehatan', 'Administrasi', 'Kreatif',
        'Keuangan', 'Pemasaran', 'Manufaktur', 'Lainnya'
    ];
    foreach ($categories as $cat) {
        $stmt = $pdo->prepare('INSERT IGNORE INTO categories (name) VALUES (?)');
        $stmt->execute([$cat]);
    }
    echo "✅ Categories seeded\n";

    // 2. Get category IDs
    $catMap = [];
    $stmt = $pdo->query('SELECT id, name FROM categories');
    foreach ($stmt->fetchAll() as $row) {
        $catMap[$row['name']] = $row['id'];
    }

    // 3. Create test users (companies & job seekers)
    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
    
    // Company 1 - Verified & Inclusive
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, "company", "active")');
    $stmt->execute(['HR TechCorp', 'hr@techcorp.id', $passwordHash]);
    $companyUser1 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('INSERT INTO companies (user_id, name, description, address, website, phone, is_verified, is_inclusive, accessibility_facilities, verification_status) VALUES (?, ?, ?, ?, ?, ?, TRUE, TRUE, ?, "verified")');
    $stmt->execute([
        $companyUser1,
        'TechCorp Indonesia',
        'Perusahaan teknologi terkemuka yang fokus pada pengembangan software enterprise dan solusi cloud. Kami berkomitmen menciptakan lingkungan kerja inklusif untuk semua talenta.',
        'Jl. Sudirman No. 123, Jakarta Pusat',
        'https://techcorp.id',
        '021-5550101',
        json_encode(['akses_kursi_roda', 'juru_bahasa_isyarat', 'screen_reader', 'akses_lift', 'toilet_inklusif'])
    ]);
    $company1 = $pdo->lastInsertId();
    echo "✅ Company 1: TechCorp Indonesia (verified, inclusive)\n";

    // Company 2 - Verified, not inclusive
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, "company", "active")');
    $stmt->execute(['HR EduKita', 'hr@edukita.id', $passwordHash]);
    $companyUser2 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('INSERT INTO companies (user_id, name, description, address, website, phone, is_verified, is_inclusive, accessibility_facilities, verification_status) VALUES (?, ?, ?, ?, ?, ?, TRUE, FALSE, ?, "verified")');
    $stmt->execute([
        $companyUser2,
        'EduKita Foundation',
        'Organisasi non-profit yang bergerak di bidang pendidikan dan pelatihan keterampilan digital untuk masyarakat.',
        'Jl. Gatot Subroto No. 45, Jakarta Selatan',
        'https://edukita.org',
        '021-5550202',
        json_encode(['akses_kursi_roda', 'waktu_tambahan'])
    ]);
    $company2 = $pdo->lastInsertId();
    echo "✅ Company 2: EduKita Foundation (verified)\n";

    // Company 3 - Pending verification
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, "company", "pending")');
    $stmt->execute(['HR CreativeHub', 'hr@creativehub.id', $passwordHash]);
    $companyUser3 = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare('INSERT INTO companies (user_id, name, description, address, website, phone, is_verified, is_inclusive, accessibility_facilities, verification_status) VALUES (?, ?, ?, ?, ?, ?, FALSE, TRUE, ?, "pending")');
    $stmt->execute([
        $companyUser3,
        'CreativeHub Agency',
        'Agensi kreatif digital yang menyediakan layanan branding, UI/UX design, dan digital marketing.',
        'Jl. Kemang Raya No. 10, Jakarta Selatan',
        'https://creativehub.id',
        '021-5550303',
        json_encode(['ruang_tenang', 'screen_reader', 'huruf_braille'])
    ]);
    $company3 = $pdo->lastInsertId();
    echo "✅ Company 3: CreativeHub Agency (pending, inclusive)\n";

    // 4. Create Job Seekers
    $seekers = [
        ['Budi Santoso', 'budi@email.com', '081234567890', '1995-03-15', 'Jl. Merdeka No. 10, Bandung', 'S1 Informatika - ITB (2013-2017)', 'Software Engineer di PT XYZ (2017-2020); Senior Developer di TechCorp (2020-sekarang)', 'PHP, Laravel, MySQL, JavaScript, Vue.js, Docker, AWS', null, null, null],
        ['Siti Rahayu', 'siti@email.com', '081298765432', '1998-07-22', 'Jl. Pahlawan No. 5, Surabaya', 'S1 Manajemen - UNAIR (2016-2020)', 'Marketing Specialist di EduKita (2020-sekarang)', 'Digital Marketing, SEO, Content Strategy, Google Analytics, Social Media', 'netra', 'Butuh screen reader dan materi dalam format audio/braille untuk tes tertulis', null],
        ['Ahmad Fauzi', 'ahmad@email.com', '081345678901', '1992-11-08', 'Jl. Diponegoro No. 20, Yogyakarta', 'D3 Teknik Mesin - Politeknik Negeri (2010-2013)', 'Operator Produksi di Manufaktur Jaya (2013-2018); Supervisor Shift (2018-sekarang)', 'Manajemen Produksi, Lean Manufacturing, Safety, Forklift', 'fisik', 'Akses kursi roda ke ruang wawancara dan toilet inklusif', null],
        ['Dewi Lestari', 'dewi@email.com', '081378901234', '2000-01-30', 'Jl. Kartini No. 15, Semarang', 'S1 Desain Komunikasi Visual - ISI Yogyakarta (2018-2022)', 'Junior Designer di CreativeHub (2022-sekarang)', 'Adobe Photoshop, Illustrator, Figma, UI/UX, Branding', null, null, null],
        ['Rizki Pratama', 'rizki@email.com', '081256789012', '1997-09-12', 'Jl. Sudirman No. 88, Jakarta', 'S1 Akuntansi - UI (2015-2019)', 'Junior Accountant di KAP ABC (2019-2021); Accountant di TechCorp (2021-sekarang)', 'Akuntansi, Pajak, Excel Advanced, SAP, Financial Reporting', null, null, null],
    ];

    $seekerIds = [];
    foreach ($seekers as $i => $s) {
        $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, "job_seeker", "active")');
        $stmt->execute([$s[0], $s[1], $passwordHash]);
        $userId = $pdo->lastInsertId();
        
        $stmt = $pdo->prepare('INSERT INTO job_seekers (user_id, phone, birthdate, address, education, experience, skills, disability_type, accommodation_needs, cv_file) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10]]);
        $seekerIds[] = $pdo->lastInsertId();
        echo "✅ Job Seeker " . ($i+1) . ": {$s[0]}" . ($s[8] ? " (disabilitas: {$s[8]})" : "") . "\n";
    }

    // 5. Create Vacancies
    $vacancies = [
        // TechCorp vacancies
        [
            'company_id' => $company1,
            'category_id' => $catMap['Teknologi'],
            'title' => 'Senior Backend Developer (PHP/Laravel)',
            'description' => "Kami mencari Senior Backend Developer yang berpengalaman dengan PHP/Laravel untuk bergabung dengan tim engineering kami. Anda akan bekerja pada pengembangan API, microservices, dan optimasi database untuk platform skala besar.\n\nTanggung jawab:\n- Mengembangkan dan memelihara RESTful API menggunakan Laravel\n- Merancang skema database dan optimasi query\n- Implementasi caching, queue, dan background jobs\n- Code review dan mentoring junior developer\n- Kolaborasi dengan tim frontend dan DevOps",
            'requirements' => "Minimal 4 tahun pengalaman dengan PHP/Laravel\nPengalaman dengan MySQL/PostgreSQL, Redis, Docker\nMemahami konsep microservices, REST API, GraphQL\nPengalaman CI/CD (GitLab CI/GitHub Actions)\nPortfolio/GitHub yang menunjukkan kualitas kode\nBisa berkomunikasi dalam Bahasa Inggris",
            'work_type' => 'hybrid',
            'location' => 'Jakarta Pusat (Hybrid: 3 hari kantor, 2 hari WFH)',
            'salary_min' => 15000000,
            'salary_max' => 25000000,
            'deadline' => date('Y-m-d', strtotime('+30 days')),
            'accessibility_tags' => json_encode(['akses_kursi_roda', 'screen_reader', 'waktu_tambahan']),
            'status' => 'open'
        ],
        [
            'company_id' => $company1,
            'category_id' => $catMap['Teknologi'],
            'title' => 'Frontend Developer (Vue.js/React)',
            'description' => "Bergabung dengan tim frontend kami untuk membangun antarmuka pengguna yang responsif, aksesibel, dan performa tinggi untuk produk-produk enterprise kami.",
            'requirements' => "Minimal 2 tahun pengalaman Vue.js atau React\nMahir JavaScript ES6+, TypeScript, HTML5, CSS3\nPengalaman state management (Pinia/Vuex/Redux)\nPemahaman aksesibilitas web (WCAG 2.1)\nPengalaman testing (Vitest/Jest, Cypress)",
            'work_type' => 'remote',
            'location' => 'Remote (Indonesia)',
            'salary_min' => 12000000,
            'salary_max' => 20000000,
            'deadline' => date('Y-m-d', strtotime('+21 days')),
            'accessibility_tags' => json_encode(['screen_reader', 'waktu_tambahan', 'ruang_tenang']),
            'status' => 'open'
        ],
        [
            'company_id' => $company1,
            'category_id' => $catMap['Teknologi'],
            'title' => 'DevOps Engineer',
            'description' => "Mengelola infrastruktur cloud, pipeline CI/CD, dan observability untuk memastikan keandalan dan skalabilitas platform kami.",
            'requirements' => "Pengalaman AWS/GCP, Kubernetes, Docker\nInfrastructure as Code (Terraform/Ansible)\nCI/CD pipelines, monitoring (Prometheus/Grafana)\nScripting (Bash/Python/Go)",
            'work_type' => 'hybrid',
            'location' => 'Jakarta Pusat',
            'salary_min' => 18000000,
            'salary_max' => 30000000,
            'deadline' => date('Y-m-d', strtotime('+14 days')),
            'accessibility_tags' => json_encode([]),
            'status' => 'open'
        ],
        
        // EduKita vacancies
        [
            'company_id' => $company2,
            'category_id' => $catMap['Pendidikan'],
            'title' => 'Kurikulum Developer - Digital Skills',
            'description' => "Mengembangkan kurikulum dan materi pelatihan keterampilan digital untuk program-program kami yang bertarget pemula hingga menengah.",
            'requirements' => "Latar belakang pendidikan/teknologi\nPengalaman membuat modul/materi pembelajaran\nFamiliar dengan platform LMS\nKemampuan presentasi dan facilitas",
            'work_type' => 'onsite',
            'location' => 'Jakarta Selatan',
            'salary_min' => 8000000,
            'salary_max' => 12000000,
            'deadline' => date('Y-m-d', strtotime('+25 days')),
            'accessibility_tags' => json_encode(['akses_kursi_roda', 'waktu_tambahan']),
            'status' => 'open'
        ],
        [
            'company_id' => $company2,
            'category_id' => $catMap['Administrasi'],
            'title' => 'Program Coordinator',
            'description' => "Mengelola operasional program pelatihan, koordinasi dengan instruktur, dan memastikan pelaksanaan sesuai jadwal dan standar kualitas.",
            'requirements' => "Pengalaman administrasi/koordinasi program\nOrganisir, detail-oriented, komunikatif\nMenguasai Google Workspace, Notion, Zoom",
            'work_type' => 'hybrid',
            'location' => 'Jakarta Selatan',
            'salary_min' => 7000000,
            'salary_max' => 10000000,
            'deadline' => date('Y-m-d', strtotime('+10 days')),
            'accessibility_tags' => json_encode(['akses_kursi_roda']),
            'status' => 'open'
        ],

        // CreativeHub vacancies
        [
            'company_id' => $company3,
            'category_id' => $catMap['Kreatif'],
            'title' => 'UI/UX Designer',
            'description' => "Merancang pengalaman pengguna dan antarmuka untuk klien-klien enterprise dan startup. Terlibat dari riset, wireframe, prototype, hingga design system.",
            'requirements' => "Portfolio UI/UX yang kuat (Figma)\nPengalaman design system, prototyping, usability testing\nPemahaman aksesibilitas desain\nKemampuan kolaborasi dengan developer",
            'work_type' => 'onsite',
            'location' => 'Jakarta Selatan',
            'salary_min' => 10000000,
            'salary_max' => 18000000,
            'deadline' => date('Y-m-d', strtotime('+18 days')),
            'accessibility_tags' => json_encode(['ruang_tenang', 'screen_reader', 'huruf_braille']),
            'status' => 'open'
        ],
        [
            'company_id' => $company3,
            'category_id' => $catMap['Pemasaran'],
            'title' => 'Digital Marketing Specialist',
            'description' => "Mengelola kampanye digital marketing end-to-end untuk klien agensi: paid ads, SEO, content, email marketing, analytics.",
            'requirements' => "Pengalaman Google Ads, Meta Ads, GA4\nKemampuan copywriting dan content strategy\nAnalitis, data-driven\nSertifikasi Google/Meta Ads diutamakan",
            'work_type' => 'hybrid',
            'location' => 'Jakarta Selatan',
            'salary_min' => 9000000,
            'salary_max' => 14000000,
            'deadline' => date('Y-m-d', strtotime('+12 days')),
            'accessibility_tags' => json_encode(['ruang_tenang']),
            'status' => 'open'
        ],

        // Expired vacancy (for testing)
        [
            'company_id' => $company1,
            'category_id' => $catMap['Teknologi'],
            'title' => 'QA Engineer (EXPIRED)',
            'description' => 'Lowongan ini sudah kedaluwarsa untuk testing.',
            'requirements' => 'Testing',
            'work_type' => 'onsite',
            'location' => 'Jakarta',
            'salary_min' => 10000000,
            'salary_max' => 15000000,
            'deadline' => date('Y-m-d', strtotime('-5 days')),
            'accessibility_tags' => json_encode([]),
            'status' => 'open'
        ],
        // Closed vacancy
        [
            'company_id' => $company2,
            'category_id' => $catMap['Pendidikan'],
            'title' => 'Instruktur Pelatihan (CLOSED)',
            'description' => 'Lowongan ditutup manual.',
            'requirements' => 'Mengajar',
            'work_type' => 'onsite',
            'location' => 'Jakarta',
            'salary_min' => 8000000,
            'salary_max' => 12000000,
            'deadline' => date('Y-m-d', strtotime('+30 days')),
            'accessibility_tags' => json_encode([]),
            'status' => 'closed'
        ],
    ];

    $vacancyIds = [];
    foreach ($vacancies as $i => $v) {
        $stmt = $pdo->prepare('INSERT INTO vacancies (company_id, category_id, title, description, requirements, work_type, location, salary_min, salary_max, deadline, accessibility_tags, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $v['company_id'], $v['category_id'], $v['title'], $v['description'],
            $v['requirements'], $v['work_type'], $v['location'],
            $v['salary_min'], $v['salary_max'], $v['deadline'],
            $v['accessibility_tags'], $v['status']
        ]);
        $vacancyIds[] = $pdo->lastInsertId();
        echo "✅ Vacancy " . ($i+1) . ": {$v['title']} ({$v['status']})\n";
    }

    // 6. Create Applications (some seekers apply to some vacancies)
    $applications = [
        // Budi applies to TechCorp Senior Backend (vacancy 0)
        ['vacancy_id' => $vacancyIds[0], 'seeker_id' => $seekerIds[0], 'cv_file' => 'cv_1_1234567890_abcdef12.pdf', 'cover_letter' => 'Saya sangat tertarik dengan posisi Senior Backend Developer di TechCorp. Dengan 6+ tahun pengalaman PHP/Laravel, saya percaya bisa berkontribusi signifikan untuk tim engineering. Pengalaman saya dengan microservices, Docker, dan AWS sangat relevan dengan kebutuhan ini.', 'status' => 'Shortlist'],
        // Budi applies to TechCorp Frontend (vacancy 1)
        ['vacancy_id' => $vacancyIds[1], 'seeker_id' => $seekerIds[0], 'cv_file' => 'cv_1_1234567890_abcdef12.pdf', 'cover_letter' => 'Meskipun fokus saya backend, saya memiliki pengalaman Vue.js 2 tahun dan ingin bertransisi ke fullstack. Saya antusias belajar dan berkontribusi di tim frontend TechCorp.', 'status' => 'Direview'],
        // Siti applies to EduKita Kurikulum (vacancy 3)
        ['vacancy_id' => $vacancyIds[3], 'seeker_id' => $seekerIds[1], 'cv_file' => 'cv_2_1234567891_fedcba21.pdf', 'cover_letter' => 'Sebagai penyandang disabilitas netra, saya memiliki pengalaman unik dalam mengakses materi pembelajaran digital. Saya ingin berkontribusi mengembangkan kurikulum yang inklusif dan aksesibel bagi semua peserta didik.', 'status' => 'Dikirim'],
        // Ahmad applies to TechCorp DevOps (vacancy 2) - not qualified but testing
        ['vacancy_id' => $vacancyIds[2], 'seeker_id' => $seekerIds[2], 'cv_file' => 'cv_3_1234567892_123abcde.pdf', 'cover_letter' => 'Saya memiliki background manufaktur tapi sedang belajar DevOps. Saya sangat termotivasi untuk belajar dan berkontribusi.', 'status' => 'Ditolak'],
        // Dewi applies to CreativeHub UI/UX (vacancy 6)
        ['vacancy_id' => $vacancyIds[6], 'seeker_id' => $seekerIds[3], 'cv_file' => 'cv_4_1234567893_abc123de.pdf', 'cover_letter' => 'Sebagai lulusan Desain Komunikasi Visual dengan pengalaman di CreativeHub, saya sangat kenal budaya dan klien perusahaan. Saya siap berkontribusi penuh sebagai UI/UX Designer.', 'status' => 'Diterima'],
        // Rizki applies to TechCorp DevOps (vacancy 2)
        ['vacancy_id' => $vacancyIds[2], 'seeker_id' => $seekerIds[4], 'cv_file' => 'cv_5_1234567894_def456ab.pdf', 'cover_letter' => 'Background akuntansi tapi minat besar di DevOps. Saya sudah belajar Docker, Kubernetes, dan AWS secara mandiri. Saya fast learner dan detail-oriented.', 'status' => 'Dikirim'],
        // Rizki applies to EduKita Program Coordinator (vacancy 4)
        ['vacancy_id' => $vacancyIds[4], 'seeker_id' => $seekerIds[4], 'cv_file' => 'cv_5_1234567894_def456ab.pdf', 'cover_letter' => 'Pengalaman administrasi keuangan dan koordinasi tim di KAP dan TechCorp membuat saya cocok untuk role Program Coordinator.', 'status' => 'Direview'],
    ];

    $appIds = [];
    foreach ($applications as $i => $app) {
        $stmt = $pdo->prepare('INSERT INTO applications (vacancy_id, seeker_id, cv_file, cover_letter, status, recruiter_note) VALUES (?, ?, ?, ?, ?, ?)');
        $note = match($app['status']) {
            'Direview' => 'CV masuk, akan direview tim teknis minggu depan.',
            'Shortlist' => 'Lolos screening administratif. Jadwalkan wawancara teknis.',
            'Ditolak' => 'Pengalaman kurang relevan dengan kebutuhan posisi.',
            'Diterima' => 'Portfolio dan wawancara sangat baik. Siap join 1 Agustus.',
            default => null
        };
        $stmt->execute([
            $app['vacancy_id'], $app['seeker_id'], $app['cv_file'],
            $app['cover_letter'], $app['status'], $note
        ]);
        $appIds[] = $pdo->lastInsertId();
        echo "✅ Application " . ($i+1) . ": Seeker {$app['seeker_id']} → Vacancy {$app['vacancy_id']} ({$app['status']})\n";
    }

    // 7. Create Interviews
    $interviews = [
        // Budi (app 0) - Shortlist → Scheduled interview
        ['application_id' => $appIds[0], 'date' => date('Y-m-d', strtotime('+3 days')), 'time' => '10:00:00', 'mode' => 'online', 'location_or_link' => 'https://meet.google.com/abc-defg-hij', 'accommodation_note' => null, 'status' => 'Scheduled'],
        // Dewi (app 4) - Diterima → Confirmed interview (already done)
        ['application_id' => $appIds[4], 'date' => date('Y-m-d', strtotime('-5 days')), 'time' => '14:00:00', 'mode' => 'offline', 'location_or_link' => 'CreativeHub Office, Jl. Kemang Raya No. 10', 'accommodation_note' => null, 'status' => 'Completed'],
        // Rizki (app 5) - Dikirim → not yet scheduled
        // Rizki (app 6) - Direview → not yet scheduled
        // Siti (app 2) - Dikirim → not yet scheduled
        // Budi (app 1) - Direview → not yet scheduled
    ];

    foreach ($interviews as $i => $iv) {
        $stmt = $pdo->prepare('INSERT INTO interviews (application_id, date, time, mode, location_or_link, accommodation_note, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute([
            $iv['application_id'], $iv['date'], $iv['time'],
            $iv['mode'], $iv['location_or_link'], $iv['accommodation_note'], $iv['status']
        ]);
        echo "✅ Interview " . ($i+1) . ": App {$iv['application_id']} on {$iv['date']} {$iv['time']} ({$iv['status']})\n";
    }

    echo "\n🎉 Seeding completed successfully!\n\n";
    echo "=== TEST ACCOUNTS ===\n";
    echo "Password for all: password123\n\n";
    echo "COMPANIES:\n";
    echo "  hr@techcorp.id      - TechCorp Indonesia (verified, inclusive)\n";
    echo "  hr@edukita.id       - EduKita Foundation (verified)\n";
    echo "  hr@creativehub.id   - CreativeHub Agency (pending, inclusive)\n\n";
    echo "JOB SEEKERS:\n";
    echo "  budi@email.com      - Budi Santoso (Senior Dev, full profile)\n";
    echo "  siti@email.com      - Siti Rahayu (Marketing, netra, needs screen reader)\n";
    echo "  ahmad@email.com     - Ahmad Fauzi (Produksi, fisik, wheelchair access)\n";
    echo "  dewi@email.com      - Dewi Lestari (Designer, CreativeHub employee)\n";
    echo "  rizki@email.com     - Rizki Pratama (Accountant, applying to DevOps/Coord)\n\n";
    echo "VACANCIES: " . count($vacancyIds) . " total (" . count(array_filter($vacancies, fn($v) => $v['status']==='open' && strtotime($v['deadline']) > time())) . " active)\n";
    echo "APPLICATIONS: " . count($appIds) . "\n";
    echo "INTERVIEWS: " . count($interviews) . "\n";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}