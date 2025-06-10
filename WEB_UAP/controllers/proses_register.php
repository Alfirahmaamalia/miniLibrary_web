<?php
include "../koneksi.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Cek apakah email sudah ada
    $check = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
    if (mysqli_num_rows($check) > 0) {
        echo "Email sudah terdaftar. <a href='register.php'>Kembali</a>";
    } else {
        // Simpan user baru
        $query = mysqli_query($koneksi, "INSERT INTO users (email, password) VALUES ('$email', '$password')");
        if ($query) {
            echo "Registrasi berhasil. <a href='login.php'>Login sekarang</a>";
        } else {
            echo "Gagal registrasi: " . mysqli_error($conn);
        }
    }
}
?>
