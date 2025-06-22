<?php
session_start();
if (!isset($_SESSION['anggota_id'])) {
    header("Location: profil.php");
    exit;
}

include '../koneksi/koneksi.php';

$anggota_id = $_SESSION['anggota_id'];
$success = "";
$error = "";

// Handle update profil
if (isset($_POST['update_profil'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $telepon = mysqli_real_escape_string($koneksi, trim($_POST['telepon']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    
    // Validasi
    if (empty($nama) || empty($email)) {
        $error = "Nama dan email harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } else {
        // Cek apakah email sudah digunakan anggota lain
        $check_email = "SELECT id FROM anggota WHERE email = '$email' AND id != '$anggota_id'";
        $result_check = mysqli_query($koneksi, $check_email);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = "Email sudah digunakan oleh anggota lain.";
        } else {
            // Update profil
            $update_query = "UPDATE anggota SET 
                           nama = '$nama',
                           email = '$email',
                           telepon = '$telepon',
                           alamat = '$alamat'
                           WHERE id = '$anggota_id'";
            
            if (mysqli_query($koneksi, $update_query)) {
                $success = "Profil berhasil diperbarui.";
                // Update session jika email berubah
                $_SESSION['anggota_email'] = $email;
            } else {
                $error = "Gagal memperbarui profil: " . mysqli_error($koneksi);
            }
        }
    }
}

// Handle ganti password
if (isset($_POST['ganti_password'])) {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Ambil password lama dari database
    $query_password = "SELECT password FROM anggota WHERE id = '$anggota_id'";
    $result_password = mysqli_query($koneksi, $query_password);
    $data_password = mysqli_fetch_assoc($result_password);
    
    // Validasi password lama
    if (!password_verify($password_lama, $data_password['password'])) {
        $error = "Password lama tidak sesuai.";
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = "Konfirmasi password tidak sesuai.";
    } elseif (strlen($password_baru) < 6) {
        $error = "Password baru minimal 6 karakter.";
    } else {
        // Update password
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $update_password = "UPDATE anggota SET password = '$password_hash' WHERE id = '$anggota_id'";
        
        if (mysqli_query($koneksi, $update_password)) {
            $success = "Password berhasil diubah.";
        } else {
            $error = "Gagal mengubah password: " . mysqli_error($koneksi);
        }
    }
}

// Handle upload foto profil
if (isset($_POST['upload_foto'])) {
    $target_dir = "uploads/profile/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION));
    $new_filename = "profile_" . $anggota_id . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validasi file
    $allowed_types = array("jpg", "jpeg", "png", "gif");
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file_extension, $allowed_types)) {
        $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
    } elseif ($_FILES["foto_profil"]["size"] > $max_size) {
        $error = "Ukuran file terlalu besar. Maksimal 5MB.";
    } elseif (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
        // Hapus foto lama jika ada
        if (!empty($anggota['foto_profil']) && file_exists($anggota['foto_profil'])) {
            unlink($anggota['foto_profil']);
        }
        
        // Update database
        $update_foto = "UPDATE anggota SET foto_profil = '$target_file' WHERE id = '$anggota_id'";
        if (mysqli_query($koneksi, $update_foto)) {
            $success = "Foto profil berhasil diupload.";
        } else {
            $error = "Gagal menyimpan foto profil ke database.";
        }
    } else {
        $error = "Gagal mengupload foto profil.";
    }
}

// Handle hapus foto profil
if (isset($_POST['hapus_foto'])) {
    if (!empty($anggota['foto_profil']) && file_exists($anggota['foto_profil'])) {
        unlink($anggota['foto_profil']);
    }
    
    $update_foto = "UPDATE anggota SET foto_profil = NULL WHERE id = '$anggota_id'";
    if (mysqli_query($koneksi, $update_foto)) {
        $success = "Foto profil berhasil dihapus.";
    } else {
        $error = "Gagal menghapus foto profil.";
    }
}

// Handle hapus akun
if (isset($_POST['hapus_akun'])) {
    $password_konfirmasi = $_POST['password_konfirmasi'];
    
    // Ambil password dari database
    $query_password = "SELECT password FROM anggota WHERE id = '$anggota_id'";
    $result_password = mysqli_query($koneksi, $query_password);
    $data_password = mysqli_fetch_assoc($result_password);
    
    if (!password_verify($password_konfirmasi, $data_password['password'])) {
        $error = "Password tidak sesuai. Akun tidak dapat dihapus.";
    } else {
        // Cek apakah masih ada peminjaman aktif
        $check_pinjaman = "SELECT COUNT(*) as aktif FROM peminjaman WHERE id_anggota = '$anggota_id' AND status = 'dipinjam'";
        $result_check = mysqli_query($koneksi, $check_pinjaman);
        $pinjaman_aktif = mysqli_fetch_assoc($result_check)['aktif'];
        
        if ($pinjaman_aktif > 0) {
            $error = "Tidak dapat menghapus akun. Anda masih memiliki $pinjaman_aktif buku yang belum dikembalikan.";
        } else {
            // Hapus foto profil jika ada
            if (!empty($anggota['foto_profil']) && file_exists($anggota['foto_profil'])) {
                unlink($anggota['foto_profil']);
            }
            
            // Hapus akun
            $delete_akun = "DELETE FROM anggota WHERE id = '$anggota_id'";
            if (mysqli_query($koneksi, $delete_akun)) {
                // Destroy session dan redirect
                session_destroy();
                header("Location: login_anggota.php?message=Akun berhasil dihapus");
                exit;
            } else {
                $error = "Gagal menghapus akun: " . mysqli_error($koneksi);
            }
        }
    }
}

// Ambil data anggota
$query_anggota = "SELECT * FROM anggota WHERE id = '$anggota_id'";
$result_anggota = mysqli_query($koneksi, $query_anggota);
$anggota = mysqli_fetch_assoc($result_anggota);

// Ambil statistik peminjaman
$query_stats = "SELECT 
                  COUNT(*) as total_peminjaman,
                  SUM(CASE WHEN status = 'dipinjam' THEN 1 ELSE 0 END) as sedang_dipinjam,
                  SUM(CASE WHEN status = 'dikembalikan' THEN 1 ELSE 0 END) as sudah_dikembalikan,
                  SUM(CASE WHEN status = 'dipinjam' AND tanggal_kembali < CURDATE() THEN 1 ELSE 0 END) as terlambat
                FROM peminjaman 
                WHERE id_anggota = '$anggota_id'";
$result_stats = mysqli_query($koneksi, $query_stats);
$stats = mysqli_fetch_assoc($result_stats);

// Ambil statistik request buku
$query_request_stats = "SELECT 
                          COUNT(*) as total_request,
                          SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                          SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                          SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                          SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                        FROM request_buku 
                        WHERE email_pemohon = '{$anggota['email']}'";
$result_request_stats = mysqli_query($koneksi, $query_request_stats);
$request_stats = mysqli_fetch_assoc($result_request_stats);

// Ambil buku favorit (paling sering dipinjam)
$query_favorit = "SELECT 
                    COALESCE(b.`judul buku`, 'Buku Tidak Dikenal') as judul,
                    COALESCE(b.penulis, 'Penulis Tidak Dikenal') as penulis,
                    COUNT(*) as jumlah_pinjam
                  FROM peminjaman p
                  JOIN buku b ON p.id_buku = b.id
                  WHERE p.id_anggota = '$anggota_id'
                  GROUP BY p.id_buku
                  ORDER BY jumlah_pinjam DESC
                  LIMIT 5";
$result_favorit = mysqli_query($koneksi, $query_favorit);

// Ambil riwayat request terbaru
$query_recent_requests = "SELECT * FROM request_buku 
                         WHERE email_pemohon = '{$anggota['email']}'
                         ORDER BY tanggal_request DESC 
                         LIMIT 5";
$result_recent_requests = mysqli_query($koneksi, $query_recent_requests);

// Ambil peminjaman aktif
$query_active_loans = "SELECT p.*, 
                         COALESCE(b.`judul buku`,  'Buku Tidak Dikenal') as judul,
                         COALESCE(b.penulis, 'Penulis Tidak Dikenal') as penulis,
                         DATEDIFF(p.tanggal_kembali, CURDATE()) as hari_tersisa
                       FROM peminjaman p
                       JOIN buku b ON p.id_buku = b.id
                       WHERE p.id_anggota = '$anggota_id' AND p.status = 'dipinjam'
                       ORDER BY p.tanggal_kembali ASC";
$result_active_loans = mysqli_query($koneksi, $query_active_loans);

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
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
         .footer {
            background: #4e73df;
            padding: 20px;
            text-align: center;
            color: white;
            font-size: 14px;
            border-top: 1px solid #eee;
            margin-top: 40px;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        /* Profile Grid */
        .profile-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            font-weight: bold;
            margin: 0 auto 20px;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }
        
        .profile-name {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .profile-email {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .profile-body {
            padding: 30px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-item {
            text-align: center;
            padding: 25px 15px;
            background: linear-gradient(135deg, #f8f9ff, #e8f0ff);
            border-radius: 15px;
            border: 1px solid rgba(102, 126, 234, 0.1);
            transition: all 0.3s ease;
        }
        
        .stat-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 8px;
            display: block;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        /* Section Cards */
        .section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .section-header {
            background: linear-gradient(135deg, #f8f9ff, #e8f0ff);
            padding: 25px 30px;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
            position: relative;
        }
        
        .section-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-body {
            padding: 30px;
        }
        
        /* Lists */
        .item-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .item-list li {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #fafbff;
        }
        
        .item-list li:hover {
            border-color: #667eea;
            background: #f0f4ff;
            transform: translateX(5px);
        }
        
        .item-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            font-weight: bold;
            margin-right: 20px;
            flex-shrink: 0;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-title {
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        
        .item-subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .item-meta {
            color: #667eea;
            font-weight: 500;
            font-size: 14px;
            text-align: right;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-pending { background: #fff3cd; color: #856404; }
        .status-approved { background: #d1ecf1; color: #0c5460; }
        .status-completed { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .status-overdue { background: #f8d7da; color: #721c24; }
        .status-due-soon { background: #fff3cd; color: #856404; }
        .status-normal { background: #d4edda; color: #155724; }
        
        /* Forms */
        .form-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .tab-navigation {
            display: flex;
            background: #f8f9ff;
            border-radius: 15px;
            padding: 8px;
            margin-bottom: 30px;
        }
        
        .tab-btn {
            flex: 1;
            padding: 15px 20px;
            background: none;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #666;
        }
        
        .tab-btn.active {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e8f0;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fafbff;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
            background: white;
        }
        
        /* Buttons */
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #495057);
            color: white;
        }
        
        .btn-secondary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 117, 125, 0.3);
        }
        
        /* Alerts */
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
        
        .alert-danger {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-state-text {
            font-size: 18px;
            margin-bottom: 10px;
        }
        
        .empty-state-subtext {
            font-size: 14px;
            opacity: 0.7;
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .profile-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .navbar-nav {
                display: none;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }
        }
        
        @media (max-width: 576px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .form-control[type="file"] {
            padding: 10px;
            background: white;
        }

        .form-control[type="file"]:focus {
            background: white;
        }
    </style>
</head>
<body>

<!-- Navbar - sama seperti index.php -->
<nav class="navbar">
    <div class="navbar-container">
        <a href="beranda_pengguna.php" class="navbar-brand">MiniLibrary</a>
        <ul class="navbar-nav">
            <li class="nav-item"><a href="beranda_pengguna.php" class="nav-link">Beranda</a></li>
            <li class="nav-item"><a href="katalog.php" class="nav-link">Katalog Buku</a></li>
            <li class="nav-item"><a href="pinjaman_saya.php" class="nav-link">Pinjaman Saya</a></li>
            <li class="nav-item"><a href="profil.php" class="nav-link active">Profil</a></li>
            <li class="nav-item"><a href="logout_anggota.php" class="nav-link">Logout</a></li>
        </ul>
    </div>
</nav>

<!-- Main Content -->
<div class="container">
    <!-- Alerts -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">
            ✅ <?php echo $success; ?>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            ❌ <?php echo $error; ?>
        </div>
    <?php endif; ?>
    
    <!-- Profile Grid -->
    <div class="profile-grid">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php if (!empty($anggota['foto_profil']) && file_exists($anggota['foto_profil'])): ?>
                        <img src="<?php echo $anggota['foto_profil']; ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($anggota['nama'], 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($anggota['nama']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($anggota['email']); ?></p>
            </div>
            <div class="profile-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['total_peminjaman']; ?></span>
                        <span class="stat-label">Total Pinjam</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $stats['sedang_dipinjam']; ?></span>
                        <span class="stat-label">Sedang Dipinjam</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number"><?php echo $request_stats['total_request']; ?></span>
                        <span class="stat-label">Total Request</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" style="color: <?php echo $stats['terlambat'] > 0 ? '#dc3545' : '#28a745'; ?>;">
                            <?php echo $stats['terlambat']; ?>
                        </span>
                        <span class="stat-label">Terlambat</span>
                    </div>
                </div>
                
                <div style="padding: 20px; background: #f8f9ff; border-radius: 12px; margin-top: 20px;">
                    <strong style="color: #333;">📋 Informasi Anggota</strong><br>
                    <div style="margin-top: 15px; line-height: 1.6;">
                        <div style="margin-bottom: 8px;">
                            <span style="color: #666;">📅 Bergabung:</span> 
                            <strong><?php echo date('d/m/Y', strtotime($anggota['tanggal_daftar'])); ?></strong>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #666;">📱 Telepon:</span> 
                            <strong><?php echo htmlspecialchars($anggota['telepon'] ?? 'Belum diisi'); ?></strong>
                        </div>
                        <div style="margin-bottom: 8px;">
                            <span style="color: #666;">🏠 Alamat:</span> 
                            <strong><?php echo htmlspecialchars($anggota['alamat'] ?? 'Belum diisi'); ?></strong>
                        </div>
                        <div>
                            <span style="color: #666;">✅ Status:</span> 
                            <span style="color: #28a745; font-weight: 600;">
                                <?php echo ucfirst($anggota['status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Stats -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">📊 Statistik Request Buku</h2>
            </div>
            <div class="section-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <span class="stat-number" style="color: #ffc107;"><?php echo $request_stats['pending']; ?></span>
                        <span class="stat-label">⏳ Menunggu</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" style="color: #17a2b8;"><?php echo $request_stats['approved']; ?></span>
                        <span class="stat-label">✅ Disetujui</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" style="color: #28a745;"><?php echo $request_stats['completed']; ?></span>
                        <span class="stat-label">📚 Selesai</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-number" style="color: #dc3545;"><?php echo $request_stats['rejected']; ?></span>
                        <span class="stat-label">❌ Ditolak</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Dashboard Grid -->
    <div class="dashboard-grid">
        <!-- Peminjaman Aktif -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">📚 Peminjaman Aktif</h2>
            </div>
            <div class="section-body">
                <?php if (mysqli_num_rows($result_active_loans) > 0): ?>
                    <ul class="item-list">
                        <?php while ($loan = mysqli_fetch_assoc($result_active_loans)): ?>
                            <li>
                                <div class="item-icon">📖</div>
                                <div class="item-details">
                                    <h4 class="item-title"><?php echo htmlspecialchars($loan['judul']); ?></h4>
                                    <p class="item-subtitle">oleh <?php echo htmlspecialchars($loan['penulis']); ?></p>
                                    <p class="item-subtitle">
                                        Dipinjam: <?php echo date('d/m/Y', strtotime($loan['tanggal_pinjam'])); ?>
                                    </p>
                                </div>
                                <div class="item-meta">
                                    <?php 
                                    $hari_tersisa = $loan['hari_tersisa'];
                                    if ($hari_tersisa < 0): 
                                        $status_class = 'status-overdue';
                                        $status_text = abs($hari_tersisa) . ' hari terlambat';
                                    elseif ($hari_tersisa <= 3): 
                                        $status_class = 'status-due-soon';
                                        $status_text = $hari_tersisa . ' hari lagi';
                                    else: 
                                        $status_class = 'status-normal';
                                        $status_text = $hari_tersisa . ' hari lagi';
                                    endif;
                                    ?>
                                    <span class="status-badge <?php echo $status_class; ?>">
                                        <?php echo $status_text; ?>
                                    </span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📚</div>
                        <div class="empty-state-text">Tidak ada peminjaman aktif</div>
                        <div class="empty-state-subtext">Anda belum meminjam buku apapun saat ini</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Request Terbaru -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">📝 Request Buku Terbaru</h2>
            </div>
            <div class="section-body">
                <?php if (mysqli_num_rows($result_recent_requests) > 0): ?>
                    <ul class="item-list">
                        <?php while ($request = mysqli_fetch_assoc($result_recent_requests)): ?>
                            <li>
                                <div class="item-icon">📋</div>
                                <div class="item-details">
                                    <h4 class="item-title"><?php echo htmlspecialchars($request['judul_buku']); ?></h4>
                                    <p class="item-subtitle">oleh <?php echo htmlspecialchars($request['penulis']); ?></p>
                                    <p class="item-subtitle">
                                        Request: <?php echo date('d/m/Y H:i', strtotime($request['tanggal_request'])); ?>
                                    </p>
                                    <?php if (!empty($request['keterangan'])): ?>
                                        <p class="item-subtitle" style="margin-top: 5px; font-style: italic;">
                                            "<?php echo htmlspecialchars($request['keterangan']); ?>"
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="item-meta">
                                    <?php
                                    $status_classes = [
                                        'pending' => 'status-pending',
                                        'approved' => 'status-approved', 
                                        'completed' => 'status-completed',
                                        'rejected' => 'status-rejected'
                                    ];
                                    $status_icons = [
                                        'pending' => '⏳',
                                        'approved' => '✅',
                                        'completed' => '📚',
                                        'rejected' => '❌'
                                    ];
                                    ?>
                                    <span class="status-badge <?php echo $status_classes[$request['status']]; ?>">
                                        <?php echo $status_icons[$request['status']] . ' ' . ucfirst($request['status']); ?>
                                    </span>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📝</div>
                        <div class="empty-state-text">Belum ada request buku</div>
                        <div class="empty-state-subtext">Ajukan request buku baru di halaman beranda</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Buku Favorit -->
        <div class="section">
            <div class="section-header">
                <h2 class="section-title">⭐ Buku Favorit</h2>
            </div>
            <div class="section-body">
                <?php if (mysqli_num_rows($result_favorit) > 0): ?>
                    <ul class="item-list">
                        <?php while ($fav = mysqli_fetch_assoc($result_favorit)): ?>
                            <li>
                                <div class="item-icon">⭐</div>
                                <div class="item-details">
                                    <h4 class="item-title"><?php echo htmlspecialchars($fav['judul']); ?></h4>
                                    <p class="item-subtitle">oleh <?php echo htmlspecialchars($fav['penulis']); ?></p>
                                </div>
                                <div class="item-meta">
                                    <strong style="color: #667eea;">
                                        <?php echo $fav['jumlah_pinjam']; ?>x dipinjam
                                    </strong>
                                </div>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">⭐</div>
                        <div class="empty-state-text">Belum ada buku favorit</div>
                        <div class="empty-state-subtext">Pinjam buku untuk melihat favorit Anda</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Form Edit -->
    <div class="form-section">
        <div class="section-header">
            <h2 class="section-title">⚙️ Pengaturan Akun</h2>
        </div>
        <div class="section-body">
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('profil')">✏️ Edit Profil</button>
                <button class="tab-btn" onclick="showTab('password')">🔒 Ganti Password</button>
                <button class="tab-btn" onclick="showTab('foto')">📷 Foto Profil</button>
                <button class="tab-btn" onclick="showTab('hapus')">🗑️ Hapus Akun</button>
            </div>
            
            <!-- Tab Edit Profil -->
            <div id="profil-tab" class="tab-content active">
                <form method="POST">
                    <div class="form-group">
                        <label for="nama">👤 Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" 
                               value="<?php echo htmlspecialchars($anggota['nama']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">📧 Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($anggota['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telepon">📱 Nomor Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" 
                               value="<?php echo htmlspecialchars($anggota['telepon'] ?? ''); ?>"
                               placeholder="Contoh: 08123456789">
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">🏠 Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                  placeholder="Masukkan alamat lengkap Anda"><?php echo htmlspecialchars($anggota['alamat'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profil" class="btn btn-primary">
                        💾 Simpan Perubahan
                    </button>
                      <!-- Footer -->
                        <div class="footer">
                            <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
                        </div>
                </form>
            </div>
            
            <!-- Tab Ganti Password -->
            <div id="password-tab" class="tab-content">
                <form method="POST">
                    <div class="form-group">
                        <label for="password_lama">🔐 Password Lama</label>
                        <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password_baru">🆕 Password Baru</label>
                        <input type="password" class="form-control" id="password_baru" name="password_baru" required>
                        <small style="color: #666; font-size: 14px; margin-top: 5px; display: block;">
                            Minimal 6 karakter, gunakan kombinasi huruf dan angka
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label for="konfirmasi_password">✅ Konfirmasi Password Baru</label>
                        <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required>
                    </div>
                    
                    <button type="submit" name="ganti_password" class="btn btn-secondary">
                        🔒 Ganti Password
                    </button>
                </form>
            </div>

            <!-- Tab Foto Profil -->
            <div id="foto-tab" class="tab-content">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 150px; height: 150px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; border: 4px solid #667eea;">
                        <?php if (!empty($anggota['foto_profil']) && file_exists($anggota['foto_profil'])): ?>
                            <img src="<?php echo $anggota['foto_profil']; ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #667eea, #764ba2); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;">
                                <?php echo strtoupper(substr($anggota['nama'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                       
                    </div>
                </div>
                
                <form method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="foto_profil">📷 Pilih Foto Profil</label>
                        <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*" required>
                        <small style="color: #666; font-size: 14px; margin-top: 5px; display: block;">
                            Format: JPG, JPEG, PNG, GIF. Maksimal 5MB.
                        </small>
                    </div>
                    
                    <div style="display: flex; gap: 15px;">
                        <button type="submit" name="upload_foto" class="btn btn-primary">
                            📤 Upload Foto
                        </button>
                        
                        <?php if (!empty($anggota['foto_profil'])): ?>
                            <button type="submit" name="hapus_foto" class="btn btn-secondary" 
                                    onclick="return confirm('Yakin ingin menghapus foto profil?')">
                                🗑️ Hapus Foto
                            </button>
                        <?php endif; ?>

                       
                    </div>
                </form>
            </div>

            <!-- Tab Hapus Akun -->
            <div id="hapus-tab" class="tab-content">
                <div style="background: linear-gradient(135deg, #f8d7da, #f5c6cb); padding: 20px; border-radius: 12px; margin-bottom: 30px; border-left: 4px solid #dc3545;">
                    <h4 style="color: #721c24; margin: 0 0 10px 0;">⚠️ Peringatan!</h4>
                    <p style="color: #721c24; margin: 0; line-height: 1.6;">
                        Menghapus akun akan menghilangkan semua data Anda secara permanen. 
                        Pastikan Anda sudah mengembalikan semua buku yang dipinjam.
                    </p>
                </div>
                
                <form method="POST">
                    <div class="form-group">
                        <label for="password_konfirmasi">🔐 Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_konfirmasi" name="password_konfirmasi" required>
                        <small style="color: #666; font-size: 14px; margin-top: 5px; display: block;">
                            Masukkan password Anda untuk mengkonfirmasi penghapusan akun
                        </small>
                    </div>
                    
                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="konfirmasi_hapus" required style="margin-right: 10px;">
                            Saya memahami bahwa tindakan ini tidak dapat dibatalkan
                        </label>
                    </div>
                    
                    <button type="submit" name="hapus_akun" class="btn" 
                            style="background: linear-gradient(135deg, #dc3545, #c82333); color: white;"
                            onclick="return confirm('PERINGATAN: Akun akan dihapus permanen! Yakin ingin melanjutkan?')"
                            disabled id="btn-hapus">
                        🗑️ Hapus Akun Permanen
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showTab(tabName) {
    // Hide all tabs
    var tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(function(tab) {
        tab.classList.remove('active');
    });
    
    // Remove active class from all buttons
    var buttons = document.querySelectorAll('.tab-btn');
    buttons.forEach(function(btn) {
        btn.classList.remove('active');
    });
    
    // Show selected tab
    document.getElementById(tabName + '-tab').classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(function() {
                alert.remove();
            }, 300);
        }, 5000);
    });
});

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const passwordForm = document.querySelector('#password-tab form');
    if (passwordForm) {
        passwordForm.addEventListener('submit', function(e) {
            const passwordBaru = document.getElementById('password_baru').value;
            const konfirmasiPassword = document.getElementById('konfirmasi_password').value;
            
            if (passwordBaru !== konfirmasiPassword) {
                e.preventDefault();
                alert('❌ Konfirmasi password tidak sesuai!');
                return false;
            }
            
            if (passwordBaru.length < 6) {
                e.preventDefault();
                alert('❌ Password minimal 6 karakter!');
                return false;
            }
        });
    }
});

// Enable delete button when checkbox is checked
document.addEventListener('DOMContentLoaded', function() {
    const checkbox = document.getElementById('konfirmasi_hapus');
    const btnHapus = document.getElementById('btn-hapus');
    
    if (checkbox && btnHapus) {
        checkbox.addEventListener('change', function() {
            btnHapus.disabled = !this.checked;
            if (this.checked) {
                btnHapus.style.opacity = '1';
                btnHapus.style.cursor = 'pointer';
            } else {
                btnHapus.style.opacity = '0.5';
                btnHapus.style.cursor = 'not-allowed';
            }
        });
    }
});
</script>

</body>
</html>
