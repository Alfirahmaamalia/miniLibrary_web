<?php
session_start();
include '../koneksi/koneksi.php';

// Cek apakah user sudah login sebagai anggota
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit;
}

// Ambil data anggota yang login
$anggota_id = $_SESSION['anggota_id'];
$query_anggota = mysqli_query($koneksi, "SELECT * FROM anggota WHERE id = $anggota_id");
$anggota = mysqli_fetch_assoc($query_anggota);

// Ambil data peminjaman aktif
$query_peminjaman = mysqli_query($koneksi, "SELECT p.*, b.`judul buku` as judul_buku 
                                           FROM peminjaman p 
                                           JOIN buku b ON p.id_buku = b.id 
                                           WHERE p.id_anggota = $anggota_id AND p.status = 'dipinjam' 
                                           ORDER BY p.tanggal_kembali ASC");

// Hitung jumlah buku yang dipinjam
$jumlah_pinjaman = mysqli_num_rows($query_peminjaman);

// Ambil buku terbaru
$query_buku_terbaru = mysqli_query($koneksi, "SELECT b.*, k.nama_kategori 
                                             FROM buku b 
                                             LEFT JOIN kategori k ON b.kategori = k.id 
                                             WHERE b.stok > 0 
                                             ORDER BY b.id DESC 
                                             LIMIT 4");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Anggota - MiniLibrary</title>
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
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        <?php
        // Tampilkan pesan jika ada
        if (isset($_SESSION['message'])) {
            $message_class = ($_SESSION['message_type'] == 'success') ? 'success' : 'error';
            echo "<div style='background: " . ($message_class == 'success' ? '#d4edda' : '#f8d7da') . "; color: " . ($message_class == 'success' ? '#155724' : '#721c24') . "; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid " . ($message_class == 'success' ? '#c3e6cb' : '#f5c6cb') . "; display: flex; align-items: center; gap: 10px;'>";
            echo ($message_class == 'success' ? '✅' : '❌') . " " . $_SESSION['message'];
            echo "</div>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .welcome-text h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .welcome-text p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4e73df;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }

        .welcome-image {
            width: 200px;
            height: 200px;
            background-image: url('assets/reading.svg');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .stat-icon.books {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }

        .stat-icon.borrowed {
            background: rgba(54, 185, 204, 0.1);
            color: #36b9cc;
        }

        .stat-icon.overdue {
            background: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }

        .stat-info h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            font-size: 18px;
            color: #333;
        }

        .section-header a {
            color: #4e73df;
            text-decoration: none;
            font-size: 14px;
        }

        .section-header a:hover {
            text-decoration: underline;
        }

        /* Book Cards */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .book-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 40px;
        }

        .book-info {
            padding: 15px;
        }

        .book-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 48px;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .book-category {
            display: inline-block;
            padding: 3px 8px;
            background: #f8f9fc;
            border-radius: 4px;
            font-size: 12px;
            color: #4e73df;
        }

        /* Loan Table */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f8f9fc;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .table tr:hover {
            background-color: #f8f9fc;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 40px;
            margin-bottom: 10px;
            color: #ddd;
        }

        /* Footer */
        .footer {
            background: #4e73df;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 14px;
            border-top: 1px solid #eee;
            margin-top: 40px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }

            .welcome-image {
                margin-top: 20px;
            }

            .navbar-nav {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 576px) {
            .stats-row {
                grid-template-columns: 1fr;
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
                <li class="nav-item"><a href="beranda_pengguna.php" class="nav-link active">Beranda</a></li>
                <li class="nav-item"><a href="katalog.php" class="nav-link">Katalog Buku</a></li>
                <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link">Pinjaman Saya</a></li>
                <li class="nav-item"><a href="profil.php" class="nav-link">Profil</a></li>
                <li class="nav-item"><a href="logout_anggota.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Selamat Datang, <?php echo htmlspecialchars($anggota['nama']); ?>!</h1>
                <p>Temukan buku favorit Anda dan jelajahi dunia pengetahuan bersama MiniLibrary.</p>
                <a href="katalog.php" class="btn">Jelajahi Katalog</a>
                <a href="#request-section" class="btn" style="margin-left: 10px; background: #28a745;" onclick="document.getElementById('request-section').scrollIntoView({behavior: 'smooth'});">📝 Request Buku</a>
            </div>
            <div class="welcome-image"></div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon books">📚</div>
                <div class="stat-info">
                    <h3>Total Koleksi</h3>
                    <?php
                    $total_buku = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku"))['total'];
                    ?>
                    <p><?php echo $total_buku; ?> Buku</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon borrowed">📖</div>
                <div class="stat-info">
                    <h3>Buku Dipinjam</h3>
                    <p><?php echo $jumlah_pinjaman; ?> Buku</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon overdue">⏰</div>
                <div class="stat-info">
                    <h3>Jatuh Tempo</h3>
                    <?php
                    $jatuh_tempo = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman 
                                                                             WHERE id_anggota = $anggota_id 
                                                                             AND status = 'dipinjam' 
                                                                             AND tanggal_kembali < CURDATE()"))['total'];
                    ?>
                    <p><?php echo $jatuh_tempo; ?> Buku</p>
                </div>
            </div>
        </div>

        <!-- Current Loans Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>Peminjaman Aktif</h2>
                <a href="pinjaman_saya.php">Lihat Semua</a>
            </div>
            
            <div class="table-responsive">
                <?php if (mysqli_num_rows($query_peminjaman) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Judul Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pinjam = mysqli_fetch_assoc($query_peminjaman)): ?>
                                <?php
                                $today = new DateTime();
                                $due_date = new DateTime($pinjam['tanggal_kembali']);
                                $is_overdue = $today > $due_date;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($pinjam['judul_buku']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_pinjam'])); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($pinjam['tanggal_kembali'])); ?></td>
                                    <td>
                                        <?php if ($is_overdue): ?>
                                            <span class="badge badge-danger">Terlambat</span>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Dipinjam</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <p>Anda belum meminjam buku apapun.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- New Books Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>Buku Terbaru</h2>
                <a href="katalog.php">Lihat Semua</a>
            </div>
            
            <div class="book-grid">
                <?php if (mysqli_num_rows($query_buku_terbaru) > 0): ?>
                    <?php while ($buku = mysqli_fetch_assoc($query_buku_terbaru)): ?>
                        <div class="book-card">
                            <div class="book-cover">📖</div>
                            <div class="book-info">
                                <h3 class="book-title"><?php echo htmlspecialchars($buku['judul buku']); ?></h3>
                                <p class="book-author"><?php echo htmlspecialchars($buku['penulis']); ?></p>
                                <span class="book-category"><?php echo htmlspecialchars($buku['nama_kategori'] ?? 'Umum'); ?></span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <p>Belum ada buku terbaru.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Request Buku Section -->
        <div class="content-section" id="request-section">
            <div class="section-header">
                <h2>📝 Request Buku Baru</h2>
                <p style="font-size: 14px; color: #666; margin: 0;">Ajukan permintaan buku yang belum tersedia di perpustakaan</p>
            </div>
            
            <!-- Form Request Buku -->
            <form method="POST" action="proses_request.php" style="margin-bottom: 30px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 20px;">
                    <div style="display: flex; flex-direction: column;">
                        <label style="font-weight: 600; margin-bottom: 8px; color: #333;">📖 Judul Buku *</label>
                        <input type="text" name="judul_buku" required 
                               style="padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px;"
                               placeholder="Masukkan judul buku yang diinginkan">
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label style="font-weight: 600; margin-bottom: 8px; color: #333;">✍️ Penulis *</label>
                        <input type="text" name="penulis" required 
                               style="padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px;"
                               placeholder="Nama penulis buku">
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <label style="font-weight: 600; margin-bottom: 8px; color: #333;">📂 Kategori *</label>
                        <select name="kategori" required 
                                style="padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px;">
                            <option value="">Pilih Kategori</option>
                            <option value="1">📚 Fiksi</option>
                            <option value="2">📰 Non-Fiksi</option>
                            <option value="3">🏛️ Sejarah</option>
                            <option value="4">🔬 Ilmiah</option>
                            <option value="5">💻 Teknologi</option>
                            <option value="6">👤 Biografi</option>
                            <option value="7">🎓 Pendidikan</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; margin-bottom: 20px;">
                    <label style="font-weight: 600; margin-bottom: 8px; color: #333;">💭 Alasan Permintaan *</label>
                    <textarea name="alasan" required rows="3"
                              style="padding: 12px; border: 2px solid #e1e5e9; border-radius: 8px; font-size: 14px; resize: vertical;"
                              placeholder="Jelaskan mengapa buku ini diperlukan (minimal 20 karakter)"></textarea>
                </div>
                <div style="text-align: center;">
                    <button type="submit" name="submit_request" 
                            style="background: linear-gradient(135deg, #4e73df, #224abe); color: white; padding: 12px 30px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s;">
                        📤 Ajukan Request Buku
                    </button>
                </div>
            </form>

            <!-- Riwayat Request User -->
            <div style="background: #f8f9fc; padding: 20px; border-radius: 8px;">
                <h3 style="margin: 0 0 15px 0; font-size: 16px; color: #333;">📋 Riwayat Request Saya</h3>
                <?php
                // Ambil riwayat request user
                $query_request = mysqli_query($koneksi, "SELECT r.*, 
                    CASE 
                        WHEN r.kategori = 1 THEN 'Fiksi'
                        WHEN r.kategori = 2 THEN 'Non-Fiksi'
                        WHEN r.kategori = 3 THEN 'Sejarah'
                        WHEN r.kategori = 4 THEN 'Ilmiah'
                        WHEN r.kategori = 5 THEN 'Teknologi'
                        WHEN r.kategori = 6 THEN 'Biografi'
                        WHEN r.kategori = 7 THEN 'Pendidikan'
                        ELSE 'Lainnya'
                    END as nama_kategori
                    FROM request_buku r 
                    WHERE r.email_pemohon = '$anggota[email]' 
                    ORDER BY r.tanggal_request DESC 
                    LIMIT 5");
                
                if (mysqli_num_rows($query_request) > 0):
                ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                            <thead>
                                <tr style="background: #e3f2fd;">
                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Tanggal</th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Buku</th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Status</th>
                                    <th style="padding: 10px; text-align: left; border-bottom: 1px solid #ddd;">Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($req = mysqli_fetch_assoc($query_request)): ?>
                                    <tr>
                                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                            <?php echo date('d/m/Y', strtotime($req['tanggal_request'])); ?>
                                        </td>
                                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                            <strong><?php echo htmlspecialchars($req['judul_buku']); ?></strong><br>
                                            <small style="color: #666;">oleh <?php echo htmlspecialchars($req['penulis']); ?></small>
                                        </td>
                                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                            <?php
                                            $status_class = '';
                                            $status_text = '';
                                            switch($req['status']) {
                                                case 'pending':
                                                    $status_class = 'background: #fff3cd; color: #856404;';
                                                    $status_text = '⏳ Menunggu';
                                                    break;
                                                case 'approved':
                                                    $status_class = 'background: #d4edda; color: #155724;';
                                                    $status_text = '✅ Disetujui';
                                                    break;
                                                case 'rejected':
                                                    $status_class = 'background: #f8d7da; color: #721c24;';
                                                    $status_text = '❌ Ditolak';
                                                    break;
                                                case 'completed':
                                                    $status_class = 'background: #d1ecf1; color: #0c5460;';
                                                    $status_text = '📚 Selesai';
                                                    break;
                                            }
                                            ?>
                                            <span style="padding: 4px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                                            <?php echo htmlspecialchars($req['keterangan'] ?? 'Belum ada keterangan'); ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #666;">
                        <div style="font-size: 40px; margin-bottom: 10px; opacity: 0.5;">📝</div>
                        <p>Anda belum pernah mengajukan request buku.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
    </div>

<script>
// Smooth scroll ke section request buku
function scrollToRequestSection() {
    document.querySelector('.content-section:last-of-type').scrollIntoView({
        behavior: 'smooth'
    });
}

// Tambahkan tombol untuk scroll ke request section
document.addEventListener('DOMContentLoaded', function() {
    // Tambahkan tombol "Request Buku" di welcome section
    const welcomeText = document.querySelector('.welcome-text');
    const requestBtn = document.createElement('a');
    requestBtn.href = '#request-section';
    requestBtn.className = 'btn';
    requestBtn.style.marginLeft = '10px';
    requestBtn.innerHTML = '📝 Request Buku';
    requestBtn.onclick = function(e) {
        e.preventDefault();
        scrollToRequestSection();
    };
    welcomeText.appendChild(requestBtn);
});
</script>
</body>
</html>
