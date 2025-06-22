<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: loginadmin.php");
    exit;
}

include '../koneksi/koneksi.php';

$admin_email = $_SESSION['email'];
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
        // Cek apakah email sudah digunakan admin lain
        $check_email = "SELECT id FROM admin WHERE email = '$email' AND email != '$admin_email'";
        $result_check = mysqli_query($koneksi, $check_email);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = "Email sudah digunakan oleh admin lain.";
        } else {
            // Update profil
            $update_query = "UPDATE admin SET 
                           nama = '$nama',
                           email = '$email',
                           telepon = '$telepon',
                           alamat = '$alamat',
                           updated_at = NOW()
                           WHERE email = '$admin_email'";
            
            if (mysqli_query($koneksi, $update_query)) {
                $success = "Profil berhasil diperbarui.";
                // Update session jika email berubah
                $_SESSION['email'] = $email;
                $admin_email = $email;
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
    $query_password = "SELECT password FROM admin WHERE email = '$admin_email'";
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
        $update_password = "UPDATE admin SET password = '$password_hash', updated_at = NOW() WHERE email = '$admin_email'";
        
        if (mysqli_query($koneksi, $update_password)) {
            $success = "Password berhasil diubah.";
        } else {
            $error = "Gagal mengubah password: " . mysqli_error($koneksi);
        }
    }
}

