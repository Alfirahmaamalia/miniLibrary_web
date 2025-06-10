<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau kata sandi salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniLibrary - Login</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="left"></div>
    <div class="right">
        <div class="logo">MiniLibrary</div>
        <h2>Hai! Selamat Datang Kembali!</h2>
        <form method="POST" action="controllers/proses_login.php">
            <input type="email" name="email" placeholder="Masukan email" required>
            <input type="password" name="password" placeholder="Masukan Kata Sandi" required>
            <button type="submit" name="submit">Masuk</button>
        </form>
        <a href="register.php" class="secondary">Buat akun</a>
    </div>
</body>
</html>
