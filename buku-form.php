<?php
require_once __DIR__ . '/inc/bootstrap.php';
requireAdmin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$edit = $id > 0;
$book = $edit ? getBookById($conn, $id) : null;
if ($edit && !$book) {
    header('Location: buku.php');
    exit;
}

$errors = [];
$message = '';
$messageOk = false;

$fields = [
    'judul' => '',
    'penulis' => '',
    'penerbit' => '',
    'tahun_terbit' => '',
    'jumlah_halaman' => '',
    'rak' => '',
    'stok' => '',
    'deskripsi' => '',
];

if ($book) {
    $fields = [
        'judul' => (string) $book['judul'],
        'penulis' => (string) $book['penulis'],
        'penerbit' => (string) $book['penerbit'],
        'tahun_terbit' => (string) $book['tahun_terbit'],
        'jumlah_halaman' => (string) $book['jumlah_halaman'],
        'rak' => (string) $book['rak'],
        'stok' => (string) $book['stok'],
        'deskripsi' => (string) $book['deskripsi'],
    ];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($fields as $k => $_) {
        $fields[$k] = trim((string) ($_POST[$k] ?? ''));
    }

    foreach (['judul', 'penulis', 'penerbit', 'rak', 'deskripsi'] as $k) {
        if ($fields[$k] === '') {
            $errors[] = 'Field wajib tidak boleh kosong.';
            break;
        }
    }

    $tahun = (int) $fields['tahun_terbit'];
    $hal = (int) $fields['jumlah_halaman'];
    $stok = (int) $fields['stok'];
    if ($tahun <= 0 || $hal <= 0 || $stok < 0) {
        $errors[] = 'Tahun terbit, jumlah halaman, dan stok harus valid.';
    }

    $uploadName = null;
    if (!empty($_FILES['foto']['name'])) {
        if ($_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload gambar gagal.';
        } elseif (!allowedImageMime($_FILES['foto']['tmp_name'])) {
            $errors[] = 'Gambar harus JPG atau PNG.';
        }
    } elseif (!$edit) {
        $errors[] = 'Gambar buku wajib diunggah (JPG/PNG).';
    }

    if (count($errors) === 0) {
        if (!empty($_FILES['foto']['name']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
                $errors[] = 'Ekstensi file harus jpg atau png.';
            } else {
                $dir = __DIR__ . '/uploads/buku';
                if (!is_dir($dir)) {
                    mkdir($dir, 0775, true);
                }
                $uploadName = 'buku_' . uniqid('', true) . '.' . ($ext === 'jpeg' ? 'jpg' : $ext);
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $dir . '/' . $uploadName)) {
                    $errors[] = 'Gagal menyimpan file gambar.';
                    $uploadName = null;
                }
            }
        }
    }

    if (count($errors) === 0) {
        $status = $stok > 0 ? 'tersedia' : 'habis';
        if ($edit) {
            $oldFoto = $book['foto'] ?? null;
            $fotoCol = $uploadName ?? $oldFoto;
            $stmt = $conn->prepare(
                'UPDATE buku SET judul=?, penulis=?, penerbit=?, tahun_terbit=?, jumlah_halaman=?, rak=?, stok=?, status=?, deskripsi=?, foto=? WHERE id_buku=?'
            );
            $typesUp = 'sss' . 'ii' . 's' . 'i' . 'sss' . 'i';
            $stmt->bind_param(
                $typesUp,
                $fields['judul'],
                $fields['penulis'],
                $fields['penerbit'],
                $tahun,
                $hal,
                $fields['rak'],
                $stok,
                $status,
                $fields['deskripsi'],
                $fotoCol,
                $id
            );
            if ($stmt->execute()) {
                if ($uploadName && $oldFoto && is_file(__DIR__ . '/uploads/buku/' . $oldFoto)) {
                    @unlink(__DIR__ . '/uploads/buku/' . $oldFoto);
                }
                $message = 'Buku berhasil diperbarui.';
                $messageOk = true;
            } else {
                $errors[] = 'Gagal memperbarui data.';
                if ($uploadName && is_file(__DIR__ . '/uploads/buku/' . $uploadName)) {
                    @unlink(__DIR__ . '/uploads/buku/' . $uploadName);
                }
            }
        } else {
            $stmt = $conn->prepare(
                'INSERT INTO buku (judul, penulis, penerbit, tahun_terbit, jumlah_halaman, rak, stok, status, deskripsi, foto) VALUES (?,?,?,?,?,?,?,?,?,?)'
            );
            $typesIn = 'sss' . 'ii' . 's' . 'i' . 'sss';
            $stmt->bind_param(
                $typesIn,
                $fields['judul'],
                $fields['penulis'],
                $fields['penerbit'],
                $tahun,
                $hal,
                $fields['rak'],
                $stok,
                $status,
                $fields['deskripsi'],
                $uploadName
            );
            if ($stmt->execute()) {
                $message = 'Buku berhasil ditambahkan.';
                $messageOk = true;
                $fields = array_map(function () {
                    return '';
                }, $fields);
            } else {
                $errors[] = 'Gagal menambah buku.';
                if ($uploadName && is_file(__DIR__ . '/uploads/buku/' . $uploadName)) {
                    @unlink(__DIR__ . '/uploads/buku/' . $uploadName);
                }
            }
        }
    }
}

