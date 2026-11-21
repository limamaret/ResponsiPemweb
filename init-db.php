<?php
/**
 * Initialize database from database.sql - connect without db first
 * Run once: php init-db.php
 */

declare(strict_types=1);

const DB_HOST = 'localhost';
const DB_USER = 'root';
const DB_PASS = '';

echo "🔧 Connecting to MySQL server...\n\n";

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    echo "✅ Connected to MySQL\n\n";
} catch (PDOException $e) {
    echo "❌ Failed to connect to MySQL: " . $e->getMessage() . "\n";
    echo "Make sure MySQL is running (XAMPP/MySQL service)\n";
    exit(1);
}

// Read and execute database.sql
$sql = file_get_contents(__DIR__ . '/database.sql');

// Remove comments
$lines = explode("\n", $sql);
$cleanLines = [];
foreach ($lines as $line) {
    $trimmed = trim($line);
    if ($trimmed === '' || str_starts_with($trimmed, '--')) continue;
    $cleanLines[] = $line;
}
$sql = implode("\n", $cleanLines);

// Split statements by semicolon at end of line
$statements = [];
$buffer = '';
$inParen = 0;

foreach (explode("\n", $sql) as $line) {
    $line = trim($line);
    if ($line === '') continue;
    
    $buffer .= ' ' . $line;
    
    $inParen += substr_count($line, '(') - substr_count($line, ')');
    
    if (str_ends_with($line, ';') && $inParen <= 0) {
        $stmt = trim($buffer);
        if ($stmt !== '') $statements[] = $stmt;
        $buffer = '';
        $inParen = 0;
    }
}

echo "Found " . count($statements) . " statements to execute\n\n";

$executed = 0;
$errors = 0;

foreach ($statements as $idx => $stmt) {
    try {
        $pdo->exec($stmt);
        $executed++;
        $preview = substr($stmt, 0, 80);
        echo "  ✅ " . ($executed) . ": $preview...\n";
    } catch (PDOException $e) {
        // Ignore "already exists" for CREATE DATABASE/TABLE IF NOT EXISTS
        if ($e->getCode() !== '42S01' && strpos($e->getMessage(), 'already exists') === false && $e->getCode() !== '42000') {
            echo "  ❌ Error: " . $e->getMessage() . "\n";
            echo "     SQL: " . substr($stmt, 0, 150) . "...\n";
            $errors++;
        }
    }
}

echo "\n✅ Executed $executed statements\n";
if ($errors > 0) echo "⚠️  $errors errors (mostly ignored)\n";

// Now connect to the jobconnect database and verify
try {
    $pdo2 = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=jobconnect;charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
    
    echo "\n📋 Verifying tables in jobconnect:\n";
    $tables = ['users', 'job_seekers', 'companies', 'categories', 'vacancies', 'applications', 'interviews'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo2->query("SELECT COUNT(*) as cnt FROM `$table`");
            $cnt = $stmt->fetch()['cnt'];
            echo "  $table: $cnt rows\n";
        } catch (PDOException $e) {
            echo "  $table: ❌ " . $e->getMessage() . "\n";
        }
    }
    
} catch (PDOException $e) {
    echo "\n❌ Could not connect to jobconnect database: " . $e->getMessage() . "\n";
}

echo "\n🎉 Database initialization complete!\n";