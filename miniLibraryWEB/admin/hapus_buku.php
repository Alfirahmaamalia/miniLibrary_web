<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

// Cek apakah ada parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = "ID buku tidak valid!";
    $_SESSION['message_type'] = "error";
    header("Location: manajemen.php");
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah buku ada
$check_query = mysqli_query($koneksi, "SELECT `judul buku` FROM buku WHERE id = $id");
if (!$check_query || mysqli_num_rows($check_query) == 0) {
    $_SESSION['message'] = "Buku tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: manajemen.php");
    exit;
}

$buku = mysqli_fetch_assoc($check_query);
$judul_buku = $buku['judul buku'];

// Hapus buku
$delete_query = mysqli_query($koneksi, "DELETE FROM buku WHERE id = $id");
if ($delete_query) {
    $_SESSION['message'] = "Buku '$judul_buku' berhasil dihapus!";
    $_SESSION['message_type'] = "success";
} else {
    $_SESSION['message'] = "Gagal menghapus buku: " . mysqli_error($koneksi);
    $_SESSION['message_type'] = "error";
}

header("Location: manajemen.php");
exit;
?>
