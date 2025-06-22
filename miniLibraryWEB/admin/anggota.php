<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Anggota Perpustakaan - MiniLibrary</title>
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
            <h1>Anggota Perpustakaan</h1>
            <p>Kelola data anggota perpustakaan dan pantau aktivitas mereka.</p>
            <a href="tambah_anggota.php" class="btn">Tambah Anggota</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Filter Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>🔍 Filter & Pencarian</h2>
        </div>
        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
            <input type="text" placeholder="🔍 Cari Anggota atau ID" id="searchInput" 
                   style="flex: 1; min-width: 300px; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px;">
            <select id="statusFilter" style="padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px; min-width: 200px;">
                <option>Semua Status</option>
                <option>Aktif</option>
                <option>Non Aktif</option>
            </select>
        </div>
    </div>

    <!-- Members Table -->
    <div class="content-section">
        <div class="section-header">
            <h2>👥 Daftar Anggota</h2>
        </div>
        
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Lengkap</th>
                        <th>Alamat</th>
                        <th>No. Telepon</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody id="memberTable">
                    <?php
                    // Perbaikan: Include file koneksi dan error handling
                    include '../koneksi/koneksi.php';
                    
                    // Cek apakah koneksi berhasil
                    if (!isset($koneksi) || mysqli_connect_error()) {
                        echo "<tr><td colspan='7' class='error-message'>Error: Koneksi database gagal!</td></tr>";
                    } else {
                        // Perbaikan: Gunakan mysqli_query dengan variabel koneksi yang benar
                        $query = "SELECT * FROM anggota ORDER BY id ASC";
                        $result = mysqli_query($koneksi, $query);
                        
                        // Cek apakah query berhasil
                        if (!$result) {
                            echo "<tr><td colspan='7' class='error-message'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                        } else if (mysqli_num_rows($result) > 0) {
                            while ($row = mysqli_fetch_assoc($result)) {
                                $status_class = strtolower(str_replace(' ', '-', $row['status']));
                                echo "<tr>
                                    <td>".str_pad($row['id'], 3, '0', STR_PAD_LEFT)."</td>
                                    <td>".htmlspecialchars($row['nama'])."</td>
                                    <td>".htmlspecialchars($row['alamat'])."</td>
                                    <td>".htmlspecialchars($row['telepon'])."</td>
                                    <td>".htmlspecialchars($row['email'])."</td>
                                    <td><span class='badge badge-".($row['status'] == 'Aktif' ? 'success' : 'warning')."'>".htmlspecialchars($row['status'])."</span></td>
                                    <td>
                                        <div style='display: flex; gap: 5px;'>
                                            <a href='detail_anggota.php?id={$row['id']}' class='action-btn btn-detail'>👁️ Detail</a>
                                            <a href='edit_anggota.php?id={$row['id']}' class='action-btn btn-edit'>✏️ Edit</a>
                                            <a href='hapus_anggota.php?id={$row['id']}' class='action-btn btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus anggota ini?\")'>🗑️ Hapus</a>
                                        </div>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center; padding: 20px; color: #666;'>Tidak ada data anggota</td></tr>";
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
    const rows = document.querySelectorAll('#memberTable tr');
    
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
    const rows = document.querySelectorAll('#memberTable tr');
    
    rows.forEach(row => {
        if (filterValue === 'Semua Status') {
            row.style.display = '';
        } else {
            const statusElement = row.querySelector('.badge');
            if (statusElement) {
                const status = statusElement.textContent.trim();
                
                if (status === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });
});
</script>

</body>
</html>
