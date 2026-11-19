<?php
require_once __DIR__ . '/config.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

header('Content-Type: application/json; charset=utf-8');

function respon(int $kode, array $isi): void {
    http_response_code($kode);
    echo json_encode($isi, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

function error_respon(int $kode, string $pesan): void {
    respon($kode, ['status' => 'error', 'message' => $pesan]);
}

// DECIMAL dari MySQLi berupa string, ubah ke angka agar JSON rapi
function format_produk(array $r): array {
    $r['id_produk'] = (int)$r['id_produk'];
    $r['harga']     = (float)$r['harga'];
    $r['stok']      = (int)$r['stok'];
    return $r;
}

try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    error_respon(500, 'Koneksi database gagal');
}
