<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi/koneksi.php';

// Get category ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: kategori.php");
    exit;
}

$id_kategori = (int)$_GET['id'];

// Fetch existing category data
$query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id = $id_kategori");
if (!$query || mysqli_num_rows($query) == 0) {
    $_SESSION['message'] = "Kategori tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: kategori.php");
    exit;
}
$kategori = mysqli_fetch_assoc($query);

// Handle form submission
if ($_POST) {
    $nama_kategori = trim($_POST['nama_kategori']);
    
    if (empty($nama_kategori)) {
        $_SESSION['message'] = "Nama kategori tidak boleh kosong!";
        $_SESSION['message_type'] = "error";
    } else {
        // Check if category name already exists (excluding current category)
        $check_query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE nama_kategori = '" . mysqli_real_escape_string($koneksi, $nama_kategori) . "' AND id != $id_kategori");
        
        if (mysqli_num_rows($check_query) > 0) {
            $_SESSION['message'] = "Kategori dengan nama tersebut sudah ada!";
            $_SESSION['message_type'] = "error";
        } else {
            $update_query = mysqli_query($koneksi, "UPDATE kategori SET nama_kategori = '" . mysqli_real_escape_string($koneksi, $nama_kategori) . "' WHERE id = $id_kategori");
            
            if ($update_query) {
                $_SESSION['message'] = "Kategori berhasil diperbarui!";
                $_SESSION['message_type'] = "success";
                header("Location: kategori.php");
                exit;
            } else {
                $_SESSION['message'] = "Gagal memperbarui kategori: " . mysqli_error($koneksi);
                $_SESSION['message_type'] = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Kategori - MiniLibrary</title>
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
            <h1>Edit Kategori</h1>
            <p>Perbarui informasi kategori buku.</p>
            <a href="kategori.php" class="btn">← Kembali ke Kategori</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Info Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>ℹ️ Informasi Kategori</h2>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
            <strong>ID Kategori:</strong> <?php echo str_pad($kategori['id'], 3, '0', STR_PAD_LEFT); ?>
        </div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Edit Kategori</h2>
        </div>

        <form method="POST" style="max-width: 500px; margin: 0 auto;">
            <div class="form-group">
                <label>📂 Nama Kategori *</label>
                <input type="text" name="nama_kategori" class="form-control"
                       value="<?php echo isset($_POST['nama_kategori']) ? htmlspecialchars($_POST['nama_kategori']) : htmlspecialchars($kategori['nama_kategori']); ?>" 
                       placeholder="Masukkan nama kategori" required>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Perbarui Kategori</button>
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
