<?php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../inc/bootstrap.php';
requireLogin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'ID tidak valid']);
    exit;
}

$book = getBookById($conn, $id);
if (!$book) {
    http_response_code(404);
    echo json_encode(['error' => 'Buku tidak ditemukan']);
    exit;
}

$book['cover_url'] = bookCoverUrl($book['foto'] ?? null);
echo json_encode($book, JSON_UNESCAPED_UNICODE);
