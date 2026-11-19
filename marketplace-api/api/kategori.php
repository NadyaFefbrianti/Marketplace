<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    error_respon(405, 'Method tidak diizinkan, gunakan GET');
}

try {
    $result = $conn->query(
        "SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) AS jumlah_produk
         FROM kategori k
         LEFT JOIN produk p ON p.id_kategori = k.id_kategori
         GROUP BY k.id_kategori, k.nama_kategori
         ORDER BY k.nama_kategori"
    );
    $data = $result->fetch_all(MYSQLI_ASSOC);
    foreach ($data as &$d) {
        $d['id_kategori']   = (int)$d['id_kategori'];
        $d['jumlah_produk'] = (int)$d['jumlah_produk'];
    }
    unset($d);

    respon(200, [
        'status'  => 'success',
        'message' => 'Data kategori berhasil diambil',
        'total'   => count($data),
        'data'    => $data
    ]);
} catch (mysqli_sql_exception $e) {
    error_respon(500, 'Terjadi kesalahan pada server');
}
