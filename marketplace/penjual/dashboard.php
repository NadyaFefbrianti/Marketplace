<?php
$judul = 'Dashboard Toko';
require_once __DIR__ . '/../includes/header.php';
wajib_role('penjual');

$idPenjual = (int)$user['id_user'];

/* Ringkasan angka: produk, pesanan, pesanan baru, pendapatan (pesanan selesai) */
$stmt = $koneksi->prepare(
    "SELECT (SELECT COUNT(*) FROM produk WHERE id_penjual = ?) AS jml_produk,
            COUNT(DISTINCT ps.id_pesanan) AS jml_pesanan,
            COUNT(DISTINCT CASE WHEN ps.status = 'pending' THEN ps.id_pesanan END) AS jml_baru,
            COALESCE(SUM(CASE WHEN ps.status = 'selesai' THEN d.subtotal END), 0) AS pendapatan
     FROM detail_pesanan d
     JOIN produk p   ON p.id_produk   = d.id_produk
     JOIN pesanan ps ON ps.id_pesanan = d.id_pesanan
     WHERE p.id_penjual = ?"
);
$stmt->bind_param('ii', $idPenjual, $idPenjual);
$stmt->execute();
$ring = $stmt->get_result()->fetch_assoc();
$stmt->close();

/* 5 pesanan terbaru yang memuat produk penjual ini */
$stmt = $koneksi->prepare(
    'SELECT ps.id_pesanan, ps.kode_pesanan, ps.tanggal_pesanan, ps.status, u.nama AS nama_pembeli,
            SUM(d.subtotal) AS total_toko
     FROM detail_pesanan d
     JOIN produk p   ON p.id_produk   = d.id_produk
     JOIN pesanan ps ON ps.id_pesanan = d.id_pesanan
     JOIN users u    ON u.id_user     = ps.id_pembeli
     WHERE p.id_penjual = ?
     GROUP BY ps.id_pesanan, ps.kode_pesanan, ps.tanggal_pesanan, ps.status, u.nama
     ORDER BY ps.tanggal_pesanan DESC, ps.id_pesanan DESC
     LIMIT 5'
);
$stmt->bind_param('i', $idPenjual);
$stmt->execute();
$terbaru = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* Produk dengan stok menipis (10 atau kurang) */
$stmt = $koneksi->prepare(
    'SELECT id_produk, nama_produk, stok, gambar FROM produk
     WHERE id_penjual = ? AND stok <= 10 ORDER BY stok ASC, nama_produk LIMIT 5'
);
$stmt->bind_param('i', $idPenjual);
$stmt->execute();
$menipis = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(1.7rem,3vw,2.3rem)">Dashboard toko</h1>
        <p class="text-secondary mb-0">Halo, <?= e($user['nama']) ?>. Ini ringkasan tokomu.</p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-brand" href="<?= e(url('penjual/produk_form.php')) ?>"><i class="bi bi-plus-lg"></i> Tambah produk</a>
        <a class="btn btn-outline-secondary" href="<?= e(url('penjual/pesanan.php')) ?>"><i class="bi bi-inbox"></i> Pesanan masuk</a>
        <a class="btn btn-outline-secondary" href="<?= e(url('penjual/kategori.php')) ?>"><i class="bi bi-tags"></i> Kategori</a>
    </div>
</div>

<div class="row row-cols-2 row-cols-lg-4 g-3 mb-4">
    <div class="col"><div class="kartu-stat"><span class="ikon"><i class="bi bi-box-seam"></i></span><b><?= (int)$ring['jml_produk'] ?></b><span>Produk</span></div></div>
    <div class="col"><div class="kartu-stat"><span class="ikon"><i class="bi bi-receipt"></i></span><b><?= (int)$ring['jml_pesanan'] ?></b><span>Total pesanan</span></div></div>
    <div class="col"><div class="kartu-stat <?= (int)$ring['jml_baru'] > 0 ? 'sorot' : '' ?>"><span class="ikon"><i class="bi bi-bell"></i></span><b><?= (int)$ring['jml_baru'] ?></b><span>Menunggu diproses</span></div></div>
    <div class="col"><div class="kartu-stat"><span class="ikon"><i class="bi bi-cash-coin"></i></span><b class="kecil"><?= rupiah($ring['pendapatan']) ?></b><span>Pendapatan (selesai)</span></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Pesanan terbaru</h2>
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('penjual/pesanan.php')) ?>">Lihat semua</a>
            </div>
            <?php if (!$terbaru): ?>
                <div class="card-body text-secondary">Belum ada pesanan untuk produkmu.</div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Kode</th><th>Pembeli</th><th class="text-end">Nilai</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($terbaru as $t): ?>
                        <tr>
                            <td><span class="fw-semibold"><?= e($t['kode_pesanan']) ?></span><br><small class="text-secondary"><?= e(date('d M Y H:i', strtotime($t['tanggal_pesanan']))) ?></small></td>
                            <td><?= e($t['nama_pembeli']) ?></td>
                            <td class="text-end"><?= rupiah($t['total_toko']) ?></td>
                            <td><?= label_status($t['status']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="h5 mb-0">Stok menipis</h2>
                <a class="btn btn-sm btn-outline-secondary" href="<?= e(url('penjual/produk.php')) ?>">Kelola produk</a>
            </div>
            <div class="card-body">
                <?php if (!$menipis): ?>
                    <p class="text-secondary mb-0">Semua stok aman (di atas 10).</p>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php foreach ($menipis as $m): ?>
                    <li class="list-group-item d-flex align-items-center gap-3 px-0">
                        <?= gambar_produk($m, 'img-thumb') ?>
                        <span class="flex-grow-1"><?= e($m['nama_produk']) ?></span>
                        <?php if ((int)$m['stok'] === 0): ?>
                            <span class="badge bg-danger">Habis</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">Sisa <?= (int)$m['stok'] ?></span>
                        <?php endif; ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?= e(url('penjual/produk_form.php?id=' . (int)$m['id_produk'])) ?>" title="Ubah stok" aria-label="Ubah stok <?= e($m['nama_produk']) ?>"><i class="bi bi-pencil"></i></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
