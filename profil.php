<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireLogin();

$uid = (int) $_SESSION['id_user'];
$user = getUserById($conn, $uid);
if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

$errors = [];
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = (string) ($_POST['password'] ?? '');

    if (!empty($_FILES['foto']['name'])) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload foto gagal.';
        } elseif (!allowedImageMime($_FILES['foto']['tmp_name'])) {
            $errors[] = 'Foto harus JPG atau PNG.';
        } else {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $errors[] = 'Ekstensi foto harus jpg atau png.';
            } else {
                $dir = __DIR__ . '/uploads/profil';
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $fname = 'user_' . $uid . '_' . uniqid('', true) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dir . '/' . $fname)) {
                    $old = $user['foto'] ?? null;
                    $stmt = $conn->prepare('UPDATE users SET foto = ? WHERE id_user = ?');
                    $stmt->bind_param('si', $fname, $uid);
                    if ($stmt->execute()) {
                        $_SESSION['foto'] = $fname;
                        if ($old && is_file($dir . '/' . $old)) {
                            @unlink($dir . '/' . $old);
                        }
                        $user['foto'] = $fname;
                        $ok = 'Foto profil diperbarui.';
                    } else {
                        @unlink($dir . '/' . $fname);
                        $errors[] = 'Gagal menyimpan foto.';
                    }
                } else {
                    $errors[] = 'Gagal menyimpan file.';
                }
            }
        }
    }

    if ($password !== '') {
        if (strlen($password) < 6) {
            $errors[] = 'Password minimal 6 karakter.';
        } else {
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare('UPDATE users SET password = ? WHERE id_user = ?');
            $stmt->bind_param('si', $hash, $uid);
            if ($stmt->execute()) {
                $ok = $ok !== '' ? $ok . ' Password diperbarui.' : 'Password diperbarui.';
            } else {
                $errors[] = 'Gagal memperbarui password.';
            }
        }
    }
}

$pageTitle = 'Profil';
$activeNav = 'profil';
$currentUser = $user;
$showBookSearch = false;
$avatar = profilePhotoUrl($user['foto'] ?? null);

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
            <h1 class="page-title">Profil</h1>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
            <?php if ($ok !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div>
            <?php endif; ?>

            <div class="card profile-header">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="" class="profile-avatar-lg" width="96" height="96">
                <div>
                    <h2 style="margin:0 0 0.25rem;font-size:1.25rem"><?php echo htmlspecialchars($user['username']); ?></h2>
                    <span class="role-pill"><?php echo htmlspecialchars($user['role']); ?></span>
                </div>
            </div>

            <div class="card" style="max-width:520px">
                <h3 style="margin-top:0;font-size:1rem">Ubah foto</h3>
                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <div class="form-group">
                        <label for="foto">Foto profil (JPG/PNG)</label>
                        <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png">
                    </div>
                    <div class="form-group">
                        <label for="password">Password baru (opsional)</label>
                        <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password">
                        <p class="form-hint">Minimal 6 karakter jika diisi.</p>
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
