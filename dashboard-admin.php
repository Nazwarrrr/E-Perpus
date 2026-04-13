<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireAdmin();

$stats = getStats($conn);
$recommendedBooks = getBooks($conn, 5, 0);
$recentActivities = [];
$activitySql = "SELECT p.status, p.tanggal_pinjam, p.tanggal_kembali, u.username, b.judul
                FROM peminjaman p
                JOIN users u ON p.id_user = u.id_user
                JOIN buku b ON p.id_buku = b.id_buku
                ORDER BY p.tanggal_pinjam DESC
                LIMIT 6";
$activityRes = $conn->query($activitySql);
if ($activityRes) {
    $recentActivities = $activityRes->fetch_all(MYSQLI_ASSOC);
}
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
            <div class="admin-page-head">
                <h1 class="page-title">Dashboard</h1>
                <div class="admin-quick-actions">
                    <a href="buku-form.php" class="btn btn-primary">Tambah Buku</a>
                    <a href="peminjaman.php" class="btn btn-ghost">Kelola Peminjaman</a>
                    <button type="button" class="btn btn-ghost" id="refreshDashboardBtn">Refresh Statistik</button>
                </div>
            </div>
            <div class="admin-summary-grid">
                <a class="admin-summary-card" href="buku.php" title="Lihat semua buku">
                    <div class="admin-summary-icon" aria-hidden="true"></div>
                    <p class="admin-summary-label">Total Buku</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['total_buku']; ?></p>
                </a>
                <a class="admin-summary-card" href="buku.php" title="Lihat stok buku tersedia">
                    <div class="admin-summary-icon" aria-hidden="true"></div>
                    <p class="admin-summary-label">Buku Tersedia</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['tersedia']; ?></p>
                </a>
                <a class="admin-summary-card" href="peminjaman.php" title="Lihat buku dipinjam">
                    <div class="admin-summary-icon" aria-hidden="true"></div>
                    <p class="admin-summary-label">Buku Dipinjam</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['dipinjam']; ?></p>
                </a>
                <a class="admin-summary-card" href="riwayat.php" title="Lihat data siswa">
                    <div class="admin-summary-icon" aria-hidden="true"></div>
                    <p class="admin-summary-label">Total Siswa</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['total_user']; ?></p>
                </a>
            </div>
            <section class="admin-insight-grid">
                <article class="card admin-chart-card">
                    <div class="admin-section-head">
                        <h2>Statistik Peminjaman</h2>
                        <p>Data dummy acak (sementara)</p>
                    </div>
                    <div class="admin-chart-toolbar">
                        <button type="button" class="chart-filter-btn is-active" data-days="7">7 Hari</button>
                        <button type="button" class="chart-filter-btn" data-days="14">14 Hari</button>
                        <button type="button" class="chart-filter-btn" data-days="30">30 Hari</button>
                    </div>
                    <canvas id="adminBorrowChart" width="960" height="300"></canvas>
                </article>

                <article class="card admin-recommend-card">
                    <div class="admin-section-head">
                        <h2>Rekomendasi Buku</h2>
                        <p>Diambil dari koleksi terbaru</p>
                    </div>
                    <div class="admin-recommend-list">
                        <?php if (count($recommendedBooks) === 0): ?>
                            <p class="empty-state" style="padding:0.5rem 0;">Belum ada buku untuk direkomendasikan.</p>
                        <?php else: ?>
                            <?php foreach ($recommendedBooks as $book): ?>
                                <a class="admin-recommend-item" href="buku.php" title="Buka katalog buku">
                                    <img
                                        src="<?php echo htmlspecialchars(bookCoverUrl($book['foto'] ?? null)); ?>"
                                        alt=""
                                        class="admin-recommend-cover"
                                        loading="lazy"
                                    >
                                    <div>
                                        <p class="admin-recommend-title"><?php echo htmlspecialchars($book['judul']); ?></p>
                                        <p class="admin-recommend-author"><?php echo htmlspecialchars($book['penulis']); ?></p>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="card admin-activity-card">
                <div class="admin-section-head">
                    <h2>Aktivitas Terbaru</h2>
                    <p>Maksimal 6 aktivitas terbaru</p>
                </div>
                <?php if (count($recentActivities) === 0): ?>
                    <p class="empty-state" style="padding:0.5rem 0;">Belum ada aktivitas peminjaman.</p>
                <?php else: ?>
                    <div class="admin-activity-list">
                        <?php foreach ($recentActivities as $activity): ?>
                            <?php
                            $isBorrowed = ($activity['status'] ?? '') === 'dipinjam';
                            $statusLabel = $isBorrowed ? 'Dipinjam' : 'Dikembalikan';
                            $statusClass = $isBorrowed ? 'badge-dipinjam' : 'badge-kembali';
                            $activityTime = $isBorrowed ? ($activity['tanggal_pinjam'] ?? null) : ($activity['tanggal_kembali'] ?? null);
                            $timeText = $activityTime ? date('d M Y H:i', strtotime((string) $activityTime)) : '-';
                            ?>
                            <a class="admin-activity-item" href="riwayat.php" title="Lihat detail riwayat">
                                <div class="admin-activity-main">
                                    <p class="admin-activity-user"><?php echo htmlspecialchars($activity['username'] ?? '-'); ?></p>
                                    <p class="admin-activity-book"><?php echo htmlspecialchars($activity['judul'] ?? '-'); ?></p>
                                </div>
                                <div class="admin-activity-meta">
                                    <span class="badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                    <span class="admin-activity-time"><?php echo htmlspecialchars($timeText); ?></span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  var canvas = document.getElementById('adminBorrowChart');
  if (!canvas) return;
  var ctx = canvas.getContext('2d');
  if (!ctx) return;
  var buttons = Array.prototype.slice.call(document.querySelectorAll('.chart-filter-btn'));
  var refreshBtn = document.getElementById('refreshDashboardBtn');

  function buildLabels(days) {
    var labels = [];
    for (var i = days - 1; i >= 0; i--) {
      labels.push('H-' + i);
    }
    return labels;
  }

  function buildValues(days) {
    var values = [];
    for (var i = 0; i < days; i++) {
      values.push(Math.floor(Math.random() * 25) + 6);
    }
    return values;
  }

  function renderChart(days) {
    var labels = buildLabels(days);
    var values = buildValues(days);
    var width = canvas.width;
    var height = canvas.height;
    var pad = { top: 24, right: 20, bottom: 34, left: 34 };
    var chartW = width - pad.left - pad.right;
    var chartH = height - pad.top - pad.bottom;
    var maxVal = Math.max.apply(null, values) || 1;
    var step = chartW / values.length;

    ctx.clearRect(0, 0, width, height);
    ctx.strokeStyle = '#cbd5e1';
    ctx.lineWidth = 1;
    ctx.beginPath();
    ctx.moveTo(pad.left, pad.top + chartH);
    ctx.lineTo(pad.left + chartW, pad.top + chartH);
    ctx.stroke();

    ctx.beginPath();
    ctx.strokeStyle = '#2563eb';
    ctx.fillStyle = 'rgba(37, 99, 235, 0.14)';
    ctx.lineWidth = 3;

    values.forEach(function (val, i) {
      var x = pad.left + i * step + step / 2;
      var y = pad.top + chartH - (val / maxVal) * chartH;
      if (i === 0) ctx.moveTo(x, y);
      else ctx.lineTo(x, y);
    });
    ctx.stroke();

    ctx.lineTo(pad.left + (values.length - 1) * step + step / 2, pad.top + chartH);
    ctx.lineTo(pad.left + step / 2, pad.top + chartH);
    ctx.closePath();
    ctx.fill();

    ctx.fillStyle = '#64748b';
    ctx.font = '12px Segoe UI, sans-serif';
    var labelStep = days > 14 ? 3 : 1;
    labels.forEach(function (label, i) {
      if (i % labelStep !== 0 && i !== labels.length - 1) return;
      var x = pad.left + i * step + step / 2;
      ctx.textAlign = 'center';
      ctx.fillText(label, x, pad.top + chartH + 19);
    });
  }

  buttons.forEach(function (btn) {
    btn.addEventListener('click', function () {
      buttons.forEach(function (b) { b.classList.remove('is-active'); });
      btn.classList.add('is-active');
      renderChart(parseInt(btn.getAttribute('data-days'), 10) || 7);
    });
  });

  if (refreshBtn) {
    refreshBtn.addEventListener('click', function () {
      var activeBtn = document.querySelector('.chart-filter-btn.is-active');
      var days = activeBtn ? parseInt(activeBtn.getAttribute('data-days'), 10) : 7;
      renderChart(days || 7);
    });
  }

  renderChart(7);
});
</script>
</body>
</html>
