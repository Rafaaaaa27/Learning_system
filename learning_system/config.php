<?php
/**
 * Configuration File - Database Connection
 * Konfigurasi koneksi database menggunakan PDO
 */

// Database credentials
define('DB_HOST', 'localhost:3306');
define('DB_NAME', 'learning_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site configuration
define('SITE_URL', 'http://localhost/learning_system');
define('UPLOAD_DIR', __DIR__ . '/uploads/');

// Create uploads directory if not exists
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
    mkdir(UPLOAD_DIR . 'materi/', 0755, true);
    mkdir(UPLOAD_DIR . 'tugas/', 0755, true);
    mkdir(UPLOAD_DIR . 'jawaban/', 0755, true);
    mkdir(UPLOAD_DIR . 'foto/', 0755, true);
}

// PDO Connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}


// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Asia/Jakarta');
?>