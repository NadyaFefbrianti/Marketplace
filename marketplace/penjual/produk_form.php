<?php
require_once __DIR__ . '/../includes/functions.php';
wajib_role('penjual');

$idPenjual = (int)$_SESSION['user']['id_user'];
$id        = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$edit      = $id > 0;

$data = ['nama_produk' => '', 'id_kategori' => 0, 'harga' => '', 'stok' => '', 'deskripsi' => '', 'gambar' => null];

if ($edit) {
    $stmt = $koneksi->prepare('SELECT * FROM produk WHERE id_produk = ? AND id_penjual = ?');
    $stmt->bind_param('ii', $id, $idPenjual);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) {
        set_flash('danger', 'Produk tidak ditemukan.');
        redirect('penjual/produk.php');
    }
    $data = $row;
}

$error = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $data['nama_produk'] = trim($_POST['nama_produk'] ?? '');
    $data['id_kategori'] = (int)($_POST['id_kategori'] ?? 0);
    $data['harga']       = $_POST['harga'] ?? '';
    $data['stok']        = $_POST['stok'] ?? '';
    $data['deskripsi']   = trim($_POST['deskripsi'] ?? '');

    if ($data['nama_produk'] === '') $error[] = 'Nama produk wajib diisi.';
    if ($data['id_kategori'] < 1)    $error[] = 'Pilih kategori.';
    if (!is_numeric($data['harga']) || $data['harga'] < 0) $error[] = 'Harga harus berupa angka >= 0.';
    if (!ctype_digit((string)$data['stok'])) $error[] = 'Stok harus berupa bilangan bulat >= 0.';

    $gambarBaru = null;
    if (!$error) {
        try {
            $gambarBaru = upload_gambar($_FILES['gambar'] ?? ['error' => UPLOAD_ERR_NO_FILE]);
        } catch (RuntimeException $ex) {
            $error[] = $ex->getMessage();
        }
    }

    if (!$error) {
        $harga = (float)$data['harga'];
        $stok  = (int)$data['stok'];
        if ($edit) {
            $gambarFinal = $gambarBaru ?? $data['gambar'];
            $stmt = $koneksi->prepare(
                'UPDATE produk SET id_kategori=?, nama_produk=?, deskripsi=?, harga=?, stok=?, gambar=?
                 WHERE id_produk=? AND id_penjual=?'
            );
            $stmt->bind_param('issdisii', $data['id_kategori'], $data['nama_produk'], $data['deskripsi'], $harga, $stok, $gambarFinal, $id, $idPenjual);
            $stmt->execute();
            $stmt->close();
            if ($gambarBaru && !empty($row['gambar'])) { hapus_gambar($row['gambar']); }
            set_flash('success', 'Produk berhasil diperbarui.');
        } else {
            $stmt = $koneksi->prepare(
                'INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok, gambar) VALUES (?,?,?,?,?,?,?)'
            );
            $stmt->bind_param('iissdis', $idPenjual, $data['id_kategori'], $data['nama_produk'], $data['deskripsi'], $harga, $stok, $gambarBaru);
            $stmt->execute();
            $stmt->close();
            set_flash('success', 'Produk berhasil ditambahkan.');
        }
        redirect('penjual/produk.php');
    }
}

$judul = $edit ? 'Edit Produk' : 'Tambah Produk';
require_once __DIR__ . '/../includes/header.php';
$kategori = $koneksi->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetch_all(MYSQLI_ASSOC);
?>
<h1 class="h4 mb-3"><?= e($judul) ?></h1>
<?php if ($error): ?>
    <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($error as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>
<div class="card shadow-sm"><div class="card-body">
<form method="post" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int)$id ?>"><?php endif; ?>
    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="nama_produk" class="form-control" maxlength="150" value="<?= e($data['nama_produk']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Kategori</label>
            <select name="id_kategori" class="form-select" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($kategori as $k): ?>
                    <option value="<?= $k['id_kategori'] ?>" <?= (int)$data['id_kategori'] === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Harga (Rp)</label>
            <input type="number" name="harga" class="form-control" min="0" step="100" value="<?= e($data['harga']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Stok</label>
            <input type="number" name="stok" class="form-control" min="0" value="<?= e($data['stok']) ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label">Gambar (JPG/PNG/WEBP, maks 2 MB)</label>
            <input type="file" name="gambar" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>
        <?php if ($edit && $data['gambar']): ?>
        <div class="col-12"><small class="text-muted d-block mb-1">Gambar saat ini:</small><?= gambar_produk($data, 'img-thumb') ?></div>
        <?php endif; ?>
        <div class="col-12">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" rows="4" class="form-control"><?= e($data['deskripsi']) ?></textarea>
        </div>
    </div>
    <div class="mt-4">
        <button class="btn btn-brand"><i class="bi bi-save"></i> Simpan</button>
        <a href="<?= e(url('penjual/produk.php')) ?>" class="btn btn-outline-secondary">Batal</a>
    </div>
</form>
</div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
