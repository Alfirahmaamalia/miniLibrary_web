<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Tampilkan pesan jika ada
if (isset($_SESSION['message'])) {
    $message_class = ($_SESSION['message_type'] == 'success') ? 'success-message' : 'error-message';
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
<html>
<head>
    <title>Manajemen Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-section {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            font-size: 14px;
        }

        .search-box::before {
            content: "🔍";
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .category-filter {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .add-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .add-btn:hover {
            background: #0056b3;
        }

        .table-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-header {
            margin-bottom: 20px;
        }

        .table-header h3 {
            font-size: 18px;
            margin: 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
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

        .actions {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            color: #666;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background: #f8f9fa;
        }

        .btn.primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .btn.primary:hover {
            background: #0056b3;
        }

        .btn.danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn.danger:hover {
            background: #f5f5f5;
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

        .stock-info {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .stock-number {
            font-weight: 600;
            color: #333;
        }

        .stock-low {
            color: #dc3545;
        }

        .stock-medium {
            color: #ffc107;
        }

        .stock-good {
            color: #28a745;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="dashboard.php">📊 Beranda</a>
    <a class="active" href="manajemen.php">📚 Manajemen Buku</a>
    <a href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Manajemen Buku</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="<?php echo $message_class; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div class="header-section">
        <div class="search-section">
            <div class="search-box">
                <input type="text" placeholder="Cari Judul Buku atau Penulis" id="searchInput">
            </div>
            <select class="category-filter" id="categoryFilter">
                <option value="">Semua Kategori</option>
                <option value="Fiksi">Fiksi</option>
                <option value="Non-Fiksi">Non-Fiksi</option>
                <option value="Sejarah">Sejarah</option>
                <option value="Ilmiah">Ilmiah</option>
                <option value="Teknologi">Teknologi</option>
                <option value="Biografi">Biografi</option>
                <option value="Pendidikan">Pendidikan</option>
            </select>
        </div>
        <!-- Ubah button menjadi link langsung -->
        <a href="tambah_buku.php" class="add-btn">+ Tambah Buku</a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Daftar Buku</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th>Kategori</th>
                    <th>Tahun Terbit</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="bookTable">
                <?php
                include 'koneksi.php';
                
                // Cek apakah koneksi berhasil
                if (!isset($conn) || $conn->connect_error) {
                    echo "<tr><td colspan='8' class='error-message'>Error: Koneksi database gagal!</td></tr>";
                } else {
                    // Query dengan nama kolom yang sesuai dengan database
                    $query = "SELECT * FROM buku ORDER BY id ASC";
                    $result = mysqli_query($conn, $query);
                    
                    // Cek apakah query berhasil
                    if (!$result) {
                        echo "<tr><td colspan='8' class='error-message'>Error: " . mysqli_error($conn) . "</td></tr>";
                    } else if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Konversi kategori ID ke nama
                            $kategori_name = getKategoriName($row['kategori']);
                            
                            // Konversi status ID ke nama dan class
                            $status_name = getStatusName($row['status']);
                            $status_class = ($row['status'] == 1) ? 'tersedia' : 'tidak-tersedia';
                            
                            // Tentukan class stok berdasarkan jumlah
                            $stok_value = intval($row['stok']);
                            if ($stok_value > 5) {
                                $stock_class = 'stock-good';
                            } else if ($stok_value > 0) {
                                $stock_class = 'stock-medium';
                            } else {
                                $stock_class = 'stock-low';
                            }
                            
                            echo "<tr>
                                <td>".str_pad($row['id'], 3, '0', STR_PAD_LEFT)."</td>
                                <td>".htmlspecialchars($row['judul_buku'])."</td>
                                <td>".htmlspecialchars($row['penulis'])."</td>
                                <td>{$kategori_name}</td>
                                <td>".htmlspecialchars($row['tahun_terbit'])."</td>
                                <td>
                                    <div class='stock-info'>
                                        <span class='stock-number {$stock_class}'>{$stok_value}</span>
                                        <span>unit</span>
                                    </div>
                                </td>
                                <td><span class='status-badge {$status_class}'>{$status_name}</span></td>
                                <td>
                                    <div class='actions'>
                                        <a href='detail_buku.php?id={$row['id']}' class='btn primary'>Detail</a>
                                        <a href='edit_buku.php?id={$row['id']}' class='btn'>✏️</a>
                                        <a href='hapus_buku.php?id={$row['id']}' class='btn danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus buku ini?\")'>🗑️</a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center; padding: 20px; color: #666;'>Tidak ada data buku</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Fungsi pencarian
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#bookTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Fungsi filter kategori
document.getElementById('categoryFilter').addEventListener('change', function(e) {
    const filterValue = e.target.value;
    const rows = document.querySelectorAll('#bookTable tr');
    
    rows.forEach(row => {
        if (filterValue === '' || filterValue === 'Semua Kategori') {
            row.style.display = '';
        } else {
            const cells = row.querySelectorAll('td');
            if (cells.length > 0) {
                const kategori = cells[3].textContent.trim(); // Kolom kategori adalah index ke-3
                
                if (kategori === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });
});

// Debug: Cek apakah JavaScript berjalan
console.log('JavaScript loaded successfully');

// Alternatif fungsi tambah buku jika diperlukan
function tambahBuku() {
    console.log('Fungsi tambahBuku dipanggil');
    window.location.href = 'tambah_buku.php';
}
</script>

</body>
</html>