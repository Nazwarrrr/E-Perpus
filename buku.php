<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireLogin();

$books = getBooks($conn);
$pageTitle = 'Katalog Buku';
$activeNav = 'buku';
$currentUser = getUserById($conn, (int) $_SESSION['id_user']);
$showBookSearch = true;
$isAdmin = isAdmin();

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
            <div class="toolbar">
                <h1 class="page-title" style="margin:0;flex:1">Buku</h1>
                <?php if ($isAdmin): ?>
                    <a href="buku-form.php" class="btn btn-primary">Tambah buku</a>
                <?php endif; ?>
            </div>

            <div class="books-layout" data-books-layout>
                <div>
                    <?php if (count($books) === 0): ?>
                        <div class="card"><p class="empty-state">Belum ada data buku.</p></div>
                    <?php else: ?>
                        <div class="book-grid" id="bookGrid">
                            <?php foreach ($books as $b): ?>
                                <?php
                                $stok = (int) $b['stok'];
                                $stLabel = $stok > 0 ? 'Tersedia' : 'Habis';
                                $stClass = $stok > 0 ? 'status-tersedia' : 'status-habis';
                                $cover = bookCoverUrl($b['foto'] ?? null);
                                ?>
                                <article
                                    class="book-card"
                                    data-book-card
                                    data-id="<?php echo (int) $b['id_buku']; ?>"
                                    data-title="<?php echo htmlspecialchars($b['judul'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-author="<?php echo htmlspecialchars($b['penulis'], ENT_QUOTES, 'UTF-8'); ?>"
                                    tabindex="0"
                                    role="button"
                                    aria-label="Detail <?php echo htmlspecialchars($b['judul'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                    <div class="book-card-cover">
                                        <img src="<?php echo htmlspecialchars($cover); ?>" alt="" loading="lazy">
                                    </div>
                                    <div class="book-card-body">
                                        <h2 class="book-card-title"><?php echo htmlspecialchars($b['judul']); ?></h2>
                                        <p class="book-card-author"><?php echo htmlspecialchars($b['penulis']); ?></p>
                                        <span class="book-card-status <?php echo $stClass; ?>"><?php echo $stLabel; ?></span>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <aside class="detail-panel">
                    <div class="detail-panel-inner" id="detailPanelInner">
                        <button type="button" class="panel-close-m" data-panel-close>Tutup panel</button>
                        <div id="detailPanelBody">
                            <p class="empty-state">Pilih buku pada grid untuk melihat detail di sini.</p>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  if (window.EPerpus) {
    EPerpus.initBookDetailPanel({ isAdmin: <?php echo $isAdmin ? 'true' : 'false'; ?>, apiUrl: 'api/book-detail.php' });
  }
});
</script>
</body>
</html>
