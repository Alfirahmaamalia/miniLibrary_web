<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi/koneksi.php';

if ($_POST) {
    $id_anggota = $_POST['id_anggota'];
    $id_buku = $_POST['id_buku'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    
    if (empty($id_anggota) || empty($id_buku) || empty($tanggal_pinjam) || empty($tanggal_kembali)) {
        $_SESSION['message'] = "Semua field harus diisi!";
        $_SESSION['message_type'] = "error";
    } else {
        // Cek apakah buku masih tersedia
        $cek_buku = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id = $id_buku");
        $buku = mysqli_fetch_assoc($cek_buku);
        
        if ($buku['stok'] <= 0) {
            $_SESSION['message'] = "Buku tidak tersedia untuk dipinjam!";
            $_SESSION['message_type'] = "error";
        } else {
            // Insert peminjaman
            $query = "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali, status) 
                     VALUES ($id_anggota, $id_buku, '$tanggal_pinjam', '$tanggal_kembali', 'dipinjam')";
            
            if (mysqli_query($koneksi, $query)) {
                // Update stok buku
                mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id = $id_buku");
                
                $_SESSION['message'] = "Peminjaman berhasil ditambahkan!";
                $_SESSION['message_type'] = "success";
                header("Location: peminjaman.php");
                exit;
            } else {
                $_SESSION['message'] = "Gagal menambahkan peminjaman: " . mysqli_error($koneksi);
                $_SESSION['message_type'] = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Peminjaman - MiniLibrary</title>
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
            <li class="nav-item"><a href="kategori.php" class="nav-link">Kategori</a></li>
            <li class="nav-item"><a href="peminjaman.php" class="nav-link active">Peminjaman</a></li>
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
            <h1>Tambah Peminjaman Baru</h1>
            <p>Catat peminjaman buku baru untuk anggota perpustakaan.</p>
            <a href="peminjaman.php" class="btn">← Kembali ke Peminjaman</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Tambah Peminjaman</h2>
        </div>

        <form method="POST" style="max-width: 600px; margin: 0 auto;">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>👤 Anggota *</label>
                    <select name="id_anggota" class="form-control" required>
                        <option value="">Pilih Anggota</option>
                        <?php
                        $anggota_query = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY nama ASC");
                        while ($anggota = mysqli_fetch_assoc($anggota_query)) {
                            echo "<option value='{$anggota['id']}'>{$anggota['nama']} - {$anggota['email']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>📚 Buku *</label>
                    <select name="id_buku" class="form-control" required>
                        <option value="">Pilih Buku</option>
                        <?php
                        $buku_query = mysqli_query($koneksi, "SELECT * FROM buku WHERE stok > 0 ORDER BY `judul buku` ASC");
                        while ($buku = mysqli_fetch_assoc($buku_query)) {
                            echo "<option value='{$buku['id']}'>{$buku['judul buku']} - Stok: {$buku['stok']}</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>📅 Tanggal Pinjam *</label>
                    <input type="date" name="tanggal_pinjam" class="form-control"
                           value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="form-group">
                    <label>📅 Tanggal Kembali *</label>
                    <input type="date" name="tanggal_kembali" class="form-control"
                           value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Simpan Peminjaman</button>
                <a href="peminjaman.php" class="btn" style="background: #6c757d;">↩️ Kembali</a>
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
