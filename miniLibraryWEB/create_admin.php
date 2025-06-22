<?php
// File untuk membuat user admin dengan password yang di-hash
// Jalankan file ini sekali saja, lalu hapus

include 'koneksi.php';

// Data admin
$email = 'admin@minilibrary.com';
$password = 'admin123'; // Password yang akan di-hash

// Hash password
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Cek apakah admin sudah ada
$check = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");

if (mysqli_num_rows($check) == 0) {
    // Insert admin baru
    $result = mysqli_query($koneksi, "INSERT INTO users (email, password) VALUES ('$email', '$password_hash')");
    
    if ($result) {
        echo "Admin berhasil dibuat!<br>";
        echo "Email: $email<br>";
        echo "Password: admin123<br>";
        echo "<br><strong>HAPUS FILE INI SETELAH SELESAI!</strong>";
    } else {
        echo "Gagal membuat admin: " . mysqli_error($koneksi);
    }
} else {
    echo "Admin sudah ada!";
}
?>
