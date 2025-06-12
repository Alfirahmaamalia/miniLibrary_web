<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

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
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
         .form-header {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
           
        }
        
        .form-header h3 {
            font-size: 24px;
            color: #333;
            margin: 0;
        } .form-header {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
           
        }
        
        .form-header h3 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        
        .btn-back {
            background: #6c757d;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        
        .btn-back:hover {
            background: #545b62;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #007bff;
        }
        
        .btn-container {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        
        .btn-submit {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-submit:hover {
            background: #0056b3;
        }
        
        .btn-cancel {
            background: #f8f9fa;
            color: #333;
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-cancel:hover {
            background: #e9ecef;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
        }
        
        .info-box {
            background: #e7f3ff;
            border: 1px solid #b3d7ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .info-box h4 {
            margin: 0 0 10px 0;
            color: #0056b3;
        }
        
        .info-item {
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .info-label {
            font-weight: 500;
            color: #666;
        }
    </style>
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
        <h1>Edit Buku</h1>
        <div class="profile">
            <span>Selamat datang, <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="form-container">
        <div class="form-header">
            <h3>Form Edit Buku</h3>
            <a href="manajemen.php" class="btn-back">← Kembali</a>
        </div>

        <div class="info-box">
            <h4>Informasi Buku Saat Ini</h4>
            <div class="info-item">
                <span class="info-label">ID:</span> <?php echo str_pad($buku['id'], 3, '0', STR_PAD_LEFT); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Kategori:</span> <?php echo getKategoriName($buku['kategori']); ?>
            </div>
            <div class="info-item">
                <span class="info-label">Status:</span> <?php echo ($buku['status'] == 1) ? 'Tersedia' : 'Tidak Tersedia'; ?>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="judul_buku">Judul Buku *</label>
                <input type="text" id="judul_buku" name="judul_buku" class="form-control" 
                       value="<?php echo htmlspecialchars($buku['judul buku']); ?>" required>
            </div>

            <div class="form-group">
                <label for="penulis">Penulis *</label>
                <input type="text" id="penulis" name="penulis" class="form-control" 
                       value="<?php echo htmlspecialchars($buku['penulis']); ?>" required>
            </div>

            <div class="form-group">
                <label for="kategori">Kategori ID</label>
                <input type="number" id="kategori" name="kategori" class="form-control" 
                       min="0" value="<?php echo htmlspecialchars($buku['kategori']); ?>">
                <small>Kategori saat ini: <?php echo getKategoriName($buku['kategori']); ?></small>
            </div>

            <div class="form-group">
                <label for="tahun_terbit">Tahun Terbit *</label>
                <input type="text" id="tahun_terbit" name="tahun_terbit" class="form-control" 
                       value="<?php echo htmlspecialchars($buku['tahun_terbit']); ?>" required>
            </div>

            <div class="form-group">
                <label for="stok">Stok *</label>
                <input type="text" id="stok" name="stok" class="form-control" 
                       value="<?php echo htmlspecialchars($buku['stok']); ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control">
                    <option value="0" <?php echo ($buku['status'] == 0) ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    <option value="1" <?php echo ($buku['status'] == 1) ? 'selected' : ''; ?>>Tersedia</option>
                </select>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn-submit">Simpan Perubahan</button>
                <a href="manajemen.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
