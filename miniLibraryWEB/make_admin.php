<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['user_email'])) {
    $user_email = mysqli_real_escape_string($koneksi, $_POST['user_email']);
    
    // Cek apakah user ada di tabel users
    $user_check = mysqli_query($koneksi, "SELECT email, password FROM users WHERE email = '$user_email'");
    
    if (mysqli_num_rows($user_check) > 0) {
        $user_data = mysqli_fetch_assoc($user_check);
        
        // Cek apakah sudah admin
        $admin_check = mysqli_query($koneksi, "SELECT email FROM admin WHERE email = '$user_email'");
        
        if (mysqli_num_rows($admin_check) == 0) {
            // Jadikan admin
            $make_admin = "INSERT INTO admin (email, password, nama, role) 
                           VALUES ('$user_email', '{$user_data['password']}', '$user_email', 'admin')";
            
            if (mysqli_query($koneksi, $make_admin)) {
                echo "✅ User $user_email berhasil dijadikan admin!<br>";
                echo "<a href='setup_database.php'>Kembali ke Setup</a>";
            } else {
                echo "❌ Error: " . mysqli_error($koneksi);
            }
        } else {
            echo "⚠️ User $user_email sudah menjadi admin!<br>";
            echo "<a href='setup_database.php'>Kembali ke Setup</a>";
        }
    } else {
        echo "❌ User tidak ditemukan!<br>";
        echo "<a href='setup_database.php'>Kembali ke Setup</a>";
    }
} else {
    header("Location: setup_database.php");
    exit;
}
?>
