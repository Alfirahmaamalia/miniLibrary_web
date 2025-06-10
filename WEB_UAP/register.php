<?php
require 'koneksi.php';

if (isset($_POST["submit"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    if ($password === $confirm_password) {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $result = mysqli_query($koneksi, "INSERT INTO users (email, password) VALUES ('$email', '$password')");
        if ($result) {
            header("Location: login.php");
        } else {
            echo "<script>alert('Registrasi gagal');</script>";
        }
    } else {
        echo "<script>alert('Konfirmasi password tidak cocok');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniLibrary - Daftar</title>
    <link rel="stylesheet" href="assets/style.css">

</head>
<body>
    <div class="left"></div>
    <div class="right">
        <div class="logo">MiniLibrary</div>
        <h2>Buat akunmu</h2>
        <form method="POST" action="controllers/proses_register.php">
            <input type="email" name="email" placeholder="Masukan email" required>
            <input type="password" name="password" placeholder="Masukan Kata Sandi" required>
            <input type="password" name="confirm_password" placeholder="Masukan Kata Sandi" required>
            <div class="checkbox">
                <input type="checkbox" required>
                <label>Saya menyetujui <span class="terms"><a href="#">Ketentuan Layanan</a></span> dan <span class="terms"><a href="#">Kebijakan Privasi</a></span></label>
            </div>
            <button type="submit" name="submit">Selanjutnya</button>
        </form>
        <a href="login.php" class="secondary">Sudah punya akun</a>
    </div>
</body>
</html>
