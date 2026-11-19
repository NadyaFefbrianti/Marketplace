<?php
require_once __DIR__ . '/../includes/functions.php';
wajib_role('penjual');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('penjual/produk.php'); }
csrf_cek();

$id        = (int)($_POST['id'] ?? 0);
$idPenjual = (int)$_SESSION['user']['id_user'];

$stmt = $koneksi->prepare('SELECT gambar FROM produk WHERE id_produk = ? AND id_penjual = ?');
$stmt->bind_param('ii', $id, $idPenjual);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('penjual/produk.php');
}

try {
    $stmt = $koneksi->prepare('DELETE FROM produk WHERE id_produk = ? AND id_penjual = ?');
    $stmt->bind_param('ii', $id, $idPenjual);
    $stmt->execute();
    $stmt->close();
    hapus_gambar($row['gambar']);
    set_flash('success', 'Produk berhasil dihapus.');
} catch (mysqli_sql_exception $ex) {
    // Foreign key RESTRICT: produk sudah pernah dipesan
    set_flash('warning', 'Produk sudah pernah dipesan sehingga tidak bisa dihapus. Ubah stok menjadi 0 untuk menonaktifkannya.');
}
redirect('penjual/produk.php');
