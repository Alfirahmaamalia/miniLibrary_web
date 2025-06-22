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

// Fetch loan data with all related information
$query = mysqli_query($koneksi, "SELECT p.*, 
                                a.nama as nama_peminjam, a.email as email_peminjam, a.telepon as telepon_peminjam,
                                b.`judul buku` as judul_buku, b.penulis as pengarang,
                                k.nama_kategori
                                FROM peminjaman p 
                                LEFT JOIN anggota a ON p.id_anggota = a.id 
                                LEFT JOIN buku b ON p.id_buku = b.id 
                                LEFT JOIN kategori k ON b.kategori = k.id
                                WHERE p.id = $id_peminjaman");

if (!$query || mysqli_num_rows($query) == 0) {
    $_SESSION['message'] = "Data peminjaman tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: peminjaman.php");
    exit;
}

$peminjaman = mysqli_fetch_assoc($query);

// Fungsi untuk mengkonversi status peminjaman
function getStatusPeminjaman($status) {
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

// Hitung durasi peminjaman
$tanggal_pinjam = new DateTime($peminjaman['tanggal_pinjam']);
$tanggal_kembali = new DateTime($peminjaman['tanggal_kembali']);
$durasi_pinjam = $tanggal_pinjam->diff($tanggal_kembali)->days;

// Hitung sisa hari atau keterlambatan
$today = new DateTime();
$sisa_hari = 0;
$hari_terlambat = 0;

if ($peminjaman['status'] == 'dipinjam') {
    if ($today <= $tanggal_kembali) {
        $sisa_hari = $today->diff($tanggal_kembali)->days;
    } else {
        $hari_terlambat = $tanggal_kembali->diff($today)->days;
    }
} elseif ($peminjaman['tanggal_dikembalikan']) {
    $tanggal_dikembalikan = new DateTime($peminjaman['tanggal_dikembalikan']);
    if ($tanggal_dikembalikan > $tanggal_kembali) {
        $hari_terlambat = $tanggal_kembali->diff($tanggal_dikembalikan)->days;
    }
}

$status_info = getStatusPeminjaman($peminjaman['status']);
$denda = hitungDenda($peminjaman['tanggal_kembali'], $peminjaman['tanggal_dikembalikan']);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Detail Peminjaman - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .detail-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .loan-id {
            background: #007bff;
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
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
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
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
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        .info-value.highlight {
            color: #007bff;
            font-weight: 600;
        }
        
        .info-value.danger {
            color: #dc3545;
            font-weight: 600;
        }
        
        .info-value.success {
            color: #28a745;
            font-weight: 600;
        }
        
        .timeline {
            margin-top: 20px;
        }
        
        .timeline-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-left: 2px solid #eee;
            padding-left: 20px;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 15px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #007bff;
        }
        
        .timeline-item.completed::before {
            background: #28a745;
        }
        
        .timeline-item.overdue::before {
            background: #dc3545;
        }
        
        .timeline-date {
            font-size: 12px;
            color: #666;
            min-width: 80px;
        }
        
        .timeline-content {
            font-size: 14px;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .detail-container {
                grid-template-columns: 1fr;
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
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-text">
            <h1>Detail Peminjaman</h1>
            <p>Informasi lengkap tentang peminjaman buku.</p>
            <a href="peminjaman.php" class="btn">← Kembali ke Daftar</a>
        </div>
        <div class="welcome-image"></div>
    </div>

    <!-- Detail Section -->
    <div class="content-section">
        <div class="section-header">
            <h2>📋 Informasi Peminjaman</h2>
            <div class="loan-id">ID: <?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div class="detail-container">
            <div class="content-section">
                <!-- Status Section -->
                <div style="margin-bottom: 30px;">
                    <h3>Status Peminjaman</h3>
                    <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 15px;">
                        <span class="status-badge <?php echo $status_info['class']; ?>">
                            <?php echo $status_info['icon']; ?> <?php echo $status_info['text']; ?>
                        </span>
                        <?php if ($sisa_hari > 0): ?>
                            <span class="info-value success">Sisa <?php echo $sisa_hari; ?> hari</span>
                        <?php elseif ($hari_terlambat > 0): ?>
                            <span class="info-value danger">Terlambat <?php echo $hari_terlambat; ?> hari</span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Member Information -->
                <div style="margin-bottom: 30px;">
                    <h3>👤 Informasi Peminjam</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nama Lengkap</span>
                            <span class="info-value highlight"><?php echo htmlspecialchars($peminjaman['nama_peminjam'] ?? 'Tidak Diketahui'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($peminjaman['email_peminjam'] ?? 'Tidak Diketahui'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Telepon</span>
                            <span class="info-value"><?php echo htmlspecialchars($peminjaman['telepon_peminjam'] ?? 'Tidak Diketahui'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Book Information -->
                <div style="margin-bottom: 30px;">
                    <h3>📚 Informasi Buku</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Judul Buku</span>
                            <span class="info-value highlight"><?php echo htmlspecialchars($peminjaman['judul_buku'] ?? 'Tidak Diketahui'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Penulis</span>
                            <span class="info-value"><?php echo htmlspecialchars($peminjaman['pengarang'] ?? 'Tidak Diketahui'); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="info-label">Kategori</span>
                            <span class="info-value"><?php echo htmlspecialchars($peminjaman['nama_kategori'] ?? 'Tidak Diketahui'); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Loan Timeline -->
                <div>
                    <h3>📅 Timeline Peminjaman</h3>
                    <div class="timeline">
                        <div class="timeline-item completed">
                            <span class="timeline-date"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_pinjam'])); ?></span>
                            <span class="timeline-content">Buku dipinjam</span>
                        </div>
                        <div class="timeline-item <?php echo ($peminjaman['status'] == 'dikembalikan') ? 'completed' : (($hari_terlambat > 0) ? 'overdue' : ''); ?>">
                            <span class="timeline-date"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_kembali'])); ?></span>
                            <span class="timeline-content">Batas waktu pengembalian</span>
                        </div>
                        <?php if ($peminjaman['tanggal_dikembalikan']): ?>
                        <div class="timeline-item completed">
                            <span class="timeline-date"><?php echo date('d/m/Y', strtotime($peminjaman['tanggal_dikembalikan'])); ?></span>
                            <span class="timeline-content">Buku dikembalikan</span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div>
                <!-- Summary Cards -->
                <div class="stats-row">
                    <div class="stat-card">
                        <div class="stat-icon books">📅</div>
                        <div class="stat-info">
                            <h3>Durasi Pinjam</h3>
                            <p><?php echo $durasi_pinjam; ?> hari</p>
                        </div>
                    </div>
                    
                    <?php if ($denda > 0): ?>
                    <div class="stat-card">
                        <div class="stat-icon overdue">💰</div>
                        <div class="stat-info">
                            <h3>Denda</h3>
                            <p style="color: #dc3545;">Rp <?php echo number_format($denda, 0, ',', '.'); ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Actions Card -->
                <div class="content-section">
                    <div class="section-header">
                        <h2>⚡ Aksi Cepat</h2>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        <?php if ($peminjaman['status'] == 'dipinjam' || $peminjaman['status'] == 'terlambat'): ?>
                            <a href="kembalikan_buku.php?id=<?php echo $peminjaman['id']; ?>" class="btn" style="background: #28a745;">
                                📚 Kembalikan Buku
                            </a>
                        <?php endif; ?>
                        
                        <a href="edit_peminjaman.php?id=<?php echo $peminjaman['id']; ?>" class="btn" style="background: #007bff;">
                            ✏️ Edit Peminjaman
                        </a>
                        
                        <a href="hapus_peminjaman.php?id=<?php echo $peminjaman['id']; ?>" 
                           class="btn" style="background: #dc3545;"
                           onclick="return confirm('Apakah Anda yakin ingin menghapus data peminjaman ini?')">
                            🗑️ Hapus Peminjaman
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<div class="footer">
    <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
</div>

</body>
</html>
