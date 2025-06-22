<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

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

// Fungsi untuk mengkonversi status ID ke nama
function getStatusName($status_id) {
    return ($status_id == 1) ? 'Tersedia' : 'Tidak Tersedia';
}

// Tentukan class stok berdasarkan jumlah
$stok_value = intval($buku['stok']);
if ($stok_value > 5) {
    $stock_class = 'stock-good';
    $stock_text = 'Stok Baik';
} else if ($stok_value > 0) {
    $stock_class = 'stock-medium';
    $stock_text = 'Stok Terbatas';
} else {
    $stock_class = 'stock-low';
    $stock_text = 'Stok Habis';
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Detail Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .detail-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detail-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
        }
        
        .detail-section h4 {
            margin: 0 0 15px 0;
            color: #495057;
            font-size: 16px;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 8px;
        }
        
        .detail-item {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .detail-item:last-child {
            margin-bottom: 0;
        }
        
        .detail-label {
            font-weight: 500;
            color: #666;
            font-size: 14px;
        }
        
        .detail-value {
            color: #333;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-badge.tersedia {
            background: #d4edda;
            color: #155724;
        }
        
        .status-badge.tidak-tersedia {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stock-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .stock-good {
            background: #d4edda;
            color: #155724;
        }
        
        .stock-medium {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-low {
            background: #f8d7da;
            color: #721c24;
        }
        
        .book-cover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 200px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            margin-bottom: 20px;
        }
        
        .book-info {
            text-align: center;
        }
        
        .book-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 8px;
        }
        
        .book-author {
            font-size: 16px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .book-id {
            background: #e9ecef;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            color: #495057;
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .detail-content {
                grid-template-columns: 1fr;
                gap: 20px;
            }
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
            <li class="nav-item"><a href="manajemen.php" class="nav-link active">Manajemen Buku</a></li>
            <li class="nav-item"><a href="anggota.php" class="nav-link">Anggota</a></li>
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
            <h1>Detail Buku</h1>
            <p>Informasi lengkap tentang buku di perpustakaan.</p>
            <a href="manajemen.php" class="btn">← Kembali ke Manajemen</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Detail Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📚 Informasi Lengkap Buku</h2>
            <div style="display: flex; gap: 10px;">
                <a href="edit_buku.php?id=<?php echo $buku['id']; ?>" class="btn" style="background: #007bff;">✏️ Edit</a>
                <a href="hapus_buku.php?id=<?php echo $buku['id']; ?>" class="btn" style="background: #dc3545;" 
                   onclick="return confirm('Apakah Anda yakin ingin menghapus buku ini?')">🗑️ Hapus</a>
            </div>
        </div>

        <div class="detail-content">
            <!-- Kolom Kiri - Cover dan Info Dasar -->
            <div class="detail-section">
                <div class="book-cover">
                    📚
                </div>
                <div class="book-info">
                    <div class="book-title"><?php echo htmlspecialchars($buku['judul buku']); ?></div>
                    <div class="book-author">oleh <?php echo htmlspecialchars($buku['penulis']); ?></div>
                    <div class="book-id">ID: <?php echo str_pad($buku['id'], 3, '0', STR_PAD_LEFT); ?></div>
                </div>
            </div>

            <!-- Kolom Kanan - Detail Informasi -->
            <div class="detail-section">
                <h4>📋 Detail Buku</h4>
                
                <div class="detail-item">
                    <span class="detail-label">Kategori</span>
                    <span class="detail-value"><?php echo getKategoriName($buku['kategori']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Tahun Terbit</span>
                    <span class="detail-value"><?php echo htmlspecialchars($buku['tahun_terbit']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Stok</span>
                    <span class="detail-value">
                        <?php echo htmlspecialchars($buku['stok']); ?> unit
                        <span class="stock-badge <?php echo $stock_class; ?>"><?php echo $stock_text; ?></span>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="detail-value">
                        <span class="status-badge <?php echo ($buku['status'] == 1) ? 'tersedia' : 'tidak-tersedia'; ?>">
                            <?php echo getStatusName($buku['status']); ?>
                        </span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistik Tambahan -->
        <div class="detail-section">
            <h4>📊 Statistik</h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                <div class="detail-item">
                    <span class="detail-label">Total Peminjaman</span>
                    <span class="detail-value">0 kali</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Sedang Dipinjam</span>
                    <span class="detail-value">0 eksemplar</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Rating</span>
                    <span class="detail-value">⭐⭐⭐⭐⭐ (0 review)</span>
                </div>
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
