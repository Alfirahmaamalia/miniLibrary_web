<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

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
            <h1>Detail Anggota</h1>
            <p>Informasi lengkap tentang anggota perpustakaan.</p>
            <a href="anggota.php" class="btn">← Kembali ke Daftar</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Detail Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>👤 Informasi Anggota</h2>
            <div class="detail-actions">
                <a href="edit_anggota.php?id=<?php echo $anggota['id']; ?>" class="btn btn-edit">✏️ Edit</a>
                <a href="hapus_anggota.php?id=<?php echo $anggota['id']; ?>" class="btn btn-delete" onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">🗑️ Hapus</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
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
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
</div>

</body>
</html>
