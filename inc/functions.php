<?php

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle)
    {
        return $needle !== '' && strpos((string) $haystack, (string) $needle) === 0;
    }
}

function login($conn, $username, $password)
{
    $stmt = $conn->prepare('SELECT id_user, username, password, role, foto FROM users WHERE username = ?');
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return false;
    }

    $user = $result->fetch_assoc();

    if (str_starts_with($user['password'], '$2y$')) {
        $passwordValid = password_verify($password, $user['password']);
    } else {
        $passwordValid = hash_equals((string) $user['password'], (string) $password);
        if ($passwordValid) {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $u = $conn->prepare('UPDATE users SET password = ? WHERE id_user = ?');
            $u->bind_param('si', $hashed, $user['id_user']);
            $u->execute();
        }
    }

    if (!$passwordValid) {
        return false;
    }

    $_SESSION['id_user'] = (int) $user['id_user'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['foto'] = $user['foto'];

    return true;
}

function isLoggedIn(): bool
{
    return isset($_SESSION['id_user']);
}

function isAdmin(): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(): void
{
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        header('Location: buku.php');
        exit;
    }
}

function getUserById($conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT id_user, username, role, foto FROM users WHERE id_user = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

function getBooks($conn): array
{
    $res = $conn->query('SELECT * FROM buku ORDER BY id_buku DESC');
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getBookById($conn, int $id): ?array
{
    $stmt = $conn->prepare('SELECT * FROM buku WHERE id_buku = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ?: null;
}

function bookStatusLabel(array $book): string
{
    $stok = (int) ($book['stok'] ?? 0);
    if ($stok <= 0) {
        return 'Habis';
    }
    return 'Tersedia';
}

function syncBookStatusFromStock($conn, int $id_buku): void
{
    $b = getBookById($conn, $id_buku);
    if (!$b) {
        return;
    }
    $stok = (int) $b['stok'];
    $status = $stok > 0 ? 'tersedia' : 'habis';
    $stmt = $conn->prepare('UPDATE buku SET status = ? WHERE id_buku = ?');
    $stmt->bind_param('si', $status, $id_buku);
    $stmt->execute();
}

function getSiswaUsers($conn): array
{
    $res = $conn->query("SELECT id_user, username FROM users WHERE role = 'siswa' ORDER BY username ASC");
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getBooksWithStock($conn): array
{
    $res = $conn->query('SELECT id_buku, judul, stok FROM buku WHERE stok > 0 ORDER BY judul ASC');
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function adminBuatPeminjaman($conn, int $id_user, int $id_buku): string
{
    $b = getBookById($conn, $id_buku);
    if (!$b || (int) $b['stok'] <= 0) {
        return 'Stok buku tidak mencukupi.';
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO peminjaman (id_user, id_buku, tanggal_pinjam, status) VALUES (?, ?, NOW(), 'dipinjam')");
        $stmt->bind_param('ii', $id_user, $id_buku);
        $stmt->execute();

        $stmt2 = $conn->prepare('UPDATE buku SET stok = stok - 1 WHERE id_buku = ? AND stok > 0');
        $stmt2->bind_param('i', $id_buku);
        $stmt2->execute();
        if ($stmt2->affected_rows !== 1) {
            throw new RuntimeException('stok');
        }

        syncBookStatusFromStock($conn, $id_buku);
        $conn->commit();
        return '';
    } catch (Throwable $e) {
        $conn->rollback();
        return 'Gagal mencatat peminjaman.';
    }
}

function adminKembalikanBuku($conn, int $id_pinjam): string
{
    $stmt = $conn->prepare("SELECT * FROM peminjaman WHERE id_pinjam = ? AND status = 'dipinjam'");
    $stmt->bind_param('i', $id_pinjam);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_assoc();
    if (!$p) {
        return 'Data peminjaman tidak valid.';
    }

    $id_buku = (int) $p['id_buku'];
    $conn->begin_transaction();
    try {
        $u = $conn->prepare("UPDATE peminjaman SET status = 'dikembalikan', tanggal_kembali = NOW() WHERE id_pinjam = ?");
        $u->bind_param('i', $id_pinjam);
        $u->execute();

        $u2 = $conn->prepare('UPDATE buku SET stok = stok + 1 WHERE id_buku = ?');
        $u2->bind_param('i', $id_buku);
        $u2->execute();

        syncBookStatusFromStock($conn, $id_buku);
        $conn->commit();
        return '';
    } catch (Throwable $e) {
        $conn->rollback();
        return 'Gagal mengembalikan buku.';
    }
}

function getPeminjamanAktif($conn): array
{
    $sql = "SELECT p.*, u.username, b.judul FROM peminjaman p
            JOIN users u ON p.id_user = u.id_user
            JOIN buku b ON p.id_buku = b.id_buku
            WHERE p.status = 'dipinjam'
            ORDER BY p.tanggal_pinjam DESC";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getAllRiwayat($conn): array
{
    $sql = "SELECT p.*, u.username, b.judul FROM peminjaman p
            JOIN users u ON p.id_user = u.id_user
            JOIN buku b ON p.id_buku = b.id_buku
            ORDER BY p.tanggal_pinjam DESC";
    $res = $conn->query($sql);
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
}

function getRiwayatSiswa($conn, int $id_user): array
{
    $stmt = $conn->prepare(
        "SELECT p.*, b.judul, b.penulis FROM peminjaman p
         JOIN buku b ON p.id_buku = b.id_buku
         WHERE p.id_user = ?
         ORDER BY p.tanggal_pinjam DESC"
    );
    $stmt->bind_param('i', $id_user);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getStats($conn): array
{
    $totalBuku = (int) $conn->query('SELECT COUNT(*) AS c FROM buku')->fetch_assoc()['c'];
    $dipinjam = (int) $conn->query("SELECT COUNT(*) AS c FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['c'];
    $row = $conn->query('SELECT COALESCE(SUM(stok), 0) AS t FROM buku')->fetch_assoc();
    $tersedia = (int) $row['t'];
    $totalUser = (int) $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'siswa'")->fetch_assoc()['c'];

    return [
        'total_buku' => $totalBuku,
        'dipinjam' => $dipinjam,
        'tersedia' => $tersedia,
        'total_user' => $totalUser,
    ];
}

function allowedImageMime(string $tmp): bool
{
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($tmp);
    return in_array($mime, ['image/jpeg', 'image/png'], true);
}

function bookCoverUrl(?string $filename): string
{
    if ($filename !== null && $filename !== '' && is_file(__DIR__ . '/../uploads/buku/' . $filename)) {
        return 'uploads/buku/' . rawurlencode($filename);
    }
    return 'assets/img/default-book.svg';
}

function profilePhotoUrl(?string $filename): string
{
    if ($filename !== null && $filename !== '' && is_file(__DIR__ . '/../uploads/profil/' . $filename)) {
        return 'uploads/profil/' . rawurlencode($filename);
    }
    return 'assets/img/default-avatar.svg';
}
