<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Get loan ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: peminjaman.php");
    exit;
}

$id_peminjaman = (int)$_GET['id'];

// Fetch loan data
$query = mysqli_query($koneksi, "SELECT p.*, a.nama as nama_peminjam, b.`judul buku` as judul_buku 
                                FROM peminjaman p 
                                LEFT JOIN anggota a ON p.id_anggota = a.id 
                                LEFT JOIN buku b ON p.id_buku = b.id 
                                WHERE p.id = $id_peminjaman");

if (!$query || mysqli_num_rows($query) == 0) {
    $_SESSION['message'] = "Data peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: peminjaman.php");
    exit;
}

$peminjaman = mysqli_fetch_assoc($query);

// Handle return submission
if ($_POST && isset($_POST['confirm_return'])) {
    $tanggal_dikembalikan = date('Y-m-d');
    $denda = 0;
    
    // Hitung denda jika terlambat
    if ($tanggal_dikembalikan > $peminjaman['tanggal_kembali']) {
        $tgl_kembali = new DateTime($peminjaman['tanggal_kembali']);
        $tgl_dikembalikan = new DateTime($tanggal_dikembalikan);
        $selisih = $tgl_kembali->diff($tgl_dikembalikan);
        $hari_terlambat = $selisih->days;
        $denda = $hari_terlambat * 1000; // Rp 1.000 per hari
    }
    
    // Update status peminjaman
    $update_query = mysqli_query($koneksi, "UPDATE peminjaman SET 
                                           status = 'dikembalikan', 
                                           tanggal_dikembalikan = '$tanggal_dikembalikan',
                                           denda = $denda 
                                           WHERE id = $id_peminjaman");
    
    if ($update_query) {
        // Update stok buku
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id = {$peminjaman['id_buku']}");
        
        $_SESSION['message'] = "Buku berhasil dikembalikan!" . ($denda > 0 ? " Denda: Rp " . number_format($denda, 0, ',', '.') : "");
        $_SESSION['message_type'] = "success";
        header("Location: peminjaman.php");
        exit;
    } else {
        $_SESSION['message'] = "Gagal mengembalikan buku: " . mysqli_error($koneksi);
        $_SESSION['message_type'] = "error";
    }
}

// Hitung denda jika ada
$denda_preview = 0;
$today = date('Y-m-d');
if ($today > $peminjaman['tanggal_kembali']) {
    $tgl_kembali = new DateTime($peminjaman['tanggal_kembali']);
    $tgl_today = new DateTime($today);
    $selisih = $tgl_kembali->diff($tgl_today);
    $hari_terlambat = $selisih->days;
    $denda_preview = $hari_terlambat * 1000;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Kembalikan Buku - MiniLibrary</title>
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
        
        .form-header h3 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        
        .return-icon {
            font-size: 48px;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .loan-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #28a745;
        }
        
        .loan-info h3 {
            margin-top: 0;
            color: #333;
        }
        
        .loan-info p {
            margin: 8px 0;
            color: #495057;
        }
        
        .denda-warning {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
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
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
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
        <h1>Kembalikan Buku</h1>
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
        <div class="return-icon">📚</div>
        <div class="form-header">
            <h2>Konfirmasi Pengembalian Buku</h2>
        </div>

        <?php if ($denda_preview > 0): ?>
            <div class="denda-warning">
                <strong>Peringatan:</strong> Buku ini terlambat dikembalikan. 
                Denda yang harus dibayar: <strong>Rp <?php echo number_format($denda_preview, 0, ',', '.'); ?></strong>
            </div>
        <?php endif; ?>
        
        <div class="loan-info">
            <h3>Detail Peminjaman:</h3>
            <p><strong>ID Peminjaman:</strong> <?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Nama Peminjam:</strong> <?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?></p>
            <p><strong>Judul Buku:</strong> <?php echo htmlspecialchars($peminjaman['judul_buku']); ?></p>
            <p><strong>Tanggal Pinjam:</strong> <?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?></p>
            <p><strong>Tanggal Kembali:</strong> <?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></p>
            <p><strong>Tanggal Dikembalikan:</strong> <?php echo date('d/m/Y'); ?></p>
            <?php if ($denda_preview > 0): ?>
                <p><strong>Denda:</strong> <span style="color: #dc3545;">Rp <?php echo number_format($denda_preview, 0, ',', '.'); ?></span></p>
            <?php endif; ?>
        </div>

        <div class="confirmation-text">
            <p><strong>Apakah Anda yakin ingin mengembalikan buku ini?</strong></p>
        </div>

        <form method="POST">
            <div class="form-actions">
                <button type="submit" name="confirm_return" class="btn btn-success">📚 Ya, Kembalikan Buku</button>
                <a href="peminjaman.php" class="btn btn-secondary">↩️ Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
