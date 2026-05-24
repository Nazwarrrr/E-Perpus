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
(function(){
    var t=localStorage.getItem('eperpustakaan-theme');
    var isDark = t==='dark';
    document.documentElement.classList.remove('light','dark');
    document.documentElement.classList.add(isDark?'dark':'light');
    document.body.classList.remove('light','dark');
    document.body.classList.add(isDark?'dark':'light');
})();
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
                    <i class="fas fa-book admin-summary-icon"></i>
                    <p class="admin-summary-label">Total Buku</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['total_buku']; ?></p>
                </a>
                <a class="admin-summary-card" href="buku.php" title="Lihat stok buku tersedia">
                    <i class="fas fa-check-circle admin-summary-icon"></i>
                    <p class="admin-summary-label">Buku Tersedia</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['tersedia']; ?></p>
                </a>
                <a class="admin-summary-card" href="peminjaman.php" title="Lihat buku dipinjam">
                    <i class="fas fa-share admin-summary-icon"></i>
                    <p class="admin-summary-label">Buku Dipinjam</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['dipinjam']; ?></p>
                </a>
                <a class="admin-summary-card" href="peminjaman.php" title="Lihat peminjaman">
                    <i class="fas fa-users admin-summary-icon"></i>
                    <p class="admin-summary-label">Total Siswa</p>
                    <p class="admin-summary-value"><?php echo (int) $stats['total_user']; ?></p>
                </a>
            </div>
            <section class="admin-insight-grid">
                <article class="card admin-chart-card">
                    <div class="admin-section-head">
                        <h2><i class="fas fa-chart-line"></i> Statistik Peminjaman</h2>
                        <p>Data dummy acak (sementara)</p>
                    </div>
                    <div class="admin-chart-toolbar">
                        <button type="button" class="chart-filter-btn is-active" data-days="7">7 Hari</button>
                        <button type="button" class="chart-filter-btn" data-days="14">14 Hari</button>
                        <button type="button" class="chart-filter-btn" data-days="30">30 Hari</button>
                    </div>
                    <div id="adminBorrowChart"></div>
                </article>

                <article class="card admin-recommend-card">
                    <div class="admin-section-head">
                        <h2><i class="fas fa-star"></i> Rekomendasi Buku</h2>
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
                    <h2><i class="fas fa-clock"></i> Aktivitas Terbaru</h2>
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
                            <a class="admin-activity-item" href="peminjaman.php" title="Lihat detail peminjaman">
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
  var chartContainer = document.getElementById('adminBorrowChart');
  if (!chartContainer) return;
  var buttons = Array.prototype.slice.call(document.querySelectorAll('.chart-filter-btn'));
  var refreshBtn = document.getElementById('refreshDashboardBtn');
  var chart = null;

  function getChartColors() {
    var isDark = document.documentElement.classList.contains('dark');
    return {
      primary: isDark ? '#3B82F6' : '#3B82F6',
      text: isDark ? '#f8fafc' : '#0f172a',
      textMuted: isDark ? '#94a3b8' : '#64748b',
      border: isDark ? '#1e293b' : '#e2e8f0',
      grid: isDark ? '#1e293b' : '#f1f5f9'
    };
  }

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
    var colors = getChartColors();

    var options = {
      chart: {
        type: 'area',
        height: 300,
        sparkline: { enabled: false },
        toolbar: { show: false },
        background: 'transparent',
        fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, sans-serif'
      },
      series: [{
        name: 'Peminjaman',
        data: values
      }],
      xaxis: {
        categories: labels,
        labels: {
          style: {
            colors: colors.textMuted,
            fontSize: '12px',
            fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, sans-serif'
          }
        },
        axisBorder: { show: false },
        axisTicks: { show: false }
      },
      yaxis: {
        labels: {
          style: {
            colors: colors.textMuted,
            fontSize: '12px',
            fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, sans-serif'
          }
        }
      },
      colors: [colors.primary],
      fill: {
        type: 'gradient',
        gradient: {
          shadeIntensity: 1,
          opacityFrom: 0.25,
          opacityTo: 0.05,
          stops: [0, 100],
          colorStops: [
            {
              offset: 0,
              color: colors.primary,
              opacity: 0.25
            },
            {
              offset: 100,
              color: colors.primary,
              opacity: 0
            }
          ]
        }
      },
      stroke: {
        curve: 'smooth',
        width: 3,
        colors: [colors.primary]
      },
      tooltip: {
        enabled: true,
        theme: document.documentElement.classList.contains('dark') ? 'dark' : 'light',
        style: {
          fontSize: '12px',
          fontFamily: 'system-ui, -apple-system, "Segoe UI", Roboto, Ubuntu, sans-serif'
        },
        x: {
          format: 'dd MMM'
        }
      },
      grid: {
        show: true,
        borderColor: colors.border,
        strokeDashArray: 3,
        xaxis: { lines: { show: false } },
        yaxis: { lines: { show: true } },
        padding: { top: 0, right: 0, bottom: 0, left: 0 }
      },
      dataLabels: { enabled: false },
      markers: {
        size: 4,
        colors: [colors.primary],
        strokeColors: [colors.primary],
        strokeWidth: 2,
        hover: { size: 6 }
      }
    };

    if (chart) {
      chart.updateOptions(options);
    } else {
      chart = new ApexCharts(chartContainer, options);
      chart.render();
    }
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
