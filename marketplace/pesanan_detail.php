<?php
$judul = 'Detail Pesanan';
require_once __DIR__ . '/includes/header.php';
wajib_role('pembeli');

$id     = (int)($_GET['id'] ?? 0);
$idUser = (int)$user['id_user'];

$stmt = $koneksi->prepare('SELECT * FROM pesanan WHERE id_pesanan = ? AND id_pembeli = ?');
$stmt->bind_param('ii', $id, $idUser);
$stmt->execute();
$ps = $stmt->get_result()->fetch_assoc();
$stmt->close();
if (!$ps) {
    set_flash('danger', 'Pesanan tidak ditemukan.');
    redirect('pesanan.php');
}

$stmt = $koneksi->prepare('SELECT * FROM detail_pesanan WHERE id_pesanan = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$detail = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Pesanan <?= e($ps['kode_pesanan']) ?> <?= label_status($ps['status']) ?></h1>
    <a href="<?= e(url('pesanan.php')) ?>" class="btn btn-sm btn-outline-secondary">&laquo; Semua pesanan</a>
</div>
<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm"><div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light"><tr><th>Produk</th><th class="text-end">Harga</th><th class="text-center">Jml</th><th class="text-end">Subtotal</th></tr></thead>
            <tbody>
            <?php foreach ($detail as $d): ?>
                <tr>
                    <td><?= e($d['nama_produk']) ?></td>
                    <td class="text-end"><?= rupiah($d['harga_satuan']) ?></td>
                    <td class="text-center"><?= (int)$d['jumlah'] ?></td>
                    <td class="text-end"><?= rupiah($d['subtotal']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <tfoot><tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end harga"><?= rupiah($ps['total_harga']) ?></td></tr></tfoot>
        </table></div></div>
    </div>
    <div class="col-lg-4">
        <div class="card shadow-sm"><div class="card-body">
            <h2 class="h6">Informasi Pesanan</h2>
            <p class="mb-1 small text-muted">Tanggal</p>
            <p><?= e(date('d M Y H:i', strtotime($ps['tanggal_pesanan']))) ?></p>
            <p class="mb-1 small text-muted">Penerima</p>
            <p><?= e($ps['nama_penerima']) ?> (<?= e($ps['no_hp_penerima']) ?>)</p>
            <p class="mb-1 small text-muted">Alamat</p>
            <p><?= nl2br(e($ps['alamat_pengiriman'])) ?></p>
            <?php if ($ps['catatan']): ?>
                <p class="mb-1 small text-muted">Catatan</p><p class="mb-0"><?= e($ps['catatan']) ?></p>
            <?php endif; ?>
        </div></div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
