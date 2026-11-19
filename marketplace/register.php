<?php
$judul = 'Daftar Akun';
require_once __DIR__ . '/includes/functions.php';
if (sudah_login()) { redirect('index.php'); }

$error = [];
$old = ['nama' => '', 'email' => '', 'role' => 'pembeli', 'no_hp' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $old['nama']  = trim($_POST['nama'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['role']  = $_POST['role'] ?? 'pembeli';
    $old['no_hp'] = trim($_POST['no_hp'] ?? '');
    $pass  = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';

    if ($old['nama'] === '') $error[] = 'Nama wajib diisi.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $error[] = 'Format email tidak valid.';
    if (!in_array($old['role'], ['penjual', 'pembeli'], true)) $error[] = 'Peran tidak valid.';
    if (strlen($pass) < 6) $error[] = 'Password minimal 6 karakter.';
    if ($pass !== $pass2) $error[] = 'Konfirmasi password tidak sama.';

    if (!$error) {
        $stmt = $koneksi->prepare('SELECT 1 FROM users WHERE email = ?');
        $stmt->bind_param('s', $old['email']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) $error[] = 'Email sudah terdaftar.';
        $stmt->close();
    }
    if (!$error) {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $stmt = $koneksi->prepare('INSERT INTO users (nama, email, password, role, no_hp) VALUES (?,?,?,?,?)');
        $stmt->bind_param('sssss', $old['nama'], $old['email'], $hash, $old['role'], $old['no_hp']);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'Pendaftaran berhasil. Silakan login.');
        redirect('login.php');
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm"><div class="card-body p-4">
            <h1 class="h4 mb-3">Daftar Akun</h1>
            <?php if ($error): ?>
                <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($error as $er): ?><li><?= e($er) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <form method="post" novalidate>
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Nama</label>
                    <input type="text" name="nama" class="form-control" value="<?= e($old['nama']) ?>" required></div>
                <div class="mb-3"><label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($old['email']) ?>" required></div>
                <div class="mb-3"><label class="form-label">No. HP</label>
                    <input type="text" name="no_hp" class="form-control" value="<?= e($old['no_hp']) ?>"></div>
                <div class="mb-3"><label class="form-label">Daftar sebagai</label>
                    <select name="role" class="form-select">
                        <option value="pembeli" <?= $old['role'] === 'pembeli' ? 'selected' : '' ?>>Pembeli</option>
                        <option value="penjual" <?= $old['role'] === 'penjual' ? 'selected' : '' ?>>Penjual</option>
                    </select></div>
                <div class="mb-3"><label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required></div>
                <div class="mb-3"><label class="form-label">Ulangi Password</label>
                    <input type="password" name="password2" class="form-control" required></div>
                <button class="btn btn-brand w-100">Daftar</button>
            </form>
            <p class="small text-center mt-3 mb-0">Sudah punya akun? <a href="<?= e(url('login.php')) ?>">Masuk</a></p>
        </div></div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
