<?php
$judul = 'Detail Produk';
require_once __DIR__ . '/includes/header.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $koneksi->prepare(
    'SELECT p.*, k.nama_kategori, u.nama AS nama_penjual, u.alamat AS alamat_penjual
     FROM produk p
     JOIN kategori k ON k.id_kategori = p.id_kategori
     JOIN users u ON u.id_user = p.id_penjual
     WHERE p.id_produk = ?'
);
$stmt->bind_param('i', $id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$p) {
    set_flash('danger', 'Produk tidak ditemukan.');
    redirect('index.php');
}
?>
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(url('index.php#produk')) ?>">Produk</a></li>
        <li class="breadcrumb-item"><a href="<?= e(url('index.php?kategori=' . $p['id_kategori'] . '#produk')) ?>"><?= e($p['nama_kategori']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= e($p['nama_produk']) ?></li>
    </ol>
</nav>

<div class="row g-4 g-lg-5 align-items-center mb-4">
    <div class="col-md-5">
        <div class="panel-detail"><?= gambar_produk($p, 'img-detail') ?></div>
    </div>
    <div class="col-md-7">
        <span class="chip-kat"><?= e($p['nama_kategori']) ?></span>
        <h1 class="mt-3 mb-2" style="font-size:clamp(1.8rem,3.5vw,2.6rem)"><?= e($p['nama_produk']) ?></h1>
        <div class="harga mb-3" style="font-size:2rem"><?= rupiah($p['harga']) ?></div>
        <p class="mb-1"><i class="bi bi-shop"></i> Dijual oleh <strong><?= e($p['nama_penjual']) ?></strong><?php if (!empty($p['alamat_penjual'])): ?> &middot; <span class="text-secondary"><?= e($p['alamat_penjual']) ?></span><?php endif; ?></p>
        <p class="mb-3">
            Stok:
            <?php if ((int)$p['stok'] > 0): ?>
                <span class="badge bg-success"><?= (int)$p['stok'] ?> tersedia</span>
            <?php else: ?>
                <span class="badge bg-danger">Habis</span>
            <?php endif; ?>
        </p>
        <h2 class="h5">Tentang produk</h2>
        <p class="mb-4" style="max-width:60ch"><?= nl2br(e($p['deskripsi'] ?: 'Belum ada deskripsi.')) ?></p>

        <?php if ((int)$p['stok'] > 0 && (!$user || $user['role'] === 'pembeli')): ?>
        <form action="<?= e(url('keranjang_aksi.php')) ?>" method="post" class="d-flex flex-wrap gap-2 align-items-center">
            <?= csrf_field() ?>
            <input type="hidden" name="aksi" value="tambah">
            <input type="hidden" name="id_produk" value="<?= (int)$p['id_produk'] ?>">
            <label class="fw-semibold me-1" for="jumlah">Jumlah</label>
            <input type="number" id="jumlah" name="jumlah" class="form-control" value="1" min="1" max="<?= (int)$p['stok'] ?>" style="width:96px">
            <button class="btn btn-brand px-4"><i class="bi bi-bag-plus"></i> Tambah ke keranjang</button>
        </form>
        <?php elseif ($user && $user['role'] === 'penjual'): ?>
            <div class="alert alert-info mb-0">Akun penjual tidak dapat berbelanja. Masuk dengan akun pembeli untuk memesan.</div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
