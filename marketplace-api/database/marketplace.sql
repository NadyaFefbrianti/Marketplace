-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2026 at 02:08 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `detail_pesanan`
--

CREATE TABLE `detail_pesanan` (
  `id_detail` int(10) UNSIGNED NOT NULL,
  `id_pesanan` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL,
  `subtotal` decimal(14,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detail_pesanan`
--

INSERT INTO `detail_pesanan` (`id_detail`, `id_pesanan`, `id_produk`, `nama_produk`, `harga_satuan`, `jumlah`, `subtotal`) VALUES
(1, 1, 8, 'Tikar Rotan Ukuran 120x180', 150000.00, 1, 150000.00),
(2, 1, 1, 'Pendap Bengkulu 500 gr', 35000.00, 1, 35000.00),
(3, 1, 2, 'Lempuk Durian 250 gr', 28000.00, 1, 28000.00),
(4, 2, 4, 'Jamu Beras Kencur 350 ml', 15000.00, 1, 15000.00),
(5, 2, 2, 'Lempuk Durian 250 gr', 28000.00, 1, 28000.00),
(6, 2, 3, 'Bumbu Rendang Instan 100 gr', 18000.00, 1, 18000.00),
(7, 2, 1, 'Pendap Bengkulu 500 gr', 35000.00, 1, 35000.00),
(8, 3, 3, 'Bumbu Rendang Instan 100 gr', 18000.00, 1, 18000.00),
(9, 3, 2, 'Lempuk Durian 250 gr', 28000.00, 1, 28000.00),
(10, 3, 5, 'Kain Batik Besurek 2 m', 175000.00, 1, 175000.00),
(11, 3, 7, 'Tas Anyaman Pandan', 85000.00, 1, 85000.00),
(12, 4, 12, 'Madu Hutan Sialang', 80000.00, 1, 80000.00),
(13, 4, 4, 'Jamu Beras Kencur 350 ml', 15000.00, 1, 15000.00),
(14, 4, 2, 'Lempuk Durian 250 gr', 28000.00, 1, 28000.00);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `nama_kategori` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(3, 'Batik & Pakaian'),
(2, 'Kerajinan Tangan'),
(6, 'Kue & Jajanan Pasar'),
(7, 'Madu & Hasil Hutan'),
(1, 'Makanan Tradisional'),
(5, 'Minuman Tradisional'),
(4, 'Rempah & Bumbu');

-- --------------------------------------------------------

--
-- Table structure for table `keranjang`
--

CREATE TABLE `keranjang` (
  `id_keranjang` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_produk` int(10) UNSIGNED NOT NULL,
  `jumlah` int(10) UNSIGNED NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pesanan`
--

CREATE TABLE `pesanan` (
  `id_pesanan` int(10) UNSIGNED NOT NULL,
  `kode_pesanan` varchar(30) NOT NULL,
  `id_pembeli` int(10) UNSIGNED NOT NULL,
  `tanggal_pesanan` datetime NOT NULL DEFAULT current_timestamp(),
  `total_harga` decimal(14,2) NOT NULL,
  `status` enum('pending','diproses','dikirim','selesai','dibatalkan') NOT NULL DEFAULT 'pending',
  `nama_penerima` varchar(100) NOT NULL,
  `no_hp_penerima` varchar(20) NOT NULL,
  `alamat_pengiriman` text NOT NULL,
  `catatan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pesanan`
--

INSERT INTO `pesanan` (`id_pesanan`, `kode_pesanan`, `id_pembeli`, `tanggal_pesanan`, `total_harga`, `status`, `nama_penerima`, `no_hp_penerima`, `alamat_pengiriman`, `catatan`) VALUES
(1, 'INV2611175F4F37', 3, '2026-11-17 11:05:26', 213000.00, 'selesai', 'Pembeli Contoh', '081234567803', 'Jl. Merdeka No. 20, Bengkulu', ''),
(2, 'INV2611174988FA', 4, '2026-11-17 11:09:11', 96000.00, 'selesai', 'Dina Angkasa', '082255217770', 'jl rawa makmur', ''),
(3, 'INV261117A087BD', 4, '2026-11-17 11:53:41', 306000.00, 'pending', 'Dina Angkasa', '082255217770', 'Jl. Merdeka No. 20, Bengkulu', ''),
(4, 'INV261117E288C4', 4, '2026-11-17 13:21:53', 123000.00, 'pending', 'Dina Angkasa', '082255217770', 'jl sawah lebar', '');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `id_produk` int(10) UNSIGNED NOT NULL,
  `id_penjual` int(10) UNSIGNED NOT NULL,
  `id_kategori` int(10) UNSIGNED NOT NULL,
  `nama_produk` varchar(150) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL,
  `stok` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `gambar` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`id_produk`, `id_penjual`, `id_kategori`, `nama_produk`, `deskripsi`, `harga`, `stok`, `gambar`, `created_at`) VALUES
(1, 1, 1, 'Pendap Ikan Khas Bengkulu', 'Nikmati kelezatan Pendap, hidangan khas pesisir Bengkulu yang telah dikenal hingga ke mancanegara. Terbuat dari potongan ikan segar yang dibalut bumbu rempah kaya rasa dan parutan kelapa, lalu dibungkus berlapis daun talas dan dikukus selama berjam-jam hingga bumbu meresap sempurna. Dikemas menggunakan teknologi vakum higienis agar aman dikirim ke seluruh Indonesia.Detail Produk:\r\n• Saran penyajian: Kukus kembali selama 10–15 menit sebelum disajikan bersama nasi hangat.\r\n• Ketahanan: Tahan 3–4 hari di suhu ruang, dan hingga 1 bulan di dalam freezer.', 35000.00, 38, '2bb0793ed7ac3cda31f27df0.jpg', '2026-11-17 03:55:26'),
(2, 1, 6, 'Lempuk Durian', 'Nikmati kelezatan autentik Lempuk Durian Asli Bengkulu, dodol legendaris khas Bumi Rafflesia yang dibuat dari 100% daging buah durian matang pohon tanpa campuran tepung maupun bahan pengawet. Dimasak secara tradisional dengan gula pasir murni selama berjam-jam hingga menghasilkan tekstur yang lembut, padat, tidak lengket di tangan, serta aroma durian yang harum menggugah selera. Dikemas dalam potongan kecil siap santap yang praktis dinikmati kapan saja maupun dijadikan buah tangan istimewa untuk keluarga dan kerabat.Detail Produk:• Komposisi: 100% daging durian asli, gula pasir, dan sedikit garam.\r\n• Berat Bersih: 250 gram (berisi potongan kecil terbungkus rapi).\r\n• Kemasan: Mika / kotak kemasan oleh-oleh tersegel higienis.\r\n• Daya Tahan: Tahan hingga 1–2 bulan di suhu ruang (simpan di tempat kering dan sejuk).', 28000.00, 56, '520fb7c0be788970288b259d.jpg', '2026-11-17 03:55:26'),
(3, 1, 4, 'Bumbu Rendang Instan 100 gr', 'Bumbu rendang racikan tradisional, cukup tambahkan daging dan santan.', 18000.00, 98, '71f0155389d4275a6c131e5a.png', '2026-11-17 03:55:26'),
(4, 1, 5, 'Jamu Beras Kencur Alami', 'Segarkan kembali tubuh dan pikiran Anda dengan Jamu Beras Kencur, ramuan herbal tradisional yang diracik dari rimpang kencur segar, beras sangrai pilihan, jahe, serta manis alami gula aren dan gula pasir murni. Diolah secara higienis tanpa bahan pengawet maupun pemanis buatan sehingga menghasilkan rasa manis-hangat yang lembut, tidak pahit, dan menyegarkan. Sangat baik dikonsumsi rutin untuk membantu meredakan pegal-pegal, menghangatkan tenggorokan, menambah nafsu makan, dan menjaga stamina harian keluarga.Detail Produk:\r\n• Komposisi: Kencur segar, beras pilihan, jahe, gula aren, gula pasir, dan sedikit asam jawa/garam.\r\n• Varian Kemasan:\r\n• Siap Minum: Botol kaca / plastik food-grade 250 ml – 500 ml (simpan di dalam kulkas, tahan 5–7 hari).\r\n• Serbuk Instan: Kemasan standing pouch 200 gram (cukup seduh 2 sendok makan dengan 150 ml air hangat, tahan hingga 6 bulan).', 15000.00, 48, 'de85b56be422b2b049c24cb0.jpg', '2026-11-17 03:55:26'),
(5, 1, 3, 'Kain Batik Besurek', 'Tampil anggun dan berwibawa dengan Kain Batik Besurek, wastra kebanggaan masyarakat Bengkulu yang memadukan keindahan seni kaligrafi Arab dengan motif bunga ikonik Rafflesia arnoldii. Dicetak di atas bahan katun primisima yang halus, adem, dan nyaman dipakai sepanjang hari, baik untuk acara formal maupun seragam kerja.Detail Produk:\r\n• Bahan: Katun Primisima (tidak panas dan menyerap keringat).\r\n• Ukuran kain: Panjang 2 meter x Lebar 1,15 meter.', 175000.00, 14, 'a1de30ce61b29a6e799afe11.jpg', '2026-11-17 03:55:26'),
(6, 1, 3, 'Kemeja Batik Pria Besurek', 'Tampil gagah, rapi, dan berwibawa dengan Kemeja Batik Besurek Pria khas Bengkulu. Menampilkan motif autentik perpaduan kaligrafi Arab dan bunga ikonik Rafflesia arnoldii yang sarat nilai budaya dan filosofi. Dijahit oleh penjahit lokal berpengalaman menggunakan bahan katun primisima pilihan yang halus, adem, serta dilengkapi lapisan dalam (furing) yang nyaman menyerap keringat. Sangat cocok dikenakan untuk acara resmi, pakaian kerja kantor, kondangan, maupun pertemuan penting.Detail Produk:• Bahan: Katun Primisima halus + lapisan furing adem.\r\n• Potongan: Regular Fit dengan jahitan rapi dan saku dada depan yang menyatu sempurna dengan motif (matching motif).\r\n• Pilihan Lengan: Lengan Panjang & Lengan Pendek.\r\n• Ukuran Tersedia: M, L, XL, XXL.', 245000.00, 12, '4d781cf15aebaaf0da8a5845.jpg', '2026-11-17 03:55:26'),
(7, 1, 2, 'Tas Anyaman Rotan', 'Percantik gaya harian Anda dengan Tas Anyaman Rotan buatan tangan perajin lokal Bengkulu. Menggunakan rotan hutan pilihan yang dianyam rapat dan dilapisi finishing halus sehingga tahan lama dan anti jamur. Desainnya yang klasik namun modern menjadikan tas ini pas untuk acara santai, liburan, maupun hadiah istimewa.Detail Produk:\r\n• Diameter: 20 cm dengan tali selempang kulit sintetis dan kancing pengait.', 85000.00, 24, 'b5a4374f034ebe26d39ddfbc.jpg', '2026-11-17 03:55:26'),
(8, 1, 2, 'Tampah Bambu Anyaman', 'Tampah Bambu Tradisional merupakan hasil karya anyaman tangan perajin Bengkulu yang terbuat dari bilah bambu pilihan berkualitas. Dikerjakan dengan teknik anyaman yang kuat dan bingkai rotan di bagian tepi agar kokoh dan tidak mudah melengkung. Selain praktis untuk keperluan dapur seperti menampi beras atau menjemur bahan makanan, tampah ini juga sangat cantik digunakan sebagai wadah penyajian tumpeng, hantaran kue tradisional, maupun dekorasi dinding bergaya etnik.Detail Produk:• Bahan: Bambu pilihan dengan ikatan bingkai rotan yang kuat.\r\n• Pilihan Diameter: Kecil (35 cm), Sedang (45 cm), Besar (55 cm).\r\n• Keunggulan: Ringan, kokoh, ramah lingkungan, dan bebas bahan kimia berbahaya.', 80000.00, 7, '69107a25e13081ebc5f41d1b.jpg', '2026-11-17 03:55:26'),
(9, 1, 5, 'Sirup Jeruk Kalamansi', 'Rasakan kesegaran alami Sirup Jeruk Kalamansi Bengkulu yang diolah langsung dari perasan jeruk kalamansi pilihan petani lokal. Memadukan cita rasa asam segar khas jeruk kalamansi dengan manis gula murni tanpa pemanis buatan. Kaya akan vitamin C dan sangat menyegarkan disajikan dingin dengan es batu maupun diseduh hangat untuk melegakan tenggorokan.Detail Produk:\r\n• Saran penyajian: Campurkan 3–4 sendok makan sirup ke dalam 200 ml air dingin atau hangat.\r\n• Isi bersih: Botol 500 ml (untuk sekitar 12–15 gelas).', 10000.00, 25, 'cccd79e50f396e3d6aed82b0.png', '2026-11-17 05:05:09'),
(10, 1, 2, 'Tikar Rotan Ukuran', 'Hadirkan nuansa alami yang hangat dan estetik di ruang keluarga Anda dengan Tikar Rotan Asli Bengkulu. Dibuat secara manual (handmade) oleh perajin lokal menggunakan rotan hutan pilihan yang telah melalui proses pengeringan dan penghalusan secara teliti. Anyamannya rapat, lentur, tidak mudah patah, serta memberikan sensasi sejuk saat diduduki. Sangat awet digunakan hingga bertahun-tahun dan mudah digulung untuk disimpan atau dibawa bepergian.Detail Produk:• Bahan: 100% rotan alam pilihan dengan finishing pelindung alami anti jamur.\r\n• Ukuran: 150 cm x 200 cm / 200 cm x 300 cm (sesuaikan dengan stok).\r\n• Perawatan: Cukup dilap dengan kain setengah basah dan diangin-anginkan secara berkala.', 150000.00, 7, 'af55a1cea84ed2e5164d5558.jpg', '2026-11-17 05:11:33'),
(11, 1, 6, 'Kue Bay Tat', 'Kue Bay Tat adalah kue kebanggaan masyarakat Bengkulu yang dulunya merupakan sajian istimewa para raja. Dibuat dari adonan tepung, mentega, dan santan pilihan yang dipanggang hingga empuk, lalu diberi olesan selai nanas asli yang manis-segar di bagian atasnya. Cocok dinikmati bersama secangkir kopi hangat maupun dijadikan buah tangan istimewa untuk kerabat.Detail Produk:\r\n• Ukuran: Loyang kotak 20x20 cm (bisa dipotong menjadi 16–20 potong).\r\n• Daya tahan: Tahan hingga 7–10 hari di suhu ruang.', 25000.00, 20, '67c7f6198404b1069164acb6.jpg', '2026-11-17 06:15:24'),
(12, 1, 7, 'Madu Hutan Sialang', 'Nikmati kemurnian alam Bumi Rafflesia dalam setiap tetes Madu Hutan Sialang Asli Bengkulu. Dipanen secara lestari langsung dari sarang lebah hutan liar (Apis dorsata) di pedalaman hutan Bengkulu tanpa proses pemanasan berlebih maupun campuran bahan pengawet. Memiliki aroma nektar bunga hutan yang khas dan rasa manis alami yang kaya akan antioksidan untuk menjaga daya tahan tubuh keluarga Anda.Keunggulan Produk:• 100% madu hutan murni tanpa campuran gula atau air.\r\n• Dikemas dalam botol food-grade tersegel rapat dan aman dikirim ke luar kota.\r\n• Tersedia dalam ukuran 250 ml dan 500 ml.', 80000.00, 9, 'bda98f3d5aeb0b5e92449186.png', '2026-11-17 06:19:56'),
(13, 1, 4, 'Tempoyak Durian Bengkulu', 'Tempoyak Durian Bengkulu terbuat dari daging buah durian lokal matang pohon yang difermentasi secara alami dengan sedikit garam. Menghasilkan cita rasa asam-gurih yang kuat dan tekstur lembut, siap diolah menjadi Gulai Tempoyak Ikan Patin, sambal tempoyak, maupun campuran pepes khas Sumatera.Detail Produk:\r\n• Kemasan: Toples plastik tebal kedap udara + segel aluminium foil anti bocor.\r\n• Berat bersih: 300 gram.', 80000.00, 5, NULL, '2026-11-17 12:49:30'),
(14, 1, 6, 'Kue Perut Punai', 'Kue Perut Punai merupakan camilan ringan khas Bengkulu dengan bentuk melingkar unik yang ikonik. Terbuat dari adonan tepung beras berkualitas yang digoreng renyah dan dibalut lapisan gula aren manis (tersedia juga varian rasa gurih). Teksturnya renyah di luar, tidak keras, dan sangat pas menemani waktu santai Anda.Detail Produk:\r\n• Varian rasa: Manis Gula Aren / Original Gurih.\r\n• Berat bersih: 200 gram.', 40000.00, 10, NULL, '2026-11-17 12:50:38'),
(15, 1, 6, 'Manisan Terong Ungu', 'Cicipi keunikan kuliner tradisional Bengkulu lewat Manisan Terong Ungu, camilan legendaris yang sering dijuluki sebagai \"kurma khas Bengkulu\". Terbuat dari buah terong ungu segar pilihan yang diolah secara teliti dengan gula pasir murni dan melalui proses pengeringan higienis tanpa bahan pengawet maupun pemanis buatan. Menghasilkan tekstur yang lembut, sedikit kenyal, tidak pahit, dan memiliki rasa manis legit yang bikin ketagihan. Sangat cocok dijadikan suguhan tamu, camilan sehat keluarga, maupun buah tangan unik untuk kerabat di luar kota.Detail Produk:• Komposisi: Terong ungu segar, gula pasir murni, dan sedikit garam/kapur sirih untuk menjaga tekstur.\r\n• Berat Bersih: 250 gram.\r\n• Kemasan: Toples plastik bening tersegel rapi atau standing pouch kedap udara.\r\n• Daya Tahan: Tahan hingga 1–2 bulan di suhu ruang (simpan di tempat sejuk dan tertutup rapat).', 45000.00, 9, '0db362e91b31fa3092f3c7c9.jpg', '2026-11-17 12:52:04'),
(16, 1, 1, 'Kerupuk Gurita Khas Kaur', 'Kerupuk Gurita Khas Kaur dibuat dari olahan daging gurita segar hasil tangkapan nelayan pesisir selatan Bengkulu. Melalui proses pengolahan yang teliti, kerupuk ini memiliki tekstur yang renyah, tidak amis, dan cita rasa gurih laut yang khas. Cocok dijadikan camilan santai maupun pelengkap makan sehari-hari.Detail Produk:\r\n• Varian rasa: Original Gurih / Balado Pedas Manis.\r\n• Berat bersih: 150 gram.', 25000.00, 5, NULL, '2026-11-17 12:53:01'),
(17, 1, 5, 'Serbuk Jahe Merah', 'Serbuk Jahe Merah Bengkulu diracik dari rimpang jahe merah pilihan yang dipadukan dengan gula aren dan rempah-rempah lokal seperti kayu manis dan cengkeh. Dikemas dalam bentuk serbuk kristal siap seduh tanpa ampas, menghasilkan sensasi hangat yang pas untuk menjaga stamina dan kebugaran tubuh setiap hari.Detail Produk:\r\n• Berat bersih: 200 gram dalam kemasan standing pouch kedap udara.', 30000.00, 2, NULL, '2026-11-17 12:54:39'),
(18, 1, 3, 'Selendang Batik Besurek', 'Selendang Batik Besurek hadir sebagai pilihan buah tangan dan aksesori fesyen yang praktis namun tetap sarat makna budaya Bengkulu. Cocok dipadukan dengan kemeja polos, blazer, maupun busana kasual untuk memberikan sentuhan etnik yang elegan. Ringan, mudah dibawa, dan dilengkapi kemasan kotak suvenir yang rapi.Detail Produk:\r\n• Ukuran: 150 cm x 30 cm.', 100000.00, 5, NULL, '2026-11-17 12:55:46'),
(19, 1, 7, 'Gula Semut Aren Rejang', '• \r\nGula Semut Aren Rejang diolah dari air nira pohon aren pilihan yang tumbuh subur di dataran tinggi pegunungan Bengkulu. Diproses secara higienis menjadi butiran kristal halus yang mudah larut, beraroma karamel alami, dan memiliki indeks glikemik lebih rendah dibandingkan gula pasir biasa. Sangat cocok untuk campuran kopi robusta, teh, hingga bahan pembuatan kue.Detail Produk:\r\n• Berat bersih: 250 gram.\r\n• Kemasan: Standing pouch dengan ziplock praktis agar tetap kering dan tahan lama.', 50000.00, 5, NULL, '2026-11-17 12:59:00'),
(20, 1, 4, 'Bumbu Masak Pendap Instan', 'Rindu masakan legendaris Bengkulu? Kini Anda bisa membuat Pendap autentik di rumah dengan mudah menggunakan bumbu siap masak ini. Diramu dari perpaduan rempah segar pilihan seperti bawang merah, bawang putih, lengkuas, kunyit, kelapa sangrai, dan rempah rahasia khas pesisir Bengkulu. Tinggal balurkan pada ikan pilihan, bungkus dengan daun talas, lalu kukus hingga matang.Detail Produk:\r\n• Takaran saji: Cukup untuk 500 gram – 1 kg ikan.\r\n• Ketahanan: Tahan hingga 3 bulan di suhu ruang dalam kemasan vakum tersegel.', 47000.00, 4, NULL, '2026-11-17 13:01:11'),
(21, 1, 2, 'Dompet Kulit Kayu Lantung', 'Dompet Kulit Kayu Lantung merupakan hasil karya kriya unggulan perajin Bengkulu yang memanfaatkan kulit pohon terap/lantung alami. Melalui proses pengolahan tradisional, kulit kayu berubah menjadi lembaran bertekstur serat alami yang lentur, kuat, dan tidak mudah sobek. Setiap produk memiliki corak serat kayu yang unik dan berbeda satu sama lain.Detail Produk:\r\n• Fitur: 2 slot uang kertas, 6 slot kartu, dan lapisan dalam kain lembut.', 100000.00, 2, NULL, '2026-11-17 13:09:25'),
(22, 1, 7, 'Kopi Robusta Petik Merah Bengkulu', 'Nikmati karakter kuat dan mantap dari Kopi Robusta Bengkulu, hasil panen petani lokal di lereng pegunungan Bukit Barisan (Kepahiang & Rejang Lebong). Diproses secara selektif hanya dari buah kopi petik merah pilihan sehingga menghasilkan cita rasa yang bersih, body yang tebal, aroma cokelat gelap serta kacang sangrai (nutty), dan tingkat keasaman yang rendah. Sangat cocok dinikmati sebagai kopi tubruk klasik, espresso, maupun campuran kopi susu gula aren.Detail Produk:\r\n• Asal: Dataran Tinggi Kepahiang / Rejang Lebong, Bengkulu.\r\n• Level Sangrai: Medium to Dark Roast.\r\n• Pilihan Bentuk: Biji Sangrai (Roasted Bean) atau Bubuk Halus/Sedang.\r\n• Berat Bersih & Kemasan: 200 gram dalam kemasan standing pouch dengan katup udara (one-way valve) dan ziplock agar kesegaran aroma kopi tetap terjaga.', 44000.00, 55, 'f24a3416bbc1ebbdbd7ff7e3.jpg', '2026-11-17 13:14:11'),
(23, 1, 7, 'Kopi Arabika Specialty Bengkulu', 'Kopi Arabika Bengkulu berasal dari perkebunan kopi dataran tinggi di atas 1.200 mdpl di kawasan pegunungan Bengkulu. Ditanam di tanah vulkanik yang subur dengan iklim sejuk, kopi ini menawarkan profil rasa yang kompleks dan elegan. Menghadirkan perpaduan aroma bunga (floral), sentuhan rasa buah tropis yang segar (fruity), manis alami seperti gula aren, serta tingkat keasaman (acidity) yang lembut dan seimbang. Sangat pas diseduh menggunakan metode manual brew seperti V60, French Press, maupun Aeropress.Detail Produk:• Asal: Pegunungan Bukit Barisan, Bengkulu (1.200–1.500 mdpl).\r\n• Level Sangrai: Medium Roast.\r\n• Pilihan Bentuk: Biji Sangrai (Whole Bean) atau Bubuk Giling Kasar/Sedang.\r\n• Berat Bersih & Kemasan: 200 gram dalam kemasan foil pouch kedap udara dilengkapi katup pengunci aroma.', 100000.00, 6, NULL, '2026-11-17 13:15:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('penjual','pembeli') NOT NULL DEFAULT 'pembeli',
  `no_hp` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `nama`, `email`, `password`, `role`, `no_hp`, `alamat`, `created_at`) VALUES
(1, 'Temu Store', 'penjual1@contoh.com', '$2y$10$bOqIBOiMIPRxfTLXMD8jZuuyZtsCimKhU9Qo4cXzM0xWT8a1O2wru', 'penjual', '081234567801', 'Temu Store', '2026-11-17 03:55:26'),
(3, 'Pembeli Contoh', 'pembeli1@contoh.com', '$2y$10$bOqIBOiMIPRxfTLXMD8jZuuyZtsCimKhU9Qo4cXzM0xWT8a1O2wru', 'pembeli', '081234567803', 'Jl. Merdeka No. 20, Bengkulu', '2026-11-17 03:55:26'),
(4, 'Dina Angkasa', 'dina@contoh.com', '$2y$10$44EscpQ7pcnweJEF2i4byOs3RCpoY/jDMT.Kcm9xIoGDKi8Z.JI8q', 'pembeli', '082255217770', NULL, '2026-11-17 04:08:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `idx_detail_pesanan` (`id_pesanan`),
  ADD KEY `idx_detail_produk` (`id_produk`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`),
  ADD UNIQUE KEY `uk_kategori_nama` (`nama_kategori`);

--
-- Indexes for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD PRIMARY KEY (`id_keranjang`),
  ADD UNIQUE KEY `uk_keranjang_user_produk` (`id_user`,`id_produk`),
  ADD KEY `idx_keranjang_produk` (`id_produk`);

--
-- Indexes for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD PRIMARY KEY (`id_pesanan`),
  ADD UNIQUE KEY `uk_pesanan_kode` (`kode_pesanan`),
  ADD KEY `idx_pesanan_pembeli` (`id_pembeli`);

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`id_produk`),
  ADD KEY `idx_produk_penjual` (`id_penjual`),
  ADD KEY `idx_produk_kategori` (`id_kategori`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uk_users_email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  MODIFY `id_detail` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `keranjang`
--
ALTER TABLE `keranjang`
  MODIFY `id_keranjang` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `pesanan`
--
ALTER TABLE `pesanan`
  MODIFY `id_pesanan` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `produk`
--
ALTER TABLE `produk`
  MODIFY `id_produk` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pesanan`
--
ALTER TABLE `detail_pesanan`
  ADD CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`id_pesanan`) REFERENCES `pesanan` (`id_pesanan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detail_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON UPDATE CASCADE;

--
-- Constraints for table `keranjang`
--
ALTER TABLE `keranjang`
  ADD CONSTRAINT `fk_keranjang_produk` FOREIGN KEY (`id_produk`) REFERENCES `produk` (`id_produk`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pesanan`
--
ALTER TABLE `pesanan`
  ADD CONSTRAINT `fk_pesanan_pembeli` FOREIGN KEY (`id_pembeli`) REFERENCES `users` (`id_user`) ON UPDATE CASCADE;

--
-- Constraints for table `produk`
--
ALTER TABLE `produk`
  ADD CONSTRAINT `fk_produk_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_produk_penjual` FOREIGN KEY (`id_penjual`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
