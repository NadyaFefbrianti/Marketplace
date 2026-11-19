<?php
$judul = 'Checkout';
require_once __DIR__ . '/includes/functions.php';
wajib_role('pembeli');

$idUser = (int)$_SESSION['user']['id_user'];

/* Ambil isi keranjang */
function ambil_keranjang(mysqli $db, int $idUser, bool $kunci = false): array
{
    $sql = 'SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar
            FROM keranjang k JOIN produk p ON p.id_produk = k.id_produk
            WHERE k.id_user = ?' . ($kunci ? ' FOR UPDATE' : '');
    $stmt = $db->prepare($sql);
    $stmt->bind_param('i', $idUser);
    $stmt->execute();
    $hasil = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $hasil;
}

$error = [];
$form  = [
    'nama_penerima'     => $_SESSION['user']['nama'] ?? '',
    'no_hp_penerima'    => $_SESSION['user']['no_hp'] ?? '',
    'alamat_pengiriman' => $_SESSION['user']['alamat'] ?? '',
    'catatan'           => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    foreach ($form as $k => $_) { $form[$k] = trim($_POST[$k] ?? ''); }

    if ($form['nama_penerima'] === '')     $error[] = 'Nama penerima wajib diisi.';
    if ($form['no_hp_penerima'] === '')    $error[] = 'No. HP penerima wajib diisi.';
    if ($form['alamat_pengiriman'] === '') $error[] = 'Alamat pengiriman wajib diisi.';

    if (!$error) {
        try {
            $koneksi->begin_transaction();

            // 1) Kunci baris keranjang + stok produk selama transaksi
            $item = ambil_keranjang($koneksi, $idUser, true);
            if (!$item) { throw new RuntimeException('Keranjang kosong.'); }

            $total = 0;
            foreach ($item as $i) {
                $stmt = $koneksi->prepare('SELECT stok FROM produk WHERE id_produk = ? FOR UPDATE');
                $stmt->bind_param('i', $i['id_produk']);
                $stmt->execute();
                $stmt->bind_result($stokSkrg);
                $stmt->fetch();
                $stmt->close();
                $stokSkrg = (int)$stokSkrg;
                if ($stokSkrg < (int)$i['jumlah']) {
                    throw new RuntimeException('Stok "' . $i['nama_produk'] . '" tidak mencukupi (tersisa ' . $stokSkrg . ').');
                }
                $total += $i['harga'] * $i['jumlah'];
            }

            // 2) Simpan header pesanan
            $kode = 'INV' . date('ymd') . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
            $stmt = $koneksi->prepare(
                'INSERT INTO pesanan (kode_pesanan, id_pembeli, total_harga, nama_penerima, no_hp_penerima, alamat_pengiriman, catatan)
                 VALUES (?,?,?,?,?,?,?)'
            );
            $stmt->bind_param('sidssss', $kode, $idUser, $total, $form['nama_penerima'], $form['no_hp_penerima'], $form['alamat_pengiriman'], $form['catatan']);
            $stmt->execute();
            $idPesanan = $koneksi->insert_id;
            $stmt->close();

            // 3) Simpan detail + kurangi stok
            $stmtDetail = $koneksi->prepare(
                'INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk, harga_satuan, jumlah, subtotal) VALUES (?,?,?,?,?,?)'
            );
            $stmtStok = $koneksi->prepare('UPDATE produk SET stok = stok - ? WHERE id_produk = ?');
            foreach ($item as $i) {
                $sub = $i['harga'] * $i['jumlah'];
                $stmtDetail->bind_param('iisdid', $idPesanan, $i['id_produk'], $i['nama_produk'], $i['harga'], $i['jumlah'], $sub);
                $stmtDetail->execute();
                $stmtStok->bind_param('ii', $i['jumlah'], $i['id_produk']);
                $stmtStok->execute();
            }
            $stmtDetail->close();
            $stmtStok->close();

            // 4) Kosongkan keranjang
            $stmt = $koneksi->prepare('DELETE FROM keranjang WHERE id_user = ?');
            $stmt->bind_param('i', $idUser);
            $stmt->execute();
            $stmt->close();

            $koneksi->commit();
            set_flash('success', 'Pesanan berhasil dibuat dengan kode ' . $kode . '.');
            redirect('pesanan_detail.php?id=' . $idPesanan);
        } catch (RuntimeException $ex) {
            $koneksi->rollback();
            $error[] = $ex->getMessage();
        } catch (mysqli_sql_exception $ex) {
            $koneksi->rollback();
            $error[] = 'Terjadi kesalahan saat menyimpan pesanan. Silakan coba lagi.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
$item  = ambil_keranjang($koneksi, $idUser);
$total = 0;
foreach ($item as $i) { $total += $i['harga'] * $i['jumlah']; }

if (!$item) {
    set_flash('warning', 'Keranjang masih kosong.');
    redirect('keranjang.php');
}
?>
<h1 class="h4 mb-3"><i class="bi bi-bag-check"></i> Checkout</h1>
<?php if ($error): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($error as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<div class="row g-4">
    <div class="col-lg-7">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6 mb-3">Data Pengiriman</h2>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Nama Penerima</label>
                    <input type="text" name="nama_penerima" class="form-control" value="<?= e($form['nama_penerima']) ?>" required></div>
                <div class="mb-3"><label class="form-label">No. HP Penerima</label>
                    <input type="text" name="no_hp_penerima" class="form-control" value="<?= e($form['no_hp_penerima']) ?>" required></div>
                <div class="mb-3"><label class="form-label">Alamat Pengiriman</label>
                    <textarea name="alamat_pengiriman" rows="3" class="form-control" required><?= e($form['alamat_pengiriman']) ?></textarea></div>
                <div class="mb-3"><label class="form-label">Catatan (opsional)</label>
                    <input type="text" name="catatan" class="form-control" maxlength="255" value="<?= e($form['catatan']) ?>"></div>
                <button class="btn btn-brand w-100"><i class="bi bi-check2-circle"></i> Buat Pesanan</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-5">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Ringkasan Pesanan</h2>
            <ul class="list-group list-group-flush mb-2">
                <?php foreach ($item as $i): ?>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span><?= e($i['nama_produk']) ?> <small class="text-muted">x<?= (int)$i['jumlah'] ?></small></span>
                    <span><?= rupiah($i['harga'] * $i['jumlah']) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span class="harga"><?= rupiah($total) ?></span></div>
        </div></div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
