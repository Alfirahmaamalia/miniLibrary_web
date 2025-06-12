<?php
require '../koneksi.php';
session_start();

// Redirect jika sudah login
if (isset($_SESSION['email'])) {
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitasi input
    $email = mysqli_real_escape_string($koneksi, $_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];

    // Validasi input
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $_SESSION['register_error'] = "Semua field harus diisi!";
        header("Location: ../register.php");
        exit;
    } 
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Format email tidak valid!";
        header("Location: ../register.php");
        exit;
    } 
    
    if (strlen($password) < 6) {
        $_SESSION['register_error'] = "Password minimal 6 karakter!";
        header("Location: ../register.php");
        exit;
    } 
    
    if ($password !== $confirm_password) {
        $_SESSION['register_error'] = "Konfirmasi password tidak cocok!";
        header("Location: ../register.php");
        exit;
    }

    // Cek apakah email sudah terdaftar
    $check_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $_SESSION['register_error'] = "Email sudah terdaftar!";
        header("Location: ../register.php");
        exit;
    }

    // Hash password dan simpan ke database
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $result = mysqli_query($koneksi, "INSERT INTO users (email, password) VALUES ('$email', '$password_hash')");
    
    if ($result) {
        $_SESSION['register_success'] = "Registrasi berhasil! Silakan login.";
        header("Location: ../login.php");
        exit;
    } else {
        $_SESSION['register_error'] = "Registrasi gagal: " . mysqli_error($koneksi);
        header("Location: ../register.php");
        exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman register
    header("Location: ../register.php");
    exit;
}
?>
