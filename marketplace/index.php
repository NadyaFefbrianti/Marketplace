<?php
$judul = 'Daftar Produk';
$hero  = true;
require_once __DIR__ . '/includes/header.php';

/* ---------- Filter & pencarian ---------- */
$q      = trim($_GET['q'] ?? '');
$kat    = (int)($_GET['kategori'] ?? 0);
$hal    = max(1, (int)($_GET['hal'] ?? 1));
$limit  = 8;
$offset = ($hal - 1) * $limit;

$where  = 'WHERE 1=1';
$tipe   = '';
$param  = [];
if ($q !== '') {
    $where .= ' AND (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)';
    $like = '%' . $q . '%';
    $tipe .= 'ss';
    array_push($param, $like, $like);
}
if ($kat > 0) {
    $where .= ' AND p.id_kategori = ?';
    $tipe .= 'i';
    $param[] = $kat;
}

/* Total data untuk pagination */
$stmt = $koneksi->prepare("SELECT COUNT(*) FROM produk p $where");
if ($param) { $stmt->bind_param($tipe, ...$param); }
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();
$totalHal = max(1, (int)ceil($total / $limit));

/* Data produk */
$stmt = $koneksi->prepare(
    "SELECT p.*, k.nama_kategori, u.nama AS nama_penjual
     FROM produk p
     JOIN kategori k ON k.id_kategori = p.id_kategori
     JOIN users u ON u.id_user = p.id_penjual
     $where ORDER BY p.created_at DESC, p.id_produk DESC LIMIT ? OFFSET ?"
);
$tipe2  = $tipe . 'ii';
$param2 = array_merge($param, [$limit, $offset]);
$stmt->bind_param($tipe2, ...$param2);
$stmt->execute();
$produk = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$kategori = $koneksi->query('SELECT * FROM kategori ORDER BY nama_kategori')->fetch_all(MYSQLI_ASSOC);

