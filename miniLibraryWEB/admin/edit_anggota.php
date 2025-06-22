<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

$success = "";
$error = "";

// Cek apakah ada parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: anggota.php");
    exit;
}

$id = (int)$_GET['id'];

// Ambil data anggota
$query = "SELECT * FROM anggota WHERE id = $id";
$result = mysqli_query($koneksi, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    header("Location: anggota.php");
    exit;
}

$anggota = mysqli_fetch_assoc($result);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitasi input
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $telepon = mysqli_real_escape_string($koneksi, trim($_POST['telepon']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);

    // Validasi input
    if (empty($nama) || empty($alamat) || empty($telepon) || empty($email)) {
        $error = "Semua field harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
    } elseif (!preg_match('/^[0-9+\-\s]+$/', $telepon)) {
        $error = "Format telepon tidak valid!";
    } else {
        // Cek apakah email sudah terdaftar (kecuali email anggota ini sendiri)
        $check_email = mysqli_query($koneksi, "SELECT email FROM anggota WHERE email = '$email' AND id != $id");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Update data anggota
            $query = "UPDATE anggota SET nama = '$nama', alamat = '$alamat', telepon = '$telepon', email = '$email', status = '$status' WHERE id = $id";
            $result = mysqli_query($koneksi, $query);
            
            if ($result) {
                $success = "Data anggota berhasil diperbarui!";
                // Refresh data anggota
                $query = "SELECT * FROM anggota WHERE id = $id";
                $result = mysqli_query($koneksi, $query);
                $anggota = mysqli_fetch_assoc($result);
            } else {
                $error = "Gagal memperbarui data anggota: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Anggota - MiniLibrary</title>
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
            <li class="nav-item"><a href="anggota.php" class="nav-link active">Anggota</a></li>
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
            <h1>Edit Anggota</h1>
            <p>Perbarui informasi anggota perpustakaan.</p>
            <a href="anggota.php" class="btn">← Kembali ke Daftar</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Edit Anggota</h2>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success">✅ <?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="alert alert-error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>👤 Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?php echo htmlspecialchars($anggota['nama']); ?>" required>
                </div>

                <div class="form-group">
                    <label>📧 Email</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($anggota['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label>📞 Nomor Telepon</label>
                    <input type="text" name="telepon" class="form-control" value="<?php echo htmlspecialchars($anggota['telepon']); ?>" required>
                </div>

                <div class="form-group">
                    <label>📊 Status</label>
                    <select name="status" class="form-control" required>
                        <option value="Aktif" <?php echo ($anggota['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Non Aktif" <?php echo ($anggota['status'] == 'Non Aktif') ? 'selected' : ''; ?>>Non Aktif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>🏠 Alamat</label>
                <textarea name="alamat" class="form-control" rows="3" required><?php echo htmlspecialchars($anggota['alamat']); ?></textarea>
            </div>

            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Simpan Perubahan</button>
                <a href="anggota.php" class="btn" style="background: #6c757d;">❌ Batal</a>
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
