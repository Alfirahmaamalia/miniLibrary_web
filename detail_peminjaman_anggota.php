<?php
session_start();
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
$query = mysqli_query($koneksi, "SELECT p.*, 
                                a.nama as nama_peminjam, a.email as email_peminjam, a.telepon as telepon_peminjam,
                                b.`judul buku` as judul_buku, b.penulis as pengarang, b.tahun_terbit,
                                k.nama_kategori,
                                DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
                                FROM peminjaman p 
                                LEFT JOIN anggota a ON p.id_anggota = a.id 
                                LEFT JOIN buku b ON p.id_buku = b.id 
                                LEFT JOIN kategori k ON b.kategori = k.id
                                WHERE p.id = $id_peminjaman AND p.id_anggota = $anggota_id");

if (!$query || mysqli_num_rows($query) == 0) {
    $_SESSION['message'] = "Data peminjaman tidak ditemukan atau bukan milik Anda!";
    $_SESSION['message_type'] = "error";
    header("Location: pinjaman_saya.php");
    exit;
}

$peminjaman = mysqli_fetch_assoc($query);

// Fungsi untuk mengkonversi status peminjaman
function getStatusPeminjaman($status, $hari_terlambat = 0) {
    if ($status == 'dipinjam' && $hari_terlambat > 0) {
        return ['text' => 'Terlambat', 'class' => 'terlambat', 'icon' => '⚠️'];
    }
    
    switch($status) {
        case 'dipinjam':
            return ['text' => 'Dipinjam', 'class' => 'dipinjam', 'icon' => '📖'];
        case 'dikembalikan':
            return ['text' => 'Dikembalikan', 'class' => 'dikembalikan', 'icon' => '✅'];
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
$hari_terlambat = $peminjaman['hari_terlambat'] > 0 ? $peminjaman['hari_terlambat'] : 0;

if ($peminjaman['status'] == 'dipinjam' && $hari_terlambat == 0) {
    $sisa_hari = $tanggal_kembali->diff($today)->days;
    if ($today > $tanggal_kembali) {
        $sisa_hari = 0;
    }
}

$status_info = getStatusPeminjaman($peminjaman['status'], $hari_terlambat);
$denda = hitungDenda($peminjaman['tanggal_kembali'], $peminjaman['tanggal_dikembalikan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Peminjaman - MiniLibrary</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
        }

        .page-header h1 {
            font-size: 28px;
            color: #333;
            margin: 0;
        }

        .back-btn {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .back-btn:hover {
            background: linear-gradient(135deg, #545b62, #343a40);
            transform: translateY(-1px);
        }

        .detail-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .detail-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .detail-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
        }
        
        .detail-header h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }
        
        .loan-id {
            background: linear-gradient(135deg, #4e73df, #224abe);
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
            padding: 10px 16px;
            border-radius: 25px;
            font-size: 14px;
            font-weight: 600;
        }
        
        .status-badge.dipinjam {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
        }
        
        .status-badge.dikembalikan {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
        }
        
        .status-badge.terlambat {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
        }
        
        .info-section {
            margin-bottom: 30px;
        }
        
        .info-section h3 {
            font-size: 18px;
            color: #333;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 8px;
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
            color: #4e73df;
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
        
        .stats-cards {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            text-align: center;
            border-left: 4px solid #4e73df;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .actions-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        .actions-card h3 {
            margin: 0 0 20px 0;
            font-size: 18px;
            color: #333;
        }
        
        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        
        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-align: center;
            transition: all 0.3s;
        }
        
        .btn.success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }
        
        .btn.success:hover {
            background: linear-gradient(135deg, #218838, #1ea080);
            transform: translateY(-1px);
        }
        
        .btn.secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }
        
        .btn.secondary:hover {
            background: linear-gradient(135deg, #545b62, #343a40);
            transform: translateY(-1px);
        }
        
        .timeline {
            margin-top: 20px;
        }
        
        .timeline-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-left: 3px solid #eee;
            padding-left: 25px;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 20px;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            background: #4e73df;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
            font-weight: 600;
        }
        
        .timeline-content {
            font-size: 14px;
            color: #333;
            font-weight: 500;
        }
        
        @media (max-width: 768px) {
            .detail-container {
                grid-template-columns: 1fr;
            }
            
            .info-grid {
                grid-template-columns: 1fr;
            }

            .page-header {
                flex-direction: column;
                gap: 15px;
                align-items: flex-start;
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
    <div class="page-header">
        <h1>📖 Detail Peminjaman</h1>
        <a href="pinjaman_saya.php" class="back-btn">
            ← Kembali ke Daftar
        </a>
    </div>

    <div class="detail-container">
        <div class="detail-card">
            <div class="detail-header">
                <h2>Informasi Peminjaman</h2>
                <div class="loan-id">ID: <?php echo str_pad($peminjaman['id'], 3, '0', STR_PAD_LEFT); ?></div>
            </div>

            <!-- Status Section -->
            <div class="info-section">
                <h3>📊 Status Peminjaman</h3>
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

            <!-- Book Information -->
            <div class="info-section">
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
                    <div class="info-item">
                        <span class="info-label">Tahun Terbit</span>
                        <span class="info-value"><?php echo htmlspecialchars($peminjaman['tahun_terbit'] ?? 'Tidak Diketahui'); ?></span>
                    </div>
                </div>
            </div>

            <!-- Loan Timeline -->
            <div class="info-section">
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
            <div class="stats-cards">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $durasi_pinjam; ?></div>
                    <div class="stat-label">Durasi Pinjam (hari)</div>
                </div>
                
                <?php if ($denda > 0): ?>
                <div class="stat-card">
                    <div class="stat-number" style="color: #dc3545;">Rp <?php echo number_format($denda, 0, ',', '.'); ?></div>
                    <div class="stat-label">Denda</div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Actions Card -->
            <div class="actions-card">
                <h3>⚡ Aksi Tersedia</h3>
                <div class="action-buttons">
                    <?php if ($peminjaman['status'] == 'dipinjam'): ?>
                        <a href="kembalikan_buku.php?id=<?php echo $peminjaman['id']; ?>" class="btn success">
                            📚 Kembalikan Buku
                        </a>
                    <?php endif; ?>
                    
                    <a href="pinjaman_saya.php" class="btn secondary">
                        ↩️ Kembali ke Daftar
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
