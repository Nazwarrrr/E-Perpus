<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireAdmin();

$err = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'kembali') {
        $idp = (int) ($_POST['id_pinjam'] ?? 0);
        $msg = adminKembalikanBuku($conn, $idp);
        if ($msg === '') {
            $ok = 'Buku ditandai dikembalikan.';
        } else {
            $err = $msg;
        }
    } else {
        $uid = (int) ($_POST['id_user'] ?? 0);
        $bid = (int) ($_POST['id_buku'] ?? 0);
        if ($uid <= 0 || $bid <= 0) {
            $err = 'Pilih siswa dan buku.';
        } else {
            $msg = adminBuatPeminjaman($conn, $uid, $bid);
            if ($msg === '') {
                $ok = 'Peminjaman berhasil dicatat (status: dipinjam).';
            } else {
                $err = $msg;
            }
        }
    }
}

$siswaList = getSiswaUsers($conn);
$bukuList = getBooksWithStock($conn);
$aktif = getPeminjamanAktif($conn);
$riwayat = getAllRiwayat($conn);

$pageTitle = 'Peminjaman';
$activeNav = 'peminjaman';
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
            <h1 class="page-title">Peminjaman</h1>

            <?php if ($err !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
            <?php endif; ?>
            <?php if ($ok !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div>
            <?php endif; ?>

            <!-- FORM TAMBAH PEMINJAMAN -->
            <div class="card" style="margin-bottom:2rem">
                <h2 style="margin-top:0;font-size:1.1rem">Tambah Peminjaman Baru</h2>
                <p style="font-size:0.875rem;color:var(--text-muted);margin:0.5rem 0 1.25rem 0">Isi data peminjaman. Status langsung berubah menjadi <strong>"Dipinjam"</strong> dan stok buku otomatis berkurang.</p>
                
                <form method="post" class="form-grid cols-2" style="gap:1rem">
                    <div class="form-group">
                        <label for="id_user"><strong>Nama Siswa</strong></label>
                        <select name="id_user" id="id_user" required>
                            <option value="">— Pilih Siswa —</option>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?php echo (int) $s['id_user']; ?>"><?php echo htmlspecialchars($s['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Pilih siswa yang akan meminjam buku</p>
                    </div>

                    <div class="form-group">
                        <label for="id_buku"><strong>Judul Buku</strong></label>
                        <select name="id_buku" id="id_buku" required>
                            <option value="">— Pilih Buku —</option>
                            <?php foreach ($bukuList as $b): ?>
                                <option value="<?php echo (int) $b['id_buku']; ?>">
                                    <?php echo htmlspecialchars($b['judul']); ?> (Stok: <?php echo (int) $b['stok']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="form-hint">Hanya buku dengan stok tersedia</p>
                    </div>

                    <div class="form-group">
                        <label for="tanggal_pinjam"><strong>Tanggal Pinjam</strong></label>
                        <input type="datetime-local" id="tanggal_pinjam" disabled value="<?php echo date('Y-m-d\TH:i'); ?>" style="background:var(--bg);cursor:not-allowed">
                        <p class="form-hint">Otomatis terisi tanggal & waktu hari ini</p>
                    </div>

                    <div class="form-group">
                        <label for="deadline"><strong>Batas Pengembalian</strong></label>
                        <input type="text" id="deadline" disabled value="<?php echo date('d/m/Y', strtotime('+7 days')); ?>" style="background:var(--bg);cursor:not-allowed">
                        <p class="form-hint">7 hari setelah tanggal pinjam</p>
                    </div>
                </form>

                <div style="display:flex;gap:0.75rem;margin-top:1.5rem">
                    <button type="button" onclick="document.querySelector('form[method=post]').submit()" class="btn btn-primary">Simpan Peminjaman</button>
                    <button type="button" onclick="location.reload()" class="btn btn-ghost">Reset</button>
                </div>
            </div>

            <!-- DAFTAR PEMINJAMAN AKTIF -->
            <div style="margin-bottom:2rem">
                <h2 style="font-size:1.1rem;margin-bottom:0.75rem">Sedang Dipinjam (<?php echo count($aktif); ?>)</h2>
                <div class="table-wrap">
                    <?php if (count($aktif) === 0): ?>
                        <div class="empty-state">✓ Tidak ada peminjaman aktif saat ini</div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Judul Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Batas Kembali</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($aktif as $p): 
                                    $tglPinjam = strtotime($p['tanggal_pinjam']);
                                    $batasKembali = date('d/m/Y', strtotime('+7 days', $tglPinjam));
                                    $hariIni = strtotime('today');
                                    $isOverdue = $hariIni > strtotime($batasKembali);
                                ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($p['username']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($p['judul']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', $tglPinjam); ?></td>
                                        <td style="<?php echo $isOverdue ? 'color:#ef4444;font-weight:bold' : ''; ?>">
                                            <?php echo $batasKembali; ?>
                                            <?php if ($isOverdue): ?> Telat<?php endif; ?>
                                        </td>
                                        <td><span class="badge badge-dipinjam">Dipinjam</span></td>
                                        <td>
                                            <form method="post" style="display:inline" onsubmit="return confirm('Tandai buku sudah dikembalikan?');">
                                                <input type="hidden" name="action" value="kembali">
                                                <input type="hidden" name="id_pinjam" value="<?php echo (int) $p['id_pinjam']; ?>">
                                                <button type="submit" class="btn btn-ghost" style="padding:0.35rem 0.65rem;font-size:0.8rem">✓ Kembali</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RIWAYAT PEMINJAMAN -->
            <div>
                <h2 style="font-size:1.1rem;margin-bottom:0.75rem"> Riwayat Semua Peminjaman</h2>
                <div class="table-wrap">
                    <?php if (count($riwayat) === 0): ?>
                        <div class="empty-state">Belum ada riwayat peminjaman</div>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Judul Buku</th>
                                    <th>Tanggal Pinjam</th>
                                    <th>Tanggal Kembali</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($riwayat as $p): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($p['username']); ?></td>
                                        <td><?php echo htmlspecialchars($p['judul']); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_pinjam'])); ?></td>
                                        <td><?php echo !empty($p['tanggal_kembali']) ? date('d/m/Y H:i', strtotime($p['tanggal_kembali'])) : '—'; ?></td>
                                        <td>
                                            <?php if ($p['status'] === 'dipinjam'): ?>
                                                <span class="badge badge-dipinjam">Dipinjam</span>
                                            <?php else: ?>
                                                <span class="badge badge-kembali">✓ Dikembalikan</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
