<?php
session_start();
include '../koneksi/koneksi.php';

// Cek apakah user sudah login sebagai anggota
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit;
}

// Ambil data anggota yang login
$anggota_id = $_SESSION['anggota_id'];
$query_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id = $anggota_id");
$anggota = mysqli_fetch_assoc($query_anggota);

// Parameter pencarian dan filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($koneksi, $_GET['search']) : '';
$kategori_filter = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'terbaru';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12; // Jumlah buku per halaman
$offset = ($page - 1) * $limit;

// Query untuk mengambil daftar kategori
$query_kategori = mysqli_query($koneksi, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Build query untuk mengambil buku
$where_conditions = array();
$where_conditions[] = "1=1"; // Base condition

if (!empty($search)) {
    $where_conditions[] = "(b.`judul buku` LIKE '%$search%' OR b.penulis LIKE '%$search%')";
}

if ($kategori_filter > 0) {
    $where_conditions[] = "b.kategori = $kategori_filter";
}

if ($status_filter === 'tersedia') {
    $where_conditions[] = "b.status = 1 AND b.stok > 0";
} elseif ($status_filter === 'habis') {
    $where_conditions[] = "b.stok = 0";
}

$where_clause = implode(' AND ', $where_conditions);

// Sorting
$order_by = "b.id DESC"; // Default: terbaru
switch ($sort_by) {
    case 'judul_asc':
        $order_by = "b.`judul buku` ASC";
        break;
    case 'judul_desc':
        $order_by = "b.`judul buku` DESC";
        break;
    case 'penulis':
        $order_by = "b.penulis ASC";
        break;
    case 'tahun_terbaru':
        $order_by = "b.tahun_terbit DESC";
        break;
    case 'tahun_terlama':
        $order_by = "b.tahun_terbit ASC";
        break;
    case 'terpopuler':
        $order_by = "peminjaman_count DESC, b.`judul buku` ASC";
        break;
}

// Query untuk menghitung total buku
$count_query = "SELECT COUNT(*) as total 
                FROM buku b 
                LEFT JOIN kategori k ON b.kategori = k.id 
                WHERE $where_clause";
$count_result = mysqli_query($koneksi, $count_query);
$total_books = mysqli_fetch_assoc($count_result)['total'];
$total_pages = ceil($total_books / $limit);

// Query untuk mengambil data buku dengan pagination
$query = "SELECT b.*, k.nama_kategori,
          (SELECT COUNT(*) FROM peminjaman p WHERE p.id_buku = b.id) as peminjaman_count
          FROM buku b 
          LEFT JOIN kategori k ON b.kategori = k.id 
          WHERE $where_clause 
          ORDER BY $order_by 
          LIMIT $limit OFFSET $offset";

$result = mysqli_query($koneksi, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - MiniLibrary</title>
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

        /* Header Section */
        .page-header {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .page-header h1 {
            font-size: 28px;
            margin-bottom: 10px;
            color: #333;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .search-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
            color: #555;
        }

        .form-control {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            border-color: #4e73df;
            outline: none;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
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

        /* Results Info */
        .results-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px 0;
        }

        .results-count {
            font-size: 16px;
            color: #666;
        }

        .sort-options {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sort-options label {
            font-size: 14px;
            color: #666;
        }

        .sort-options select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Book Grid */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .book-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: all 0.3s;
            position: relative;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            height: 200px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 48px;
            position: relative;
        }

        .book-status {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-available {
            background: rgba(40, 167, 69, 0.9);
            color: white;
        }

        .status-unavailable {
            background: rgba(220, 53, 69, 0.9);
            color: white;
        }

        .book-info {
            padding: 20px;
        }

        .book-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 48px;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .book-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .book-category {
            display: inline-block;
            padding: 3px 8px;
            background: #f8f9fc;
            border-radius: 4px;
            font-size: 12px;
            color: #4e73df;
            border: 1px solid #e3e6f0;
        }

        .book-year {
            font-size: 12px;
            color: #999;
        }

        .book-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            font-size: 12px;
            color: #666;
        }

        .stock-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stock-number {
            font-weight: 600;
            color: #28a745;
        }

        .stock-number.low {
            color: #ffc107;
        }

        .stock-number.empty {
            color: #dc3545;
        }

        .book-actions {
            display: flex;
            gap: 8px;
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }

        .btn-outline {
            background: transparent;
            border: 1px solid #4e73df;
            color: #4e73df;
        }

        .btn-outline:hover {
            background: #4e73df;
            color: white;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-top: 40px;
        }

        .pagination a,
        .pagination span {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            color: #666;
            transition: all 0.3s;
        }

        .pagination a:hover {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
        }

        .pagination .current {
            background: #4e73df;
            color: white;
            border-color: #4e73df;
        }

        .pagination .disabled {
            color: #ccc;
            cursor: not-allowed;
        }

        .footer {
            background: #4e73df;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 14px;
            border-top: 1px solid #eee;
            margin-top: 40px;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .empty-state-icon {
            font-size: 64px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            font-size: 20px;
            color: #666;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #999;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .search-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .results-info {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
            }

            .book-grid {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
                gap: 20px;
            }

            .navbar-nav {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .book-grid {
                grid-template-columns: 1fr;
            }

            .container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="beranda_pengguna.php" class="navbar-brand">MiniLibrary</a>
            <ul class="navbar-nav">
                <li class="nav-item"><a href="beranda_pengguna.php" class="nav-link">Beranda</a></li>
                <li class="nav-item"><a href="katalog.php" class="nav-link active">Katalog Buku</a></li>
                <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link">Pinjaman Saya</a></li>
                <li class="nav-item"><a href="profil.php" class="nav-link">Profil</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Katalog Buku</h1>
            <p>Jelajahi koleksi buku perpustakaan dan temukan bacaan favorit Anda</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <form method="GET" action="">
                <div class="search-row">
                    <div class="form-group">
                        <label for="search">Cari Buku</label>
                        <input type="text" id="search" name="search" class="form-control" 
                               placeholder="Cari judul, penulis, atau penerbit..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="kategori">Kategori</label>
                        <select id="kategori" name="kategori" class="form-control">
                            <option value="">Semua Kategori</option>
                            <?php while ($kategori = mysqli_fetch_assoc($query_kategori)): ?>
                                <option value="<?php echo $kategori['id']; ?>" 
                                        <?php echo ($kategori_filter == $kategori['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($kategori['nama_kategori']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="tersedia" <?php echo ($status_filter == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                            <option value="habis" <?php echo ($status_filter == 'habis') ? 'selected' : ''; ?>>Habis</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="sort">Urutkan</label>
                        <select id="sort" name="sort" class="form-control">
                            <option value="terbaru" <?php echo ($sort_by == 'terbaru') ? 'selected' : ''; ?>>Terbaru</option>
                            <option value="judul_asc" <?php echo ($sort_by == 'judul_asc') ? 'selected' : ''; ?>>Judul A-Z</option>
                            <option value="judul_desc" <?php echo ($sort_by == 'judul_desc') ? 'selected' : ''; ?>>Judul Z-A</option>
                            <option value="penulis" <?php echo ($sort_by == 'penulis') ? 'selected' : ''; ?>>Penulis</option>
                            <option value="tahun_terbaru" <?php echo ($sort_by == 'tahun_terbaru') ? 'selected' : ''; ?>>Tahun Terbaru</option>
                            <option value="tahun_terlama" <?php echo ($sort_by == 'tahun_terlama') ? 'selected' : ''; ?>>Tahun Terlama</option>
                            <option value="terpopuler" <?php echo ($sort_by == 'terpopuler') ? 'selected' : ''; ?>>Terpopuler</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Results Info -->
        <div class="results-info">
            <div class="results-count">
                Menampilkan <?php echo min($limit, $total_books - $offset); ?> dari <?php echo $total_books; ?> buku
                <?php if (!empty($search)): ?>
                    untuk pencarian "<strong><?php echo htmlspecialchars($search); ?></strong>"
                <?php endif; ?>
            </div>
            
            <?php if (!empty($search) || $kategori_filter > 0 || !empty($status_filter)): ?>
                <div>
                    <a href="katalog.php" class="btn btn-secondary btn-sm">Reset Filter</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Book Grid -->
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="book-grid">
                <?php while ($buku = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $is_available = ($buku['status'] == 1 && $buku['stok'] > 0);
                    $stock_class = '';
                    if ($buku['stok'] == 0) {
                        $stock_class = 'empty';
                    } elseif ($buku['stok'] <= 2) {
                        $stock_class = 'low';
                    }
                    ?>
                    <div class="book-card">
                        <div class="book-cover">
                            📚
                            <div class="book-status <?php echo $is_available ? 'status-available' : 'status-unavailable'; ?>">
                                <?php echo $is_available ? 'Tersedia' : 'Habis'; ?>
                            </div>
                        </div>
                        
                        <div class="book-info">
                            <h3 class="book-title"><?php echo htmlspecialchars($buku['judul buku']); ?></h3>
                            <p class="book-author">oleh <?php echo htmlspecialchars($buku['penulis']); ?></p>
                            
                            <div class="book-meta">
                                <span class="book-category">
                                    <?php echo htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?>
                                </span>
                                <span class="book-year"><?php echo htmlspecialchars($buku['tahun_terbit']); ?></span>
                            </div>
                            
                            <div class="book-stats">
                                <div class="stock-info">
                                    <span>Stok:</span>
                                    <span class="stock-number <?php echo $stock_class; ?>">
                                        <?php echo $buku['stok']; ?> unit
                                    </span>
                                </div>
                                <div>
                                    <span><?php echo $buku['peminjaman_count']; ?> peminjaman</span>
                                </div>
                            </div>
                            
                            <div class="book-actions">
                                <a href="detail_buku_anggota.php?id=<?php echo $buku['id']; ?>" 
                                   class="btn btn-outline btn-sm">Detail</a>
                                <?php if ($is_available): ?>
                                    <a href="pinjam_buku.php?id=<?php echo $buku['id']; ?>" 
                                       class="btn btn-primary btn-sm">Pinjam</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php
                    $query_params = $_GET;
                    
                    // Previous page
                    if ($page > 1):
                        $query_params['page'] = $page - 1;
                        $prev_url = 'katalog.php?' . http_build_query($query_params);
                    ?>
                        <a href="<?php echo $prev_url; ?>">&laquo; Sebelumnya</a>
                    <?php else: ?>
                        <span class="disabled">&laquo; Sebelumnya</span>
                    <?php endif; ?>

                    <?php
                    // Page numbers
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    for ($i = $start_page; $i <= $end_page; $i++):
                        $query_params['page'] = $i;
                        $page_url = 'katalog.php?' . http_build_query($query_params);
                    ?>
                        <?php if ($i == $page): ?>
                            <span class="current"><?php echo $i; ?></span>
                        <?php else: ?>
                            <a href="<?php echo $page_url; ?>"><?php echo $i; ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php
                    // Next page
                    if ($page < $total_pages):
                        $query_params['page'] = $page + 1;
                        $next_url = 'katalog.php?' . http_build_query($query_params);
                    ?>
                        <a href="<?php echo $next_url; ?>">Selanjutnya &raquo;</a>
                    <?php else: ?>
                        <span class="disabled">Selanjutnya &raquo;</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3>Tidak ada buku ditemukan</h3>
                <p>Coba ubah kata kunci pencarian atau filter yang Anda gunakan</p>
                <a href="katalog.php" class="btn btn-primary">Lihat Semua Buku</a>
            </div>
             <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
            </div>
        <?php endif; ?>
    </div>
      <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
            </div>
</body>
</html>
