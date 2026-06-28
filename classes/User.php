<?php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        return $this->db->query("SELECT id, nama, username, role FROM user ORDER BY id")->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, nama, username, role FROM user WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function search($keyword) {
        $stmt = $this->db->prepare("SELECT id, nama, username, role FROM user WHERE nama LIKE ? OR username LIKE ?");
        $stmt->execute(["%$keyword%", "%$keyword%"]);
        return $stmt->fetchAll();
    }

    public function create($nama, $username, $password, $role) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO user (nama, username, password, role) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$nama, $username, $hashed, $role]);
    }

    public function update($id, $nama, $username, $role, $password = null) {
        if ($password) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("UPDATE user SET nama=?, username=?, password=?, role=? WHERE id=?");
            return $stmt->execute([$nama, $username, $hashed, $role, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE user SET nama=?, username=?, role=? WHERE id=?");
            return $stmt->execute([$nama, $username, $role, $id]);
        }
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM user WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function usernameExists($username, $excludeId = null) {
        if ($excludeId) {
            $stmt = $this->db->prepare("SELECT id FROM user WHERE username = ? AND id != ?");
            $stmt->execute([$username, $excludeId]);
        } else {
            $stmt = $this->db->prepare("SELECT id FROM user WHERE username = ?");
            $stmt->execute([$username]);
        }
        return $stmt->fetch() !== false;
    }
}
