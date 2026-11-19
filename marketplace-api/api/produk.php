<?php
require_once __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    error_respon(405, 'Method tidak diizinkan, gunakan GET');
}

try {
    // ---- Detail produk: ?id=1 ----
    if (isset($_GET['id'])) {
        $id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
        if ($id === false || $id < 1) {
            error_respon(400, 'Parameter id harus berupa angka positif');
        }

        $stmt = $conn->prepare(
            "SELECT p.id_produk, p.nama_produk, p.deskripsi, p.harga, p.stok, p.gambar,
                    k.nama_kategori AS kategori, u.nama AS penjual
             FROM produk p
             JOIN kategori k ON p.id_kategori = k.id_kategori
             JOIN users u    ON p.id_penjual  = u.id_user
             WHERE p.id_produk = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) {
            error_respon(404, "Produk dengan id $id tidak ditemukan");
        }
        respon(200, [
            'status'  => 'success',
            'message' => 'Detail produk berhasil diambil',
            'data'    => format_produk($row)
        ]);
    }

    // ---- Semua produk (opsional filter ?kategori=1) ----
    $sql = "SELECT p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar,
                   k.nama_kategori AS kategori, u.nama AS penjual
            FROM produk p
            JOIN kategori k ON p.id_kategori = k.id_kategori
            JOIN users u    ON p.id_penjual  = u.id_user";

    if (isset($_GET['kategori'])) {
        $kat = filter_var($_GET['kategori'], FILTER_VALIDATE_INT);
        if ($kat === false || $kat < 1) {
            error_respon(400, 'Parameter kategori harus berupa angka positif');
        }
        $stmt = $conn->prepare($sql . " WHERE p.id_kategori = ? ORDER BY p.id_produk");
        $stmt->bind_param('i', $kat);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $rows = $conn->query($sql . " ORDER BY p.id_produk")->fetch_all(MYSQLI_ASSOC);
    }

    $data = array_map('format_produk', $rows);
    respon(200, [
        'status'  => 'success',
        'message' => 'Data produk berhasil diambil',
        'total'   => count($data),
        'data'    => $data
    ]);
} catch (mysqli_sql_exception $e) {
    error_respon(500, 'Terjadi kesalahan pada server');
}
