<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

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
        }
        
        .form-header h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        
        .category-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .category-info strong {
            color: #495057;
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
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus {
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
    <a class="active" href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Edit Kategori</h1>
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
            <h2>Edit Kategori</h2>
        </div>
        
        <div class="category-info">
            <strong>ID Kategori:</strong> <?php echo str_pad($kategori['id'], 3, '0', STR_PAD_LEFT); ?>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="nama_kategori">Nama Kategori *</label>
                <input type="text" id="nama_kategori" name="nama_kategori" 
                       value="<?php echo isset($_POST['nama_kategori']) ? htmlspecialchars($_POST['nama_kategori']) : htmlspecialchars($kategori['nama_kategori']); ?>" 
                       placeholder="Masukkan nama kategori" required>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Perbarui Kategori</button>
                <a href="kategori.php" class="btn btn-secondary">↩️ Kembali</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
