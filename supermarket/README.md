# 🛒 Supermarket POS — Sistem Manajemen Inventori

## Fitur
- ✅ **CRUD** — Barang, Kategori, Transaksi, User
- ✅ **JOIN** — Semua query menggunakan JOIN antar tabel
- ✅ **Report** — Laporan periodik, bulanan, top barang, bisa dicetak
- ✅ **Search** — Pencarian di semua halaman
- ✅ **Login/Logout** — Session-based, role admin & petugas
- ✅ **OOP** — Semua logika dibungkus dalam class PHP

## Struktur Folder
```
supermarket/
├── index.php              ← Halaman login
├── database.sql           ← Script setup database
├── config/
│   └── database.php       ← Koneksi DB (Singleton)
├── classes/
│   ├── Auth.php           ← Login, logout, session
│   ├── Barang.php         ← CRUD barang + JOIN kategori
│   ├── Kategori.php       ← CRUD kategori
│   ├── Transaksi.php      ← CRUD transaksi + report
│   └── User.php           ← CRUD user
├── pages/
│   ├── sidebar.php        ← Template sidebar
│   ├── dashboard.php      ← Ringkasan & statistik
│   ├── barang.php         ← Manajemen barang
│   ├── transaksi.php      ← Manajemen transaksi
│   ├── kategori.php       ← Manajemen kategori (admin)
│   ├── user.php           ← Manajemen user (admin)
│   ├── report.php         ← Laporan & cetak
│   └── logout.php         ← Proses logout
└── assets/css/style.css   ← Stylesheet
```

## Cara Install

### 1. Persiapan
- XAMPP / WAMP / Laragon
- PHP 7.4+, MySQL 5.7+

### 2. Setup Database
```sql
-- Buka phpMyAdmin atau MySQL CLI, lalu jalankan:
source /path/ke/supermarket/database.sql
```

### 3. Letakkan di htdocs
```
C:/xampp/htdocs/supermarket/
```

### 4. Sesuaikan config database (jika perlu)
Edit `config/database.php`:
```php
private $host = 'localhost';
private $dbname = 'supermarket';
private $username = 'root';
private $password = '';    // Isi password MySQL Anda
```

### 5. Akses di browser
```
http://localhost/supermarket/
```

## Akun Demo
| Username | Password   | Role    |
|----------|------------|---------|
| admin    | admin123   | Admin   |
| petugas  | petugas123 | Petugas |

> **Catatan:** Hash password di database.sql menggunakan `password_hash()` PHP.
> Jika login gagal, generate ulang hash dengan:
> ```php
> php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"
> ```

## Role & Akses
| Fitur         | Admin | Petugas |
|---------------|-------|---------|
| Dashboard     | ✅    | ✅      |
| Lihat Barang  | ✅    | ✅      |
| Edit Barang   | ✅    | ✅      |
| Hapus Barang  | ✅    | ❌      |
| Transaksi     | ✅    | ✅      |
| Laporan       | ✅    | ✅      |
| Kategori      | ✅    | ❌      |
| User Manager  | ✅    | ❌      |
