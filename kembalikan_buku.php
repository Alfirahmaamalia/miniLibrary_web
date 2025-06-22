<?php
session_start();
// Cek session anggota, bukan admin
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit;
}
include '../koneksi/koneksi.php';

// Get loan ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: pinjaman_saya.php");
    exit;
}

$id_peminjaman = (int)$_GET['id'];
$anggota_id = $_SESSION['anggota_id'];

// Fetch loan data - hanya milik anggota yang login
$query = mysqli_query($koneksi, "SELECT p.*, a.nama as nama_peminjam, b.`judul buku` as judul_buku 
                                FROM peminjaman p 
                                LEFT JOIN anggota a ON p.id_anggota = a.id 
                                LEFT JOIN buku b ON p.id_buku = b.id 
                                WHERE p.id = $id_peminjaman AND p.id_anggota = $anggota_id");

if (!$query || mysqli_num_rows($query) == 0) {
    $_SESSION['message'] = "Data peminjaman tidak ditemukan atau bukan milik Anda!";
    $_SESSION['message_type'] = "error";
    header("Location: pinjaman_saya.php");
    exit;
}

$peminjaman = mysqli_fetch_assoc($query);

// Cek apakah buku masih dipinjam
if ($peminjaman['status'] != 'dipinjam') {
    $_SESSION['message'] = "Buku ini sudah dikembalikan!";
    $_SESSION['message_type'] = "error";
    header("Location: pinjaman_saya.php");
    exit;
}

// Handle return submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_return'])) {
    $tanggal_dikembalikan = date('Y-m-d');
    $denda = 0;
    
    // Hitung denda jika terlambat
    if ($tanggal_dikembalikan > $peminjaman['tanggal_kembali']) {
        $tgl_kembali = new DateTime($peminjaman['tanggal_kembali']);
        $tgl_dikembalikan = new DateTime($tanggal_dikembalikan);
        $selisih = $tgl_kembali->diff($tgl_dikembalikan);
        $hari_terlambat = $selisih->days;
        $denda = $hari_terlambat * 1000; // Rp 1.000 per hari
    }
    
    // Update status peminjaman
    $update_query = mysqli_query($koneksi, "UPDATE peminjaman SET 
                                           status = 'dikembalikan', 
                                           tanggal_dikembalikan = '$tanggal_dikembalikan',
                                           denda = $denda 
                                           WHERE id = $id_peminjaman AND id_anggota = $anggota_id");
    
    if ($update_query) {
        // Update stok buku
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id = {$peminjaman['id_buku']}");
        
        $_SESSION['message'] = "Buku berhasil dikembalikan!" . ($denda > 0 ? " Denda: Rp " . number_format($denda, 0, ',', '.') : "");
        $_SESSION['message_type'] = "success";
        header("Location: pinjaman_saya.php");
        exit;
    } else {
        $_SESSION['message'] = "Gagal mengembalikan buku: " . mysqli_error($koneksi);
        $_SESSION['message_type'] = "error";
    }
}

// Hitung denda jika ada
$denda_preview = 0;
$today = date('Y-m-d');
if ($today > $peminjaman['tanggal_kembali']) {
    $tgl_kembali = new DateTime($peminjaman['tanggal_kembali']);
    $tgl_today = new DateTime($today);
    $selisih = $tgl_kembali->diff($tgl_today);
    $hari_terlambat = $selisih->days;
    $denda_preview = $hari_terlambat * 1000;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kembalikan Buku - MiniLibrary</title>
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

        /* Navbar - sama seperti index.php */
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

        /* Container */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .form-card {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .return-icon {
            font-size: 64px;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 28px;
            color: #333;
            margin-bottom: 10px;
        }
        
        .form-header p {
            color: #666;
            font-size: 16px;
        }
        
        .loan-info {
            background: linear-gradient(135deg, #f8f9ff, #e8f0ff);
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            border-left: 4px solid #28a745;
        }
        
        .loan-info h3 {
            margin-top: 0;
            color: #333;
            margin-bottom: 15px;
            font-size: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }
        
        .info-value {
            font-size: 16px;
            color: #333;
            font-weight: 600;
        }
        
        .denda-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            border: 2px solid #ffc107;
            text-align: center;
        }
        
        .denda-warning strong {
            font-size: 18px;
            display: block;
            margin-bottom: 10px;
        }
        
        .confirmation-text {
            text-align: center;
            margin-bottom: 30px;
            font-size: 18px;
            color: #333;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }
        
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-align: center;
            transition: all 0.3s;
            min-width: 180px;
            justify-content: center;
        }
        
        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1ea080);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-secondary:hover {
            background: linear-gradient(135deg, #545b62, #343a40);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
        }
        
        .alert {
            padding: 20px 25px;
            margin-bottom: 25px;
            border: none;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-card {
                padding: 25px;
            }
            
            .form-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="index.php" class="navbar-brand">MiniLibrary</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="index.php" class="nav-link">Beranda</a></li>
            <li class="nav-item"><a href="katalog.php" class="nav-link">Katalog Buku</a></li>
            <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link active">Pinjaman Saya</a></li>
            <li class="nav-item"><a href="profil.php" class="nav-link">Profil</a></li>
            <li class="nav-item"><a href="logout_anggota.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['message_type'] == 'success') ? 'success' : 'error'; ?>">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <div class="form-card">
        <div class="return-icon">📚</div>
        <div class="form-header">
            <h2>Kembalikan Buku</h2>
            <p>Konfirmasi pengembalian buku yang Anda pinjam</p>
        </div>

        <?php if ($denda_preview > 0): ?>
            <div class="denda-warning">
                <strong>⚠️ Peringatan Keterlambatan!</strong>
                Buku ini terlambat dikembalikan.<br>
                <strong>Denda yang harus dibayar: Rp <?php echo number_format($denda_preview, 0, ',', '.'); ?></strong>
            </div>
        <?php endif; ?>
        
        <div class="loan-info">
            <h3>📋 Detail Peminjaman</h3>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">ID Peminjaman</span>
                    <span class="info-value">#<?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nama Peminjam</span>
                    <span class="info-value"><?php echo htmlspecialchars($peminjaman['nama_peminjam']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Judul Buku</span>
                    <span class="info-value"><?php echo htmlspecialchars($peminjaman['judul_buku']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pinjam</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Batas Kembali</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Kembali</span>
                    <span class="info-value"><?php echo date('d/m/Y'); ?> (Hari ini)</span>
                </div>
                <?php if ($denda_preview > 0): ?>
                <div class="info-item">
                    <span class="info-label">Denda</span>
                    <span class="info-value" style="color: #dc3545;">Rp <?php echo number_format($denda_preview, 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="confirmation-text">
            <strong>🤔 Apakah Anda yakin ingin mengembalikan buku ini?</strong><br>
            <small style="color: #666; margin-top: 10px; display: block;">
                Pastikan buku dalam kondisi baik dan tidak rusak
            </small>
        </div>

        <form method="POST">
            <div class="form-actions">
                <button type="submit" name="confirm_return" class="btn btn-success">
                    ✅ Ya, Kembalikan Buku
                </button>
                <a href="pinjaman_saya.php" class="btn btn-secondary">
                    ❌ Batal
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
