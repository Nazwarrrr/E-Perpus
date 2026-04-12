<?php
$showSearch = $showBookSearch ?? false;
$me = $currentUser ?? null;
$uname = $me['username'] ?? ($_SESSION['username'] ?? '');
$foto = profilePhotoUrl($me['foto'] ?? ($_SESSION['foto'] ?? null));
?>
<header class="topbar">
    <div class="topbar-left">
        <?php if ($showSearch): ?>
            <label class="search-wrap" for="globalBookSearch">
                <span class="search-icon" aria-hidden="true"></span>
                <input type="search" id="globalBookSearch" class="search-input" placeholder="Cari judul atau penulis..." autocomplete="off" data-book-search>
            </label>
        <?php else: ?>
            <div class="topbar-placeholder" aria-hidden="true"></div>
        <?php endif; ?>
    </div>
    <div class="topbar-right">
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Ganti tema terang/gelap"></button>
        <div class="profile-dd" data-dropdown>
            <button type="button" class="profile-trigger" data-dropdown-trigger aria-expanded="false" aria-haspopup="true">
                <img src="<?php echo htmlspecialchars($foto); ?>" alt="" class="profile-avatar-sm" width="36" height="36">
                <span class="profile-name"><?php echo htmlspecialchars($uname); ?></span>
                <span class="caret" aria-hidden="true"></span>
            </button>
            <div class="profile-menu" data-dropdown-menu hidden>
                <a href="profil.php">Profil</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </div>
</header>
