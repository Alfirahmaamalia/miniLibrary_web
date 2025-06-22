<?php
$host = "localhost";
$username = "root";
$password = "";
$database = "projek_web"; 

// Membuat koneksi
$koneksi = mysqli_connect($host, $username, $password, $database);

// Cek koneksi
if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}


// Set charset untuk menghindari masalah encoding
mysqli_set_charset($koneksi, "utf8mb4");

// Fungsi untuk debug (hapus di production)
function debug_query($query, $koneksi) {
    $result = mysqli_query($koneksi, $query);
    if (!$result) {
        echo "Error: " . mysqli_error($koneksi) . "<br>";
        echo "Query: " . $query . "<br>";
    }
    return $result;
}
?>
