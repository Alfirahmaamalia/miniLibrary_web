<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: kategori.php");
    exit;
}

$id_kategori = (int)$_GET['id'];

// Check if category exists
$check_query = mysqli_query($koneksi, "SELECT * FROM kategori WHERE id = $id_kategori");
if (!$check_query || mysqli_num_rows($check_query) == 0) {
    $_SESSION['message'] = "Kategori tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: kategori.php");
    exit;
}

$kategori = mysqli_fetch_assoc($check_query);

// Check if category is being used by any books
$book_check = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku WHERE kategori = $id_kategori");
$book_count = mysqli_fetch_assoc($book_check)['total'];

// Handle deletion
if ($_POST && isset($_POST['confirm_delete'])) {
    if ($book_count > 0) {
        $_SESSION['message'] = "Kategori tidak dapat dihapus karena masih digunakan oleh $book_count buku!";
        $_SESSION['message_type'] = "error";
    } else {
        $delete_query = mysqli_query($koneksi, "DELETE FROM kategori WHERE id = $id_kategori");
        
        if ($delete_query) {
            $_SESSION['message'] = "Kategori '{$kategori['nama_kategori']}' berhasil dihapus!";
            $_SESSION['message_type'] = "success";
            header("Location: kategori.php");
            exit;
        } else {
            $_SESSION['message'] = "Gagal menghapus kategori: " . mysqli_error($koneksi);
            $_SESSION['message_type'] = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hapus Kategori - MiniLibrary</title>
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
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 24px;
            color: #dc3545;
            margin: 0;
        }
        
        .warning-icon {
            font-size: 48px;
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .category-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #dc3545;
        }
        
        .category-info h3 {
            margin-top: 0;
            color: #333;
        }
        
        .category-info p {
            margin: 8px 0;
            color: #495057;
        }
        
        .confirmation-text {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            color: #333;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
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
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
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
        
        .alert-warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #ffeaa7;
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
        <h1>Hapus Kategori</h1>
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
        <div class="warning-icon">⚠️</div>
        <div class="form-header">
            <h2>Konfirmasi Penghapusan</h2>
        </div>

        <?php if ($book_count > 0): ?>
            <div class="alert-warning">
                <strong>Peringatan:</strong> Kategori ini masih digunakan oleh <?php echo $book_count; ?> buku. 
                Anda harus memindahkan atau menghapus buku-buku tersebut terlebih dahulu sebelum menghapus kategori ini.
            </div>
        <?php endif; ?>
        
        <div class="category-info">
            <h3>Detail Kategori yang akan dihapus:</h3>
            <p><strong>ID:</strong> <?php echo str_pad($kategori['id'], 3, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Nama Kategori:</strong> <?php echo htmlspecialchars($kategori['nama_kategori']); ?></p>
            <p><strong>Jumlah Buku:</strong> <?php echo $book_count; ?> buku</p>
        </div>

        <?php if ($book_count == 0): ?>
            <div class="confirmation-text">
                <p><strong>Apakah Anda yakin ingin menghapus kategori ini?</strong></p>
                <p>Tindakan ini tidak dapat dibatalkan!</p>
            </div>

            <form method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirm_delete" class="btn btn-danger">🗑️ Ya, Hapus Kategori</button>
                    <a href="kategori.php" class="btn btn-secondary">↩️ Batal</a>
                </div>
            </form>
        <?php else: ?>
            <div class="form-actions">
                <a href="kategori.php" class="btn btn-secondary">↩️ Kembali ke Daftar Kategori</a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
