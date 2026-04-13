<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buku.php');
    exit;
}

$id = isset($_POST['id_buku']) ? (int) $_POST['id_buku'] : 0;
if ($id <= 0) {
    header('Location: buku.php');
    exit;
}

$b = getBookById($conn, $id);
if ($b) {
    $stmt = $conn->prepare('DELETE FROM buku WHERE id_buku = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    if (!empty($b['foto']) && is_file(__DIR__ . '/uploads/buku/' . $b['foto'])) {
        @unlink(__DIR__ . '/uploads/buku/' . $b['foto']);
    }
}

header('Location: buku.php');
exit;
