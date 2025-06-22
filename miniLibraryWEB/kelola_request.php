<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login_anggota.php");
    exit;
}

include 'koneksi.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Request Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="index.php">📊 Beranda</a>
    <a href="katalog.php">📚 Katalog Buku</a>
    <a href="pinjaman_saya.php">📖 Pinjaman Saya</a>
    <a class="active" href="simple_request.php">📝 Request Buku</a>
    <a href="profil.php">👤 Profil</a>
    <a href="logout_anggota.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Request Buku Baru</h1>
        <div class="profile">
            <span>Anggota: <?php echo htmlspecialchars($_SESSION['email']); ?></span>
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="card">
        <h3>📝 Ajukan Permintaan Buku</h3>
        <p>Halaman ini berfungsi dengan baik! Anda dapat mengajukan permintaan buku baru di sini.</p>
        
        <form method="POST" style="margin-top: 20px;">
            <div style="margin-bottom: 15px;">
                <label>Judul Buku:</label>
                <input type="text" name="judul_buku" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Penulis:</label>
                <input type="text" name="penulis" required style="width: 100%; padding: 8px; margin-top: 5px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Alasan:</label>
                <textarea name="alasan" required style="width: 100%; padding: 8px; margin-top: 5px; height: 80px;"></textarea>
            </div>
            
            <button type="submit" name="submit_request" style="background: #28a745; color: white; padding: 10px 20px; border: none; border-radius: 5px;">
                Ajukan Request
            </button>
        </form>

        <?php
        if (isset($_POST['submit_request'])) {
            echo "<div style='background: #d4edda; color: #155724; padding: 15px; margin-top: 20px; border-radius: 5px;'>";
            echo "✅ Request berhasil diajukan! (Demo - belum tersimpan ke database)";
            echo "</div>";
        }
        ?>
    </div>
</div>

</body>
</html>