$pageTitle = $edit ? 'Edit Buku' : 'Tambah Buku';
$activeNav = 'buku';
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
            <div class="toolbar">
                <h1 class="page-title" style="margin:0"><?php echo htmlspecialchars($pageTitle); ?></h1>
                <a href="buku.php" class="btn btn-ghost"><i class="fas fa-arrow-left"></i> Kembali ke katalog</a>
            </div>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
            <?php if ($message !== ''): ?>
                <div class="alert <?php echo $messageOk ? 'alert-success' : 'alert-error'; ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <div class="card" style="max-width:720px">
                <?php if ($edit && $book): ?>
                    <p style="margin-top:0;font-size:0.875rem;color:var(--text-muted)">Sampul saat ini:</p>
                    <img src="<?php echo htmlspecialchars(bookCoverUrl($book['foto'] ?? null)); ?>" alt="" style="max-width:160px;border-radius:12px;margin-bottom:1rem">
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data" class="form-grid">
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label for="judul">Judul</label>
                            <input type="text" name="judul" id="judul" required value="<?php echo htmlspecialchars($fields['judul']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="penulis">Penulis</label>
                            <input type="text" name="penulis" id="penulis" required value="<?php echo htmlspecialchars($fields['penulis']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="penerbit">Penerbit</label>
                        <input type="text" name="penerbit" id="penerbit" required value="<?php echo htmlspecialchars($fields['penerbit']); ?>">
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label for="tahun_terbit">Tahun terbit</label>
                            <input type="number" name="tahun_terbit" id="tahun_terbit" required min="1000" max="9999" value="<?php echo htmlspecialchars($fields['tahun_terbit']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="jumlah_halaman">Jumlah halaman</label>
                            <input type="number" name="jumlah_halaman" id="jumlah_halaman" required min="1" value="<?php echo htmlspecialchars($fields['jumlah_halaman']); ?>">
                        </div>
                    </div>
                    <div class="form-grid cols-2">
                        <div class="form-group">
                            <label for="rak">Rak</label>
                            <input type="text" name="rak" id="rak" required value="<?php echo htmlspecialchars($fields['rak']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="stok">Stok</label>
                            <input type="number" name="stok" id="stok" required min="0" value="<?php echo htmlspecialchars($fields['stok']); ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="foto">Upload gambar (JPG/PNG)<?php echo $edit ? ' — kosongkan jika tidak diganti' : ''; ?></label>
                        <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png" <?php echo $edit ? '' : 'required'; ?>>
                        <p class="form-hint">Format wajib JPG atau PNG.</p>
                    </div>
                    <div class="form-group">
                        <label for="deskripsi">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" required><?php echo htmlspecialchars($fields['deskripsi']); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i> <?php echo $edit ? 'Simpan perubahan' : 'Simpan buku'; ?></button>
                </form>
            </div>
        </div>
    </main>
</div>
<script src="assets/js/app.js"></script>
</body>
</html>
