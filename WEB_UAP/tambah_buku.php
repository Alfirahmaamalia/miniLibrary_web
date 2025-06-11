<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

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

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="dashboard.php">📊 Beranda</a>
    <a class="active" href="manajemen.php">📚 Manajemen Buku</a>
    <a href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Tambah Buku</h1>
        <div class="profile">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="form-container">
        <div class="form-card">
            <div class="form-header">
                <h2>Form Tambah Buku</h2>
                <a href="manajemen.php" class="btn-back">← Kembali</a>
            </div>

            <?php if (isset($message) && isset($message_type)): ?>
                <div class="<?php echo htmlspecialchars($message_type); ?>"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="judul_buku">Judul Buku *</label>
                    <input type="text" id="judul_buku" name="judul_buku" value="<?php echo isset($_POST['judul_buku']) ? htmlspecialchars($_POST['judul_buku']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="penulis">Penulis *</label>
                    <input type="text" id="penulis" name="penulis" value="<?php echo isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="kategori">Kategori ID</label>
                    <input type="number" id="kategori" name="kategori" min="0" value="<?php echo isset($_POST['kategori']) ? htmlspecialchars($_POST['kategori']) : '0'; ?>">
                    <small>Masukkan ID kategori (0 jika belum ada kategori)</small>
                </div>

                <div class="form-group">
                    <label for="tahun_terbit">Tahun Terbit *</label>
                    <input type="text" id="tahun_terbit" name="tahun_terbit" value="<?php echo isset($_POST['tahun_terbit']) ? htmlspecialchars($_POST['tahun_terbit']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok *</label>
                    <input type="text" id="stok" name="stok" value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>" required>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="0" <?php echo (isset($_POST['status']) && $_POST['status'] == '0') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                        <option value="1" <?php echo (isset($_POST['status']) && $_POST['status'] == '1') ? 'selected' : ''; ?>>Tersedia</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">Tambah Buku</button>
                    <button type="reset" class="btn-secondary">Reset Form</button>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>
