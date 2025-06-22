<?php
session_start();
include '../koneksi/koneksi.php';

// Cek apakah user sudah login sebagai anggota
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit;
}

// Get book ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: katalog.php");
    exit;
}

$id_buku = (int)$_GET['id'];
$anggota_id = $_SESSION['anggota_id'];

// Fetch book data
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori 
                                FROM buku b 
                                LEFT JOIN kategori k ON b.kategori = k.id 
                                WHERE b.id = $id_buku");

if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: katalog.php");
    exit;
}

$buku = mysqli_fetch_assoc($query);

// Cek apakah anggota sudah meminjam buku ini dan belum dikembalikan
$check_loan = mysqli_query($koneksi, "SELECT * FROM peminjaman 
                                     WHERE id_anggota = $anggota_id 
                                     AND id_buku = $id_buku 
                                     AND status = 'dipinjam'");
$already_borrowed = mysqli_num_rows($check_loan) > 0;

// Hitung statistik buku
$total_peminjaman = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_buku = $id_buku"))['total'];
$peminjaman_aktif = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_buku = $id_buku AND status = 'dipinjam'"))['total'];

// Ambil buku serupa (kategori sama)
$similar_books = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori 
                                        FROM buku b 
                                        LEFT JOIN kategori k ON b.kategori = k.id 
                                        WHERE b.kategori = {$buku['kategori']} 
                                        AND b.id != $id_buku 
                                        AND b.status = 1 
                                        LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($buku['judul buku']); ?> - MiniLibrary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fc;
            color: #333;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: #4e73df;
            padding: 15px 20px;
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
        }

        .nav-item {
            margin-left: 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 15px;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white;
        }

        .nav-link.active {
            color: white;
            font-weight: 600;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .breadcrumb {
            margin-bottom: 20px;
            font-size: 14px;
        }

        .breadcrumb a {
            color: #4e73df;
            text-decoration: none;
        }

        .breadcrumb a:hover {
            text-decoration: underline;
        }

        .book-detail {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 40px;
            margin-bottom: 40px;
        }

        .book-cover-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            text-align: center;
        }

        .book-cover-large {
            width: 100%;
            max-width: 300px;
            height: 400px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 72px;
            margin-bottom: 20px;
            position: relative;
        }

        .availability-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .available {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .unavailable {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .book-info-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .book-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: #333;
        }

        .book-author {
            font-size: 18px;
            color: #666;
            margin-bottom: 20px;
        }

        .book-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .meta-value {
            font-size: 16px;
            color: #333;
            font-weight: 500;
        }

        .stock-info {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 30px;
        }

        .stock-number {
            font-size: 24px;
            font-weight: 700;
        }

        .stock-good {
            color: #28a745;
        }

        .stock-low {
            color: #ffc107;
        }

        .stock-empty {
            color: #dc3545;
        }

        .stock-label {
            font-size: 14px;
            color: #666;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-bottom: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4e73df;
            color: white;
        }

        .btn-primary:hover {
            background: #2e59d9;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #4e73df;
            color: #4e73df;
        }

        .btn-outline:hover {
            background: #4e73df;
            color: white;
        }

        .btn:disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .stats-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
        }

        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        .similar-books {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .similar-books h3 {
            margin-bottom: 20px;
            color: #333;
        }

        .similar-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .similar-book {
            border: 1px solid #e3e6f0;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            transition: all 0.3s;
        }

        .similar-book:hover {
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .similar-cover {
            width: 100%;
            height: 120px;
            background: linear-gradient(135deg, #36b9cc 0%, #4e73df 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 10px;
        }

        .similar-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .similar-author {
            font-size: 12px;
            color: #666;
            margin-bottom: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .book-detail {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .book-meta {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .action-buttons {
                flex-direction: column;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .similar-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .navbar-nav {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .container {
                padding: 0 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .similar-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="index.php" class="navbar-brand">MiniLibrary</a>
            <ul class="navbar-nav">
                <li class="nav-item"><a href="index.php" class="nav-link">Beranda</a></li>
                <li class="nav-item"><a href="katalog.php" class="nav-link active">Katalog Buku</a></li>
                <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link">Pinjaman Saya</a></li>
                <li class="nav-item"><a href="profil.php" class="nav-link">Profil</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Breadcrumb -->
        <div class="breadcrumb">
            <a href="index.php">Beranda</a> &raquo; 
            <a href="katalog.php">Katalog</a> &raquo; 
            <?php echo htmlspecialchars($buku['judul buku']); ?>
        </div>

        <!-- Book Detail -->
        <div class="book-detail">
            <!-- Book Cover Section -->
            <div class="book-cover-section">
                <div class="book-cover-large">
                    📚
                    <?php
                    $is_available = ($buku['status'] == 1 && $buku['stok'] > 0);
                    ?>
                    <div class="availability-badge <?php echo $is_available ? 'available' : 'unavailable'; ?>">
                        <?php echo $is_available ? 'Tersedia' : 'Habis'; ?>
                    </div>
                </div>
                
                <div class="stock-info">
                    <?php
                    $stock_class = 'stock-good';
                    if ($buku['stok'] == 0) {
                        $stock_class = 'stock-empty';
                    } elseif ($buku['stok'] <= 2) {
                        $stock_class = 'stock-low';
                    }
                    ?>
                    <span class="stock-number <?php echo $stock_class; ?>"><?php echo $buku['stok']; ?></span>
                    <span class="stock-label">unit tersedia</span>
                </div>
            </div>

            <!-- Book Info Section -->
            <div class="book-info-section">
                <h1 class="book-title"><?php echo htmlspecialchars($buku['judul buku']); ?></h1>
                <p class="book-author">oleh <?php echo htmlspecialchars($buku['penulis']); ?></p>

                <?php if ($already_borrowed): ?>
                    <div class="alert alert-info">
                        <strong>Info:</strong> Anda sedang meminjam buku ini. Silakan kembalikan terlebih dahulu jika ingin meminjam lagi.
                    </div>
                <?php endif; ?>

                <div class="book-meta">
                    <div class="meta-item">
                        <span class="meta-label">Penerbit</span>
                        <span class="meta-value"><?php echo htmlspecialchars($buku['penerbit'] ?? 'Tidak diketahui'); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Tahun Terbit</span>
                        <span class="meta-value"><?php echo htmlspecialchars($buku['tahun_terbit']); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Kategori</span>
                        <span class="meta-value"><?php echo htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?></span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">ISBN</span>
                        <span class="meta-value"><?php echo htmlspecialchars($buku['isbn'] ?? 'Tidak tersedia'); ?></span>
                    </div>
                </div>

                <div class="action-buttons">
                    <?php if ($is_available && !$already_borrowed): ?>
                        <a href="pinjam_buku.php?id=<?php echo $buku['id']; ?>" class="btn btn-primary">
                            📚 Pinjam Buku
                        </a>
                    <?php elseif ($already_borrowed): ?>
                        <button class="btn" disabled>
                            ✅ Sudah Dipinjam
                        </button>
                    <?php else: ?>
                        <button class="btn" disabled>
                            ❌ Tidak Tersedia
                        </button>
                    <?php endif; ?>
                    
                    <a href="katalog.php" class="btn btn-outline">
                        ← Kembali ke Katalog
                    </a>
                </div>

                <?php if (!empty($buku['deskripsi'])): ?>
                    <div style="margin-top: 30px;">
                        <h3 style="margin-bottom: 15px; color: #333;">Deskripsi</h3>
                        <p style="line-height: 1.8; color: #666;">
                            <?php echo nl2br(htmlspecialchars($buku['deskripsi'])); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Statistics Section -->
        <div class="stats-section">
            <h3 style="margin-bottom: 20px; color: #333;">Statistik Buku</h3>
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_peminjaman; ?></div>
                    <div class="stat-label">Total Peminjaman</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $peminjaman_aktif; ?></div>
                    <div class="stat-label">Sedang Dipinjam</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $buku['stok']; ?></div>
                    <div class="stat-label">Stok Tersedia</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo $buku['tahun_terbit']; ?></div>
                    <div class="stat-label">Tahun Terbit</div>
                </div>
            </div>
        </div>

        <!-- Similar Books -->
        <?php if (mysqli_num_rows($similar_books) > 0): ?>
            <div class="similar-books">
                <h3>Buku Serupa</h3>
                <div class="similar-grid">
                    <?php while ($similar = mysqli_fetch_assoc($similar_books)): ?>
                        <a href="detail_buku_anggota.php?id=<?php echo $similar['id']; ?>" class="similar-book" style="text-decoration: none; color: inherit;">
                            <div class="similar-cover">📖</div>
                            <div class="similar-title"><?php echo htmlspecialchars($similar['judul buku']); ?></div>
                            <div class="similar-author"><?php echo htmlspecialchars($similar['penulis']); ?></div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
