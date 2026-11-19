<?php
$judul = 'Pesanan Saya';
require_once __DIR__ . '/includes/header.php';
wajib_role('pembeli');

$idUser = (int)$user['id_user'];
$stmt = $koneksi->prepare(
    'SELECT p.*, (SELECT COALESCE(SUM(jumlah),0) FROM detail_pesanan d WHERE d.id_pesanan = p.id_pesanan) AS total_item
     FROM pesanan p WHERE p.id_pembeli = ? ORDER BY p.tanggal_pesanan DESC'
);
$stmt->bind_param('i', $idUser);
$stmt->execute();
$daftar = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<h1 class="h4 mb-3"><i class="bi bi-receipt"></i> Pesanan Saya</h1>
<?php if (!$daftar): ?>
    <div class="alert alert-secondary">Belum ada pesanan.</div>
<?php else: ?>
<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light"><tr><th>Kode</th><th>Tanggal</th><th>Item</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($daftar as $d): ?>
        <tr>
            <td class="fw-semibold"><?= e($d['kode_pesanan']) ?></td>
            <td><?= e(date('d M Y H:i', strtotime($d['tanggal_pesanan']))) ?></td>
            <td><?= (int)$d['total_item'] ?></td>
            <td class="text-end"><?= rupiah($d['total_harga']) ?></td>
            <td><?= label_status($d['status']) ?></td>
            <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="<?= e(url('pesanan_detail.php?id=' . $d['id_pesanan'])) ?>">Detail</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>
<?php endif; ?>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
