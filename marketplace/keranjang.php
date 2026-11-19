<?php
$judul = 'Keranjang Belanja';
require_once __DIR__ . '/includes/header.php';
wajib_role('pembeli');

$idUser = (int)$user['id_user'];
$stmt = $koneksi->prepare(
    'SELECT k.id_keranjang, k.jumlah, p.id_produk, p.nama_produk, p.harga, p.stok, p.gambar
     FROM keranjang k JOIN produk p ON p.id_produk = k.id_produk
     WHERE k.id_user = ? ORDER BY k.created_at DESC'
);
$stmt->bind_param('i', $idUser);
$stmt->execute();
$item = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
?>
<h1 class="mb-4" style="font-size:clamp(1.7rem,3vw,2.3rem)">Keranjang belanja</h1>

<?php if (!$item): ?>
    <div class="alert alert-secondary">Keranjangmu masih kosong. <a href="<?= e(url('index.php#produk')) ?>">Pilih produk dulu</a></div>
<?php else: ?>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card"><div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light"><tr><th>Produk</th><th class="text-end">Harga</th><th style="width:150px">Jumlah</th><th class="text-end">Subtotal</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($item as $i):
                $sub = $i['harga'] * $i['jumlah'];
                $total += $sub; ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <?= gambar_produk($i, 'img-thumb') ?>
                            <a class="text-decoration-none text-dark" href="<?= e(url('produk_detail.php?id=' . $i['id_produk'])) ?>"><?= e($i['nama_produk']) ?></a>
                        </div>
                    </td>
                    <td class="text-end"><?= rupiah($i['harga']) ?></td>
                    <td>
                        <form action="<?= e(url('keranjang_aksi.php')) ?>" method="post" class="input-group input-group-sm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="ubah">
                            <input type="hidden" name="id_keranjang" value="<?= (int)$i['id_keranjang'] ?>">
                            <input type="number" name="jumlah" class="form-control" value="<?= (int)$i['jumlah'] ?>" min="0" max="<?= (int)$i['stok'] ?>">
                            <button class="btn btn-outline-secondary" title="Perbarui"><i class="bi bi-arrow-repeat"></i></button>
                        </form>
                        <small class="text-muted">Stok: <?= (int)$i['stok'] ?></small>
                    </td>
                    <td class="text-end fw-semibold"><?= rupiah($sub) ?></td>
                    <td class="text-end">
                        <form action="<?= e(url('keranjang_aksi.php')) ?>" method="post" onsubmit="return confirm('Hapus item ini?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id_keranjang" value="<?= (int)$i['id_keranjang'] ?>">
                            <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card ringkasan"><div class="card-body p-4">
            <h2 class="h5 mb-3">Ringkasan belanja</h2>
            <div class="d-flex justify-content-between"><span>Total item</span><span><?= array_sum(array_column($item, 'jumlah')) ?></span></div>
            <hr>
            <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span class="harga"><?= rupiah($total) ?></span></div>
            <a href="<?= e(url('checkout.php')) ?>" class="btn btn-brand w-100 mt-3">Lanjut ke checkout</a>
            <a href="<?= e(url('index.php#produk')) ?>" class="btn btn-link w-100">Tambah produk lain</a>
        </div></div>
    </div>
</div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
