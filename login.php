<?php
require_once __DIR__ . '/inc/bootstrap.php';

if (isLoggedIn()) {
    header('Location: ' . (isAdmin() ? 'dashboard-admin.php' : 'buku.php'));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string) ($_POST['username'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'Username dan password wajib diisi.';
    } elseif (!login($conn, $username, $password)) {
        $error = 'Username atau password salah.';
    } else {
        if (isAdmin()) {
            header('Location: dashboard-admin.php');
        } else {
            header('Location: buku.php');
        }
        exit;
    }
}

$pageTitle = 'Masuk — E-Perpustakaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
<body class="light login-page">
    <div class="login-bg-books" id="loginFlyBooks" aria-hidden="true"></div>

    <button type="button" class="theme-toggle" id="loginThemeToggle" style="position:fixed;top:1.25rem;right:1.25rem;z-index:5" aria-label="Ganti tema terang/gelap"></button>

    <div class="login-card-wrap">
        <div class="login-card">
            <div class="login-logo-container">
                <img src="assets/img/logo.png" alt="E-Perpustakaan Logo" class="login-logo">
            </div>
            <h1>E-Perpustakaan</h1>
            <p class="sub">Masuk untuk melanjutkan</p>

            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" class="form-grid" autocomplete="on">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required maxlength="100" value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:0.5rem">Masuk</button>
            </form>
        </div>
    </div>

    <script src="assets/js/login.js"></script>
    <script>
      (function () {
        var KEY = 'eperpustakaan-theme';
        function apply(mode) {
          document.body.classList.remove('light', 'dark');
          document.body.classList.add(mode === 'dark' ? 'dark' : 'light');
        }
        var saved = localStorage.getItem(KEY);
        var prefers = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
        apply(saved || (prefers ? 'dark' : 'light'));
        var btn = document.getElementById('loginThemeToggle');
        if (btn) {
          btn.addEventListener('click', function () {
            var next = document.body.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem(KEY, next);
            apply(next);
          });
        }
      })();
    </script>
</body>
</html>
