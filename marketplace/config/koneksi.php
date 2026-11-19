<?php
/**
 * CONTOH konfigurasi koneksi database.
 * Cara pakai: salin file ini menjadi "koneksi.php" lalu isi sesuai hosting/lokal Anda.
 * File "koneksi.php" TIDAK ikut di-upload ke GitHub (lihat .gitignore).
 */

define('DB_HOST', 'localhost');          // InfinityFree: contoh sql123.infinityfree.com
define('DB_USER', 'root');               // InfinityFree: contoh if0_12345678
define('DB_PASS', '');                   // isi password database Anda
define('DB_NAME', 'marketplace');        // InfinityFree: contoh if0_12345678_marketplace

// Folder aplikasi relatif terhadap domain.
// Di InfinityFree (diletakkan langsung di htdocs) -> ''
// Di XAMPP (htdocs/marketplace)                   -> '/marketplace'
define('BASE_URL', '/marketplace');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $koneksi->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    http_response_code(500);
    die('Koneksi database gagal. Periksa file config/koneksi.php.');
}
