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

// Handle edit biodata
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_biodata') {
    $email = (string) ($_POST['email'] ?? '');
    $no_hp = (string) ($_POST['no_hp'] ?? '');
    $alamat = (string) ($_POST['alamat'] ?? '');
    $tanggal_lahir = (string) ($_POST['tanggal_lahir'] ?? '');

    if (updateUserBiodata($conn, $uid, $email, $no_hp, $alamat, $tanggal_lahir)) {
        $ok = 'Biodata berhasil diperbarui!';
        $user = getUserById($conn, $uid);
    } else {
        $errors[] = 'Gagal memperbarui biodata.';
    }
}

// Handle add to favorites (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_favorite') {
    header('Content-Type: application/json');
    $id_buku = (int) ($_POST['id_buku'] ?? 0);
    
    if ($id_buku > 0) {
        if (addToFavorites($conn, $uid, $id_buku)) {
            echo json_encode(['success' => true, 'message' => 'Ditambahkan ke favorit']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menambahkan ke favorit']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
    }
    exit;
}

// Handle remove from favorites (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_favorite') {
    header('Content-Type: application/json');
    $id_buku = (int) ($_POST['id_buku'] ?? 0);
    
    if ($id_buku > 0) {
        if (removeFromFavorites($conn, $uid, $id_buku)) {
            echo json_encode(['success' => true, 'message' => 'Dihapus dari favorit']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Gagal menghapus dari favorit']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ID buku tidak valid']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['action'])) {
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

// Get favorite books (from favorit_buku table)
$favBooks = getFavoriteBooks($conn, $uid);

// Also get borrowed books for reference
$borrowedBooks = [];
$stmt = $conn->prepare('
    SELECT b.id_buku, b.judul, b.penulis, b.foto, p.tanggal_pinjam
    FROM peminjaman p
    JOIN buku b ON p.id_buku = b.id_buku
    WHERE p.id_user = ?
    ORDER BY p.tanggal_pinjam DESC
    LIMIT 5
');
$stmt->bind_param('i', $uid);
$stmt->execute();
$borrowedBooks = $stmt->get_result()->fetch_all(MYSQLI_ASSOC) ?? [];

$pageTitle = 'Profil';
$activeNav = 'profil';
$currentUser = $user;
$showBookSearch = false;
$avatar = profilePhotoUrl($user['foto'] ?? null);

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
            <h1 class="page-title">Profil Saya</h1>

            <?php foreach ($errors as $e): ?>
                <div class="alert alert-error"><?php echo htmlspecialchars($e); ?></div>
            <?php endforeach; ?>
            <?php if ($ok !== ''): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($ok); ?></div>
            <?php endif; ?>

            <div class="profile-layout">
                <!-- LEFT COLUMN: MAIN PROFILE -->
                <div class="profile-main">
                    <!-- Avatar & Username Card -->
                    <div class="card profile-card-header">
                        <img src="<?php echo htmlspecialchars($avatar); ?>" alt="" class="profile-avatar-lg" width="96" height="96">
                        <div class="profile-header-text">
                            <h2 class="profile-username"><?php echo htmlspecialchars($user['username']); ?></h2>
                            <span class="role-pill"><?php echo strtoupper(htmlspecialchars($user['role'])); ?></span>
                            <p style="margin: 0.75rem 0 0; font-size: 0.9rem; color: var(--text-muted);">Bergabung sejak <?php echo date('d F Y', strtotime($user['created_at'] ?? 'now')); ?></p>
                        </div>
                    </div>

                    <!-- Biodata Card -->
                    <div class="card profile-biodata">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 class="profile-section-title">Biodata</h3>
                            <button type="button" class="profile-section-title-action" onclick="openEditBiodataModal()" style="color: var(--primary); cursor: pointer; border: none; background: none; padding: 0; font-size: 0.9rem;">Edit Biodata</button>
                        </div>
                        <div class="biodata-list">
                            <div class="biodata-item">
                                <div class="biodata-icon"></div>
                                <div class="biodata-content">
                                    <p class="biodata-label">Nama Lengkap</p>
                                    <p class="biodata-value"><?php echo htmlspecialchars($user['username']); ?></p>
                                </div>
                            </div>
                            <div class="biodata-item">
                                <div class="biodata-icon"></div>
                                <div class="biodata-content">
                                    <p class="biodata-label">Email</p>
                                    <p class="biodata-value"><?php echo htmlspecialchars($user['email'] ?? 'Tidak diisi'); ?></p>
                                </div>
                            </div>
                            <div class="biodata-item">
                                <div class="biodata-icon"></div>
                                <div class="biodata-content">
                                    <p class="biodata-label">No HP</p>
                                    <p class="biodata-value"><?php echo htmlspecialchars($user['no_hp'] ?? 'Tidak diisi'); ?></p>
                                </div>
                            </div>
                            <div class="biodata-item">
                                <div class="biodata-icon"></div>
                                <div class="biodata-content">
                                    <p class="biodata-label">Role</p>
                                    <p class="biodata-value"><?php echo ucfirst(htmlspecialchars($user['role'])); ?> Perpus</p>
                                </div>
                            </div>
                            <div class="biodata-item">
                                <div class="biodata-icon"></div>
                                <div class="biodata-content">
                                    <p class="biodata-label">Alamat</p>
                                    <p class="biodata-value"><?php echo htmlspecialchars($user['alamat'] ?? 'Tidak diisi'); ?></p>
                                </div>
                            </div>
                            <div class="biodata-item">
                                <div class="biodata-icon"></div>
                                <div class="biodata-content">
                                    <p class="biodata-label">Tanggal Lahir</p>
                                    <p class="biodata-value"><?php echo ($user['tanggal_lahir'] ?? null) ? date('d F Y', strtotime($user['tanggal_lahir'])) : 'Tidak diisi'; ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Form Card -->
                    <div class="card profile-edit-form">
                        <h3 class="profile-section-title">Ubah Profil</h3>
                        <form method="post" enctype="multipart/form-data" class="form-grid">
                            <div class="form-group">
                                <label for="foto">Foto profil (JPG/PNG)</label>
                                <input type="file" name="foto" id="foto" accept=".jpg,.jpeg,.png,image/jpeg,image/png" class="form-input-file">
                                <p class="form-hint">Ukuran maksimal 5MB. Format: JPG atau PNG.</p>
                            </div>
                            <div class="form-group">
                                <label for="password">Password baru (opsional)</label>
                                <input type="password" name="password" id="password" placeholder="Kosongkan jika tidak diubah" autocomplete="new-password" class="form-input">
                                <p class="form-hint">Minimal 6 karakter jika diisi.</p>
                            </div>
                            <button type="submit" class="btn btn-submit">Simpan Perubahan</button>
                        </form>
                    </div>
                </div>

                <!-- RIGHT COLUMN: FAVORITE BOOKS -->
                <div class="profile-sidebar">
                    <div class="card profile-favorites">
                        <div class="profile-fav-header">
                            <h3 class="profile-section-title" style="margin-bottom: 0;">Buku Favorit</h3>
                            <button type="button" class="btn-add-fav" onclick="openAddFavoritesModal()">+ Tambah Buku</button>
                        </div>
                        <?php if (count($favBooks) > 0): ?>
                            <div class="favorites-list">
                                <?php foreach ($favBooks as $idx => $book): ?>
                                    <div class="favorite-item" id="fav-<?php echo $book['id_buku']; ?>">
                                        <?php 
                                            $bookCoverPath = 'uploads/buku/' . htmlspecialchars($book['foto']);
                                            $coverUrl = file_exists($bookCoverPath) ? $bookCoverPath : 'assets/img/placeholder-book.jpg';
                                        ?>
                                        <img src="<?php echo htmlspecialchars($coverUrl); ?>" alt="<?php echo htmlspecialchars($book['judul']); ?>" class="favorite-cover">
                                        <div class="favorite-content">
                                            <p class="favorite-title"><?php echo htmlspecialchars($book['judul']); ?></p>
                                            <p class="favorite-author"><?php echo htmlspecialchars($book['penulis']); ?></p>
                                            <div class="favorite-tags">
                                                <span class="favorite-tag">Favorit</span>
                                            </div>
                                        </div>
                                        <div class="favorite-actions">
                                            <button type="button" class="btn-icon-fav" title="Hapus dari favorit" onclick="removeFavorite(<?php echo $book['id_buku']; ?>)"><i class="fas fa-trash"></i></button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-favorites">
                                <p>Belum ada buku favorit</p>
                            </div>
                        <?php endif; ?>
                    </div>


                </div>
            </div>
        </div>
    </main>
</div>

<!-- MODAL: Edit Biodata -->
<div id="editBiodataModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeEditBiodataModal()"></div>
    <div class="modal-content">
        <div class="modal-header">
            <h2>Edit Biodata</h2>
            <button type="button" class="modal-close" onclick="closeEditBiodataModal()">×</button>
        </div>
        <form method="post" class="modal-form">
            <input type="hidden" name="action" value="edit_biodata">
            
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" class="form-input" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
            </div>
            
            <div class="form-group">
                <label for="no_hp">No HP</label>
                <input type="tel" name="no_hp" id="no_hp" class="form-input" placeholder="+62 8XX XXXX XXXX" value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>">
            </div>
            
            
            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea name="alamat" id="alamat" class="form-input" rows="3" placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($user['alamat'] ?? ''); ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="tanggal_lahir">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" id="tanggal_lahir" class="form-input" value="<?php echo htmlspecialchars($user['tanggal_lahir'] ?? ''); ?>">
            </div>
            
            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" onclick="closeEditBiodataModal()">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Biodata</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: Add Favorites -->
<div id="addFavoritesModal" class="modal" style="display: none;">
    <div class="modal-overlay" onclick="closeAddFavoritesModal()"></div>
    <div class="modal-content modal-large">
        <div class="modal-header">
            <h2>Tambah Buku ke Favorit</h2>
            <button type="button" class="modal-close" onclick="closeAddFavoritesModal()">×</button>
        </div>
        <div class="modal-body">
            <input type="text" id="searchBooks" class="form-input" placeholder="Cari buku..." onkeyup="filterBooks()">
            <div id="booksList" class="books-grid"></div>
        </div>
    </div>
</div>

<style>
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 1000;
}

.modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
}

.modal-content {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: var(--card);
    border-radius: 12px;
    width: 90%;
    max-width: 500px;
    max-height: 80vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    z-index: 1001;
}

.modal-content.modal-large {
    max-width: 700px;
}

.modal-header {
    padding: 2rem;
    border-bottom: 1px solid rgba(59, 130, 246, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h2 {
    margin: 0;
    font-size: 1.5rem;
    color: var(--text);
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--text-muted);
    transition: color 0.2s;
}

.modal-close:hover {
    color: var(--text);
}

.modal-form {
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.modal-body {
    padding: 2rem;
}

.modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    padding: 2rem;
    border-top: 1px solid rgba(59, 130, 246, 0.1);
}

.btn-primary, .btn-secondary {
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-primary {
    background: var(--primary);
    color: white;
}

.btn-primary:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
}

.btn-secondary {
    background: rgba(59, 130, 246, 0.1);
    color: var(--primary);
}

.btn-secondary:hover {
    background: rgba(59, 130, 246, 0.2);
}

.books-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.book-card {
    border-radius: 10px;
    overflow: hidden;
    border: 1px solid rgba(59, 130, 246, 0.2);
    background: rgba(59, 130, 246, 0.05);
    cursor: pointer;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
}

.book-card:hover {
    border-color: rgba(59, 130, 246, 0.5);
    background: rgba(59, 130, 246, 0.1);
    transform: translateY(-4px);
}

.book-cover {
    width: 100%;
    height: 180px;
    object-fit: cover;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(139, 92, 246, 0.1));
}

.book-info {
    padding: 1rem;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.book-title {
    margin: 0 0 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: var(--text);
    text-align: center;
    line-height: 1.3;
}

.book-add-btn {
    width: 100%;
    padding: 0.5rem;
    margin-top: auto;
    border: none;
    border-radius: 6px;
    background: var(--primary);
    color: white;
    font-weight: 600;
    font-size: 0.85rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.book-add-btn:hover {
    background: var(--primary-hover);
}

.book-add-btn.added {
    background: #10b981;
}
</style>

<script>
function openEditBiodataModal() {
    document.getElementById('editBiodataModal').style.display = 'block';
}

function closeEditBiodataModal() {
    document.getElementById('editBiodataModal').style.display = 'none';
}

function openAddFavoritesModal() {
    document.getElementById('addFavoritesModal').style.display = 'block';
    loadBooksForFavorites();
}

function closeAddFavoritesModal() {
    document.getElementById('addFavoritesModal').style.display = 'none';
}

function loadBooksForFavorites() {
    const booksList = document.getElementById('booksList');
    booksList.innerHTML = '<p style="text-align: center; color: var(--text-muted);">Memuat buku...</p>';
    
    // Get all available books
    fetch('api/book-search.php?action=all')
        .then(r => r.json())
        .then(books => {
            if (!Array.isArray(books) || books.length === 0) {
                booksList.innerHTML = '<p style="text-align: center; color: var(--text-muted);">Tidak ada buku yang tersedia.</p>';
                return;
            }
            
            booksList.innerHTML = books.map(book => `
                <div class="book-card">
                    <img src="uploads/buku/${book.foto}" alt="${book.judul}" class="book-cover" onerror="this.src='assets/img/placeholder-book.jpg'">
                    <div class="book-info">
                        <p class="book-title">${book.judul}</p>
                        <button type="button" class="book-add-btn" onclick="addFavorite(${book.id_buku})">+ Favorit</button>
                    </div>
                </div>
            `).join('');
        })
        .catch(err => {
            console.error('Error loading books:', err);
            booksList.innerHTML = '<p style="text-align: center; color: var(--text-muted);">Error memuat daftar buku.</p>';
        });
}

function filterBooks() {
    const search = document.getElementById('searchBooks').value.toLowerCase();
    const cards = document.querySelectorAll('.book-card');
    
    cards.forEach(card => {
        const title = card.querySelector('.book-title').textContent.toLowerCase();
        card.style.display = title.includes(search) ? '' : 'none';
    });
}

function addFavorite(idBuku) {
    const formData = new FormData();
    formData.append('action', 'add_favorite');
    formData.append('id_buku', idBuku);
    
    fetch('profil.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Ditambahkan ke favorit!');
            closeAddFavoritesModal();
            location.reload();
        } else {
            alert(data.message || 'Gagal menambahkan ke favorit');
        }
    })
    .catch(err => {
        alert('Error: ' + err);
    });
}

function removeFavorite(idBuku) {
    if (!confirm('Hapus dari favorit?')) return;
    
    const formData = new FormData();
    formData.append('action', 'remove_favorite');
    formData.append('id_buku', idBuku);
    
    fetch('profil.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById('fav-' + idBuku);
            if (item) {
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                setTimeout(() => item.remove(), 300);
            }
        } else {
            alert(data.message || 'Gagal menghapus dari favorit');
        }
    })
    .catch(err => {
        alert('Error: ' + err);
    });
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    const editModal = document.getElementById('editBiodataModal');
    const favModal = document.getElementById('addFavoritesModal');
    
    if (event.target === editModal) closeEditBiodataModal();
    if (event.target === favModal) closeAddFavoritesModal();
});
</script>
<script src="assets/js/app.js"></script>
</body>
</html>
