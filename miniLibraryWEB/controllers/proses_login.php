<?php
include "../koneksi.php";
session_start();

// Redirect jika sudah login
if (isset($_SESSION['email'])) {
    header("Location: ../dashboard.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitasi input untuk mencegah SQL Injection
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password']; // Password tidak perlu di-escape karena tidak digunakan dalam query SQL
    
    // Cek user di database
    $query = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
    
    if (mysqli_num_rows($query) == 1) {
        $user = mysqli_fetch_assoc($query);
        
        // Verifikasi password dengan password_verify
        if (password_verify($password, $user['password'])) {
            // Set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['email'] = $email;
            
            // Redirect ke dashboard
            header("Location: ../dashboard.php");
            exit;
        } else {
            // Password salah
            $_SESSION['login_error'] = "Email atau password salah!";
            header("Location: ../login.php");
            exit;
        }
    } else {
        // User tidak ditemukan
        $_SESSION['login_error'] = "Email atau password salah!";
        header("Location: ../login.php");
        exit;
    }
} else {
    // Jika bukan method POST, redirect ke halaman login
    header("Location: ../login.php");
    exit;
}
?>
