<?php
/**
 * Fungsi bantu global: session, keamanan, autentikasi, format, upload.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Buffer output agar redirect (header Location) tetap aman setelah HTML tampil
if (ob_get_level() === 0) {
    ob_start();
}
require_once __DIR__ . '/../config/koneksi.php';

/* ---------- Umum ---------- */
function e($teks): string
{
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

function rupiah($angka): string
{
    return 'Rp ' . number_format((float)$angka, 0, ',', '.');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

/* ---------- Flash message ---------- */
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_cek(): void
{
    $kirim = $_POST['csrf'] ?? '';
    if (!hash_equals($_SESSION['csrf'] ?? '', $kirim)) {
        http_response_code(403);
        die('Token keamanan tidak valid. Silakan kembali dan muat ulang halaman.');
    }
}

/* ---------- Autentikasi ---------- */
function sudah_login(): bool
{
    return isset($_SESSION['user']);
}

function user_aktif(): ?array
{
    return $_SESSION['user'] ?? null;
}

function wajib_login(): void
{
    if (!sudah_login()) {
        set_flash('warning', 'Silakan login terlebih dahulu.');
        redirect('login.php');
    }
}

function wajib_role(string $role): void
{
    wajib_login();
    if ($_SESSION['user']['role'] !== $role) {
        set_flash('danger', 'Anda tidak memiliki akses ke halaman tersebut.');
        redirect('index.php');
    }
}

/* ---------- Jumlah item keranjang (untuk badge navbar) ---------- */
function jumlah_keranjang(mysqli $db): int
{
    if (!sudah_login() || $_SESSION['user']['role'] !== 'pembeli') {
        return 0;
    }
    $id = (int)$_SESSION['user']['id_user'];
    $stmt = $db->prepare('SELECT COALESCE(SUM(jumlah),0) FROM keranjang WHERE id_user = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->bind_result($n);
    $stmt->fetch();
    $stmt->close();
    return (int)$n;
}

/* ---------- Upload gambar produk ---------- */
const MAKS_UKURAN_GAMBAR = 2097152; // 2 MB

/**
 * Return: nama file baru, null jika tidak ada file diunggah.
 * Melempar RuntimeException jika file tidak valid.
 */
function upload_gambar(array $file): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Gagal mengunggah gambar.');
    }
    if ($file['size'] > MAKS_UKURAN_GAMBAR) {
        throw new RuntimeException('Ukuran gambar maksimal 2 MB.');
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime  = $finfo->file($file['tmp_name']);
    $ekstensi = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    if (!isset($ekstensi[$mime])) {
        throw new RuntimeException('Format gambar harus JPG, PNG, atau WEBP.');
    }
    $nama = bin2hex(random_bytes(12)) . '.' . $ekstensi[$mime];
    $tujuan = __DIR__ . '/../uploads/produk/' . $nama;
    if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
        throw new RuntimeException('Gagal menyimpan gambar ke server.');
    }
    return $nama;
}

function hapus_gambar(?string $nama): void
{
    if ($nama && preg_match('/^[a-f0-9]{24}\.(jpg|png|webp)$/', $nama)) {
        $path = __DIR__ . '/../uploads/produk/' . $nama;
        if (is_file($path)) {
            @unlink($path);
        }
    }
}

/* ---------- Tampilan gambar produk (fallback ilustrasi emoji) ---------- */
function emoji_produk(string $nama): string
{
    $peta = [
        'batik' => '👘', 'kemeja' => '👔', 'kain' => '🧵', 'tas' => '👜', 'tikar' => '🧺', 'anyam' => '🧺',
        'jamu' => '🍵', 'kencur' => '🍵', 'bumbu' => '🌶️', 'rendang' => '🌶️', 'durian' => '🍈',
        'lempuk' => '🍬', 'dodol' => '🍬', 'pendap' => '🍱', 'madu' => '🍯', 'kopi' => '☕', 'teh' => '🍵',
    ];
    $kecil = strtolower($nama);
    foreach ($peta as $kata => $emoji) {
        if (strpos($kecil, $kata) !== false) {
            return $emoji;
        }
    }
    return '🛍️';
}

function gambar_produk(array $p, string $kelas = ''): string
{
    if (!empty($p['gambar'])) {
        return '<img src="' . e(url('uploads/produk/' . $p['gambar'])) . '" class="' . e($kelas) . '" alt="' . e($p['nama_produk']) . '">';
    }
    $nama   = (string)$p['nama_produk'];
    $warna  = ['#FBE7A1', '#FFD9A8', '#F6C9C2', '#D8EBC0', '#F2E3B5'];
    $tint   = $warna[abs(crc32($nama)) % count($warna)];
    return '<div class="img-placeholder ' . e($kelas) . '" style="--tint:' . $tint . '" role="img" aria-label="' . e($nama) . '"><span>' . emoji_produk($nama) . '</span></div>';
}

function label_status(string $status): string
{
    $map = [
        'pending'    => ['warning text-dark', 'Menunggu'],
        'diproses'   => ['info text-dark',    'Diproses'],
        'dikirim'    => ['primary',           'Dikirim'],
        'selesai'    => ['success',           'Selesai'],
        'dibatalkan' => ['danger',            'Dibatalkan'],
    ];
    [$warna, $teks] = $map[$status] ?? ['secondary', $status];
    return '<span class="badge bg-' . $warna . '">' . e($teks) . '</span>';
}
