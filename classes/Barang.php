<?php
require_once __DIR__ . '/../config/database.php';

class Barang {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // JOIN barang dengan kategori
    public function getAllWithKategori() {
        return $this->db->query("
            SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
            ORDER BY b.id_barang
        ")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
            WHERE b.id_barang = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($keyword) {
        $stmt = $this->db->prepare("
            SELECT b.*, k.nama_kategori 
            FROM barang b 
            LEFT JOIN kategori k ON b.id_kategori = k.id_kategori 
            WHERE b.nama_barang LIKE ? OR k.nama_kategori LIKE ?
            ORDER BY b.id_barang
        ");
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetchAll();
    }

    public function create($nama_barang, $harga, $stok, $id_kategori) {
        $stmt = $this->db->prepare("INSERT INTO barang (nama_barang, harga, stok, id_kategori) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nama_barang, $harga, $stok, $id_kategori]);
    }

    public function update($id, $nama_barang, $harga, $stok, $id_kategori) {
        $stmt = $this->db->prepare("UPDATE barang SET nama_barang=?, harga=?, stok=?, id_kategori=? WHERE id_barang=?");
        return $stmt->execute([$nama_barang, $harga, $stok, $id_kategori, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM barang WHERE id_barang = ?");
        return $stmt->execute([$id]);
    }

    public function updateStok($id, $jumlah, $jenis) {
        if ($jenis === 'masuk') {
            $stmt = $this->db->prepare("UPDATE barang SET stok = stok + ? WHERE id_barang = ?");
        } else {
            $stmt = $this->db->prepare("UPDATE barang SET stok = stok - ? WHERE id_barang = ?");
        }
        return $stmt->execute([$jumlah, $id]);
    }

    public function getLowStock($limit = 10) {
        $stmt = $this->db->prepare("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori = k.id_kategori WHERE b.stok <= ? ORDER BY b.stok ASC");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
