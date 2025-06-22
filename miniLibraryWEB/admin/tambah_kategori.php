<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi/koneksi.php';

if ($_POST) {
    $nama_kategori = trim($_POST['nama_kategori']);
    
    if (empty($nama_kategori)) {
        $_SESSION['message'] = "Nama kategori tidak boleh kosong!";
        $_SESSION['message_type'] = "error";
    } else {
        // Check if category already exists
        $check_query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE nama_kategori = '" . mysqli_real_escape_string($koneksi, $nama_kategori) . "'");
        
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['message'] = "Kategori dengan nama tersebut sudah ada!";
            $_SESSION['message_type'] = "error";
        } else {
            $query = mysqli_query($koneksi, "INSERT INTO kategori (nama_kategori) VALUES ('" . mysqli_real_escape_string($koneksi, $nama_kategori) . "')");
            
            if ($query) {
                $_SESSION['message'] = "Kategori berhasil ditambahkan!";
                $_SESSION['message_type'] = "success";
                header("Location: kategori.php");
                exit;
            } else {
                $_SESSION['message'] = "Gagal menambahkan kategori: " . mysqli_error($koneksi);
                $_SESSION['message_type'] = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Kategori - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="dashboard.php" class="navbar-brand">MiniLibrary Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="manajemen.php" class="nav-link">Manajemen Buku</a></li>
            <li class="nav-item"><a href="anggota.php" class="nav-link">Anggota</a></li>
            <li class="nav-item"><a href="kategori.php" class="nav-link active">Kategori</a></li>
            <li class="nav-item"><a href="peminjaman.php" class="nav-link">Peminjaman</a></li>
            <li class="nav-item"><a href="profil_admin.php" class="nav-link">Profil</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['message_type'] == 'success') ? 'success' : 'error'; ?>">
            <?php 
            echo ($_SESSION['message_type'] == 'success' ? '✅' : '❌') . " " . $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Tambah Kategori Baru</h1>
            <p>Buat kategori baru untuk mengorganisir koleksi buku perpustakaan.</p>
            <a href="kategori.php" class="btn">← Kembali ke Kategori</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Tambah Kategori</h2>
        </div>

        <form method="POST" style="max-width: 500px; margin: 0 auto;">
            <div class="form-group">
                <label>📂 Nama Kategori *</label>
                <input type="text" name="nama_kategori" class="form-control"
                       value="<?php echo isset($_POST['nama_kategori']) ? htmlspecialchars($_POST['nama_kategori']) : ''; ?>" 
                       placeholder="Masukkan nama kategori" required>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Simpan Kategori</button>
                <a href="kategori.php" class="btn" style="background: #6c757d;">↩️ Kembali</a>
            </div>
        </form>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
</div>

</body>
</html>
