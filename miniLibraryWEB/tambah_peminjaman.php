<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

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
    <style>
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
        }
        
        .form-header {
            margin-bottom: 30px;
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
           
        }
        
        .form-header h2 {
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
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-primary:hover {
            background: #0056b3;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
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
    <a href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a class="active" href="peminjaman.php">🔒 Peminjaman</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Tambah Peminjaman</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="<?php echo ($_SESSION['message_type'] == 'success') ? 'success-message' : 'error-message'; ?>">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-header">
            <h2>Tambah Peminjaman Baru</h2>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="id_anggota">Anggota *</label>
                <select id="id_anggota" name="id_anggota" required>
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
                <label for="id_buku">Buku *</label>
                <select id="id_buku" name="id_buku" required>
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
                <label for="tanggal_pinjam">Tanggal Pinjam *</label>
                <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" 
                       value="<?php echo date('Y-m-d'); ?>" required>
            </div>

            <div class="form-group">
                <label for="tanggal_kembali">Tanggal Kembali *</label>
                <input type="date" id="tanggal_kembali" name="tanggal_kembali" 
                       value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Simpan Peminjaman</button>
                <a href="peminjaman.php" class="btn btn-secondary">↩️ Kembali</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
