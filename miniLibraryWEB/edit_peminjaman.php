<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

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
    <style>
        .form-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 700px;
        }
        
        .form-header {
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        
        .loan-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #007bff;
        }
        
        .loan-info strong {
            color: #495057;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .form-group.conditional {
            display: none;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn.primary {
            background: #007bff;
            color: white;
        }
        
        .btn.primary:hover {
            background: #0056b3;
        }
        
        .btn.secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn.secondary:hover {
            background: #545b62;
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
        
        .warning-box {
            background: #fff3cd;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #ffeaa7;
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
        <h1>Edit Peminjaman</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="<?php echo ($_SESSION['message_type'] == 'success') ? 'success-message' : 'error-message'; ?>">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="form-header">
            <h2>Edit Data Peminjaman</h2>
        </div>
        
        <div class="loan-info">
            <strong>ID Peminjaman:</strong> <?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?> | 
            <strong>Peminjam:</strong> <?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?> | 
            <strong>Buku:</strong> <?php echo htmlspecialchars($peminjaman['judul_buku']); ?>
        </div>

        <div class="warning-box">
            <strong>Perhatian:</strong> Mengubah data peminjaman dapat mempengaruhi stok buku. Pastikan data yang dimasukkan sudah benar.
        </div>

        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label for="id_anggota">Anggota *</label>
                    <select id="id_anggota" name="id_anggota" required>
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
                    <label for="id_buku">Buku *</label>
                    <select id="id_buku" name="id_buku" required>
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
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="tanggal_pinjam">Tanggal Pinjam *</label>
                    <input type="date" id="tanggal_pinjam" name="tanggal_pinjam" 
                           value="<?php echo $peminjaman['tanggal_pinjam']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="tanggal_kembali">Tanggal Kembali *</label>
                    <input type="date" id="tanggal_kembali" name="tanggal_kembali" 
                           value="<?php echo $peminjaman['tanggal_kembali']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="status">Status *</label>
                    <select id="status" name="status" required>
                        <option value="dipinjam" <?php echo ($peminjaman['status'] == 'dipinjam') ? 'selected' : ''; ?>>Dipinjam</option>
                        <option value="dikembalikan" <?php echo ($peminjaman['status'] == 'dikembalikan') ? 'selected' : ''; ?>>Dikembalikan</option>
                        <option value="terlambat" <?php echo ($peminjaman['status'] == 'terlambat') ? 'selected' : ''; ?>>Terlambat</option>
                    </select>
                </div>

                <div class="form-group conditional" id="tanggal_dikembalikan_group">
                    <label for="tanggal_dikembalikan">Tanggal Dikembalikan</label>
                    <input type="date" id="tanggal_dikembalikan" name="tanggal_dikembalikan" 
                           value="<?php echo $peminjaman['tanggal_dikembalikan']; ?>">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary">💾 Perbarui Peminjaman</button>
                <a href="peminjaman.php" class="btn secondary">↩️ Kembali</a>
            </div>
        </form>
    </div>
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
