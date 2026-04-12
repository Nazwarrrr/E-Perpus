<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireAdmin();

$stats = getStats($conn);
$pageTitle = 'Dashboard Admin';
$activeNav = 'dashboard';
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
            <h1 class="page-title">Dashboard</h1>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true"></div>
                    <div>
                        <p class="stat-label">Total buku</p>
                        <p class="stat-value"><?php echo (int) $stats['total_buku']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true"></div>
                    <div>
                        <p class="stat-label">Buku tersedia (stok)</p>
                        <p class="stat-value"><?php echo (int) $stats['tersedia']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true"></div>
                    <div>
                        <p class="stat-label">Sedang dipinjam</p>
                        <p class="stat-value"><?php echo (int) $stats['dipinjam']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon" aria-hidden="true"></div>
                    <div>
                        <p class="stat-label">Total siswa</p>
                        <p class="stat-value"><?php echo (int) $stats['total_user']; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
