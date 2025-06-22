<?php
session_start();
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit();
}

include '../koneksi/koneksi.php';

$anggota_id = $_SESSION['anggota_id'];

// Query untuk mengambil data peminjaman
$query = "SELECT p.*, 
                 b.`judul buku` as judul_buku, 
                 b.penulis, 
                 b.tahun_terbit,
                 k.nama_kategori,
                 DATEDIFF(CURDATE(), p.tanggal_kembali) as hari_terlambat
          FROM peminjaman p
          LEFT JOIN buku b ON p.id_buku = b.id
          LEFT JOIN kategori k ON b.kategori = k.id
          WHERE p.id_anggota = $anggota_id
          ORDER BY p.tanggal_pinjam DESC";

$result = mysqli_query($koneksi, $query);

// Fungsi untuk menghitung denda
function hitungDenda($tanggal_kembali, $tanggal_dikembalikan = null) {
    if (!$tanggal_dikembalikan) {
        $tanggal_dikembalikan = date('Y-m-d');
    }
    
    $tgl_kembali = new DateTime($tanggal_kembali);
    $tgl_dikembalikan = new DateTime($tanggal_dikembalikan);
    
    if ($tgl_dikembalikan > $tgl_kembali) {
        $selisih = $tgl_kembali->diff($tgl_dikembalikan);
        $hari_terlambat = $selisih->days;
        return $hari_terlambat * 1000; // Rp 1.000 per hari
    }
    
    return 0;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjaman Saya - MiniLibrary</title>
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
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 32px;
            color: #333;
            margin-bottom: 10px;
        }

        .page-header p {
            color: #666;
            font-size: 16px;
        }
         .footer {
            background: #4e73df;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 14px;
            border-top: 1px solid #eee;
            margin-top: 40px;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            margin-bottom: 25px;
            border: none;
            border-radius: 10px;
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            border-left: 4px solid #4e73df;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
        }

        .stat-label {
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            padding: 20px 25px;
        }

        .table-header h3 {
            font-size: 20px;
            margin: 0;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 15px 20px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background: #f8f9fc;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
        }

        .table td {
            font-size: 14px;
            color: #555;
        }

        .table tbody tr:hover {
            background: #f8f9fc;
        }

        /* Status Badges */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-dipinjam {
            background: #fff3cd;
            color: #856404;
        }

        .status-dikembalikan {
            background: #d4edda;
            color: #155724;
        }

        .status-terlambat {
            background: #f8d7da;
            color: #721c24;
        }

        /* Action Buttons */
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s;
            margin: 2px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #224abe, #1e3a8a);
            transform: translateY(-1px);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #218838, #1ea080);
            transform: translateY(-1px);
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: #212529;
        }

        .btn-warning:hover {
            background: linear-gradient(135deg, #e0a800, #ea6100);
            transform: translateY(-1px);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h3 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .empty-state p {
            font-size: 16px;
            margin-bottom: 30px;
        }

        .btn-large {
            padding: 15px 30px;
            font-size: 16px;
            border-radius: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .table-container {
                overflow-x: auto;
            }

            .table {
                min-width: 800px;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .navbar-nav {
                display: none;
            }
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="beranda_pengguna.php" class="navbar-brand">MiniLibrary</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="beranda_pengguna.php" class="nav-link">Beranda</a></li>
            <li class="nav-item"><a href="katalog.php" class="nav-link">Katalog Buku</a></li>
            <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link active">Pinjaman Saya</a></li>
            <li class="nav-item"><a href="profil.php" class="nav-link">Profil</a></li>
            <li class="nav-item"><a href="logout_anggota.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <h1>📚 Pinjaman Saya</h1>
        <p>Kelola dan pantau semua buku yang Anda pinjam</p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo ($_SESSION['message_type'] == 'success') ? 'success' : 'error'; ?>">
            <?php 
            echo $_SESSION['message']; 
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
            ?>
        </div>
    <?php endif; ?>

    <?php if ($result && mysqli_num_rows($result) > 0): ?>
        <!-- Statistics -->
        <?php
        $total_pinjaman = 0;
        $sedang_dipinjam = 0;
        $sudah_dikembalikan = 0;
        $terlambat = 0;
        $total_denda = 0;

        // Hitung statistik
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            $total_pinjaman++;
            if ($row['status'] == 'dipinjam') {
                $sedang_dipinjam++;
                if ($row['hari_terlambat'] > 0) {
                    $terlambat++;
                    $total_denda += hitungDenda($row['tanggal_kembali']);
                }
            } else {
                $sudah_dikembalikan++;
                if ($row['denda'] > 0) {
                    $total_denda += $row['denda'];
                }
            }
        }
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_pinjaman; ?></div>
                <div class="stat-label">Total Pinjaman</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $sedang_dipinjam; ?></div>
                <div class="stat-label">Sedang Dipinjam</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $sudah_dikembalikan; ?></div>
                <div class="stat-label">Sudah Dikembalikan</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" style="color: #dc3545;"><?php echo $terlambat; ?></div>
                <div class="stat-label">Terlambat</div>
            </div>
        </div>

        <!-- Table -->
        <div class="table-container">
            <div class="table-header">
                <h3>📋 Riwayat Peminjaman</h3>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Status</th>
                        <th>Denda</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)):
                        $denda = 0;
                        $status_class = 'status-' . $row['status'];
                        $status_text = ucfirst($row['status']);
                        $status_icon = '📖';

                        if ($row['status'] == 'dipinjam') {
                            if ($row['hari_terlambat'] > 0) {
                                $status_class = 'status-terlambat';
                                $status_text = 'Terlambat';
                                $status_icon = '⚠️';
                                $denda = hitungDenda($row['tanggal_kembali']);
                            }
                        } else {
                            $status_icon = '✅';
                            $denda = $row['denda'] ?? 0;
                        }
                    ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600; margin-bottom: 5px;">
                                <?php echo htmlspecialchars($row['judul_buku']); ?>
                            </div>
                            <div style="font-size: 12px; color: #666;">
                                <?php echo htmlspecialchars($row['penulis']); ?>
                                <?php if ($row['nama_kategori']): ?>
                                    • <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_pinjam'])); ?></td>
                        <td>
                            <?php echo date('d/m/Y', strtotime($row['tanggal_kembali'])); ?>
                            <?php if ($row['status'] == 'dipinjam' && $row['hari_terlambat'] > 0): ?>
                                <br><small style="color: #dc3545;">Terlambat <?php echo $row['hari_terlambat']; ?> hari</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-badge <?php echo $status_class; ?>">
                                <?php echo $status_icon; ?> <?php echo $status_text; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($denda > 0): ?>
                                <span style="color: #dc3545; font-weight: 600;">
                                    Rp <?php echo number_format($denda, 0, ',', '.'); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #28a745;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="detail_peminjaman_anggota.php?id=<?php echo $row['id']; ?>" class="btn btn-primary">
                                👁️ Detail
                            </a>
                            <?php if ($row['status'] == 'dipinjam'): ?>
                                <a href="kembalikan_buku.php?id=<?php echo $row['id']; ?>" class="btn btn-success">
                                    📚 Kembalikan
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>
        <!-- Empty State -->
        <div class="table-container">
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <h3>Belum Ada Peminjaman</h3>
                <p>Anda belum meminjam buku apapun. Mulai jelajahi koleksi kami!</p>
                <a href="katalog.php" class="btn btn-primary btn-large">
                    🔍 Jelajahi Katalog
                </a>
            </div>
        </div>
    <?php endif; ?>
      <!-- Footer -->
            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
            </div>
</div>

</body>
</html>
