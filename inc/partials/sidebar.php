<?php
$active = $activeNav ?? '';
$admin = isAdmin();
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo $admin ? 'dashboard-admin.php' : 'buku.php'; ?>" class="sidebar-logo">
            <img src="assets/img/logo.png" width="200" height="48" alt="E-Perpustakaan" class="logo-img">
        </a>
    </div>
    <nav class="sidebar-nav" aria-label="Menu utama">
        <?php if ($admin): ?>
            <a href="dashboard-admin.php" class="nav-link<?php echo $active === 'dashboard' ? ' is-active' : ''; ?>">
                <span class="nav-ic" aria-hidden="true"></span><span>Dashboard</span>
            </a>
            <a href="buku.php" class="nav-link<?php echo $active === 'buku' ? ' is-active' : ''; ?>">
                <span class="nav-ic nav-ic--book" aria-hidden="true"></span><span>Buku</span>
            </a>
            <a href="peminjaman.php" class="nav-link<?php echo $active === 'peminjaman' ? ' is-active' : ''; ?>">
                <span class="nav-ic nav-ic--loan" aria-hidden="true"></span><span>Peminjaman</span>
            </a>
            <a href="riwayat.php" class="nav-link<?php echo $active === 'riwayat' ? ' is-active' : ''; ?>">
                <span class="nav-ic nav-ic--history" aria-hidden="true"></span><span>Riwayat</span>
            </a>
        <?php else: ?>
            <a href="buku.php" class="nav-link<?php echo $active === 'buku' ? ' is-active' : ''; ?>">
                <span class="nav-ic nav-ic--book" aria-hidden="true"></span><span>Buku</span>
            </a>
            <a href="riwayat.php" class="nav-link<?php echo $active === 'riwayat' ? ' is-active' : ''; ?>">
                <span class="nav-ic nav-ic--history" aria-hidden="true"></span><span>Riwayat</span>
            </a>
        <?php endif; ?>
        <a href="profil.php" class="nav-link<?php echo $active === 'profil' ? ' is-active' : ''; ?>">
            <span class="nav-ic nav-ic--user" aria-hidden="true"></span><span>Profil</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link nav-link--logout">
            <span class="nav-ic nav-ic--out" aria-hidden="true"></span><span>Logout</span>
        </a>
    </div>
</aside>
