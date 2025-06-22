<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

include '../koneksi/koneksi.php';

// Buat tabel admin jika belum ada (fallback)
$create_admin_table = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(255) DEFAULT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_admin_table);

// Buat tabel admin_log jika belum ada
$create_log_table = "CREATE TABLE IF NOT EXISTS admin_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_email VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    target_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_log_table);

// Update tabel request_buku jika belum ada kolom approved_by
$alter_request = "ALTER TABLE request_buku 
    ADD COLUMN IF NOT EXISTS approved_by VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS buku_id INT DEFAULT NULL";
mysqli_query($koneksi, $alter_request);

// Cek apakah user ada di tabel admin
$user_email = mysqli_real_escape_string($koneksi, $_SESSION['email']);
$admin_check = mysqli_query($koneksi, "SELECT email, role FROM admin WHERE email = '$user_email'");

if (mysqli_num_rows($admin_check) == 0) {
    // Jika tidak ada di tabel admin, coba cek di tabel users dan jadikan admin otomatis
    $user_check = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$user_email'");
    if (mysqli_num_rows($user_check) > 0) {
        // Auto-promote user menjadi admin
        $user_data = mysqli_fetch_assoc($user_check);
        $auto_admin = "INSERT IGNORE INTO admin (email, password, nama, role) 
                       SELECT email, password, email, 'admin' FROM users WHERE email = '$user_email'";
        mysqli_query($koneksi, $auto_admin);
        
        $_SESSION['message'] = "✅ Anda telah otomatis dijadikan admin!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "❌ Akses ditolak! Anda bukan admin.";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = (int)$_POST['request_id'];
    $action = mysqli_real_escape_string($koneksi, $_POST['action']);
    $keterangan = mysqli_real_escape_string($koneksi, trim($_POST['keterangan']));
    $approved_by = $user_email;
    $approved_at = date('Y-m-d H:i:s');
    
    // Validasi keterangan
    if (strlen($keterangan) < 10) {
        $_SESSION['message'] = "❌ Keterangan minimal 10 karakter!";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit;
    }
    
    // Cek apakah request exists
    $check_request = mysqli_query($koneksi, "SELECT * FROM request_buku WHERE id = $request_id");
    if (mysqli_num_rows($check_request) == 0) {
        $_SESSION['message'] = "❌ Request tidak ditemukan!";
        $_SESSION['message_type'] = "error";
        header("Location: dashboard.php");
        exit;
    }
    
    $request_data = mysqli_fetch_assoc($check_request);
    
    $status = '';
    $message = '';
    $buku_id = null;
    
    switch($action) {
        case 'approve':
            if ($request_data['status'] != 'pending') {
                $_SESSION['message'] = "❌ Request sudah diproses sebelumnya!";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit;
            }
            $status = 'approved';
            $message = "✅ Request buku '{$request_data['judul_buku']}' berhasil disetujui!";
            break;
            
        case 'reject':
            if ($request_data['status'] != 'pending') {
                $_SESSION['message'] = "❌ Request sudah diproses sebelumnya!";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit;
            }
            $status = 'rejected';
            $message = "❌ Request buku '{$request_data['judul_buku']}' berhasil ditolak!";
            break;
            
        case 'complete':
            if ($request_data['status'] != 'approved') {
                $_SESSION['message'] = "❌ Request harus disetujui terlebih dahulu!";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit;
            }
            
            // Ketika complete, tambahkan buku ke tabel buku
            $judul_buku = mysqli_real_escape_string($koneksi, $request_data['judul_buku']);
            $penulis = mysqli_real_escape_string($koneksi, $request_data['penulis']);
            $kategori = (int)$request_data['kategori'];
            $tahun_terbit = date('Y'); // Default tahun sekarang
            $stok = 5; // Default stok 5
            $status_buku = 1; // Status tersedia
            
            // Cek apakah buku sudah ada (untuk menghindari duplikasi)
            $check_existing = mysqli_query($koneksi, "SELECT id FROM buku WHERE `judul buku` = '$judul_buku' AND penulis = '$penulis'");
            
            if (mysqli_num_rows($check_existing) > 0) {
                // Jika buku sudah ada, tambah stok
                $existing_book = mysqli_fetch_assoc($check_existing);
                $buku_id = $existing_book['id'];
                $update_stok = mysqli_query($koneksi, "UPDATE buku SET stok = CAST(stok AS UNSIGNED) + $stok WHERE id = $buku_id");
                $message = "📚 Request selesai! Stok buku '{$request_data['judul_buku']}' berhasil ditambahkan!";
            } else {
                // Jika buku belum ada, buat buku baru
                $insert_buku = "INSERT INTO buku (`judul buku`, penulis, stok, kategori, tahun_terbit, status) 
                               VALUES ('$judul_buku', '$penulis', '$stok', $kategori, '$tahun_terbit', $status_buku)";
                
                if (mysqli_query($koneksi, $insert_buku)) {
                    $buku_id = mysqli_insert_id($koneksi);
                    $message = "📚 Request selesai! Buku '{$request_data['judul_buku']}' berhasil ditambahkan ke koleksi perpustakaan!";
                } else {
                    $_SESSION['message'] = "❌ Error menambahkan buku: " . mysqli_error($koneksi);
                    $_SESSION['message_type'] = "error";
                    header("Location: dashboard.php");
                    exit;
                }
            }
            
            $status = 'completed';
            break;
            
        case 'delete':
            // Validasi bahwa request sudah diproses (tidak pending)
            if ($request_data['status'] == 'pending') {
                $_SESSION['message'] = "❌ Request yang masih pending tidak dapat dihapus! Tolak atau setujui terlebih dahulu.";
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit;
            }
            
            // Hapus request dari database
            $delete_query = mysqli_query($koneksi, "DELETE FROM request_buku WHERE id = $request_id");
            
            if ($delete_query) {
                $message = "🗑️ Request buku '{$request_data['judul_buku']}' berhasil dihapus dari sistem!";
                $_SESSION['message'] = $message;
                $_SESSION['message_type'] = "success";
                
                // Log aktivitas admin
                $check_log_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'admin_log'");
                if (mysqli_num_rows($check_log_table) > 0) {
                    $log_description = "Menghapus request buku: {$request_data['judul_buku']} dari {$request_data['email_pemohon']}";
                    $log_query = "INSERT INTO admin_log (admin_email, action, target_id, description, created_at) 
                                  VALUES ('$approved_by', 'delete_request', $request_id, '$log_description', '$approved_at')";
                    mysqli_query($koneksi, $log_query);
                }
                
                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['message'] = "❌ Error menghapus request: " . mysqli_error($koneksi);
                $_SESSION['message_type'] = "error";
                header("Location: dashboard.php");
                exit;
            }
            break;
            
        default:
            $_SESSION['message'] = "❌ Aksi tidak valid!";
            $_SESSION['message_type'] = "error";
            header("Location: dashboard.php");
            exit;
    }
    
    // Update request dengan buku_id jika ada
    $query = "UPDATE request_buku SET 
              status = '$status', 
              keterangan = '$keterangan',
              approved_by = '$approved_by',
              approved_at = '$approved_at'";
    
    if ($buku_id) {
        $query .= ", buku_id = $buku_id";
    }
    
    $query .= " WHERE id = $request_id";
    
    if (mysqli_query($koneksi, $query)) {
        $_SESSION['message'] = $message;
        $_SESSION['message_type'] = "success";
        
        // Log aktivitas admin - dengan pengecekan tabel terlebih dahulu
        $check_log_table = mysqli_query($koneksi, "SHOW TABLES LIKE 'admin_log'");
        if (mysqli_num_rows($check_log_table) > 0) {
            $log_description = "Request buku: {$request_data['judul_buku']}";
            if ($buku_id) {
                $log_description .= " (Buku ID: $buku_id)";
            }
            $log_query = "INSERT INTO admin_log (admin_email, action, target_id, description, created_at) 
                          VALUES ('$approved_by', '$action', $request_id, '$log_description', '$approved_at')";
            mysqli_query($koneksi, $log_query);
        }
        
    } else {
        $_SESSION['message'] = "❌ Error: " . mysqli_error($koneksi);
        $_SESSION['message_type'] = "error";
    }
} else {
    $_SESSION['message'] = "❌ Method tidak diizinkan!";
    $_SESSION['message_type'] = "error";
}

header("Location: dashboard.php");
exit;
?>
