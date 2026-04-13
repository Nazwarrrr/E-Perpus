<?php
/**
 * Jalankan sekali dari browser untuk membuat akun demo (admin/siswa, password: password123).
 * Hapus file ini setelah digunakan di lingkungan produksi.
 */
require_once __DIR__ . '/inc/db.php';

$check = $conn->query("SELECT COUNT(*) AS c FROM users");
$row = $check->fetch_assoc();
if ((int) $row['c'] > 0) {
    die('Tabel users sudah berisi data. Seed dilewati.');
}

$hash = password_hash('password123', PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?), (?, ?, ?)");
$admin = 'admin';
$siswa = 'siswa';
$roleA = 'admin';
$roleS = 'siswa';
$stmt->bind_param('ssssss', $admin, $hash, $roleA, $siswa, $hash, $roleS);
$stmt->execute();
echo 'OK: user admin & siswa dibuat (password: password123). Hapus install-seed.php.';
