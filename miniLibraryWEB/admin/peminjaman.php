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
        return $hari_terlambat * 2000; 
    }
    
    return 0;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Peminjaman Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="dashboard.php" class="navbar-brand">MiniLibrary Admin</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="dashboard.php" class="nav-link">Dashboard</a></li>
            <li class="nav-item"><a href="manajemen.php" class="nav-link">Manajemen Buku</a></li>
            <li class="nav-item"><a href="anggota.php" class="nav-link">Anggota</a></li>
            <li class="nav-item"><a href="kategori.php" class="nav-link">Kategori</a></li>
            <li class="nav-item"><a href="peminjaman.php" class="nav-link active">Peminjaman</a></li>
            <li class="nav-item"><a href="profil_admin.php" class="nav-link">Profil</a></li>
            <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <?php if (isset($message)): ?>
        <div class="alert alert-<?php echo $message_type; ?>">
            <?php echo ($message_class == 'success-message' ? '✅' : '❌') . " " . $message; ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Peminjaman Buku</h1>
            <p>Kelola dan pantau semua aktivitas peminjaman buku di perpustakaan.</p>
            <a href="tambah_peminjaman.php" class="btn">Tambah Peminjaman</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Statistik Peminjaman -->
    <div class="stats-row">
        <?php
        include '../koneksi/koneksi.php';
        
        // Hitung statistik
        $total_peminjaman = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman"))['total'];
        $sedang_dipinjam = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'"))['total'];
        $terlambat = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'terlambat' OR (status = 'dipinjam' AND tanggal_kembali < CURDATE())"))['total'];
        $dikembalikan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan'"))['total'];
        ?>
        <div class="stat-card">
            <div class="stat-icon books">📚</div>
            <div class="stat-info">
                <h3>Total Peminjaman</h3>
                <p><?php echo $total_peminjaman; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon borrowed">📖</div>
            <div class="stat-info">
                <h3>Sedang Dipinjam</h3>
                <p><?php echo $sedang_dipinjam; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon overdue">⚠️</div>
            <div class="stat-info">
                <h3>Terlambat</h3>
                <p><?php echo $terlambat; ?></p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon returned">✅</div>
            <div class="stat-info">
                <h3>Dikembalikan</h3>
                <p><?php echo $dikembalikan; ?></p>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>🔍 Filter & Pencarian</h2>
        </div>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <input type="text" placeholder="🔍 Cari Nama Peminjam atau Judul Buku" id="searchInput" 
                   style="flex: 1; min-width: 300px; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px;">
            <select id="statusFilter" style="padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px; min-width: 200px;">
                <option value="">Semua Status</option>
                <option value="dipinjam">Dipinjam</option>
                <option value="dikembalikan">Dikembalikan</option>
                <option value="terlambat">Terlambat</option>
            </select>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="content-section">
        <div class="section-header">
            <h2>📋 Daftar Peminjaman</h2>
        </div>
        
        <div class="table-responsive">
            <table class="table">
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
                                    <td><span class='badge badge-".($status == 'dikembalikan' ? 'success' : ($status == 'terlambat' ? 'danger' : 'warning'))."'>{$status_info['text']}</span></td>
                                    <td>".($denda > 0 ? "<span style='color: #dc3545; font-weight: 600;'>Rp ".number_format($denda, 0, ',', '.')."</span>" : "-")."</td>
                                    <td>
                                        <div style='display: flex; gap: 5px;'>
                                            <a href='detail_peminjaman.php?id={$row['id']}' class='action-btn btn-detail'>👁️ Detail</a>
                                            <a href='edit_peminjaman.php?id={$row['id']}' class='action-btn btn-edit'>✏️ Edit</a>
                                            <a href='hapus_peminjaman.php?id={$row['id']}' class='action-btn btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus data peminjaman ini?\")'>🗑️ Hapus</a>
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
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
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
            const statusCell = row.querySelector('.badge');
            if (statusCell) {
                const statusText = statusCell.textContent.toLowerCase();
                const shouldShow = statusText.includes(filterValue.toLowerCase());
                row.style.display = shouldShow ? '' : 'none';
            }
        }
    });
});

console.log('Loan management JavaScript loaded successfully');
</script>

</body>
</html>
