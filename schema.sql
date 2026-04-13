-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 13, 2026 at 04:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `schema`
--

-- --------------------------------------------------------

--
-- Table structure for table `buku`
--

CREATE TABLE `buku` (
  `id_buku` int(10) UNSIGNED NOT NULL,
  `judul` varchar(255) NOT NULL,
  `penulis` varchar(255) NOT NULL,
  `penerbit` varchar(255) NOT NULL,
  `tahun_terbit` int(11) NOT NULL,
  `jumlah_halaman` int(11) NOT NULL,
  `rak` varchar(50) NOT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `status` varchar(32) NOT NULL DEFAULT 'tersedia',
  `foto` varchar(255) DEFAULT NULL,
  `deskripsi` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `buku`
--

INSERT INTO `buku` (`id_buku`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `jumlah_halaman`, `rak`, `stok`, `status`, `foto`, `deskripsi`) VALUES
(1, 'Hujan', 'Tere Liye', 'Gramedia pustaka Utama', 2016, 320, 'Fiksi', 9, 'tersedia', 'buku_69dce48fa05169.32487363.jpg', 'Novel Hujan berlatar di masa depan, ketika bumi mengalami bencana besar yang mengubah peradaban manusia secara drastis. Cerita berfokus pada seorang gadis bernama Lail yang harus menghadapi kehilangan orang-orang terdekatnya akibat bencana tersebut. Dalam perjalanan hidupnya, Lail bertemu dengan Esok, seorang pemuda jenius yang kemudian menjadi bagian penting dalam hidupnya.\r\nKisah ini tidak hanya tentang cinta, tetapi juga tentang kehilangan, pengorbanan, dan bagaimana manusia berusaha bangkit dari masa lalu yang menyakitkan. Dengan alur yang emosional dan penuh refleksi, novel ini mengajak pembaca memahami arti melupakan dan memaafkan, serta pentingnya harapan di tengah kehancuran.'),
(2, 'Bumi', 'Tere Liye', 'Gramedia pustaka Utama', 2014, 440, 'Fiksi', 10, 'tersedia', 'buku_69dce5e6cfe825.57344739.jpg', 'Bumi merupakan awal dari seri petualangan dunia paralel karya Tere Liye. Cerita mengikuti Raib, seorang remaja perempuan yang sejak kecil memiliki kemampuan aneh, yaitu bisa menghilang. Kehidupan Raib berubah ketika ia bertemu dengan Seli dan Ali, dua temannya yang juga memiliki keunikan masing-masing.\r\nMereka kemudian menemukan bahwa dunia tidak hanya satu, melainkan terdiri dari berbagai klan seperti Klan Bumi, Bulan, dan Matahari. Petualangan mereka dimulai ketika mereka harus menghadapi ancaman dari dunia lain dan mengungkap rahasia besar tentang kekuatan yang mereka miliki.\r\nNovel ini penuh dengan unsur fantasi, persahabatan, dan misteri, serta mengajarkan tentang keberanian dan pentingnya bekerja sama dalam menghadapi masalah besar.'),
(3, 'Bulan', 'Tere Liye', 'Gramedia pustaka Utama', 2015, 400, 'Fiksi', 5, 'tersedia', 'buku_69dce68e8b3639.17884317.jpg', 'Melanjutkan cerita Bumi, Raib, Seli, dan Ali pergi ke Klan Matahari untuk menjalankan misi penting. Mereka menghadapi berbagai tantangan dan konflik yang semakin rumit di dunia paralel.'),
(4, 'Matahari', 'Tere Liye', 'Gramedia pustaka Utama', 2016, 390, 'Fiksi', 8, 'tersedia', 'buku_69dce704a13679.43469831.jpg', 'Petualangan berlanjut ke dunia Klan Matahari dengan fokus pada karakter Ali. Cerita dipenuhi aksi, teknologi canggih, dan konflik besar, serta mengungkap lebih dalam rahasia tiap tokoh.'),
(5, 'Dilan 1990', 'Pidi Baiq', 'Pastel Books', 2014, 348, 'Fiksi', 10, 'tersedia', 'buku_69dcea51a96937.62294184.jpg', 'Novel ini menceritakan kisah cinta remaja antara Dilan dan Milea di Bandung tahun 1990. Dilan dikenal unik dan romantis dengan cara pendekatan yang tidak biasa, sementara Milea adalah siswi pindahan dari Jakarta. Ceritanya ringan, lucu, dan penuh makna tentang cinta masa SMA.'),
(6, 'Laskar Pelangi', 'Andrea Hinata', 'Bentang Pustaka', 2005, 529, 'Fiksi', 8, 'tersedia', 'buku_69dceac481c9d1.58132107.jpg', 'Mengisahkan perjuangan 10 anak dari Belitung dalam mengejar pendidikan di tengah keterbatasan. Cerita ini penuh inspirasi tentang semangat, persahabatan, dan mimpi besar.'),
(7, 'Dear Nathan', 'Erisca Febriani', 'Best Media', 2016, 520, 'Fiksi', 7, 'tersedia', 'buku_69dceb49a21319.04328471.jpg', 'Kisah cinta antara Nathan, siswa nakal, dan Salma, siswi teladan. Cerita ini mengangkat tema cinta remaja, perubahan diri, dan masalah kehidupan sekolah.'),
(8, 'Negeri 5 Mnara', 'Ahmad Fuadi', 'Gramedia pustaka Utama', 2009, 423, 'Fiksi', 5, 'tersedia', 'buku_69dcebf0c70512.36381503.jpg', 'Mengisahkan kehidupan Alif dan teman-temannya di pondok pesantren. Penuh motivasi tentang mimpi, kerja keras, dan semboyan “Man Jadda Wajada” (siapa yang bersungguh-sungguh akan berhasil).');

-- --------------------------------------------------------

--
-- Table structure for table `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id_pinjam` int(10) UNSIGNED NOT NULL,
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_buku` int(10) UNSIGNED NOT NULL,
  `tanggal_pinjam` datetime NOT NULL,
  `tanggal_kembali` datetime DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') NOT NULL DEFAULT 'dipinjam'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','siswa') NOT NULL DEFAULT 'siswa',
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `foto`) VALUES
(1, 'admin', '$2y$10$Vrk8oM3ELwRqg9jJiJI8MuJJU/Y33H63e.ZTybUE3J5mztuC8AIWi', 'admin', NULL),
(2, 'siswa', '$2y$10$Vrk8oM3ELwRqg9jJiJI8MuJJU/Y33H63e.ZTybUE3J5mztuC8AIWi', 'siswa', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id_buku`);

--
-- Indexes for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id_pinjam`),
  ADD KEY `fk_pinjam_user` (`id_user`),
  ADD KEY `fk_pinjam_buku` (`id_buku`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uq_users_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `buku`
--
ALTER TABLE `buku`
  MODIFY `id_buku` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id_pinjam` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `fk_pinjam_buku` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id_buku`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pinjam_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