/* Data pendukung tampilan: statistik, jumlah produk per kategori, kategori minuman */
$stat = $koneksi->query("SELECT (SELECT COUNT(*) FROM produk) AS produk,
                                (SELECT COUNT(*) FROM users WHERE role = 'penjual') AS penjual,
                                (SELECT COUNT(*) FROM kategori) AS kategori")->fetch_assoc();
$jmlKat = [];
foreach ($koneksi->query('SELECT id_kategori, COUNT(*) AS n FROM produk GROUP BY id_kategori') as $r) {
    $jmlKat[(int)$r['id_kategori']] = (int)$r['n'];
}
$idMinuman = 0;
foreach ($kategori as $k) {
    if (stripos($k['nama_kategori'], 'minuman') !== false) { $idMinuman = (int)$k['id_kategori']; }
}

function link_hal(int $h, string $q, int $kat): string
{
    return url('index.php') . '?' . http_build_query(['q' => $q, 'kategori' => $kat, 'hal' => $h]) . '#produk';
}
?>
<!-- ============ HERO ============ -->
<section class="hero">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <h1>Cita rasa Nusantara, langsung dari penjualnya</h1>
                <p class="lead-hero">Jajanan khas, rempah, jamu, batik, dan kerajinan tangan dari penjual lokal. Pilih, masukkan keranjang, dan pesan.</p>
                <form id="cari" class="hero-cari" method="get" action="<?= e(url('index.php')) ?>#produk" role="search">
                    <input type="text" name="q" class="form-control" placeholder="Cari produk..." aria-label="Cari produk" value="<?= e($q) ?>">
                    <select name="kategori" class="form-select" aria-label="Kategori">
                        <option value="0">Semua kategori</option>
                        <?php foreach ($kategori as $k): ?>
                            <option value="<?= $k['id_kategori'] ?>" <?= $kat === (int)$k['id_kategori'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="btn btn-brand"><i class="bi bi-search"></i> Cari</button>
                </form>
            </div>
            <div class="col-lg-6">
                <div class="hero-gambar">
                    <img class="hero-daun-kanan" src="<?= e(url('assets/img/leaves.svg')) ?>" alt="">
                    <img class="mangkuk" src="<?= e(url('assets/img/hero-bowl.svg')) ?>" alt="Mangkuk berisi buah dan kacang segar">
                    <img class="hero-daun" src="<?= e(url('assets/img/leaves.svg')) ?>" alt="">
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ TENTANG ============ -->
<section class="bagian krem torn-bawah ke-halaman" id="tentang">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-5">
                <div class="kartu-putih justify-content-center">
                    <div class="statistik"><b><?= (int)$stat['produk'] ?></b><span>Produk</span></div>
                    <div class="statistik"><b><?= (int)$stat['penjual'] ?></b><span>Penjual</span></div>
                    <div class="statistik"><b><?= (int)$stat['kategori'] ?></b><span>Kategori</span></div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-4 flex-wrap flex-md-nowrap">
                    <img class="gambar-samping" src="<?= e(url('assets/img/nuts-cluster.svg')) ?>" alt="">
                    <div>
                        <h2 class="judul-bagian">Dari pasar ke meja makanmu</h2>
                        <p class="sub-bagian mb-0">Setiap produk dijual langsung oleh pembuat atau pedagang lokal. Stok selalu diperbarui, dan setiap pesanan tercatat rapi sampai barang tiba.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============ DAFTAR PRODUK ============ -->
<section class="bagian halaman torn-bawah ke-krem" id="produk">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
            <div>
                <h2 class="judul-bagian">Produk pilihan</h2>
                <p class="sub-bagian mb-0"><?= $q !== '' ? 'Hasil pencarian "' . e($q) . '"' : 'Segar, asli, dan buatan tangan penjual lokal.' ?></p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a class="chip-kat <?= $kat === 0 ? 'aktif' : '' ?>" href="<?= e(url('index.php?q=' . urlencode($q) . '#produk')) ?>">Semua</a>
                <?php foreach ($kategori as $k): ?>
                    <a class="chip-kat <?= $kat === (int)$k['id_kategori'] ? 'aktif' : '' ?>" href="<?= e(url('index.php?kategori=' . (int)$k['id_kategori'] . '&q=' . urlencode($q) . '#produk')) ?>"><?= e($k['nama_kategori']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (!$produk): ?>
            <div class="alert alert-secondary">Produk tidak ditemukan. Coba kata kunci lain atau pilih kategori Semua.</div>
        <?php else: ?>
        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-3 g-lg-4">
            <?php foreach ($produk as $p): ?>
            <div class="col">
                <article class="kartu-produk">
                    <a class="foto" href="<?= e(url('produk_detail.php?id=' . $p['id_produk'])) ?>" aria-label="Lihat <?= e($p['nama_produk']) ?>">
                        <?= gambar_produk($p) ?>
                        <?php if ((int)$p['stok'] === 0): ?><span class="label-habis">Stok habis</span><?php endif; ?>
                    </a>
                    <div class="kat"><?= e($p['nama_kategori']) ?></div>
                    <h2><a href="<?= e(url('produk_detail.php?id=' . $p['id_produk'])) ?>"><?= e($p['nama_produk']) ?></a></h2>
                    <div class="penjual"><i class="bi bi-shop"></i> <?= e($p['nama_penjual']) ?> &middot; stok <?= (int)$p['stok'] ?></div>
                    <div class="baris-beli">
                        <span class="harga"><?= rupiah($p['harga']) ?></span>
                        <?php if ((int)$p['stok'] > 0 && (!$user || $user['role'] === 'pembeli')): ?>
                        <form action="<?= e(url('keranjang_aksi.php')) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="aksi" value="tambah">
                            <input type="hidden" name="id_produk" value="<?= (int)$p['id_produk'] ?>">
                            <input type="hidden" name="jumlah" value="1">
                            <button class="btn btn-tambah" title="Tambah ke keranjang"><i class="bi bi-bag-plus"></i> Tambah</button>
                        </form>
                        <?php elseif ((int)$p['stok'] === 0): ?>
                            <button class="btn btn-tambah" disabled>Habis</button>
                        <?php else: ?>
                            <a class="btn btn-tambah" href="<?= e(url('produk_detail.php?id=' . $p['id_produk'])) ?>">Lihat</a>
                        <?php endif; ?>
                    </div>
                </article>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($totalHal > 1): ?>
        <nav class="mt-5" aria-label="Halaman produk">
            <ul class="pagination justify-content-center">
                <li class="page-item <?= $hal <= 1 ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(link_hal($hal - 1, $q, $kat)) ?>" aria-label="Sebelumnya">&laquo;</a></li>
                <?php for ($i = 1; $i <= $totalHal; $i++): ?>
                    <li class="page-item <?= $i === $hal ? 'active' : '' ?>"><a class="page-link" href="<?= e(link_hal($i, $q, $kat)) ?>"><?= $i ?></a></li>
                <?php endfor; ?>
                <li class="page-item <?= $hal >= $totalHal ? 'disabled' : '' ?>"><a class="page-link" href="<?= e(link_hal($hal + 1, $q, $kat)) ?>" aria-label="Berikutnya">&raquo;</a></li>
            </ul>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<!-- ============ KEUNGGULAN ============ -->
<section class="bagian krem torn-bawah ke-halaman">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-3 text-center">
                <img class="gambar-samping" src="<?= e(url('assets/img/nuts-bowl.svg')) ?>" alt="Semangkuk kacang">
            </div>
            <div class="col-lg-3"><div class="fitur"><span class="ikon"><i class="bi bi-shop-window"></i></span><div><h3>Langsung dari penjual</h3><p>Tanpa perantara, harga yang tertera adalah harga dari penjualnya.</p></div></div></div>
            <div class="col-lg-3 garis-tegak"><div class="fitur ps-lg-2"><span class="ikon"><i class="bi bi-box-seam"></i></span><div><h3>Stok selalu terbarui</h3><p>Barang yang habis tidak bisa dipesan, jadi pesananmu pasti tersedia.</p></div></div></div>
            <div class="col-lg-3 garis-tegak"><div class="fitur ps-lg-2"><span class="ikon"><i class="bi bi-receipt"></i></span><div><h3>Pesanan tercatat</h3><p>Pantau status pesanan dari menunggu, diproses, sampai selesai.</p></div></div></div>
        </div>
    </div>
</section>

<!-- ============ PROMO KATEGORI ============ -->
<section class="bagian halaman pb-0">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4">
                <div class="panel-kategori">
                    <h2 class="h4 mb-3">Jelajahi kategori</h2>
                    <ul>
                        <?php foreach ($kategori as $k): ?>
                        <li><a href="<?= e(url('index.php?kategori=' . (int)$k['id_kategori'] . '#produk')) ?>"><span><?= e($k['nama_kategori']) ?></span><small><?= $jmlKat[(int)$k['id_kategori']] ?? 0 ?> produk</small></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="panel-promo">
                    <div class="position-relative" style="z-index:1">
                        <h2>Jamu segar, diseduh tradisional</h2>
                        <p class="sub-bagian">Beras kencur dan minuman rempah lain, tanpa pemanis buatan.</p>
                        <a class="btn btn-brand" href="<?= e(url('index.php?kategori=' . $idMinuman . '#produk')) ?>">Lihat minuman</a>
                    </div>
                    <img class="botol" src="<?= e(url('assets/img/bottles.svg')) ?>" alt="Tiga botol jamu">
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
