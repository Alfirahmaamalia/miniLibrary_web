<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Anggota - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .detail-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .detail-header {
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-header h3 {
            font-size: 20px;
            margin: 0;
            color: #333;
        }
        
        .detail-actions {
            display: flex;
            gap: 10px;
        }
        
        .detail-item {
            margin-bottom: 20px;
            border-bottom: 1px solid #f1f1f1;
            padding-bottom: 15px;
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: #666;
            margin-bottom: 5px;
            font-size: 14px;
        }
        
        .detail-value {
            color: #333;
            font-size: 16px;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.aktif {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.non-aktif {
            background: #fff3cd;
            color: #856404;
        }
        
        .btn {
            padding: 8px 15px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        
        .btn-edit {
            background: #007bff;
            color: white;
            border: none;
        }
        
        .btn-edit:hover {
            background: #0056b3;
        }
        
        .btn-delete {
            background: white;
            color: #dc3545;
            border: 1px solid #dc3545;
        }
        
        .btn-delete:hover {
            background: #f8d7da;
        }
        
        .btn-back {
            background: #f8f9fa;
            color: #333;
            border: 1px solid #ddd;
            display: inline-block;
            margin-top: 20px;
        }
        
        .btn-back:hover {
            background: #e9ecef;
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
        <h1>Detail Anggota</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="detail-container">
        <div class="detail-header">
            <h3>Informasi Anggota</h3>
            <div class="detail-actions">
                <a href="edit_anggota.php?id=<?php echo $anggota['id']; ?>" class="btn btn-edit">Edit</a>
                <a href="hapus_anggota.php?id=<?php echo $anggota['id']; ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">Hapus</a>
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">ID Anggota</div>
            <div class="detail-value"><?php echo str_pad($anggota['id'], 3, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Nama Lengkap</div>
            <div class="detail-value"><?php echo htmlspecialchars($anggota['nama']); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Alamat</div>
            <div class="detail-value"><?php echo nl2br(htmlspecialchars($anggota['alamat'])); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Nomor Telepon</div>
            <div class="detail-value"><?php echo htmlspecialchars($anggota['telepon']); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Email</div>
            <div class="detail-value"><?php echo htmlspecialchars($anggota['email']); ?></div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Status</div>
            <div class="detail-value">
                <span class="status-badge <?php echo strtolower(str_replace(' ', '-', $anggota['status'])); ?>">
                    <?php echo htmlspecialchars($anggota['status']); ?>
                </span>
            </div>
        </div>

        <div class="detail-item">
            <div class="detail-label">Tanggal Daftar</div>
            <div class="detail-value"><?php echo date('d F Y H:i', strtotime($anggota['created_at'])); ?></div>
        </div>

        <a href="anggota.php" class="btn btn-back">← Kembali ke Daftar Anggota</a>
    </div>
</div>

</body>
</html>