// Handle upload foto profil
if (isset($_POST['upload_foto'])) {
    $target_dir = "uploads/admin/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($_FILES["foto_profil"]["name"], PATHINFO_EXTENSION));
    $new_filename = "admin_" . md5($admin_email) . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Validasi file
    $allowed_types = array("jpg", "jpeg", "png", "gif");
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if (!in_array($file_extension, $allowed_types)) {
        $error = "Hanya file JPG, JPEG, PNG & GIF yang diperbolehkan.";
    } elseif ($_FILES["foto_profil"]["size"] > $max_size) {
        $error = "Ukuran file terlalu besar. Maksimal 5MB.";
    } elseif (move_uploaded_file($_FILES["foto_profil"]["tmp_name"], $target_file)) {
        // Ambil data admin untuk hapus foto lama
        $query_admin = "SELECT foto_profil FROM admin WHERE email = '$admin_email'";
        $result_admin = mysqli_query($koneksi, $query_admin);
        $admin_data = mysqli_fetch_assoc($result_admin);
        
        // Hapus foto lama jika ada
        if (!empty($admin_data['foto_profil']) && file_exists($admin_data['foto_profil'])) {
            unlink($admin_data['foto_profil']);
        }
        
        // Update database
        $update_foto = "UPDATE admin SET foto_profil = '$target_file', updated_at = NOW() WHERE email = '$admin_email'";
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
    $query_admin = "SELECT foto_profil FROM admin WHERE email = '$admin_email'";
    $result_admin = mysqli_query($koneksi, $query_admin);
    $admin_data = mysqli_fetch_assoc($result_admin);
    
    if (!empty($admin_data['foto_profil']) && file_exists($admin_data['foto_profil'])) {
        unlink($admin_data['foto_profil']);
    }
    
    $update_foto = "UPDATE admin SET foto_profil = NULL, updated_at = NOW() WHERE email = '$admin_email'";
    if (mysqli_query($koneksi, $update_foto)) {
        $success = "Foto profil berhasil dihapus.";
    } else {
        $error = "Gagal menghapus foto profil.";
    }
}

// Ambil data admin
$query_admin = "SELECT * FROM admin WHERE email = '$admin_email'";
$result_admin = mysqli_query($koneksi, $query_admin);
$admin = mysqli_fetch_assoc($result_admin);

// Ambil statistik admin
$total_buku_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
$total_buku = $total_buku_query ? mysqli_fetch_assoc($total_buku_query)['total'] : 0;

$total_anggota_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM anggota");
$total_anggota = $total_anggota_query ? mysqli_fetch_assoc($total_anggota_query)['total'] : 0;

$total_peminjaman_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman");
$total_peminjaman = $total_peminjaman_query ? mysqli_fetch_assoc($total_peminjaman_query)['total'] : 0;

$total_request_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM request_buku");
$total_request = $total_request_query ? mysqli_fetch_assoc($total_request_query)['total'] : 0;

// Aktivitas admin terbaru (simulasi - bisa diganti dengan log table)
$aktivitas_terbaru = [
    ['aksi' => 'Menambah buku baru', 'waktu' => '2 jam yang lalu', 'detail' => 'Buku "Pemrograman Web"'],
    ['aksi' => 'Menyetujui request buku', 'waktu' => '5 jam yang lalu', 'detail' => 'Request dari anggota@email.com'],
    ['aksi' => 'Mengelola peminjaman', 'waktu' => '1 hari yang lalu', 'detail' => 'Peminjaman ID #001'],
    ['aksi' => 'Update profil', 'waktu' => '3 hari yang lalu', 'detail' => 'Mengubah informasi kontak'],
];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Admin - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .admin-profile-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .admin-profile-header {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        
        .admin-avatar {
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
        
        .admin-name {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .admin-email {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .admin-role {
            background: rgba(255, 255, 255, 0.2);
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 15px;
            display: inline-block;
        }
        
        .admin-tab-navigation {
            display: flex;
            background: #f8f9ff;
            border-radius: 15px;
            padding: 8px;
            margin-bottom: 30px;
        }
        
        .admin-tab-btn {
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
        
        .admin-tab-btn.active {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }
        
        .admin-tab-content {
            display: none;
        }
        
        .admin-tab-content.active {
            display: block;
        }

        .admin-profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .admin-profile-grid {
            display: none; /* Hide the old grid */
        }
        
        .admin-stats-grid {
            display: none;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .admin-stat-item {
            text-align: center;
            padding: 20px 15px;
            background: linear-gradient(135deg, #f8f9ff, #e8f0ff);
            border-radius: 12px;
            border: 1px solid rgba(78, 115, 223, 0.1);
            transition: all 0.3s ease;
        }
        
        .admin-stat-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(78, 115, 223, 0.15);
        }
        
        .admin-stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #4e73df;
            margin-bottom: 5px;
            display: block;
        }
        
        .admin-stat-label {
            color: #666;
            font-size: 12px;
            font-weight: 500;
        }
        
        .admin-dashboard-grid {
            display: none;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .admin-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        
        .admin-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        
        .admin-section-header {
            background: linear-gradient(135deg, #f8f9ff, #e8f0ff);
            padding: 25px 30px;
            border-bottom: 1px solid rgba(78, 115, 223, 0.1);
            position: relative;
        }
        
        .admin-section-header::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: linear-gradient(135deg, #4e73df, #224abe);
        }
        
        .admin-section-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .admin-section-body {
            padding: 30px;
        }

        .admin-activity-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .admin-activity-item {
            display: flex;
            align-items: center;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
            background: #fafbff;
        }
        
        .admin-activity-item:hover {
            border-color: #4e73df;
            background: #f0f4ff;
            transform: translateX(5px);
        }
        
        .admin-activity-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #4e73df, #224abe);
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
        
        .admin-activity-details {
            flex: 1;
        }
        
        .admin-activity-title {
            font-weight: 600;
            color: #333;
            margin: 0 0 5px 0;
            font-size: 16px;
        }
        
        .admin-activity-subtitle {
            color: #666;
            font-size: 14px;
            margin: 0;
        }
        
        .admin-activity-time {
            color: #4e73df;
            font-weight: 500;
            font-size: 14px;
            text-align: right;
        }

        .admin-form-section {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        
        
        

        @media (max-width: 1200px) {
            .admin-profile-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .admin-profile-container {
                padding: 15px;
            }
            
            .admin-stats-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
            }
        }

        /* New CSS */
        .content-grid {
            display: none;
            grid-template-columns: 300px 1fr; /* Sidebar width and remaining space */
            gap: 30px;
        }

        .content-sidebar {
            /* Styles for the sidebar */
        }

        .content-main {
            /* Styles for the main content area */
        }

        .stats-grid {
            display: none;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Responsive stat cards */
            gap: 20px;
        }

        .stat-card {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .stat-icon {
            font-size: 24px;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            margin-bottom: 10px;
        }

        .stat-number {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }

        .stat-label {
            font-size: 14px;
            color: #666;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr; /* Stack on smaller screens */
            }

            .content-sidebar {
                margin-bottom: 30px; /* Add space before main content */
            }
        }

        /* General Styles */
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f7f9;
            color: #333;
            line-height: 1.6;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }

        .content-section {
            background: #fff;
            padding: 30px;
            margin-bottom: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }

        .section-header h2 {
            font-size: 24px;
            color: #333;
            margin: 0;
        }

        /* Navbar Styles */
        .navbar {
            background: #4e73df;
            color: #fff;
            padding: 15px 0;
            margin-bottom: 30px;
        }

        .navbar-container {
            max-width: 1200px;
            margin: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px;
        }

        .navbar-brand {
            font-size: 24px;
            font-weight: bold;
            color: #fff;
            text-decoration: none;
        }

        .navbar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        .nav-item {
            margin-left: 20px;
        }

        .nav-link {
            color: #fff;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .nav-link:hover,
        .nav-link.active {
            color: #ffffff;
        }

        /* Welcome Section Styles */
        .welcome-section {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: #fff; 
            padding: 50px 0;
            margin-bottom: 30px;
            border-radius: 10px;
            text-align: center;
        }

        .welcome-section * {
            color: #ffffff !important; 
        }

        .welcome-text {
            max-width: 800px;
            margin: auto;
            padding: 0 20px;
        }

        .welcome-text h1 {
            font-size: 36px;
            margin-bottom: 20px;
            color: #ffffff; 
        }

        .welcome-text p {
            font-size: 18px;
            opacity: 0.8;
            color: #ffffff;
        }

        /* Stats Row Styles */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .stat-icon {
            font-size: 48px;
            margin-bottom: 15px;
            color: #fff;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .stat-icon.books {
            background: #17a2b8;
        }

        .stat-icon.members {
            background: #28a745;
        }

        .stat-icon.borrowed {
            background: #ffc107;
            color: #333;
        }

        .stat-icon.requests {
            background: #dc3545;
        }

        .stat-info h3 {
            font-size: 22px;
            margin-bottom: 10px;
        }

        .stat-info p {
            font-size: 18px;
            color: #666;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 16px;
            margin-bottom: 8px;
            color: #555;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            color: #444;
            transition: border-color 0.3s ease;
        }

        .form-control:focus {
            border-color: #4e73df;
            outline: none;
            box-shadow: 0 0 5px rgba(78, 115, 223, 0.3);
        }

        .btn {
            background: #4e73df;
            color: #fff;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn:hover {
            background: #2e59d9;
        }

        /* Alert Styles */
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 16px;
            opacity: 1;
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: translateY(0);
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Footer Styles */
        .footer {
            background: #4e73df;
            color: #fff;
            text-align: center;
            padding: 20px 0;
            margin-top: 30px;
        }

        .footer p {
            margin: 0;
            font-size: 14px;
        }

        /* Responsive Styles */
        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }

            .welcome-text h1 {
                font-size: 30px;
            }

            .welcome-text p {
                font-size: 16px;
            }

            .stats-row {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            }

            .stat-card {
                padding: 20px;
            }

            .stat-info h3 {
                font-size: 20px;
            }

            .stat-info p {
                font-size: 16px;
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
                <li class="nav-item"><a href="peminjaman.php" class="nav-link">Peminjaman</a></li>
                <li class="nav-item"><a href="profil_admin.php" class="nav-link active">Profil</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php
        // Tampilkan pesan jika ada
        if (!empty($success)) {
            echo "<div class='alert alert-success'>";
            echo "✅ " . $success;
            echo "</div>";
        }
        
        if (!empty($error)) {
            echo "<div class='alert alert-error'>";
            echo "❌ " . $error;
            echo "</div>";
        }
        ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Profil Administrator</h1>
                <p>Kelola informasi dan pengaturan akun administrator sistem perpustakaan digital.</p>
                <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
                <a href="manajemen.php" class="btn" style="margin-left: 10px; background: #28a745;">Kelola Buku</a>
            </div>
            <div class="welcome-image"></div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon books">📚</div>
                <div class="stat-info">
                    <h3>Total Buku</h3>
                    <p><?php echo number_format($total_buku); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon members">👥</div>
                <div class="stat-info">
                    <h3>Total Anggota</h3>
                    <p><?php echo number_format($total_anggota); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon borrowed">📖</div>
                <div class="stat-info">
                    <h3>Total Peminjaman</h3>
                    <p><?php echo number_format($total_peminjaman); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon requests">⏳</div>
                <div class="stat-info">
                    <h3>Total Request</h3>
                    <p><?php echo number_format($total_request); ?></p>
                </div>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="admin-profile-card">
            <div class="admin-profile-header">
                <div class="admin-avatar">
                    <?php if (!empty($admin['foto_profil']) && file_exists($admin['foto_profil'])): ?>
                        <img src="<?php echo $admin['foto_profil']; ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                    <?php else: ?>
                        <?php echo strtoupper(substr($admin['nama'] ?? 'Admin', 0, 1)); ?>
                    <?php endif; ?>
                </div>
                <h2 class="admin-name"><?php echo htmlspecialchars($admin['nama'] ?? 'Administrator'); ?></h2>
                <p class="admin-email"><?php echo htmlspecialchars($admin['email']); ?></p>
                <span class="admin-role">👑 Administrator</span>
            </div>
        </div>

        <!-- Profile Management Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>⚙️ Pengaturan Profil Admin</h2>
            </div>
            
            <div class="admin-tab-navigation">
                <button class="admin-tab-btn active" onclick="showAdminTab('profil')">✏️ Edit Profil</button>
                <button class="admin-tab-btn" onclick="showAdminTab('password')">🔒 Ganti Password</button>
                <button class="admin-tab-btn" onclick="showAdminTab('foto')">📷 Foto Profil</button>
            </div>
            
            <!-- Tab Edit Profil -->
            <div id="admin-profil-tab" class="admin-tab-content active">
                <form method="POST">
                    <div class="form-group">
                        <label for="nama">👤 Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama" name="nama" 
                               value="<?php echo htmlspecialchars($admin['nama'] ?? ''); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">📧 Email</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="telepon">📱 Nomor Telepon</label>
                        <input type="text" class="form-control" id="telepon" name="telepon" 
                               value="<?php echo htmlspecialchars($admin['telepon'] ?? ''); ?>"
                               placeholder="Contoh: 08123456789">
                    </div>
                    
                    <div class="form-group">
                        <label for="alamat">🏠 Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3" 
                                  placeholder="Masukkan alamat lengkap Anda"><?php echo htmlspecialchars($admin['alamat'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_profil" class="btn">
                        💾 Simpan Perubahan
                    </button>
                </form>
            </div>
            
            <!-- Tab Ganti Password -->
            <div id="admin-password-tab" class="admin-tab-content">
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
                    
                    <button type="submit" name="ganti_password" class="btn" style="background: #6c757d;">
                        🔒 Ganti Password
                    </button>
                </form>
            </div>

            <!-- Tab Foto Profil -->
            <div id="admin-foto-tab" class="admin-tab-content">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="width: 150px; height: 150px; margin: 0 auto 20px; border-radius: 50%; overflow: hidden; border: 4px solid #4e73df;">
                        <?php if (!empty($admin['foto_profil']) && file_exists($admin['foto_profil'])): ?>
                            <img src="<?php echo $admin['foto_profil']; ?>" alt="Foto Profil" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <div style="width: 100%; height: 100%; background: linear-gradient(135deg, #4e73df, #224abe); display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;">
                                <?php echo strtoupper(substr($admin['nama'] ?? 'A', 0, 1)); ?>
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
                        <button type="submit" name="upload_foto" class="btn">
                            📤 Upload Foto
                        </button>
                        
                        <?php if (!empty($admin['foto_profil'])): ?>
                            <button type="submit" name="hapus_foto" class="btn" style="background: #6c757d;" 
                                    onclick="return confirm('Yakin ingin menghapus foto profil?')">
                                🗑️ Hapus Foto
                            </button>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Admin Info Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>📋 Informasi Administrator</h2>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <div style="padding: 20px; background: #f8f9ff; border-radius: 12px; border-left: 4px solid #4e73df;">
                    <h4 style="color: #333; margin-bottom: 15px;">📅 Informasi Akun</h4>
                    <div style="line-height: 1.8;">
                        <div><strong>Bergabung:</strong> <?php echo $admin['created_at'] ? date('d/m/Y', strtotime($admin['created_at'])) : 'Tidak diketahui'; ?></div>
                        <div><strong>Update Terakhir:</strong> <?php echo $admin['updated_at'] ? date('d/m/Y H:i', strtotime($admin['updated_at'])) : 'Belum pernah'; ?></div>
                    </div>
                </div>
                
                <div style="padding: 20px; background: #f8f9ff; border-radius: 12px; border-left: 4px solid #28a745;">
                    <h4 style="color: #333; margin-bottom: 15px;">📞 Kontak</h4>
                    <div style="line-height: 1.8;">
                        <div><strong>Telepon:</strong> <?php echo htmlspecialchars($admin['telepon'] ?? 'Belum diisi'); ?></div>
                        <div><strong>Alamat:</strong> <?php echo htmlspecialchars($admin['alamat'] ?? 'Belum diisi'); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
    </div>

    <script>
    function showAdminTab(tabName) {
        // Hide all tabs
        var tabs = document.querySelectorAll('.admin-tab-content');
        tabs.forEach(function(tab) {
            tab.classList.remove('active');
        });
        
        // Remove active class from all buttons
        var buttons = document.querySelectorAll('.admin-tab-btn');
        buttons.forEach(function(btn) {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById('admin-' + tabName + '-tab').classList.add('active');
        
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
        const passwordForm = document.querySelector('#admin-password-tab form');
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
    </script>
</body>
</html>
