<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../inc/bootstrap.php';
requireLogin();

$action = isset($_GET['action']) ? trim((string) $_GET['action']) : '';
$query = isset($_GET['q']) ? trim((string) $_GET['q']) : '';

// Get all books
if ($action === 'all' || $action === 'all_books') {
    $stmt = $conn->prepare('SELECT id_buku, judul, penulis, foto, stok FROM buku ORDER BY judul ASC');
    $stmt->execute();
    $result = $stmt->get_result();
    $books = $result->fetch_all(MYSQLI_ASSOC) ?? [];
    
    echo json_encode($books, JSON_UNESCAPED_UNICODE);
    exit;
}

if (strlen($query) < 2) {
    // Jika query terlalu pendek, return empty
    echo json_encode(['books' => []]);
    exit;
}

$query_lower = strtolower($query);

// Ambil semua buku tanpa limit
$stmt = $conn->prepare('SELECT id_buku, judul, penulis, foto, stok FROM buku ORDER BY id_buku DESC');
$stmt->execute();
$result = $stmt->get_result();
$allBooks = $result->fetch_all(MYSQLI_ASSOC);

// Filter di PHP (untuk case-insensitive search)
$filtered = array_filter($allBooks, function($book) use ($query_lower) {
    $title = strtolower($book['judul']);
    $author = strtolower($book['penulis']);
    return strpos($title, $query_lower) !== false || strpos($author, $query_lower) !== false;
});

echo json_encode([
    'query' => $query,
    'total' => count($allBooks),
    'matched' => count($filtered),
    'books' => array_values($filtered)  // Reset keys
], JSON_UNESCAPED_UNICODE);
?>
