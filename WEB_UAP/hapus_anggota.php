<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID anggota tidak valid!";
    $_SESSION['message_type'] = "error";
    header("Location: anggota.php");
    exit;
}

include 'koneksi.php';

// Ambil ID anggota dan pastikan itu angka
$id = mysqli_real_escape_string($conn, $_GET['id']);

// Cek apakah anggota dengan ID tersebut ada
$check_query = "SELECT id, nama FROM anggota WHERE id = '$id'";
$check_result = mysqli_query($conn, $check_query);

if (!$check_result || mysqli_num_rows($check_result) == 0) {
    $_SESSION['message'] = "Anggota dengan ID tersebut tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: anggota.php");
    exit;
}

$anggota = mysqli_fetch_assoc($check_result);
$nama_anggota = $anggota['nama'];

// Cek apakah ada konfirmasi dari form
if (isset($_POST['confirm']) && $_POST['confirm'] == 'yes') {
    // Hapus anggota dari database
    $delete_query = "DELETE FROM anggota WHERE id = '$id'";
    
    if (mysqli_query($conn, $delete_query)) {
        $_SESSION['message'] = "Anggota '$nama_anggota' berhasil dihapus!";
        $_SESSION['message_type'] = "success";
        header("Location: anggota.php");
        exit;
    } else {
        $_SESSION['message'] = "Error: " . mysqli_error($conn);
        $_SESSION['message_type'] = "error";
        header("Location: anggota.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Hapus Anggota - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .confirmation-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 500px;
            margin: 0 auto;
            text-align: center;
        }

        .confirmation-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }

        .confirmation-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 15px;
        }

        .confirmation-message {
            font-size: 16px;
            color: #666;
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .confirmation-actions {
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .back-link::before {
            content: "←";
            margin-right: 8px;
            font-size: 16px;
        }

        .member-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: left;
        }

        .member-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .member-info strong {
            font-weight: 600;
            color: #333;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="dashboard.php">📊 Beranda</a>
    <a href="manajemen.php">📚 Manajemen Buku</a>
    <a class="active" href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Hapus Anggota</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <a href="anggota.php" class="back-link">Kembali ke Daftar Anggota</a>

    <div class="confirmation-container">
        <div class="confirmation-icon">🗑️</div>
        <h2 class="confirmation-title">Konfirmasi Penghapusan</h2>
        
        <div class="member-info">
            <p><strong>ID:</strong> <?php echo str_pad($id, 3, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Nama:</strong> <?php echo htmlspecialchars($nama_anggota); ?></p>
        </div>
        
        <p class="confirmation-message">
            Apakah Anda yakin ingin menghapus anggota ini?<br>
            <strong>Perhatian:</strong> Tindakan ini tidak dapat dibatalkan!
        </p>
        
        <form method="POST" action="">
            <input type="hidden" name="confirm" value="yes">
            <div class="confirmation-actions">
                <button type="submit" class="btn btn-danger">Ya, Hapus Anggota</button>
                <a href="anggota.php" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>