<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include 'koneksi.php';

// Cek apakah ada parameter id
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: anggota.php");
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah anggota ada
$check_query = mysqli_query($koneksi, "SELECT id FROM anggota WHERE id = $id");
if (!$check_query || mysqli_num_rows($check_query) == 0) {
    $_SESSION['error'] = "Anggota tidak ditemukan!";
    header("Location: anggota.php");
    exit;
}

// Hapus anggota
$delete_query = mysqli_query($koneksi, "DELETE FROM anggota WHERE id = $id");
if ($delete_query) {
    $_SESSION['success'] = "Anggota berhasil dihapus!";
} else {
    $_SESSION['error'] = "Gagal menghapus anggota: " . mysqli_error($koneksi);
}

header("Location: anggota.php");
exit;
?>
