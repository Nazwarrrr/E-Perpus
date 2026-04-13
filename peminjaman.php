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

$pageTitle = 'Peminjaman';
$activeNav = 'peminjaman';
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
            <h1 class="page-title">Peminjaman</h1>

            <?php if ($err !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div>
            <?php endif; ?>
            <?php if ($ok !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div>
            <?php endif; ?>

            <div class="card" style="max-width:560px;margin-bottom:1.25rem">
                <h2 style="margin-top:0;font-size:1.05rem">Tambah peminjaman</h2>
                <p style="font-size:0.875rem;color:var(--text-muted);margin-top:0">Admin memilih siswa dan buku. Status langsung <strong>dipinjam</strong> dan stok berkurang.</p>
                <form method="post" class="form-grid" style="margin-top:1rem">
                    <div class="form-group">
                        <label for="id_user">Siswa</label>
                        <select name="id_user" id="id_user" required>
                            <option value="">— Pilih —</option>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?php echo (int) $s['id_user']; ?>"><?php echo htmlspecialchars($s['username']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="id_buku">Buku (stok &gt; 0)</label>
                        <select name="id_buku" id="id_buku" required>
                            <option value="">— Pilih —</option>
                            <?php foreach ($bukuList as $b): ?>
                                <option value="<?php echo (int) $b['id_buku']; ?>"><?php echo htmlspecialchars($b['judul']); ?> (stok <?php echo (int) $b['stok']; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan peminjaman</button>
                </form>
            </div>

            <h2 style="font-size:1.1rem;margin-bottom:0.75rem">Sedang dipinjam</h2>
            <div class="table-wrap">
                <?php if (count($aktif) === 0): ?>
                    <p class="empty-state">Tidak ada peminjaman aktif.</p>
                <?php else: ?>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Siswa</th>
                                <th>Buku</th>
                                <th>Tanggal pinjam</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($aktif as $p): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($p['username']); ?></td>
                                    <td><?php echo htmlspecialchars($p['judul']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($p['tanggal_pinjam'])); ?></td>
                                    <td><span class="badge badge-dipinjam">Dipinjam</span></td>
                                    <td>
                                        <form method="post" style="display:inline" onsubmit="return confirm('Tandai dikembalikan?');">
                                            <input type="hidden" name="action" value="kembali">
                                            <input type="hidden" name="id_pinjam" value="<?php echo (int) $p['id_pinjam']; ?>">
                                            <button type="submit" class="btn btn-ghost" style="padding:0.35rem 0.65rem;font-size:0.8rem">Dikembalikan</button>
                                        </form>
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
