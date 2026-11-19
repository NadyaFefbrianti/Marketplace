# Marketplace Tradisional

Aplikasi web marketplace sederhana (penjual menawarkan produk, pembeli memesan) — **PHP Native + MySQLi + Bootstrap 5**.

Tampilan: tema hangat bernuansa buah & kacang (kuning mentega, krem, tepi kertas sobek, tombol cokelat-jingga) dengan font Fraunces + Outfit dan ilustrasi SVG di `assets/img/`.

## Fitur
- Daftar produk (cari, filter kategori, pagination) & detail produk
- CRUD produk oleh penjual (termasuk unggah gambar)
- Keranjang belanja (tambah, ubah jumlah, hapus)
- Checkout → transaksi disimpan ke database (`pesanan` + `detail_pesanan`), stok berkurang otomatis (database transaction)
- Riwayat pesanan pembeli & pesanan masuk penjual (ubah status)
- Login/registrasi dengan `password_hash`, prepared statement, token CSRF

## Instalasi lokal (XAMPP/Laragon)
1. Letakkan folder di `htdocs/marketplace`.
2. Buat database `marketplace` di phpMyAdmin, lalu **Import** `database/marketplace.sql`.
3. Salin `config/koneksi.example.php` → `config/koneksi.php`, sesuaikan:
   - `DB_USER`, `DB_PASS`, `DB_NAME`
   - `BASE_URL` = `'/marketplace'` (lokal) atau `''` (jika di root domain / InfinityFree)
4. Buka `http://localhost/marketplace/`.

## Akun contoh (password: `password123`)
| Peran | Email |
|---|---|
| Penjual | penjual1@contoh.com / penjual2@contoh.com |
| Pembeli | pembeli1@contoh.com |

## Deploy ke InfinityFree
1. Buat akun hosting + database MySQL di panel, import `database/marketplace.sql` lewat phpMyAdmin.
2. Buat `config/koneksi.php` dari contoh, isi host/user/password/nama database dari panel (`BASE_URL` = `''`).
3. Upload semua file ke folder `htdocs`.

> `config/koneksi.php` sengaja masuk `.gitignore` agar kredensial tidak ter-upload ke GitHub.

## Struktur database
`users`, `kategori`, `produk`, `keranjang`, `pesanan`, `detail_pesanan` — lihat `database/marketplace.sql`.
