<?php
$servername = "localhost";
$username = "root";
$password = ""; // Kosong untuk XAMPP default
$dbname = "rull1"; // Ganti dengan nama database Anda

// Buat koneksi menggunakan mysqli
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset untuk menghindari masalah encoding
$conn->set_charset("utf8");
?>