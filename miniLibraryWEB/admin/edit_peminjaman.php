<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include '../koneksi/koneksi.php';

// Get loan ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: peminjaman.php");
    exit;
}

$id_peminjaman = (int)$_GET['id'];

// Fetch existing loan data
$query = mysqli_query($koneksi, "SELECT p.*, a.nama as nama_peminjam, b.`judul buku` as judul_buku 
                                FROM peminjaman p 
                                LEFT JOIN anggota a ON p.id_anggota = a.id 
                                LEFT JOIN buku b ON p.id_buku = b.id 
                                WHERE p.id = $id_peminjaman");

if (!$query || mysqli_num_rows($query) == 0) {
    $_SESSION['message'] = "Data peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: peminjaman.php");
    exit;
}

$peminjaman = mysqli_fetch_assoc($query);

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

// Handle form submission
if ($_POST) {
    $id_anggota = $_POST['id_anggota'];
    $id_buku = $_POST['id_buku'];
    $tanggal_pinjam = $_POST['tanggal_pinjam'];
    $tanggal_kembali = $_POST['tanggal_kembali'];
    $status = $_POST['status'];
    $tanggal_dikembalikan = !empty($_POST['tanggal_dikembalikan']) ? "'{$_POST['tanggal_dikembalikan']}'" : 'NULL';
    
    if (empty($id_anggota) || empty($id_buku) || empty($tanggal_pinjam) || empty($tanggal_kembali)) {
        $_SESSION['message'] = "Field wajib tidak boleh kosong!";
        $_SESSION['message_type'] = "error";
    } else {
        // Jika buku berubah, update stok
        if ($peminjaman['id_buku'] != $id_buku) {
            // Kembalikan stok buku lama jika status masih dipinjam
            if ($peminjaman['status'] == 'dipinjam') {
                mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id = {$peminjaman['id_buku']}");
            }
            
            // Kurangi stok buku baru jika status dipinjam
            if ($status == 'dipinjam') {
                $cek_stok = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id = $id_buku");
                $stok = mysqli_fetch_assoc($cek_stok)['stok'];
                
                if ($stok <= 0) {
                    $_SESSION['message'] = "Buku yang dipilih tidak tersedia!";
                    $_SESSION['message_type'] = "error";
                    goto skip_update;
                }
                
                mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id = $id_buku");
            }
        } else {
            // Jika buku sama tapi status berubah
            if ($peminjaman['status'] == 'dipinjam' && $status != 'dipinjam') {
                // Kembalikan stok
                mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id = $id_buku");
            } elseif ($peminjaman['status'] != 'dipinjam' && $status == 'dipinjam') {
                // Kurangi stok
                $cek_stok = mysqli_query($koneksi, "SELECT stok FROM buku WHERE id = $id_buku");
                $stok = mysqli_fetch_assoc($cek_stok)['stok'];
                
                if ($stok <= 0) {
                    $_SESSION['message'] = "Buku tidak tersedia untuk dipinjam!";
                    $_SESSION['message_type'] = "error";
                    goto skip_update;
                }
                
                mysqli_query($koneksi, "UPDATE buku SET stok = stok - 1 WHERE id = $id_buku");
            }
        }
        
        // Hitung denda jika ada
        $denda = 0;
        if ($status == 'dikembalikan' && !empty($_POST['tanggal_dikembalikan'])) {
            $denda = hitungDenda($tanggal_kembali, $_POST['tanggal_dikembalikan']);
        }
        
        // Update peminjaman
        $update_query = "UPDATE peminjaman SET 
                        id_anggota = $id_anggota,
                        id_buku = $id_buku,
                        tanggal_pinjam = '$tanggal_pinjam',
                        tanggal_kembali = '$tanggal_kembali',
                        status = '$status',
                        tanggal_dikembalikan = $tanggal_dikembalikan,
                        denda = $denda
                        WHERE id = $id_peminjaman";
        
        if (mysqli_query($koneksi, $update_query)) {
            $_SESSION['message'] = "Data peminjaman berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            header("Location: peminjaman.php");
            exit;
        } else {
            $_SESSION['message'] = "Gagal memperbarui data peminjaman: " . mysqli_error($koneksi);
            $_SESSION['message_type'] = "error";
        }
        
        skip_update:
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Peminjaman - MiniLibrary</title>
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
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['message_type'] == 'success') ? 'success' : 'error'; ?>">
            <?php 
            echo ($_SESSION['message_type'] == 'success' ? '✅' : '❌') . " " . $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Edit Peminjaman</h1>
            <p>Perbarui data peminjaman buku.</p>
            <a href="peminjaman.php" class="btn">← Kembali ke Peminjaman</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Info Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>ℹ️ Informasi Peminjaman</h2>
        </div>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; border-left: 4px solid #007bff;">
            <strong>ID Peminjaman:</strong> <?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?> | 
            <strong>Peminjam:</strong> <?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?> | 
            <strong>Buku:</strong> <?php echo htmlspecialchars($peminjaman['judul_buku']); ?>
        </div>
    </div>

    <!-- Warning Section -->
    <div class="content-section">
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border: 1px solid #ffeaa7;">
            <strong>⚠️ Perhatian:</strong> Mengubah data peminjaman dapat mempengaruhi stok buku. Pastikan data yang dimasukkan sudah benar.
        </div>
    </div>

    <!-- Form Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📝 Form Edit Peminjaman</h2>
        </div>

        <form method="POST">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 20px;">
                <div class="form-group">
                    <label>👤 Anggota *</label>
                    <select name="id_anggota" class="form-control" required>
                        <option value="">Pilih Anggota</option>
                        <?php
                        $anggota_query = mysqli_query($koneksi, "SELECT * FROM anggota ORDER BY nama ASC");
                        while ($anggota = mysqli_fetch_assoc($anggota_query)) {
                            $selected = ($anggota['id'] == $peminjaman['id_anggota']) ? 'selected' : '';
                            echo "<option value='{$anggota['id']}' $selected>{$anggota['nama']} - {$anggota['email']}</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>📚 Buku *</label>
                    <select name="id_buku" class="form-control" required>
                        <option value="">Pilih Buku</option>
                        <?php
                        // Tampilkan semua buku, termasuk yang sedang dipinjam
                        $buku_query = mysqli_query($koneksi, "SELECT * FROM buku ORDER BY `judul buku` ASC");
                        while ($buku = mysqli_fetch_assoc($buku_query)) {
                            $selected = ($buku['id'] == $peminjaman['id_buku']) ? 'selected' : '';
                            $stok_info = ($buku['stok'] > 0) ? "Stok: {$buku['stok']}" : "Stok: 0 (Tidak Tersedia)";
                            echo "<option value='{$buku['id']}' $selected>{$buku['judul buku']} - $stok_info</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>📅 Tanggal Pinjam *</label>
                    <input type="date" name="tanggal_pinjam" class="form-control"
                           value="<?php echo $peminjaman['tanggal_pinjam']; ?>" required>
                </div>

                <div class="form-group">
                    <label>📅 Tanggal Kembali *</label>
                    <input type="date" name="tanggal_kembali" class="form-control"
                           value="<?php echo $peminjaman['tanggal_kembali']; ?>" required>
                </div>

                <div class="form-group">
                    <label>📊 Status *</label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="dipinjam" <?php echo ($peminjaman['status'] == 'dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                        <option value="dikembalikan" <?php echo ($peminjaman['status'] == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                        <option value="terlambat" <?php echo ($peminjaman['status'] == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                    </select>
                </div>

                <div class="form-group" id="tanggal_dikembalikan_group" style="display: none;">
                    <label>📅 Tanggal Dikembalikan</label>
                    <input type="date" id="tanggal_dikembalikan" name="tanggal_dikembalikan" class="form-control"
                           value="<?php echo $peminjaman['tanggal_dikembalikan']; ?>">
                </div>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: center; margin-top: 30px;">
                <button type="submit" class="btn" style="background: #28a745;">💾 Perbarui Peminjaman</button>
                <a href="peminjaman.php" class="btn" style="background: #6c757d;">↩️ Kembali</a>
            </div>
        </form>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
</div>

<script>
// Show/hide tanggal dikembalikan based on status
document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const tanggalDikembalikanGroup = document.getElementById('tanggal_dikembalikan_group');
    const tanggalDikembalikanInput = document.getElementById('tanggal_dikembalikan');
    
    if (status === 'dikembalikan') {
        tanggalDikembalikanGroup.style.display = 'block';
        tanggalDikembalikanInput.required = true;
        if (!tanggalDikembalikanInput.value) {
            tanggalDikembalikanInput.value = new Date().toISOString().split('T')[0];
        }
    } else {
        tanggalDikembalikanGroup.style.display = 'none';
        tanggalDikembalikanInput.required = false;
        if (status === 'dipinjam') {
            tanggalDikembalikanInput.value = '';
        }
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('status').dispatchEvent(new Event('change'));
});
</script>

</body>
</html>
