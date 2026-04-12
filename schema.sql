-- E-Perpustakaan — skema database (MySQL/MariaDB)
-- CREATE DATABASE eperpustakaan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE eperpustakaan;

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS users (
  id_user INT UNSIGNED NOT NULL AUTO_INCREMENT,
  username VARCHAR(100) NOT NULL,
  password VARCHAR(255) NOT NULL,
  role ENUM('admin', 'siswa') NOT NULL DEFAULT 'siswa',
  foto VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (id_user),
  UNIQUE KEY uq_users_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS buku (
  id_buku INT UNSIGNED NOT NULL AUTO_INCREMENT,
  judul VARCHAR(255) NOT NULL,
  penulis VARCHAR(255) NOT NULL,
  penerbit VARCHAR(255) NOT NULL,
  tahun_terbit INT NOT NULL,
  jumlah_halaman INT NOT NULL,
  rak VARCHAR(50) NOT NULL,
  stok INT NOT NULL DEFAULT 0,
  status VARCHAR(32) NOT NULL DEFAULT 'tersedia',
  foto VARCHAR(255) DEFAULT NULL,
  deskripsi TEXT NOT NULL,
  PRIMARY KEY (id_buku)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS peminjaman (
  id_pinjam INT UNSIGNED NOT NULL AUTO_INCREMENT,
  id_user INT UNSIGNED NOT NULL,
  id_buku INT UNSIGNED NOT NULL,
  tanggal_pinjam DATETIME NOT NULL,
  tanggal_kembali DATETIME DEFAULT NULL,
  status ENUM('dipinjam', 'dikembalikan') NOT NULL DEFAULT 'dipinjam',
  PRIMARY KEY (id_pinjam),
  KEY fk_pinjam_user (id_user),
  KEY fk_pinjam_buku (id_buku),
  CONSTRAINT fk_pinjam_user FOREIGN KEY (id_user) REFERENCES users (id_user) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT fk_pinjam_buku FOREIGN KEY (id_buku) REFERENCES buku (id_buku) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- INSERT data user default
INSERT IGNORE INTO users (username, password, role) VALUES 
('admin', '$2y$10$h3Dd4fVTcXv8kV8Y.O7EZuZ6X7Y8Z9A0B1C2D3E4F5G6H7I8J9K0L', 'admin'),
('siswa', '$2y$10$h3Dd4fVTcXv8kV8Y.O7EZuZ6X7Y8Z9A0B1C2D3E4F5G6H7I8J9K0L', 'siswa');

