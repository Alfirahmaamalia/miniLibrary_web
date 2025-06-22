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
            <li class="nav-item"><a href="kategori.php" class="nav-link active">Kategori</a></li>
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
            <?php echo ($message_class == 'success-message' ? '✅' : '❌') . " " . $message; ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Kategori Buku</h1>
            <p>Kelola kategori buku untuk mengorganisir koleksi perpustakaan dengan lebih baik.</p>
            <a href="tambah_kategori.php" class="btn">Tambah Kategori</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Filter Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>🔍 Pencarian Kategori</h2>
           
        </div>
        <input type="text" placeholder="🔍 Cari Nama Kategori" id="searchInput" 
               style="width: 100%; max-width: 400px; padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px;">
    </div>

    <!-- Categories Table -->
    <div class="content-section">
        <div class="section-header">
            <h2>📂 Daftar Kategori</h2>
        </div>
        
        <div class="table-responsive">
            <table class="table">
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
                    include '../koneksi/koneksi.php';
                    
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
                                // Query untuk mendapatkan ID buku pertama dari kategori ini
                                $book_query = "SELECT id FROM buku WHERE kategori = {$row['id']} LIMIT 1";
                                $book_result = mysqli_query($koneksi, $book_query);
                                $book_id = null;
                                if ($book_result && mysqli_num_rows($book_result) > 0) {
                                    $book_row = mysqli_fetch_assoc($book_result);
                                    $book_id = $book_row['id'];
                                }
                                
                                echo "<tr>
                                    <td>".str_pad($row['id'], 3, '0', STR_PAD_LEFT)."</td>
                                    <td>".htmlspecialchars($row['nama_kategori'])."</td>
                                    <td>
                                        <span class='badge badge-info'>{$row['jumlah_buku']} buku</span>
                                    </td>
                                    <td>
                                        <div style='display: flex; gap: 5px;'>";
                                
                                // Tampilkan tombol Detail hanya jika ada buku dalam kategori ini
                                if ($book_id) {
                                    echo "<a href='detail_buku.php?id={$book_id}' class='action-btn btn-detail'>👁️ Detail</a>";
                                }
                                
                                echo "      <a href='edit_kategori.php?id={$row['id']}' class='action-btn btn-edit'>✏️ Edit</a>
                                            <a href='hapus_kategori.php?id={$row['id']}' class='action-btn btn-delete' onclick='return confirm(\"Apakah Anda yakin ingin menghapus kategori ini?\")'>🗑️ Hapus</a>
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
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
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
