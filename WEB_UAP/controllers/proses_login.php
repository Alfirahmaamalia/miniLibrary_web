<?php
include "../koneksi.php";
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND password='$password'");
    if (mysqli_num_rows($query) == 1) {
        $_SESSION['email'] = $email;
        header("Location: ../dashboard.php");
        exit;
    } else {
        echo "Login gagal! Email atau password salah. <a href='login.php'>Coba lagi</a>";
    }
}
?>
