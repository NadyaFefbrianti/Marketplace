<?php
require_once __DIR__ . '/functions.php';
$user  = user_aktif();
$badge = jumlah_keranjang($koneksi);
$flash = get_flash();
$judul = $judul ?? 'Temu Store';
$hero  = $hero ?? false;   // true = halaman utama dengan hero lebar
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($judul) ?> | Temu Store</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,600;9..144,700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
<header class="site-head <?= $hero ? 'site-head--hero' : '' ?>">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="<?= e(url('index.php')) ?>"><span class="logo"><i class="bi bi-basket3-fill"></i></span>Temu Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Buka menu"><span class="navbar-toggler-icon"></span></button>
            <div class="collapse navbar-collapse" id="menu">
                <ul class="navbar-nav mx-lg-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= e(url('index.php#produk')) ?>">Produk</a></li>
                    <?php if ($user && $user['role'] === 'penjual'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('penjual/dashboard.php')) ?>">Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('penjual/produk.php')) ?>">Kelola Produk</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('penjual/kategori.php')) ?>">Kategori</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('penjual/pesanan.php')) ?>">Pesanan Masuk</a></li>
                    <?php elseif ($user && $user['role'] === 'pembeli'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('pesanan.php')) ?>">Pesanan Saya</a></li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav align-items-lg-center gap-lg-1">
                    <li class="nav-item"><a class="ikon-cari" href="<?= e(url('index.php#cari')) ?>" title="Cari produk" aria-label="Cari produk"><i class="bi bi-search"></i></a></li>
                    <?php if ($user && $user['role'] === 'pembeli'): ?>
                        <li class="nav-item">
                            <a class="ikon-keranjang" href="<?= e(url('keranjang.php')) ?>" title="Keranjang" aria-label="Keranjang"><i class="bi bi-bag"></i>
                                <?php if ($badge > 0): ?><span class="badge rounded-pill"><?= $badge ?></span><?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($user): ?>
                        <li class="nav-item"><span class="nav-link nama-user"><i class="bi bi-person-circle"></i> <?= e($user['nama']) ?></span></li>
                        <li class="nav-item"><a class="btn btn-brand btn-sm" href="<?= e(url('logout.php')) ?>">Keluar</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= e(url('login.php')) ?>">Masuk</a></li>
                        <li class="nav-item"><a class="btn btn-brand btn-sm ms-lg-1" href="<?= e(url('register.php')) ?>">Daftar</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
<main class="flex-grow-1 <?= $hero ? '' : 'container halaman-dalam' ?>">
<?php if ($flash): ?>
    <div class="<?= $hero ? 'container position-relative' : '' ?>" style="<?= $hero ? 'z-index:6;margin-top:5.5rem;margin-bottom:-4.5rem' : '' ?>">
        <div class="alert alert-<?= e($flash['tipe']) ?> alert-dismissible fade show mt-2" role="alert">
            <?= e($flash['pesan']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button>
        </div>
    </div>
<?php endif; ?>
