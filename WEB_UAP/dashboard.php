<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
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
    <a href="kategori">📂 Kategori Buku</a>
    <a href="peminjaman">🔒 Peminjaman</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Beranda</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="cards">
        <div class="card">
            <h3>Total Buku</h3>
            <p>1.886</p>
        </div>
        <div class="card">
            <h3>Total Anggota</h3>
            <p>1.886</p>
        </div>
        <div class="card">
            <h3>Peminjaman Aktif</h3>
            <p>1.000</p>
        </div>
        <div class="card">
            <h3>Buku Tersedia</h3>
            <p>1.886</p>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <h3>Kategori Terpopuler</h3>
        <p></p>
    </div>
</div>

</body>
</html>
