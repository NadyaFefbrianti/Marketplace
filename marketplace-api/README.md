# REST API Marketplace Tradisional (PHP Native + MySQLi)

## Instalasi (XAMPP)
1. Start Apache dan MySQL di XAMPP Control Panel.
2. Buat database `marketplace` di phpMyAdmin, lalu Import `database/marketplace.sql`.
3. Salin `config/config.example.php` menjadi `config/config.php`, isi sesuai MySQL lokal.
4. Letakkan folder ini di `C:\xampp\htdocs\marketplace-api\`.

## Endpoint
| Method | URL | Fungsi |
|---|---|---|
| GET | /api/produk.php | Semua produk (opsional ?kategori=ID) |
| GET | /api/produk.php?id=1 | Detail produk |
| GET | /api/kategori.php | Daftar kategori |
| POST | /api/pesanan.php | Buat pesanan baru (body JSON) |
| GET | /api/pesanan.php?id=1 | Detail pesanan |
