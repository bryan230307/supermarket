<?php
require_once __DIR__ . '/../config/database.php';

class Kategori {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM kategori ORDER BY id_kategori")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM kategori WHERE id_kategori = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($keyword) {
        $stmt = $this->db->prepare("SELECT * FROM kategori WHERE nama_kategori LIKE ?");
        $stmt->execute(["%$keyword%"]);
        return $stmt->fetchAll();
    }

    public function create($nama_kategori) {
        $stmt = $this->db->prepare("INSERT INTO kategori (nama_kategori) VALUES (?)");
        return $stmt->execute([$nama_kategori]);
    }

    public function update($id, $nama_kategori) {
        $stmt = $this->db->prepare("UPDATE kategori SET nama_kategori = ? WHERE id_kategori = ?");
        return $stmt->execute([$nama_kategori, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM kategori WHERE id_kategori = ?");
        return $stmt->execute([$id]);
    }
}
