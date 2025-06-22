<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

$error = "";
$success = "";

// Cek apakah ada parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manajemen.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data buku
$query = "SELECT * FROM buku WHERE id = $id";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = "Buku tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: manajemen.php");
    exit;
}

$buku = mysqli_fetch_assoc($result);

// Fungsi untuk mengkonversi kategori ID ke nama
function getKategoriName($kategori_id) {
    $kategori_names = [
        1 => 'Fiksi',
        2 => 'Non-Fiksi', 
        3 => 'Sejarah',
        4 => 'Ilmiah',
        5 => 'Teknologi',
        6 => 'Biografi',
        7 => 'Pendidikan'
    ];
    return isset($kategori_names[$kategori_id]) ? $kategori_names[$kategori_id] : 'Tidak Diketahui';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitasi input
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
    
    // Jika tidak ada error, update ke database
    if (empty($errors)) {
        // Query update sesuai dengan nama kolom yang ada di database
        $query = "UPDATE buku SET 
                  `judul buku` = '$judul_buku', 
                  penulis = '$penulis', 
                  kategori = '$kategori', 
                  tahun_terbit = '$tahun_terbit', 
                  stok = '$stok', 
                  status = '$status' 
                  WHERE id = $id";
        
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['message'] = "Buku '$judul_buku' berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            header("Location: manajemen.php");
            exit;
        } else {
            $error = "Error: " . mysqli_error($koneksi);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Buku - MiniLibrary</title>
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
            <h1>Edit Buku</h1>
            <p>Perbarui informasi buku di perpustakaan.</p>
            <a href="manajemen.php" class="btn">← Kembali ke Manajemen</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Info Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>ℹ️ Informasi Buku Saat Ini</h2>
        </div>
        <div style="background: #e7f3ff; padding: 15px; border-radius: 8px; border-left: 4px solid #007bff;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                <div><strong>ID:</strong> <?php echo str_pad($buku['id'], 3, '0', STR_PAD_LEFT); ?></div>
                <div><strong>Kategori:</strong> <?php echo getKategoriName($buku['kategori']); ?></div>
                <div><strong>Status:</strong> <?php echo ($buku['status'] == 1) ? 'Tersedia' : 'Tidak Tersedia'; ?></div>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Edit Buku</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-error">❌ <?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>📖 Judul Buku *</label>
                    <input type="text" name="judul_buku" class="form-control" 
                           value="<?php echo htmlspecialchars($buku['judul buku']); ?>" required>
                </div>

                <div class="form-group">
                    <label>✍️ Penulis *</label>
                    <input type="text" name="penulis" class="form-control" 
                           value="<?php echo htmlspecialchars($buku['penulis']); ?>" required>
                </div>

                <div class="form-group">
                    <label>📂 Kategori ID</label>
                    <input type="number" name="kategori" class="form-control" 
                           min="0" value="<?php echo htmlspecialchars($buku['kategori']); ?>">
                    <small style="color: #666; font-size: 12px; margin-top: 5px; display: block;">Kategori saat ini: <?php echo getKategoriName($buku['kategori']); ?></small>
                </div>

                <div class="form-group">
                    <label>📅 Tahun Terbit *</label>
                    <input type="text" name="tahun_terbit" class="form-control" 
                           value="<?php echo htmlspecialchars($buku['tahun_terbit']); ?>" required>
                </div>

                <div class="form-group">
                    <label>📦 Stok *</label>
                    <input type="text" name="stok" class="form-control" 
                           value="<?php echo htmlspecialchars($buku['stok']); ?>" required>
                </div>

                <div class="form-group">
                    <label>📊 Status</label>
                    <select name="status" class="form-control">
                        <option value="0" <?php echo ($buku['status'] == 0) ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        <option value="1" <?php echo ($buku['status'] == 1) ? 'selected' : ''; ?>>Tersedia</option>
                    </select>
                </div>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Simpan Perubahan</button>
                <a href="manajemen.php" class="btn" style="background: #6c757d;">❌ Batal</a>
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
