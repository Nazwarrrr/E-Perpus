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
                <i class="fas fa-chart-line nav-icon"></i><span>Dashboard</span>
            </a>
            <a href="buku.php" class="nav-link<?php echo $active === 'buku' ? ' is-active' : ''; ?>">
                <i class="fas fa-book nav-icon"></i><span>Buku</span>
            </a>
            <a href="peminjaman.php" class="nav-link<?php echo $active === 'peminjaman' ? ' is-active' : ''; ?>">
                <i class="fas fa-handshake nav-icon"></i><span>Peminjaman</span>
            </a>
        <?php else: ?>
            <a href="buku.php" class="nav-link<?php echo $active === 'buku' ? ' is-active' : ''; ?>">
                <i class="fas fa-book nav-icon"></i><span>Buku</span>
            </a>
        <?php endif; ?>
        <a href="profil.php" class="nav-link<?php echo $active === 'profil' ? ' is-active' : ''; ?>">
            <i class="fas fa-user nav-icon"></i><span>Profil</span>
        </a>
    </nav>
    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link nav-link--logout">
            <i class="fas fa-sign-out-alt nav-icon"></i><span>Logout</span>
        </a>
    </div>
</aside>
