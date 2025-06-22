<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: loginadmin.php");
    exit;
}

// Tampilkan pesan jika ada
if (isset($_SESSION['message'])) {
    $message_class = ($_SESSION['message_type'] == 'success') ? 'success' : 'error';
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    
    // Hapus pesan dari session agar tidak muncul lagi saat refresh
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .filter-section {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .filter-controls {
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
        }

        .search-input {
            flex: 1;
            min-width: 300px;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
        }

        .search-input:focus {
            outline: none;
            border-color: #4e73df;
        }

        .category-select {
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            min-width: 200px;
        }

        .category-select:focus {
            outline: none;
            border-color: #4e73df;
        }

        .books-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .book-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            height: 180px;
            background: linear-gradient(135deg, #4e73df, #224abe);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 40px;
        }

        .book-info {
            padding: 20px;
        }

        .book-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
            line-height: 1.4;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 15px;
        }

        .book-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
        }

        .meta-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 3px;
        }

        .meta-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }

        .stock-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stock-number {
            font-weight: 700;
            font-size: 14px;
        }

        .stock-good { color: #28a745; }
        .stock-medium { color: #f6c23e; }
        .stock-low { color: #e74a3b; }

        .book-actions {
            display: flex;
            gap: 8px;
            padding: 0 20px 20px;
        }

        .action-btn {
            flex: 1;
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .btn-detail {
            background: #4e73df;
            color: white;
        }

        .btn-detail:hover {
            background: #2e59d9;
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .stats-bar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 20px;
            font-weight: 700;
            color: #4e73df;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 5px;
        }

        .add-btn {
            background: #28a745;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .add-btn:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .filter-controls {
                flex-direction: column;
                align-items: stretch;
            }

            .search-input {
                min-width: auto;
            }

            .books-grid {
                grid-template-columns: 1fr;
            }

            .stats-bar {
                flex-direction: column;
                text-align: center;
            }

            .book-meta {
                grid-template-columns: 1fr;
            }

            .book-actions {
                flex-direction: column;
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
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo ($message_class == 'success' ? '✅' : '❌') . " " . $message; ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Manajemen Buku</h1>
            <p>Kelola dan pantau seluruh koleksi buku digital perpustakaan Anda dengan mudah.</p>
            <a href="kategori.php" class="btn" style="margin-left: 10px; background: #2e59d9;;">Kelola Kategori</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Statistics Bar -->
    <?php
    include '../koneksi/koneksi.php';
    
    $total_buku = 0;
    $buku_tersedia = 0;
    $total_stok = 0;
    
    if (isset($koneksi) && !mysqli_connect_error()) {
        $total_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
        $total_buku = $total_query ? mysqli_fetch_assoc($total_query)['total'] : 0;
        
        $tersedia_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku WHERE CAST(stok AS UNSIGNED) > 0");
        $buku_tersedia = $tersedia_query ? mysqli_fetch_assoc($tersedia_query)['total'] : 0;
        
        $stok_query = mysqli_query($koneksi, "SELECT SUM(CAST(stok AS UNSIGNED)) as total FROM buku");
        $total_stok = $stok_query ? mysqli_fetch_assoc($stok_query)['total'] : 0;
    }
    ?>

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($total_buku); ?></span>
            <span class="stat-label">Total Buku</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($buku_tersedia); ?></span>
            <span class="stat-label">Buku Tersedia</span>
        </div>
        <div class="stat-item">
            <span class="stat-number"><?php echo number_format($total_stok); ?></span>
            <span class="stat-label">Total Stok</span>
        </div>
        <a href="tambah_buku.php" class="add-btn">
            ➕ Tambah Buku Baru
        </a>
    </div>

    <!-- Filter Section -->
    <div class="filter-section">
        <div class="filter-controls">
            <input type="text" class="search-input" placeholder="🔍 Cari judul buku atau penulis..." id="searchInput">
            <select class="category-select" id="categoryFilter">
                <option value="">📂 Semua Kategori</option>
                <option value="Fiksi">📖 Fiksi</option>
                <option value="Non-Fiksi">📰 Non-Fiksi</option>
                <option value="Sejarah">🏛️ Sejarah</option>
                <option value="Ilmiah">🔬 Ilmiah</option>
                <option value="Teknologi">💻 Teknologi</option>
                <option value="Biografi">👤 Biografi</option>
                <option value="Pendidikan">🎓 Pendidikan</option>
            </select>
        </div>
    </div>

    <!-- Books Grid -->
    <div class="books-grid" id="booksGrid">
        <?php
        if (!isset($koneksi) || mysqli_connect_error()) {
            echo '<div class="empty-state">
                    <div class="empty-state-icon">❌</div>
                    <h3>Koneksi Database Gagal</h3>
                    <p>Tidak dapat terhubung ke database. Silakan periksa koneksi Anda.</p>
                  </div>';
        } else {
            $query = "SELECT * FROM buku ORDER BY id DESC";
            $result = mysqli_query($koneksi, $query);
            
            if (!$result) {
                echo '<div class="empty-state">
                        <div class="empty-state-icon">⚠️</div>
                        <h3>Error Query</h3>
                        <p>Terjadi kesalahan saat mengambil data: ' . mysqli_error($koneksi) . '</p>
                      </div>';
            } else if (mysqli_num_rows($result) > 0) {
                while ($row = mysqli_fetch_assoc($result)) {
                    $kategori_name = getKategoriName($row['kategori']);
                    $status_name = getStatusName($row['status']);
                    $status_class = ($row['status'] == 1) ? 'tersedia' : 'tidak-tersedia';
                    
                    $stok_value = intval($row['stok']);
                    if ($stok_value > 5) {
                        $stock_class = 'stock-good';
                        $stock_icon = '✅';
                    } else if ($stok_value > 0) {
                        $stock_class = 'stock-medium';
                        $stock_icon = '⚠️';
                    } else {
                        $stock_class = 'stock-low';
                        $stock_icon = '❌';
                    }
                    
                    echo '<div class="book-card" data-title="' . htmlspecialchars($row['judul buku']) . '" data-author="' . htmlspecialchars($row['penulis']) . '" data-category="' . $kategori_name . '">
                            <div class="book-cover">
                                📚
                            </div>
                            <div class="book-info">
                                <h3 class="book-title">' . htmlspecialchars($row['judul buku']) . '</h3>
                                <p class="book-author">oleh ' . htmlspecialchars($row['penulis']) . '</p>
                                
                                <div class="book-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Kategori</span>
                                        <span class="meta-value">📂 ' . $kategori_name . '</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Tahun Terbit</span>
                                        <span class="meta-value">📅 ' . htmlspecialchars($row['tahun_terbit']) . '</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Stok</span>
                                        <div class="stock-info">
                                            <span class="stock-number ' . $stock_class . '">' . $stok_value . '</span>
                                            <span>' . $stock_icon . '</span>
                                        </div>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Status</span>
                                        <span class="status-badge ' . $status_class . '">' . $status_name . '</span>
                                    </div>
                                </div>
                            </div>
                            <div class="book-actions">
                                <a href="detail_buku.php?id=' . $row['id'] . '" class="action-btn btn-detail">
                                    👁️ Detail
                                </a>
                                <a href="edit_buku.php?id=' . $row['id'] . '" class="action-btn btn-edit">
                                    ✏️ Edit
                                </a>
                                <a href="hapus_buku.php?id=' . $row['id'] . '" class="action-btn btn-delete" onclick="return confirm(\'Apakah Anda yakin ingin menghapus buku ini?\')">
                                    🗑️ Hapus
                                </a>
                            </div>
                          </div>';
                }
            } else {
                echo '<div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <h3>Belum Ada Buku</h3>
                        <p>Koleksi perpustakaan masih kosong. Mulai tambahkan buku pertama Anda!</p>
                        <a href="tambah_buku.php" class="btn">➕ Tambah Buku Pertama</a>
                      </div>';
            }
        }
        ?>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
</div>

<script>
// Search functionality
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        const title = card.dataset.title.toLowerCase();
        const author = card.dataset.author.toLowerCase();
        
        if (title.includes(searchTerm) || author.includes(searchTerm)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    
    updateEmptyState();
});

// Category filter functionality
document.getElementById('categoryFilter').addEventListener('change', function(e) {
    const filterValue = e.target.value;
    const bookCards = document.querySelectorAll('.book-card');
    
    bookCards.forEach(card => {
        const category = card.dataset.category;
        
        if (filterValue === '' || category === filterValue) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }
    });
    
    updateEmptyState();
});

// Update empty state based on visible cards
function updateEmptyState() {
    const bookCards = document.querySelectorAll('.book-card');
    const visibleCards = Array.from(bookCards).filter(card => card.style.display !== 'none');
    const grid = document.getElementById('booksGrid');
    
    // Remove existing empty state
    const existingEmptyState = grid.querySelector('.empty-state');
    if (existingEmptyState) {
        existingEmptyState.remove();
    }
    
    // Add empty state if no visible cards
    if (visibleCards.length === 0 && bookCards.length > 0) {
        const emptyState = document.createElement('div');
        emptyState.className = 'empty-state';
        emptyState.innerHTML = `
            <div class="empty-state-icon">🔍</div>
            <h3>Tidak Ada Hasil</h3>
            <p>Tidak ditemukan buku yang sesuai dengan pencarian atau filter Anda.</p>
        `;
        grid.appendChild(emptyState);
    }
}

// Auto-hide alert messages
<?php if (isset($message)): ?>
setTimeout(function() {
    const alert = document.querySelector('.alert');
    if (alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-20px)';
        setTimeout(() => alert.remove(), 300);
    }
}, 5000);
<?php endif; ?>
</script>
</body>
</html>
