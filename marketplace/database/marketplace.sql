-- ==========================================================
-- Database: Marketplace Tradisional
-- Import lewat phpMyAdmin (pilih database dulu, lalu tab Import)
-- Akun contoh -> password semua: password123
-- ==========================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS detail_pesanan;
DROP TABLE IF EXISTS pesanan;
DROP TABLE IF EXISTS keranjang;
DROP TABLE IF EXISTS produk;
DROP TABLE IF EXISTS kategori;
DROP TABLE IF EXISTS users;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id_user      INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama         VARCHAR(100) NOT NULL,
  email        VARCHAR(100) NOT NULL,
  password     VARCHAR(255) NOT NULL,
  role         ENUM('penjual','pembeli') NOT NULL DEFAULT 'pembeli',
  no_hp        VARCHAR(20)  DEFAULT NULL,
  alamat       TEXT         DEFAULT NULL,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_user),
  UNIQUE KEY uk_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE kategori (
  id_kategori   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nama_kategori VARCHAR(60)  NOT NULL,
  PRIMARY KEY (id_kategori),
  UNIQUE KEY uk_kategori_nama (nama_kategori)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE produk (
  id_produk   INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_penjual  INT UNSIGNED NOT NULL,
  id_kategori INT UNSIGNED NOT NULL,
  nama_produk VARCHAR(150) NOT NULL,
  deskripsi   TEXT         DEFAULT NULL,
  harga       DECIMAL(12,2) NOT NULL,
  stok        INT UNSIGNED NOT NULL DEFAULT 0,
  gambar      VARCHAR(255) DEFAULT NULL,
  created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_produk),
  KEY idx_produk_penjual (id_penjual),
  KEY idx_produk_kategori (id_kategori),
  CONSTRAINT fk_produk_penjual  FOREIGN KEY (id_penjual)  REFERENCES users(id_user)       ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_produk_kategori FOREIGN KEY (id_kategori) REFERENCES kategori(id_kategori) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE keranjang (
  id_keranjang INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user      INT UNSIGNED NOT NULL,
  id_produk    INT UNSIGNED NOT NULL,
  jumlah       INT UNSIGNED NOT NULL DEFAULT 1,
  created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id_keranjang),
  UNIQUE KEY uk_keranjang_user_produk (id_user, id_produk),
  KEY idx_keranjang_produk (id_produk),
  CONSTRAINT fk_keranjang_user   FOREIGN KEY (id_user)   REFERENCES users(id_user)    ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_keranjang_produk FOREIGN KEY (id_produk) REFERENCES produk(id_produk) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pesanan (
  id_pesanan        INT UNSIGNED NOT NULL AUTO_INCREMENT,
  kode_pesanan      VARCHAR(30)  NOT NULL,
  id_pembeli        INT UNSIGNED NOT NULL,
  tanggal_pesanan   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total_harga       DECIMAL(14,2) NOT NULL,
  status            ENUM('pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  nama_penerima     VARCHAR(100) NOT NULL,
  no_hp_penerima    VARCHAR(20)  NOT NULL,
  alamat_pengiriman TEXT         NOT NULL,
  catatan           VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_pesanan),
  UNIQUE KEY uk_pesanan_kode (kode_pesanan),
  KEY idx_pesanan_pembeli (id_pembeli),
  CONSTRAINT fk_pesanan_pembeli FOREIGN KEY (id_pembeli) REFERENCES users(id_user) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE detail_pesanan (
  id_detail    INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_pesanan   INT UNSIGNED NOT NULL,
  id_produk    INT UNSIGNED NOT NULL,
  nama_produk  VARCHAR(150) NOT NULL,
  harga_satuan DECIMAL(12,2) NOT NULL,
  jumlah       INT UNSIGNED NOT NULL,
  subtotal     DECIMAL(14,2) NOT NULL,
  PRIMARY KEY (id_detail),
  KEY idx_detail_pesanan (id_pesanan),
  KEY idx_detail_produk (id_produk),
  CONSTRAINT fk_detail_pesanan FOREIGN KEY (id_pesanan) REFERENCES pesanan(id_pesanan) ON DELETE CASCADE  ON UPDATE CASCADE,
  CONSTRAINT fk_detail_produk  FOREIGN KEY (id_produk)  REFERENCES produk(id_produk)   ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------- DATA CONTOH -------------------------
INSERT INTO users (nama, email, password, role, no_hp, alamat) VALUES
('Toko Sari Rasa',   'penjual1@contoh.com', '$2y$10$bOqIBOiMIPRxfTLXMD8jZuuyZtsCimKhU9Qo4cXzM0xWT8a1O2wru', 'penjual', '081234567801', 'Jl. Pasar Baru No. 12, Bengkulu'),
('Rumah Batik Besurek','penjual2@contoh.com', '$2y$10$bOqIBOiMIPRxfTLXMD8jZuuyZtsCimKhU9Qo4cXzM0xWT8a1O2wru', 'penjual', '081234567802', 'Jl. Fatmawati No. 5, Bengkulu'),
('Pembeli Contoh',   'pembeli1@contoh.com', '$2y$10$bOqIBOiMIPRxfTLXMD8jZuuyZtsCimKhU9Qo4cXzM0xWT8a1O2wru', 'pembeli', '081234567803', 'Jl. Merdeka No. 20, Bengkulu');

INSERT INTO kategori (nama_kategori) VALUES
('Makanan Tradisional'), ('Kerajinan Tangan'), ('Batik & Pakaian'), ('Rempah & Bumbu'), ('Minuman Tradisional');

INSERT INTO produk (id_penjual, id_kategori, nama_produk, deskripsi, harga, stok) VALUES
(1, 1, 'Pendap Bengkulu 500 gr',       'Pendap khas Bengkulu dari ikan dan daun talas, dibungkus daun, rasa gurih pedas.', 35000, 40),
(1, 1, 'Lempuk Durian 250 gr',         'Dodol durian asli, manis legit, tahan lama tanpa pengawet.', 28000, 60),
(1, 4, 'Bumbu Rendang Instan 100 gr',  'Bumbu rendang racikan tradisional, cukup tambahkan daging dan santan.', 18000, 100),
(1, 5, 'Jamu Beras Kencur 350 ml',     'Jamu beras kencur segar, diseduh tradisional tanpa pemanis buatan.', 15000, 50),
(2, 3, 'Kain Batik Besurek 2 m',       'Batik khas Bengkulu motif kaligrafi Arab (Besurek), bahan katun halus.', 175000, 15),
(2, 3, 'Kemeja Batik Pria Besurek',    'Kemeja batik lengan panjang motif Besurek, tersedia ukuran M-XL.', 245000, 12),
(2, 2, 'Tas Anyaman Pandan',           'Tas anyaman pandan buatan pengrajin lokal, kuat dan ringan.', 85000, 25),
(2, 2, 'Tikar Rotan Ukuran 120x180',   'Tikar rotan anyaman halus, cocok untuk ruang tamu.', 150000, 8);
