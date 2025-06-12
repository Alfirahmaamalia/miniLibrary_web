<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
include 'koneksi.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: peminjaman.php");
    exit;
}

$id_peminjaman = (int)$_GET['id'];

// Check if loan exists and get details
$check_query = mysqli_query($koneksi, "SELECT p.*, a.nama as nama_peminjam, b.`judul buku` as judul_buku 
                                      FROM peminjaman p 
                                      LEFT JOIN anggota a ON p.id_anggota = a.id 
                                      LEFT JOIN buku b ON p.id_buku = b.id 
                                      WHERE p.id = $id_peminjaman");

if (!$check_query || mysqli_num_rows($check_query) == 0) {
    $_SESSION['message'] = "Data peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: peminjaman.php");
    exit;
}

$peminjaman = mysqli_fetch_assoc($check_query);

// Handle deletion
if ($_POST && isset($_POST['confirm_delete'])) {
    // Jika buku masih dalam status dipinjam, kembalikan stok
    if ($peminjaman['status'] == 'dipinjam') {
        mysqli_query($koneksi, "UPDATE buku SET stok = stok + 1 WHERE id = {$peminjaman['id_buku']}");
    }
    
    // Delete the loan record
    $delete_query = mysqli_query($koneksi, "DELETE FROM peminjaman WHERE id = $id_peminjaman");
    
    if ($delete_query) {
        $_SESSION['message'] = "Data peminjaman berhasil dihapus!";
        $_SESSION['message_type'] = "success";
        header("Location: peminjaman.php");
        exit;
    } else {
        $_SESSION['message'] = "Gagal menghapus data peminjaman: " . mysqli_error($koneksi);
        $_SESSION['message_type'] = "error";
    }
}

// Fungsi untuk mengkonversi status peminjaman
function getStatusInfo($status) {
    switch($status) {
        case 'dipinjam':
            return ['text' => 'Dipinjam', 'class' => 'dipinjam', 'icon' => '📖'];
        case 'dikembalikan':
            return ['text' => 'Dikembalikan', 'class' => 'dikembalikan', 'icon' => '✅'];
        case 'terlambat':
            return ['text' => 'Terlambat', 'class' => 'terlambat', 'icon' => '⚠️'];
        default:
            return ['text' => 'Tidak Diketahui', 'class' => 'unknown', 'icon' => '❓'];
    }
}

$status_info = getStatusInfo($peminjaman['status']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Hapus Peminjaman - MiniLibrary</title>
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
            text-align: center;
            margin-bottom: 30px;
        }
        
        .form-header h2 {
            font-size: 24px;
            color: #dc3545;
            margin: 0;
        }
        
        .warning-icon {
            font-size: 48px;
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .loan-info {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #dc3545;
        }
        
        .loan-info h3 {
            margin-top: 0;
            color: #333;
            font-size: 18px;
            margin-bottom: 20px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 15px;
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
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        .info-value.highlight {
            color: #007bff;
            font-weight: 600;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 5px;
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
        
        .confirmation-text {
            text-align: center;
            margin-bottom: 25px;
            font-size: 16px;
            color: #333;
        }
        
        .warning-list {
            background: #fff3cd;
            color: #856404;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            border: 1px solid #ffeaa7;
        }
        
        .warning-list h4 {
            margin-top: 0;
            color: #856404;
            font-size: 16px;
        }
        
        .warning-list ul {
            margin: 10px 0 0 20px;
            padding: 0;
        }
        
        .warning-list li {
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
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
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
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
        
        @media (max-width: 768px) {
            .info-grid {
                grid-template-columns: 1fr;
            }
            
            .form-actions {
                flex-direction: column;
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
        <h1>Hapus Peminjaman</h1>
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
        <div class="warning-icon">⚠️</div>
        <div class="form-header">
            <h2>Konfirmasi Penghapusan</h2>
        </div>

        <div class="warning-list">
            <h4>⚠️ Peringatan Penting:</h4>
            <ul>
                <li>Tindakan ini akan menghapus data peminjaman secara permanen</li>
                <li>Data yang sudah dihapus tidak dapat dikembalikan</li>
                <?php if ($peminjaman['status'] == 'dipinjam'): ?>
                <li><strong>Stok buku akan dikembalikan</strong> karena status masih "Dipinjam"</li>
                <?php endif; ?>
                <li>Riwayat peminjaman akan hilang dari sistem</li>
            </ul>
        </div>
        
        <div class="loan-info">
            <h3>Detail Peminjaman yang akan dihapus:</h3>
            
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">ID Peminjaman</span>
                    <span class="info-value highlight"><?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="status-badge <?php echo $status_info['class']; ?>">
                        <?php echo $status_info['icon']; ?> <?php echo $status_info['text']; ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Nama Peminjam</span>
                    <span class="info-value"><?php echo htmlspecialchars($peminjaman['nama_peminjam'] ?? 'Tidak Diketahui'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Judul Buku</span>
                    <span class="info-value"><?php echo htmlspecialchars($peminjaman['judul_buku'] ?? 'Tidak Diketahui'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Pinjam</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Tanggal Kembali</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></span>
                </div>
                <?php if ($peminjaman['tanggal_dikembalikan']): ?>
                <div class="info-item">
                    <span class="info-label">Tanggal Dikembalikan</span>
                    <span class="info-value"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_dikembalikan'])); ?></span>
                </div>
                <?php endif; ?>
                <?php if ($peminjaman['denda'] > 0): ?>
                <div class="info-item">
                    <span class="info-label">Denda</span>
                    <span class="info-value" style="color: #dc3545;">Rp <?php echo number_format($peminjaman['denda'], 0, ',', '.'); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="confirmation-text">
            <p><strong>Apakah Anda yakin ingin menghapus data peminjaman ini?</strong></p>
            <p>Tindakan ini tidak dapat dibatalkan!</p>
        </div>

        <form method="POST">
            <div class="form-actions">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    🗑️ Ya, Hapus Peminjaman
                </button>
                <a href="peminjaman.php" class="btn btn-secondary">
                    ↩️ Batal
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
