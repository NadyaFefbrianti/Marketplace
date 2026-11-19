<?php
$judul = 'Masuk';
require_once __DIR__ . '/includes/functions.php';
if (sudah_login()) { redirect('index.php'); }

$error = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_cek();
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    $stmt = $koneksi->prepare('SELECT id_user, nama, email, password, role, no_hp, alamat FROM users WHERE email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $u = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($u && password_verify($pass, $u['password'])) {
        session_regenerate_id(true);
        unset($u['password']);
        $_SESSION['user'] = $u;
        set_flash('success', 'Selamat datang, ' . $u['nama'] . '!');
        redirect($u['role'] === 'penjual' ? 'penjual/dashboard.php' : 'index.php');
    }
    $error = 'Email atau password salah.';
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm"><div class="card-body p-4">
            <h1 class="h4 mb-3">Masuk</h1>
            <?php if ($error): ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
            <form method="post">
                <?= csrf_field() ?>
                <div class="mb-3"><label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="<?= e($email) ?>" required autofocus></div>
                <div class="mb-3"><label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required></div>
                <button class="btn btn-brand w-100">Masuk</button>
            </form>
            <p class="small text-center mt-3 mb-1">Belum punya akun? <a href="<?= e(url('register.php')) ?>">Daftar</a></p>
            <p class="small text-muted text-center mb-0">Demo: pembeli1@contoh.com / penjual1@contoh.com &mdash; password123</p>
        </div></div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
