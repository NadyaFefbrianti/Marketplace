<?php
$judul = 'Kelola Kategori';
require_once __DIR__ . '/../includes/header.php';
wajib_role('penjual');

/* ---------- Proses tambah / ubah / hapus ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $aksi = $_POST['aksi'] ?? '';
    $id   = (int)($_POST['id_kategori'] ?? 0);
    $nama = trim($_POST['nama_kategori'] ?? '');

    try {
        if ($aksi === 'tambah' || $aksi === 'ubah') {
            if ($nama === '' || mb_strlen($nama) > 60) {
                set_flash('danger', 'Nama kategori wajib diisi dan maksimal 60 karakter.');
            } elseif ($aksi === 'tambah') {
                $stmt = $koneksi->prepare('INSERT INTO kategori (nama_kategori) VALUES (?)');
                $stmt->bind_param('s', $nama);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Kategori "' . $nama . '" ditambahkan.');
            } else {
                $stmt = $koneksi->prepare('UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?');
                $stmt->bind_param('si', $nama, $id);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Nama kategori diperbarui.');
            }
        } elseif ($aksi === 'hapus') {
            $stmt = $koneksi->prepare('SELECT COUNT(*) FROM produk WHERE id_kategori = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->bind_result($dipakai);
            $stmt->fetch();
            $stmt->close();
            if ($dipakai > 0) {
                set_flash('warning', 'Kategori masih dipakai ' . $dipakai . ' produk. Pindahkan produknya ke kategori lain dulu.');
            } else {
                $stmt = $koneksi->prepare('DELETE FROM kategori WHERE id_kategori = ?');
                $stmt->bind_param('i', $id);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Kategori dihapus.');
            }
        }
    } catch (mysqli_sql_exception $e) {
        if ((int)$e->getCode() === 1062) {
            set_flash('danger', 'Nama kategori "' . $nama . '" sudah ada. Gunakan nama lain.');
        } else {
            set_flash('danger', 'Perubahan kategori gagal disimpan.');
        }
    }
    redirect('penjual/kategori.php');
}

$daftar = $koneksi->query(
    'SELECT k.id_kategori, k.nama_kategori, COUNT(p.id_produk) AS jml
     FROM kategori k LEFT JOIN produk p ON p.id_kategori = k.id_kategori
     GROUP BY k.id_kategori, k.nama_kategori ORDER BY k.nama_kategori'
)->fetch_all(MYSQLI_ASSOC);
?>
<div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
    <div>
        <h1 class="mb-1" style="font-size:clamp(1.7rem,3vw,2.3rem)">Kelola kategori</h1>
        <p class="text-secondary mb-0">Kategori dipakai di form produk dan filter halaman utama.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= e(url('penjual/produk.php')) ?>">&laquo; Kelola produk</a>
</div>

<div class="card mb-4"><div class="card-body p-4">
    <h2 class="h5 mb-3">Tambah kategori</h2>
    <form method="post" class="row g-2">
        <?= csrf_field() ?>
        <input type="hidden" name="aksi" value="tambah">
        <div class="col-md-8 col-lg-9">
            <label class="visually-hidden" for="nama_baru">Nama kategori</label>
            <input type="text" id="nama_baru" name="nama_kategori" class="form-control" maxlength="60" placeholder="Contoh: Kue &amp; Jajanan Pasar" required>
        </div>
        <div class="col-md-4 col-lg-3 d-grid"><button class="btn btn-brand"><i class="bi bi-plus-lg"></i> Tambah</button></div>
    </form>
</div></div>

<div class="card"><div class="table-responsive">
<table class="table align-middle mb-0">
    <thead><tr><th>Nama kategori</th><th class="text-center">Produk</th><th class="text-end">Aksi</th></tr></thead>
    <tbody>
    <?php foreach ($daftar as $k): ?>
        <tr>
            <td style="min-width:240px">
                <form method="post" class="d-flex gap-2">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="ubah">
                    <input type="hidden" name="id_kategori" value="<?= (int)$k['id_kategori'] ?>">
                    <input type="text" name="nama_kategori" class="form-control form-control-sm" maxlength="60" value="<?= e($k['nama_kategori']) ?>" aria-label="Nama kategori" required>
                    <button class="btn btn-sm btn-outline-primary" title="Simpan nama" aria-label="Simpan nama <?= e($k['nama_kategori']) ?>"><i class="bi bi-check-lg"></i></button>
                </form>
            </td>
            <td class="text-center"><?= (int)$k['jml'] ?></td>
            <td class="text-end">
                <form method="post" class="d-inline" onsubmit="return confirm('Hapus kategori ini?')">
                    <?= csrf_field() ?>
                    <input type="hidden" name="aksi" value="hapus">
                    <input type="hidden" name="id_kategori" value="<?= (int)$k['id_kategori'] ?>">
                    <button class="btn btn-sm btn-outline-danger" title="Hapus" aria-label="Hapus <?= e($k['nama_kategori']) ?>" <?= (int)$k['jml'] > 0 ? 'disabled' : '' ?>><i class="bi bi-trash"></i></button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table></div></div>
<p class="small text-secondary mt-3">Tombol hapus nonaktif selama kategori masih dipakai produk.</p>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
