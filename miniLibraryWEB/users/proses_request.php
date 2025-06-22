<?php
session_start();
if (!isset($_SESSION['anggota_id'])) {
    header("Location: login_anggota.php");
    exit;
}

include '../koneksi/koneksi.php';

// Buat tabel request_buku jika belum ada
$create_request_table = "CREATE TABLE IF NOT EXISTS request_buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul_buku VARCHAR(255) NOT NULL,
    penulis VARCHAR(255) NOT NULL,
    kategori INT NOT NULL,
    alasan TEXT NOT NULL,
    email_pemohon VARCHAR(255) NOT NULL,
    tanggal_request DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    keterangan TEXT,
    approved_by VARCHAR(255),
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_request_table);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    // Ambil data anggota
    $anggota_id = $_SESSION['anggota_id'];
    $query_anggota = mysqli_query($koneksi, "SELECT email FROM anggota WHERE id = $anggota_id");
    $anggota = mysqli_fetch_assoc($query_anggota);
    
    $judul_buku = trim(mysqli_real_escape_string($koneksi, $_POST['judul_buku']));
    $penulis = trim(mysqli_real_escape_string($koneksi, $_POST['penulis']));
    $kategori = (int)$_POST['kategori'];
    $alasan = trim(mysqli_real_escape_string($koneksi, $_POST['alasan']));
    $email_pemohon = $anggota['email'];
    $tanggal_request = date('Y-m-d H:i:s');
    
    // Validasi input
    $errors = [];
    if (empty($judul_buku)) $errors[] = "Judul buku tidak boleh kosong";
    if (empty($penulis)) $errors[] = "Nama penulis tidak boleh kosong";
    if ($kategori < 1 || $kategori > 7) $errors[] = "Kategori tidak valid";
    if (empty($alasan)) $errors[] = "Alasan permintaan tidak boleh kosong";
    if (strlen($alasan) < 20) $errors[] = "Alasan permintaan minimal 20 karakter";
    
    // Cek duplikasi request dalam 24 jam terakhir
    $check_duplicate = mysqli_query($koneksi, "SELECT id FROM request_buku 
                                              WHERE email_pemohon = '$email_pemohon' 
                                              AND judul_buku = '$judul_buku' 
                                              AND penulis = '$penulis'
                                              AND tanggal_request >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    if (mysqli_num_rows($check_duplicate) > 0) {
        $errors[] = "Anda sudah mengajukan request untuk buku yang sama dalam 24 jam terakhir";
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO request_buku (judul_buku, penulis, kategori, alasan, email_pemohon, tanggal_request, status) 
                  VALUES ('$judul_buku', '$penulis', $kategori, '$alasan', '$email_pemohon', '$tanggal_request', 'pending')";
        
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['message'] = "Permintaan buku berhasil diajukan! Menunggu persetujuan admin.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($koneksi);
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
    }
}

header("Location: beranda_pengguna.php");
exit;
?>
