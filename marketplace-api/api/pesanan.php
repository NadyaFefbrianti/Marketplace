<?php
require_once __DIR__ . '/../config/koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    // ================= GET: detail pesanan =================
    if ($method === 'GET') {
        $id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
        if ($id === false || $id === null || $id < 1) {
            error_respon(400, 'Parameter id pesanan wajib berupa angka positif');
        }

        $stmt = $conn->prepare(
            "SELECT id_pesanan, kode_pesanan, id_pembeli, tanggal_pesanan, total_harga, status,
                    nama_penerima, no_hp_penerima, alamat_pengiriman, catatan
             FROM pesanan WHERE id_pesanan = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $pesanan = $stmt->get_result()->fetch_assoc();
        if (!$pesanan) {
            error_respon(404, "Pesanan dengan id $id tidak ditemukan");
        }
        $pesanan['id_pesanan']  = (int)$pesanan['id_pesanan'];
        $pesanan['id_pembeli']  = (int)$pesanan['id_pembeli'];
        $pesanan['total_harga'] = (float)$pesanan['total_harga'];

        $stmt = $conn->prepare(
            "SELECT id_produk, nama_produk, harga_satuan, jumlah, subtotal
             FROM detail_pesanan WHERE id_pesanan = ?"
        );
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        foreach ($items as &$it) {
            $it['id_produk']    = (int)$it['id_produk'];
            $it['harga_satuan'] = (float)$it['harga_satuan'];
            $it['jumlah']       = (int)$it['jumlah'];
            $it['subtotal']     = (float)$it['subtotal'];
        }
        unset($it);
        $pesanan['items'] = $items;

        respon(200, ['status' => 'success', 'message' => 'Detail pesanan berhasil diambil', 'data' => $pesanan]);
    }

    // ================= POST: buat pesanan =================
    if ($method === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            error_respon(400, 'Body harus berupa JSON yang valid');
        }

        $idPembeli = filter_var($input['id_pembeli'] ?? null, FILTER_VALIDATE_INT);
        $nama      = trim($input['nama_penerima'] ?? '');
        $hp        = trim($input['no_hp_penerima'] ?? '');
        $alamat    = trim($input['alamat_pengiriman'] ?? '');
        $catatan   = trim($input['catatan'] ?? '');
        $catatan   = ($catatan === '') ? null : $catatan;
        $items     = $input['items'] ?? [];

        if (!$idPembeli || $nama === '' || $hp === '' || $alamat === '' || !is_array($items) || count($items) === 0) {
            error_respon(400, 'Field id_pembeli, nama_penerima, no_hp_penerima, alamat_pengiriman, dan items wajib diisi');
        }

        // Gabungkan item duplikat & validasi
        $gabung = [];
        foreach ($items as $it) {
            $idp    = filter_var($it['id_produk'] ?? null, FILTER_VALIDATE_INT);
            $jumlah = filter_var($it['jumlah'] ?? null, FILTER_VALIDATE_INT);
            if (!$idp || !$jumlah || $jumlah < 1) {
                error_respon(400, 'Setiap item harus punya id_produk dan jumlah (angka positif)');
            }
            $gabung[$idp] = ($gabung[$idp] ?? 0) + $jumlah;
        }

        // Pastikan pembeli ada dan berperan 'pembeli'
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE id_user = ? AND role = 'pembeli'");
        $stmt->bind_param('i', $idPembeli);
        $stmt->execute();
        if (!$stmt->get_result()->fetch_assoc()) {
            error_respon(404, "Pembeli dengan id $idPembeli tidak ditemukan");
        }

        $conn->begin_transaction();
        $total  = 0;
        $detail = [];

        $cek = $conn->prepare("SELECT nama_produk, harga, stok FROM produk WHERE id_produk = ? FOR UPDATE");
        foreach ($gabung as $idp => $jumlah) {
            $cek->bind_param('i', $idp);
            $cek->execute();
            $p = $cek->get_result()->fetch_assoc();
            if (!$p) {
                $conn->rollback();
                error_respon(404, "Produk dengan id $idp tidak ditemukan");
            }
            if ((int)$p['stok'] < $jumlah) {
                $conn->rollback();
                error_respon(409, "Stok '{$p['nama_produk']}' tidak mencukupi (tersisa {$p['stok']})");
            }
            $subtotal = (float)$p['harga'] * $jumlah;
            $total   += $subtotal;
            $detail[] = [
                'id' => $idp, 'nama' => $p['nama_produk'],
                'harga' => (float)$p['harga'], 'jumlah' => $jumlah, 'subtotal' => $subtotal
            ];
        }

        $kode = 'INV' . date('ymd') . strtoupper(bin2hex(random_bytes(3)));

        $stmt = $conn->prepare(
            "INSERT INTO pesanan (kode_pesanan, id_pembeli, total_harga, nama_penerima, no_hp_penerima, alamat_pengiriman, catatan)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param('sidssss', $kode, $idPembeli, $total, $nama, $hp, $alamat, $catatan);
        $stmt->execute();
        $idPesanan = $conn->insert_id;

        $insD = $conn->prepare(
            "INSERT INTO detail_pesanan (id_pesanan, id_produk, nama_produk, harga_satuan, jumlah, subtotal)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $updS = $conn->prepare("UPDATE produk SET stok = stok - ? WHERE id_produk = ?");
        foreach ($detail as $d) {
            $insD->bind_param('iisdid', $idPesanan, $d['id'], $d['nama'], $d['harga'], $d['jumlah'], $d['subtotal']);
            $insD->execute();
            $updS->bind_param('ii', $d['jumlah'], $d['id']);
            $updS->execute();
        }

        $conn->commit();
        respon(201, [
            'status'  => 'success',
            'message' => 'Pesanan berhasil dibuat',
            'data'    => [
                'id_pesanan'   => $idPesanan,
                'kode_pesanan' => $kode,
                'total_harga'  => $total,
                'status'       => 'pending'
            ]
        ]);
    }

    error_respon(405, 'Method tidak diizinkan');
} catch (mysqli_sql_exception $e) {
    if (isset($conn)) { try { $conn->rollback(); } catch (Throwable $t) {} }
    error_respon(500, 'Terjadi kesalahan pada server');
}
