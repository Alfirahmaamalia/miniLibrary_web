<?php
session_start();
include '../koneksi/koneksi.php';

// Cek apakah user sudah login sebagai anggota
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit;
}

// Get book ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: katalog.php");
    exit;
}

$id_buku = (int)$_GET['id'];
$anggota_id = $_SESSION['anggota_id'];

$error = "";
$success = "";

// Fetch book data
$query = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori 
                                FROM buku b 
                                LEFT JOIN kategori k ON b.kategori = k.id 
                                WHERE b.id = $id_buku");

if (!$query || mysqli_num_rows($query) == 0) {
    header("Location: katalog.php");
    exit;
}

$buku = mysqli_fetch_assoc($query);

// Cek apakah buku tersedia
$is_available = ($buku['status'] == 1 && $buku['stok'] > 0);

// Cek apakah anggota sudah meminjam buku ini
$check_loan = mysqli_query($koneksi, "SELECT * FROM peminjaman 
                                     WHERE id_anggota = $anggota_id 
                                     AND id_buku = $id_buku 
                                     AND status = 'dipinjam'");
$already_borrowed = mysqli_num_rows($check_loan) > 0;

// Cek jumlah buku yang sedang dipinjam anggota
$current_loans = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE id_anggota = $anggota_id AND status = 'dipinjam'"))['total'];
$max_loans = 3; // Maksimal 3 buku per anggota

// Proses peminjaman
if (isset($_POST['pinjam']) && $is_available && !$already_borrowed) {
    if ($current_loans >= $max_loans) {
        $error = "Anda sudah mencapai batas maksimal peminjaman ($max_loans buku). Silakan kembalikan buku terlebih dahulu.";
    } else {
        $tanggal_pinjam = date('Y-m-d');
        $tanggal_kembali = date('Y-m-d', strtotime('+7 days')); // 7 hari dari sekarang
        
        // Mulai transaksi
        mysqli_begin_transaction($koneksi);
        
        try {
            // Insert peminjaman
            $insert_loan = "INSERT INTO peminjaman (id_anggota, id_buku, tanggal_pinjam, tanggal_kembali, status) 
                           VALUES ($anggota_id, $id_buku, '$tanggal_pinjam', '$tanggal_kembali', 'dipinjam')";
            
            if (!mysqli_query($koneksi, $insert_loan)) {
                throw new Exception("Gagal menyimpan data peminjaman: " . mysqli_error($koneksi));
            }
            
            // Update stok buku
            $update_stock = "UPDATE buku SET stok = stok - 1 WHERE id = $id_buku";
            if (!mysqli_query($koneksi, $update_stock)) {
                throw new Exception("Gagal mengupdate stok buku: " . mysqli_error($koneksi));
            }
            
            // Commit transaksi
            mysqli_commit($koneksi);
            
            $success = "Buku berhasil dipinjam! Harap kembalikan sebelum tanggal " . date('d/m/Y', strtotime($tanggal_kembali));
            
            // Update data buku untuk tampilan
            $buku['stok'] = $buku['stok'] - 1;
            $already_borrowed = true;
            
        } catch (Exception $e) {
            // Rollback transaksi
            mysqli_rollback($koneksi);
            $error = $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pinjam Buku - <?php echo htmlspecialchars($buku['judul buku']); ?></title>
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

        /* Navbar */
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

        /* Main Content */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .loan-form {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }

        .form-header h1 {
            font-size: 24px;
            color: #333;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
        }

        .book-summary {
            display: flex;
            gap: 20px;
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fc;
            border-radius: 8px;
        }

        .book-cover-small {
            width: 80px;
            height: 120px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            flex-shrink: 0;
        }

        .book-details {
            flex: 1;
        }

        .book-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .book-meta {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            font-size: 14px;
        }

        .meta-item {
            display: flex;
            justify-content: space-between;
        }

        .meta-label {
            color: #666;
        }

        .meta-value {
            font-weight: 500;
            color: #333;
        }

        .loan-info {
            background: #e7f3ff;
            border: 1px solid #b8daff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }

        .loan-info h3 {
            color: #004085;
            margin-bottom: 15px;
        }

        .loan-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .loan-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .loan-label {
            color: #004085;
            font-weight: 500;
        }

        .loan-value {
            color: #004085;
            font-weight: 600;
        }

        .alert {
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .alert-warning {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .alert-info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4e73df;
            color: white;
        }

        .btn-primary:hover {
            background: #2e59d9;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn:disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .terms {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .terms h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .terms ul {
            margin-left: 20px;
            color: #666;
        }

        .terms li {
            margin-bottom: 8px;
        }

        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        .checkbox-group label {
            color: #333;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .book-summary {
                flex-direction: column;
                text-align: center;
            }

            .book-meta {
                grid-template-columns: 1fr;
            }

            .loan-details {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
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
                <li class="nav-item"><a href="katalog.php" class="nav-link active">Katalog Buku</a></li>
                <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link">Pinjaman Saya</a></li>
                <li class="nav-item"><a href="profil.php" class="nav-link">Profil</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <div class="loan-form">
            <div class="form-header">
                <h1>Pinjam Buku</h1>
                <p>Konfirmasi peminjaman buku dari perpustakaan</p>
            </div>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <strong>Berhasil!</strong> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <strong>Error!</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Book Summary -->
            <div class="book-summary">
                <div class="book-cover-small">📚</div>
                <div class="book-details">
                    <h3 class="book-title"><?php echo htmlspecialchars($buku['judul buku']); ?></h3>
                    <p class="book-author">oleh <?php echo htmlspecialchars($buku['penulis']); ?></p>
                    <div class="book-meta">
                        <div class="meta-item">
                            <span class="meta-label">Kategori:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Tahun:</span>
                            <span class="meta-value"><?php echo htmlspecialchars($buku['tahun_terbit']); ?></span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Stok:</span>
                            <span class="meta-value"><?php echo $buku['stok']; ?> unit</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Status:</span>
                            <span class="meta-value"><?php echo $is_available ? 'Tersedia' : 'Tidak Tersedia'; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($already_borrowed): ?>
                <div class="alert alert-info">
                    <strong>Info:</strong> Anda sudah meminjam buku ini. Silakan kembalikan terlebih dahulu jika ingin meminjam lagi.
                </div>
            <?php elseif (!$is_available): ?>
                <div class="alert alert-warning">
                    <strong>Maaf!</strong> Buku ini sedang tidak tersedia untuk dipinjam.
                </div>
            <?php elseif ($current_loans >= $max_loans): ?>
                <div class="alert alert-warning">
                    <strong>Batas Tercapai!</strong> Anda sudah mencapai batas maksimal peminjaman (<?php echo $max_loans; ?> buku). Silakan kembalikan buku terlebih dahulu.
                </div>
            <?php else: ?>
                <!-- Loan Information -->
                <div class="loan-info">
                    <h3>📋 Informasi Peminjaman</h3>
                    <div class="loan-details">
                        <div class="loan-item">
                            <span class="loan-label">Tanggal Pinjam:</span>
                            <span class="loan-value"><?php echo date('d/m/Y'); ?></span>
                        </div>
                        <div class="loan-item">
                            <span class="loan-label">Tanggal Kembali:</span>
                            <span class="loan-value"><?php echo date('d/m/Y', strtotime('+7 days')); ?></span>
                        </div>
                        <div class="loan-item">
                            <span class="loan-label">Durasi Pinjam:</span>
                            <span class="loan-value">7 hari</span>
                        </div>
                        <div class="loan-item">
                            <span class="loan-label">Peminjaman Aktif:</span>
                            <span class="loan-value"><?php echo $current_loans; ?>/<?php echo $max_loans; ?> buku</span>
                        </div>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="terms">
                    <h4>📜 Syarat dan Ketentuan Peminjaman</h4>
                    <ul>
                        <li>Buku harus dikembalikan tepat waktu (maksimal 7 hari)</li>
                        <li>Keterlambatan pengembalian dikenakan denda Rp 2.000 per hari</li>
                        <li>Maksimal peminjaman <?php echo $max_loans; ?> buku per anggota</li>
                        <li>Buku yang hilang atau rusak akan dikenakan ganti rugi</li>
                        <li>Jaga kebersihan dan kondisi buku selama peminjaman</li>
                        <li>Tidak diperbolehkan meminjamkan buku kepada orang lain</li>
                    </ul>
                </div>

                <form method="POST" action="">
                    <div class="checkbox-group">
                        <input type="checkbox" id="agree" name="agree" required>
                        <label for="agree">Saya setuju dengan syarat dan ketentuan peminjaman di atas</label>
                    </div>

                    <div class="form-actions">
                        <button type="submit" name="pinjam" class="btn btn-primary">
                            📚 Konfirmasi Peminjaman
                        </button>
                        <a href="detail_buku_anggota.php?id=<?php echo $buku['id']; ?>" class="btn btn-secondary">
                            ← Kembali ke Detail
                        </a>
                    </div>
                </form>
            <?php endif; ?>

            <?php if ($already_borrowed || !$is_available || $current_loans >= $max_loans): ?>
                <div class="form-actions">
                    <a href="detail_buku_anggota.php?id=<?php echo $buku['id']; ?>" class="btn btn-secondary">
                        ← Kembali ke Detail
                    </a>
                    <a href="katalog.php" class="btn btn-primary">
                        📚 Lihat Buku Lain
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
