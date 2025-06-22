<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Ambil data dari form - sesuaikan dengan nama kolom di database
    $judul_buku = mysqli_real_escape_string($koneksi, trim($_POST['judul_buku']));
    $penulis = mysqli_real_escape_string($koneksi, trim($_POST['penulis']));
    $kategori = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $tahun_terbit = mysqli_real_escape_string($koneksi, $_POST['tahun_terbit']);
    $stok = mysqli_real_escape_string($koneksi, $_POST['stok']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    
    // Validasi input
    $errors = array();
    if (empty($judul_buku)) {
        $errors[] = "Judul buku harus diisi.";
    }
    if (empty($penulis)) {
        $errors[] = "Penulis harus diisi.";
    }
    if (empty($tahun_terbit)) {
        $errors[] = "Tahun terbit harus diisi.";
    }
    if (empty($stok)) {
        $errors[] = "Stok harus diisi.";
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        // Query sesuai dengan nama kolom yang ada di database (judul buku dengan spasi)
        $query = "INSERT INTO buku (`judul buku`, penulis, kategori, tahun_terbit, stok, status) 
                  VALUES ('$judul_buku', '$penulis', '$kategori', '$tahun_terbit', '$stok', '$status')";
        
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['message'] = "Buku '$judul_buku' berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            header("Location: manajemen.php");
            exit;
        } else {
            $message = "Error: " . mysqli_error($koneksi);
            $message_type = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="dashboard.php" class="navbar-brand">MiniLibrary Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="manajemen.php" class="nav-link active">Manajemen Buku</a></li>
            <li class="nav-item"><a href="anggota.php" class="nav-link">Anggota</a></li>
            <li class="nav-item"><a href="kategori.php" class="nav-link">Kategori</a></li>
            <li class="nav-item"><a href="peminjaman.php" class="nav-link">Peminjaman</a></li>
            <li class="nav-item"><a href="profil_admin.php" class="nav-link">Profil</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Tambah Buku Baru</h1>
            <p>Tambahkan buku baru ke koleksi perpustakaan digital Anda.</p>
            <a href="manajemen.php" class="btn">← Kembali ke Manajemen</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Tambah Buku</h2>
        </div>

        <?php if (isset($message) && isset($message_type)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo ($message_type == 'success' ? '✅' : '❌') . " " . $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>📖 Judul Buku *</label>
                    <input type="text" name="judul_buku" class="form-control" value="<?php echo isset($_POST['judul_buku']) ? htmlspecialchars($_POST['judul_buku']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>✍️ Penulis *</label>
                    <input type="text" name="penulis" class="form-control" value="<?php echo isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>📂 Kategori ID</label>
                    <input type="number" name="kategori" class="form-control" min="0" value="<?php echo isset($_POST['kategori']) ? htmlspecialchars($_POST['kategori']) : '0'; ?>">
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Masukkan ID kategori (0 jika belum ada kategori)</small>
                </div>

                <div class="form-group">
                    <label>📅 Tahun Terbit *</label>
                    <input type="text" name="tahun_terbit" class="form-control" value="<?php echo isset($_POST['tahun_terbit']) ? htmlspecialchars($_POST['tahun_terbit']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>📦 Stok *</label>
                    <input type="text" name="stok" class="form-control" value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label>📊 Status</label>
                    <select name="status" class="form-control">
                        <option value="0" <?php echo (isset($_POST['status']) && $_POST['status'] == '0') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        <option value="1" <?php echo (isset($_POST['status']) && $_POST['status'] == '1') ? 'selected' : ''; ?>>Tersedia</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Tambah Buku</button>
                <button type="reset" class="btn" style="background: #6c757d;">🔄 Reset Form</button>
                <a href="manajemen.php" class="btn" style="background: #dc3545;">❌ Batal</a>
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
