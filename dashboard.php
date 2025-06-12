<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Include koneksi untuk mengambil data
include 'koneksi.php';

// Query untuk mendapatkan statistik dengan error handling (sesuai struktur database yang ada)
$total_buku_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
$total_buku = $total_buku_query ? mysqli_fetch_assoc($total_buku_query)['total'] : 0;

$total_anggota_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM anggota");
$total_anggota = $total_anggota_query ? mysqli_fetch_assoc($total_anggota_query)['total'] : 0;

// Karena belum ada tabel peminjaman, set ke 0
$peminjaman_aktif = 0;

// Buku tersedia berdasarkan stok > 0 (stok adalah varchar, jadi perlu casting)
$buku_tersedia_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku WHERE CAST(stok AS UNSIGNED) > 0");
$buku_tersedia = $buku_tersedia_query ? mysqli_fetch_assoc($buku_tersedia_query)['total'] : 0;
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a class="active" href="dashboard.php">📊 Beranda</a>
    <a href="manajemen.php">📚 Manajemen Buku</a>
    <a href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Beranda</h1>
        <div class="profile">
            <span>Selamat datang, Admin</span>
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Buku</h3>
            <p><?php echo number_format($total_buku); ?></p>
        </div>
        <div class="card">
            <h3>Total Anggota</h3>
            <p><?php echo number_format($total_anggota); ?></p>
        </div>
        <div class="card">
            <h3>Peminjaman Aktif</h3>
            <p><?php echo number_format($peminjaman_aktif); ?></p>
        </div>
        <div class="card">
            <h3>Buku Tersedia</h3>
            <p><?php echo number_format($buku_tersedia); ?></p>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <h3>Kategori Terpopuler</h3>
        <div class="kategori-list">
            
        </div>
    </div>
</div>

</body>
</html>
