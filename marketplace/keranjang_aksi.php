<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { redirect('keranjang.php'); }
wajib_role('pembeli');
csrf_cek();

$idUser = (int)$_SESSION['user']['id_user'];
$aksi   = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $idProduk = (int)($_POST['id_produk'] ?? 0);
    $jumlah   = max(1, (int)($_POST['jumlah'] ?? 1));

    $stmt = $koneksi->prepare('SELECT stok, nama_produk FROM produk WHERE id_produk = ?');
    $stmt->bind_param('i', $idProduk);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$p || (int)$p['stok'] < 1) {
        set_flash('danger', 'Produk tidak tersedia.');
        redirect('index.php');
    }

    // Jumlah yang sudah ada di keranjang
    $stmt = $koneksi->prepare('SELECT jumlah FROM keranjang WHERE id_user = ? AND id_produk = ?');
    $stmt->bind_param('ii', $idUser, $idProduk);
    $stmt->execute();
    $ada = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $baru = min((int)$p['stok'], $jumlah + (int)($ada['jumlah'] ?? 0));

    if ($ada) {
        $stmt = $koneksi->prepare('UPDATE keranjang SET jumlah = ? WHERE id_user = ? AND id_produk = ?');
        $stmt->bind_param('iii', $baru, $idUser, $idProduk);
    } else {
        $stmt = $koneksi->prepare('INSERT INTO keranjang (id_user, id_produk, jumlah) VALUES (?,?,?)');
        $stmt->bind_param('iii', $idUser, $idProduk, $baru);
    }
    $stmt->execute();
    $stmt->close();

    set_flash('success', '"' . $p['nama_produk'] . '" ditambahkan ke keranjang.');
    $kembali = $_SERVER['HTTP_REFERER'] ?? '';
    // Hanya redirect balik jika referer berasal dari situs yang sama
    if ($kembali !== '' && parse_url($kembali, PHP_URL_HOST) === ($_SERVER['HTTP_HOST'] ?? '')) {
        header('Location: ' . $kembali);
        exit;
    }
    redirect('keranjang.php');
}

if ($aksi === 'ubah') {
    $idKeranjang = (int)($_POST['id_keranjang'] ?? 0);
    $jumlah      = (int)($_POST['jumlah'] ?? 1);

    $stmt = $koneksi->prepare(
        'SELECT p.stok FROM keranjang k JOIN produk p ON p.id_produk = k.id_produk
         WHERE k.id_keranjang = ? AND k.id_user = ?'
    );
    $stmt->bind_param('ii', $idKeranjang, $idUser);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        if ($jumlah < 1) {
            $stmt = $koneksi->prepare('DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?');
            $stmt->bind_param('ii', $idKeranjang, $idUser);
        } else {
            $jumlah = min($jumlah, (int)$row['stok']);
            $stmt = $koneksi->prepare('UPDATE keranjang SET jumlah = ? WHERE id_keranjang = ? AND id_user = ?');
            $stmt->bind_param('iii', $jumlah, $idKeranjang, $idUser);
        }
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Keranjang diperbarui.');
    }
    redirect('keranjang.php');
}

if ($aksi === 'hapus') {
    $idKeranjang = (int)($_POST['id_keranjang'] ?? 0);
    $stmt = $koneksi->prepare('DELETE FROM keranjang WHERE id_keranjang = ? AND id_user = ?');
    $stmt->bind_param('ii', $idKeranjang, $idUser);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Item dihapus dari keranjang.');
    redirect('keranjang.php');
}

redirect('keranjang.php');
