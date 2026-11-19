<?php
$judul = 'Pesanan Masuk';
require_once __DIR__ . '/../includes/header.php';
wajib_role('penjual');

$idPenjual = (int)$user['id_user'];

/* Update status pesanan (hanya untuk pesanan yang memuat produk penjual ini) */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $idPesanan = (int)($_POST['id_pesanan'] ?? 0);
    $status    = $_POST['status'] ?? '';
    if (in_array($status, ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'], true)) {
        $stmt = $koneksi->prepare(
            'UPDATE pesanan SET status = ? WHERE id_pesanan = ? AND EXISTS (
                SELECT 1 FROM detail_pesanan d JOIN produk p ON p.id_produk = d.id_produk
                WHERE d.id_pesanan = pesanan.id_pesanan AND p.id_penjual = ?)'
        );
        $stmt->bind_param('sii', $status, $idPesanan, $idPenjual);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Status pesanan diperbarui.');
    }
    redirect('penjual/pesanan.php');
}

$stmt = $koneksi->prepare(
    'SELECT ps.id_pesanan, ps.kode_pesanan, ps.tanggal_pesanan, ps.status, ps.nama_penerima, ps.no_hp_penerima,
            ps.alamat_pengiriman, u.nama AS nama_pembeli,
            d.nama_produk, d.jumlah, d.subtotal
     FROM detail_pesanan d
     JOIN produk p  ON p.id_produk  = d.id_produk
     JOIN pesanan ps ON ps.id_pesanan = d.id_pesanan
     JOIN users u   ON u.id_user    = ps.id_pembeli
     WHERE p.id_penjual = ?
     ORDER BY ps.tanggal_pesanan DESC, ps.id_pesanan DESC'
);
$stmt->bind_param('i', $idPenjual);
$stmt->execute();
$baris = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

/* Kelompokkan per pesanan */
$grup = [];
foreach ($baris as $b) { $grup[$b['id_pesanan']][] = $b; }
?>
<h1 class="h4 mb-3"><i class="bi bi-inbox"></i> Pesanan Masuk</h1>
<?php if (!$grup): ?>
    <div class="alert alert-secondary">Belum ada pesanan untuk produk Anda.</div>
<?php endif; ?>
<?php foreach ($grup as $idPs => $rows): $h = $rows[0]; ?>
<div class="card shadow-sm mb-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div><strong><?= e($h['kode_pesanan']) ?></strong>
            <small class="text-muted ms-2"><?= e(date('d M Y H:i', strtotime($h['tanggal_pesanan']))) ?></small></div>
        <form method="post" class="d-flex gap-2">
            <?= csrf_field() ?>
            <input type="hidden" name="id_pesanan" value="<?= (int)$idPs ?>">
            <select name="status" class="form-select form-select-sm">
                <?php foreach (['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'] as $s): ?>
                    <option value="<?= $s ?>" <?= $h['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-sm btn-brand">Simpan</button>
        </form>
    </div>
    <div class="card-body">
        <p class="small mb-2"><i class="bi bi-person"></i> <?= e($h['nama_penerima']) ?> (<?= e($h['no_hp_penerima']) ?>) &middot; <?= e($h['alamat_pengiriman']) ?></p>
        <ul class="mb-0">
            <?php foreach ($rows as $r): ?>
                <li><?= e($r['nama_produk']) ?> &times; <?= (int)$r['jumlah'] ?> &mdash; <?= rupiah($r['subtotal']) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endforeach; ?>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
