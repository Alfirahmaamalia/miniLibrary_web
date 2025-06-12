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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Kategori Buku - MiniLibrary</title>
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
            padding: 0;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .table-card table {
            margin-top: 0;
            padding: 0 20px 20px;
        }
        
        .table-card thead th {
            background: #ffffff;
            border-bottom: 2px solid #f1f1f1;
        }

        .table-header {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px 5px 0 0;
            border-bottom: 1px solid #eee;
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

        .category-stats {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .book-count {
            background: #e9ecef;
            color: #495057;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
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
        <h1>Kategori Buku</h1>
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
                <input type="text" placeholder="Cari Nama Kategori" id="searchInput">
            </div>
        </div>
        <a href="tambah_kategori.php" class="add-btn">+ Tambah Kategori</a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Daftar Kategori</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Kategori</th>
                    <th>Jumlah Buku</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="categoryTable">
                <?php
                include 'koneksi.php';
                
                // Cek apakah koneksi berhasil
                if (!isset($koneksi) || mysqli_connect_error()) {
                    echo "<tr><td colspan='4' class='error-message'>Error: Koneksi database gagal!</td></tr>";
                } else {
                    // Query untuk mengambil kategori dengan jumlah buku
                    $query = "SELECT k.*, COUNT(b.id) as jumlah_buku 
                             FROM kategori k 
                             LEFT JOIN buku b ON k.id = b.kategori 
                             GROUP BY k.id 
                             ORDER BY k.id ASC";
                    $result = mysqli_query($koneksi, $query);
                    
                    // Cek apakah query berhasil
                    if (!$result) {
                        echo "<tr><td colspan='4' class='error-message'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                    } else if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>
                                <td>".str_pad($row['id'], 3, '0', STR_PAD_LEFT)."</td>
                                <td>".htmlspecialchars($row['nama_kategori'])."</td>
                                <td>
                                    <div class='category-stats'>
                                        <span class='book-count'>{$row['jumlah_buku']} buku</span>
                                    </div>
                                </td>
                                <td>
                                    <div class='actions'>
                                        <a href='edit_kategori.php?id={$row['id']}' class='btn primary'>✏️ Edit</a>
                                        <a href='hapus_kategori.php?id={$row['id']}' class='btn danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus kategori ini?\")'>🗑️ Hapus</a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4' style='text-align: center; padding: 20px; color: #666;'>Tidak ada data kategori</td></tr>";
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
    const rows = document.querySelectorAll('#categoryTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Debug: Cek apakah JavaScript berjalan
console.log('Category management JavaScript loaded successfully');
</script>

</body>
</html>
