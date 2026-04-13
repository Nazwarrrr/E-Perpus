<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireLogin();

if (isAdmin()) {
    $rows = getAllRiwayat($conn);
    $pageTitle = 'Riwayat Peminjaman';
} else {
    $rows = getRiwayatSiswa($conn, (int) $_SESSION['id_user']);
    $pageTitle = 'Riwayat Saya';
}

$activeNav = 'riwayat';
$currentUser = getUserById($conn, (int) $_SESSION['id_user']);
$showBookSearch = false;

require __DIR__ . '/inc/partials/head.php';
?>
<body class="light">
<script>
(function(){var t=localStorage.getItem('eperpustakaan-theme');document.body.classList.remove('light','dark');document.body.classList.add(t==='dark'?'dark':'light');})();
</script>
<div class="app">
    <?php require __DIR__ . '/inc/partials/sidebar.php'; ?>
    <main class="main-content">
        <?php require __DIR__ . '/inc/partials/topbar.php'; ?>
        <div class="content">
            <h1 class="page-title"><?php echo htmlspecialchars($pageTitle); ?></h1>

            <div class="table-wrap">
                <?php if (count($rows) === 0): ?>
                    <p class="empty-state">Belum ada riwayat.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <?php if (isAdmin()): ?><th>Siswa</th><?php endif; ?>
                                <th>Buku</th>
                                <th>Tanggal pinjam</th>
                                <th>Tanggal kembali</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $p): ?>
                                <tr>
                                    <?php if (isAdmin()): ?>
                                        <td><?php echo htmlspecialchars($p['username']); ?></td>
                                    <?php endif; ?>
                                    <td><?php echo htmlspecialchars($p['judul']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_pinjam'])); ?></td>
                                    <td><?php echo !empty($p['tanggal_kembali']) ? date('d/m/Y H:i', strtotime($p['tanggal_kembali'])) : '—'; ?></td>
                                    <td>
                                        <?php if ($p['status'] === 'dipinjam'): ?>
                                            <span class="badge badge-dipinjam">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="badge badge-kembali">Dikembalikan</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
