<?php
/**
 * File untuk reset dan seed database dengan user yang benar
 * Hapus setelah digunakan
 */
require_once __DIR__ . '/inc/db.php';

// Drop tabel existing
$conn->query("DROP TABLE IF EXISTS peminjaman");
$conn->query("DROP TABLE IF EXISTS buku");
$conn->query("DROP TABLE IF EXISTS users");

// Baca schema.sql dan jalankan (tanpa data insert)
$schema = file_get_contents(__DIR__ . '/schema.sql');

// Pisahkan INSERT statement
$parts = explode('-- INSERT data user default', $schema);
$createTables = $parts[0];

// Jalankan CREATE TABLE
if ($conn->multi_query($createTables)) {
    while ($conn->next_result()) {}
    echo "✓ Tabel berhasil dibuat.<br>";
} else {
    die("Error: " . $conn->error);
}

// Insert data user dengan password yang benar
$hash = password_hash('password123', PASSWORD_BCRYPT);
$stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?), (?, ?, ?)");
$admin = 'admin';
$siswa = 'siswa';
$roleA = 'admin';
$roleS = 'siswa';
$stmt->bind_param('ssssss', $admin, $hash, $roleA, $siswa, $hash, $roleS);

if ($stmt->execute()) {
    echo "✓ User admin & siswa berhasil dibuat.<br>";
    echo "Password: <strong>password123</strong><br><br>";
    echo "<a href='login.php'>Klik di sini untuk login</a>";
} else {
    die("Error: " . $stmt->error);
}
?>
