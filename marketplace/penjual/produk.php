<?php
$judul = 'Kelola Produk';
require_once __DIR__ . '/../includes/header.php';
wajib_role('penjual');

$idPenjual = (int)$user['id_user'];
$stmt = $koneksi->prepare(
    'SELECT p.*, k.nama_kategori FROM produk p
     JOIN kategori k ON k.id_kategori = p.id_kategori
     WHERE p.id_penjual = ? ORDER BY p.created_at DESC, p.id_produk DESC'
);
$stmt->bind_param('i', $idPenjual);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0"><i class="bi bi-box-seam"></i> Produk Saya</h1>
    <a href="<?= e(url('penjual/produk_form.php')) ?>" class="btn btn-brand"><i class="bi bi-plus-lg"></i> Tambah Produk</a>
</div>
<?php if (!$daftar): ?>
    <div class="alert alert-secondary">Anda belum memiliki produk.</div>
<?php else: ?>
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Produk</th><th>Kategori</th><th class="text-end">Harga</th><th class="text-center">Stok</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($daftar as $p): ?>
        <tr>
            <td><div class="d-flex align-items-center gap-2"><?= gambar_produk($p, 'img-thumb') ?><span><?= e($p['nama_produk']) ?></span></div></td>
            <td><?= e($p['nama_kategori']) ?></td>
            <td class="text-end"><?= rupiah($p['harga']) ?></td>
            <td class="text-center"><?= (int)$p['stok'] ?></td>
            <td class="text-end text-nowrap">
                <a href="<?= e(url('produk_detail.php?id=' . $p['id_produk'])) ?>" class="btn btn-sm btn-outline-secondary" title="Lihat"><i class="bi bi-eye"></i></a>
                <a href="<?= e(url('penjual/produk_form.php?id=' . $p['id_produk'])) ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="bi bi-pencil"></i></a>
                <form action="<?= e(url('penjual/produk_hapus.php')) ?>" method="post" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= (int)$p['id_produk'] ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Hapus"><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>
<?php endif; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
