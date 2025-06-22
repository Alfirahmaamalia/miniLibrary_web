<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

$success = "";
$error = "";

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
        // Cek apakah email sudah terdaftar
        $check_email = mysqli_query($koneksi, "SELECT email FROM anggota WHERE email = '$email'");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Insert data anggota
            $query = "INSERT INTO anggota (nama, alamat, telepon, email, status) VALUES ('$nama', '$alamat', '$telepon', '$email', '$status')";
            $result = mysqli_query($koneksi, $query);
            
            if ($result) {
                $success = "Anggota berhasil ditambahkan!";
                // Reset form
                $_POST = array();
            } else {
                $error = "Gagal menambahkan anggota: " . mysqli_error($koneksi);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Anggota - MiniLibrary</title>
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
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
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
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #c3e6cb;
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
    </style>
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="dashboard.php">📊 Beranda</a>
    <a href="manajemen.php">📚 Manajemen Buku</a>
    <a class="active" href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Tambah Anggota</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="form-container">
        <div class="form-header">
            <h3>Form Tambah Anggota</h3>
        </div>

        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input type="text" id="nama" name="nama" class="form-control" value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="alamat">Alamat</label>
                <textarea id="alamat" name="alamat" class="form-control" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
            </div>

            <div class="form-group">
                <label for="telepon">Nomor Telepon</label>
                <input type="text" id="telepon" name="telepon" class="form-control" value="<?php echo isset($_POST['telepon']) ? htmlspecialchars($_POST['telepon']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" class="form-control" required>
                    <option value="Aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                    <option value="Non Aktif" <?php echo (isset($_POST['status']) && $_POST['status'] == 'Non Aktif') ? 'selected' : ''; ?>>Non Aktif</option>
                </select>
            </div>

            <div class="btn-container">
                <button type="submit" class="btn-submit">Simpan</button>
                <a href="anggota.php" class="btn-cancel">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
