-- Update schema untuk Profil Lengkap dan Buku Favorit
-- Run this file to update your database

-- Add new fields to users table
ALTER TABLE `users` ADD COLUMN `email` varchar(255) DEFAULT NULL AFTER `username`;
ALTER TABLE `users` ADD COLUMN `no_hp` varchar(20) DEFAULT NULL AFTER `email`;
ALTER TABLE `users` ADD COLUMN `alamat` text DEFAULT NULL AFTER `no_hp`;
ALTER TABLE `users` ADD COLUMN `tanggal_lahir` date DEFAULT NULL AFTER `alamat`;
ALTER TABLE `users` ADD COLUMN `created_at` timestamp DEFAULT CURRENT_TIMESTAMP AFTER `tanggal_lahir`;

-- Create favorites table untuk buku favorit
CREATE TABLE IF NOT EXISTS `favorit_buku` (
  `id_favorit` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_buku` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_favorit`),
  UNIQUE KEY `unique_user_book` (`id_user`, `id_buku`),
  FOREIGN KEY (`id_user`) REFERENCES `users`(`id_user`) ON DELETE CASCADE,
  FOREIGN KEY (`id_buku`) REFERENCES `buku`(`id_buku`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
