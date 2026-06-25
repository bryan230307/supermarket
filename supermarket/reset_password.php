<?php
/**
 * RESET PASSWORD HELPER
 * Jalankan file ini SEKALI via browser: http://localhost/supermarket/reset_password.php
 * Lalu HAPUS file ini setelah selesai!
 */
require_once 'config/database.php';

$db = Database::getInstance()->getConnection();

$passwords = [
    'admin'   => 'admin123',
    'petugas' => 'petugas123',
];

foreach ($passwords as $username => $plaintext) {
    $hash = password_hash($plaintext, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE user SET password = ? WHERE username = ?");
    $stmt->execute([$hash, $username]);
    echo "✅ Password untuk <strong>$username</strong> berhasil di-reset ke: <code>$plaintext</code><br>";
}

echo "<br><strong style='color:red'>⚠️ HAPUS file reset_password.php ini sekarang!</strong>";
