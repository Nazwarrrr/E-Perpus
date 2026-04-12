<?php
$host = 'localhost';
$db   = 'schema';
$user = 'root';
$pass = '';

// Koneksi tanpa database terlebih dahulu untuk membuat database jika belum ada
$conn_temp = new mysqli($host, $user, $pass);

if ($conn_temp->connect_error) {
    die('Koneksi database gagal: ' . $conn_temp->connect_error);
}

// Buat database jika belum ada
$conn_temp->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn_temp->close();

// Koneksi ke database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}

$conn->set_charset('utf8mb4');
