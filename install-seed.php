    <?php
/**
 * Jalankan sekali dari browser untuk membuat akun demo (admin/siswa, password: password123).
 * Hapus file ini setelah digunakan di lingkungan produksi.
 */
require_once __DIR__ . '/inc/db.php';

$messages = [];

// Step 1: Run schema updates
try {
    // Add new fields to users table
    $conn->query("ALTER TABLE `users` ADD COLUMN `email` varchar(255) DEFAULT NULL AFTER `username`");
    $conn->query("ALTER TABLE `users` ADD COLUMN `no_hp` varchar(20) DEFAULT NULL AFTER `email`");
    $conn->query("ALTER TABLE `users` ADD COLUMN `alamat` text DEFAULT NULL AFTER `no_hp`");
    $conn->query("ALTER TABLE `users` ADD COLUMN `tanggal_lahir` date DEFAULT NULL AFTER `alamat`");
    $conn->query("ALTER TABLE `users` ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `tanggal_lahir`");
    
    // Create favorites table
    $conn->query("CREATE TABLE IF NOT EXISTS `favorit_buku` (
      `id_favorit` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
      `id_user` int(10) UNSIGNED NOT NULL,
      `id_buku` int(10) UNSIGNED NOT NULL,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id_favorit`),
      UNIQUE KEY `unique_user_book` (`id_user`, `id_buku`),
      FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
      FOREIGN KEY (`id_buku`) REFERENCES `buku`(`id_buku`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    $messages[] = 'OK: Schema berhasil diperbarui.';
} catch (Exception $e) {
    $messages[] = 'Info: Schema sudah diperbarui atau sudah ada. (' . $e->getMessage() . ')';
}

// Step 2: Seed demo users
$check = $conn->query("SELECT COUNT(*) AS c FROM users");
$row = $check->fetch_assoc();
if ((int) $row['c'] > 0) {
    $messages[] = 'Info: Tabel users sudah berisi data. User seed dilewati.';
} else {
    $hash = password_hash('password123', PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?), (?, ?, ?)");
    $admin = 'admin';
    $siswa = 'siswa';
    $roleA = 'admin';
    $roleS = 'siswa';
    $stmt->bind_param('ssssss', $admin, $hash, $roleA, $siswa, $hash, $roleS);
    $stmt->execute();
    $messages[] = 'OK: User admin & siswa dibuat (password: password123).';
}

echo implode('<br>', $messages) . '<br><br>Hapus install-seed.php.';
