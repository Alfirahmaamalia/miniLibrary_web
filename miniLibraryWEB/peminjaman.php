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

// Fungsi untuk mengkonversi status peminjaman
function getStatusPeminjaman($status) {
    switch($status) {
        case 'dipinjam':
            return ['text' => 'Dipinjam', 'class' => 'dipinjam'];
        case 'dikembalikan':
            return ['text' => 'Dikembalikan', 'class' => 'dikembalikan'];
        case 'terlambat':
            return ['text' => 'Terlambat', 'class' => 'terlambat'];
        default:
            return ['text' => 'Tidak Diketahui', 'class' => 'unknown'];
    }
}

// Fungsi untuk menghitung denda keterlambatan
function hitungDenda($tanggal_kembali, $tanggal_dikembalikan = null) {
    if (!$tanggal_dikembalikan) {
        $tanggal_dikembalikan = date('Y-m-d');
    }
    
    $tgl_kembali = new DateTime($tanggal_kembali);
    $tgl_dikembalikan = new DateTime($tanggal_dikembalikan);
    
    if ($tgl_dikembalikan > $tgl_kembali) {
        $selisih = $tgl_kembali->diff($tgl_dikembalikan);
        $hari_terlambat = $selisih->days;
        return $hari_terlambat * 1000; // Denda Rp 1.000 per hari
    }
    
    return 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Peminjaman Buku - MiniLibrary</title>
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

        .status-filter {
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

        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.dipinjam {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.dikembalikan {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.terlambat {
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

        .btn.success {
            background: #28a745;
            color: white;
            border-color: #28a745;
        }

        .btn.success:hover {
            background: #218838;
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

        .denda-info {
            color: #dc3545;
            font-weight: 600;
        }

        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #007bff;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
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
        <h1>Peminjaman Buku</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <?php if (isset($message)): ?>
        <div class="<?php echo $message_class; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Statistik Peminjaman -->
    <div class="stats-cards">
        <?php
        include 'koneksi.php';
        
        // Hitung statistik
        $total_peminjaman = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
        $sedang_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'"))['total'];
        $terlambat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat' OR (status = 'dipinjam' AND tanggal_kembali < CURDATE())"))['total'];
        $dikembalikan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan'"))['total'];
        ?>
        <div class="stat-card">
            <div class="stat-number"><?php echo $total_peminjaman; ?></div>
            <div class="stat-label">Total Peminjaman</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #ffc107;"><?php echo $sedang_dipinjam; ?></div>
            <div class="stat-label">Sedang Dipinjam</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #dc3545;"><?php echo $terlambat; ?></div>
            <div class="stat-label">Terlambat</div>
        </div>
        <div class="stat-card">
            <div class="stat-number" style="color: #28a745;"><?php echo $dikembalikan; ?></div>
            <div class="stat-label">Dikembalikan</div>
        </div>
    </div>

    <div class="header-section">
        <div class="search-section">
            <div class="search-box">
                <input type="text" placeholder="Cari Nama Peminjam atau Judul Buku" id="searchInput">
            </div>
            <select class="status-filter" id="statusFilter">
                <option value="">Semua Status</option>
                <option value="dipinjam">Dipinjam</option>
                <option value="dikembalikan">Dikembalikan</option>
                <option value="terlambat">Terlambat</option>
            </select>
        </div>
        <a href="tambah_peminjaman.php" class="add-btn">+ Tambah Peminjaman</a>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Daftar Peminjaman</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Peminjam</th>
                    <th>Judul Buku</th>
                    <th>Tanggal Pinjam</th>
                    <th>Tanggal Kembali</th>
                    <th>Status</th>
                    <th>Denda</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="loanTable">
                <?php
                if (!isset($koneksi) || mysqli_connect_error()) {
                    echo "<tr><td colspan='8' class='error-message'>Error: Koneksi database gagal!</td></tr>";
                } else {
                    // Query untuk mengambil data peminjaman dengan join ke tabel anggota dan buku
                    $query = "SELECT p.*, a.nama as nama_peminjam, b.`judul buku` as judul_buku 
                             FROM peminjaman p 
                             LEFT JOIN anggota a ON p.id_anggota = a.id 
                             LEFT JOIN buku b ON p.id_buku = b.id 
                             ORDER BY p.id DESC";
                    $result = mysqli_query($koneksi, $query);
                    
                    if (!$result) {
                        echo "<tr><td colspan='8' class='error-message'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                    } else if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            // Tentukan status berdasarkan tanggal
                            $status = $row['status'];
                            if ($status == 'dipinjam' && $row['tanggal_kembali'] < date('Y-m-d')) {
                                $status = 'terlambat';
                            }
                            
                            $status_info = getStatusPeminjaman($status);
                            $denda = hitungDenda($row['tanggal_kembali'], $row['tanggal_dikembalikan']);
                            
                            echo "<tr>
                                <td>".str_pad($row['id'], 3, '0', STR_PAD_LEFT)."</td>
                                <td>".htmlspecialchars($row['nama_peminjam'] ?? 'Tidak Diketahui')."</td>
                                <td>".htmlspecialchars($row['judul_buku'] ?? 'Tidak Diketahui')."</td>
                                <td>".date('d/m/Y', strtotime($row['tanggal_pinjam']))."</td>
                                <td>".date('d/m/Y', strtotime($row['tanggal_kembali']))."</td>
                                <td><span class='status-badge {$status_info['class']}'>{$status_info['text']}</span></td>
                                <td>".($denda > 0 ? "<span class='denda-info'>Rp ".number_format($denda, 0, ',', '.')."</span>" : "-")."</td>
                                <td>
                                    <div class='actions'>";
                            
                            if ($status == 'dipinjam' || $status == 'terlambat') {
                                echo "<a href='kembalikan_buku.php?id={$row['id']}' class='btn success'>📚 Kembalikan</a>";
                            }
                            
                            echo "      <a href='detail_peminjaman.php?id={$row['id']}' class='btn primary'>👁️ Detail</a>
                                        <a href='edit_peminjaman.php?id={$row['id']}' class='btn'>✏️ Edit</a>
                                        <a href='hapus_peminjaman.php?id={$row['id']}' class='btn danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data peminjaman ini?\")'>🗑️ Hapus</a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center; padding: 20px; color: #666;'>Tidak ada data peminjaman</td></tr>";
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
    const rows = document.querySelectorAll('#loanTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Fungsi filter status
document.getElementById('statusFilter').addEventListener('change', function(e) {
    const filterValue = e.target.value;
    const rows = document.querySelectorAll('#loanTable tr');
    
    rows.forEach(row => {
        if (filterValue === '') {
            row.style.display = '';
        } else {
            const statusCell = row.querySelector('.status-badge');
            if (statusCell) {
                const hasClass = statusCell.classList.contains(filterValue);
                row.style.display = hasClass ? '' : 'none';
            }
        }
    });
});

console.log('Loan management JavaScript loaded successfully');
</script>

</body>
</html>
