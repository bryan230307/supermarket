<?php
require_once __DIR__ . '/../config/database.php';

class Transaksi {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // JOIN transaksi dengan barang dan kategori
    public function getAllWithDetail() {
        return $this->db->query("
            SELECT t.*, b.nama_barang, b.harga, k.nama_kategori,
                   (t.jumlah * b.harga) as total_harga
            FROM transaksi t
            LEFT JOIN barang b ON t.id_barang = b.id_barang
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            ORDER BY t.tanggal DESC, t.id_transaksi DESC
        ")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, b.nama_barang, b.harga, k.nama_kategori,
                   (t.jumlah * b.harga) as total_harga
            FROM transaksi t
            LEFT JOIN barang b ON t.id_barang = b.id_barang
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            WHERE t.id_transaksi = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($keyword) {
        $stmt = $this->db->prepare("
            SELECT t.*, b.nama_barang, b.harga, k.nama_kategori,
                   (t.jumlah * b.harga) as total_harga
            FROM transaksi t
            LEFT JOIN barang b ON t.id_barang = b.id_barang
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            WHERE b.nama_barang LIKE ? OR t.jenis LIKE ? OR t.tanggal LIKE ?
            ORDER BY t.tanggal DESC
        ");
        $stmt->execute(["%$keyword%", "%$keyword%", "%$keyword%"]);
        return $stmt->fetchAll();
    }

    public function create($id_barang, $tanggal, $jumlah, $jenis) {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO transaksi (id_barang, tanggal, jumlah, jenis) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id_barang, $tanggal, $jumlah, $jenis]);

            // Update stok barang
            $barang = new Barang();
            $barang->updateStok($id_barang, $jumlah, $jenis);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    public function delete($id) {
        $transaksi = $this->getById($id);
        if (!$transaksi) return false;

        $this->db->beginTransaction();
        try {
            // Rollback stok
            $jenis_balik = ($transaksi['jenis'] === 'masuk') ? 'keluar' : 'masuk';
            $barang = new Barang();
            $barang->updateStok($transaksi['id_barang'], $transaksi['jumlah'], $jenis_balik);

            $stmt = $this->db->prepare("DELETE FROM transaksi WHERE id_transaksi = ?");
            $stmt->execute([$id]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    // REPORT: Laporan per tanggal
    public function getReportByDate($dari, $sampai) {
        $stmt = $this->db->prepare("
            SELECT t.*, b.nama_barang, b.harga, k.nama_kategori,
                   (t.jumlah * b.harga) as total_harga
            FROM transaksi t
            LEFT JOIN barang b ON t.id_barang = b.id_barang
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori
            WHERE t.tanggal BETWEEN ? AND ?
            ORDER BY t.tanggal ASC
        ");
        $stmt->execute([$dari, $sampai]);
        return $stmt->fetchAll();
    }

    // REPORT: Ringkasan masuk/keluar
    public function getSummary() {
        return $this->db->query("
            SELECT 
                jenis,
                COUNT(*) as jumlah_transaksi,
                SUM(t.jumlah) as total_barang,
                SUM(t.jumlah * b.harga) as total_nilai
            FROM transaksi t
            LEFT JOIN barang b ON t.id_barang = b.id_barang
            GROUP BY jenis
        ")->fetchAll();
    }

    // REPORT: Top barang
public function getTopBarang($limit = 5) {
    $limit = (int)$limit;
    $stmt = $this->db->query("
        SELECT b.nama_barang, SUM(t.jumlah) as total_qty,
               SUM(t.jumlah * b.harga) as total_nilai, t.jenis
        FROM transaksi t
        LEFT JOIN barang b ON t.id_barang = b.id_barang
        WHERE t.jenis = 'keluar'
        GROUP BY t.id_barang
        ORDER BY total_qty DESC
        LIMIT $limit
    ");
    return $stmt->fetchAll();
}

    // REPORT: Per bulan
    public function getMonthlyReport($year) {
        $stmt = $this->db->prepare("
            SELECT 
                MONTH(t.tanggal) as bulan,
                MONTHNAME(t.tanggal) as nama_bulan,
                t.jenis,
                SUM(t.jumlah * b.harga) as total_nilai
            FROM transaksi t
            LEFT JOIN barang b ON t.id_barang = b.id_barang
            WHERE YEAR(t.tanggal) = ?
            GROUP BY MONTH(t.tanggal), t.jenis
            ORDER BY bulan
        ");
        $stmt->execute([$year]);
        return $stmt->fetchAll();
    }
}
