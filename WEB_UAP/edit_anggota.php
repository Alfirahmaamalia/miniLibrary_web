<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

// Cek apakah ada parameter ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['message'] = "ID anggota tidak valid!";
    $_SESSION['message_type'] = "error";
    header("Location: anggota.php");
    exit;
}

include 'koneksi.php';

$id = mysqli_real_escape_string($conn, $_GET['id']);
$message = '';
$message_type = '';

// Ambil data anggota berdasarkan ID
$query = "SELECT * FROM anggota WHERE id = '$id'";
$result = mysqli_query($conn, $query);

if (!$result || mysqli_num_rows($result) == 0) {
    $_SESSION['message'] = "Anggota dengan ID tersebut tidak ditemukan!";
    $_SESSION['message_type'] = "error";
    header("Location: anggota.php");
    exit;
}

$anggota = mysqli_fetch_assoc($result);

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $nama = mysqli_real_escape_string($conn, trim($_POST['nama']));
    $alamat = mysqli_real_escape_string($conn, trim($_POST['alamat']));
    $telepon = mysqli_real_escape_string($conn, trim($_POST['telepon']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validasi input
    $errors = [];
    
    if (empty($nama)) {
        $errors[] = "Nama lengkap harus diisi";
    }
    
    if (empty($alamat)) {
        $errors[] = "Alamat harus diisi";
    }
    
    if (empty($telepon)) {
        $errors[] = "No. telepon harus diisi";
    } elseif (!preg_match('/^[0-9+\-\s()]+$/', $telepon)) {
        $errors[] = "Format no. telepon tidak valid";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    } else {
        // Cek apakah email sudah ada (kecuali email anggota ini sendiri)
        $check_email = "SELECT id FROM anggota WHERE email = '$email' AND id != '$id'";
        $result_check = mysqli_query($conn, $check_email);
        if (mysqli_num_rows($result_check) > 0) {
            $errors[] = "Email sudah digunakan oleh anggota lain";
        }
    }
    
    if (empty($status)) {
        $errors[] = "Status harus dipilih";
    }
    
    // Jika tidak ada error, update data ke database
    if (empty($errors)) {
        $update_query = "UPDATE anggota SET 
                        nama = '$nama', 
                        alamat = '$alamat', 
                        telepon = '$telepon', 
                        email = '$email', 
                        status = '$status' 
                        WHERE id = '$id'";
        
        if (mysqli_query($conn, $update_query)) {
            $_SESSION['message'] = "Data anggota berhasil diperbarui!";
            $_SESSION['message_type'] = "success";
            header("Location: anggota.php");
            exit;
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
        
        // Update data anggota dengan data dari form untuk ditampilkan kembali
        $anggota['nama'] = $nama;
        $anggota['alamat'] = $alamat;
        $anggota['telepon'] = $telepon;
        $anggota['email'] = $email;
        $anggota['status'] = $status;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Anggota - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
        }

        .form-header {
            margin-bottom: 30px;
            text-align: center;
        }

        .form-header h2 {
            color: #333;
            margin: 0;
            font-size: 24px;
        }

        .form-header .member-id {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }

        .required {
            color: #dc3545;
        }

        .form-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #007bff;
            color: white;
        }

        .btn-primary:hover {
            background: #0056b3;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            color: #007bff;
            text-decoration: none;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .back-link::before {
            content: "←";
            margin-right: 8px;
            font-size: 16px;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .info-box {
            background: #e9ecef;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            color: #495057;
        }

        .info-box strong {
            color: #333;
        }

        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }
            
            .form-container {
                margin: 20px;
                padding: 20px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="dashboard.php">📊 Beranda</a>
    <a href="manajemen.php">📚 Manajemen Buku</a>
    <a class="active" href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Edit Anggota</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <a href="anggota.php" class="back-link">Kembali ke Daftar Anggota</a>

    <div class="form-container">
        <div class="form-header">
            <h2>Edit Data Anggota</h2>
            <div class="member-id">ID Anggota: <?php echo str_pad($id, 3, '0', STR_PAD_LEFT); ?></div>
        </div>

        <div class="info-box">
            <strong>Info:</strong> Pastikan semua data yang dimasukkan sudah benar sebelum menyimpan perubahan.
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="editAnggotaForm">
            <div class="form-group">
                <label for="nama">Nama Lengkap <span class="required">*</span></label>
                <input type="text" id="nama" name="nama" required 
                       value="<?php echo htmlspecialchars($anggota['nama']); ?>"
                       placeholder="Masukkan nama lengkap">
            </div>

            <div class="form-group">
                <label for="alamat">Alamat <span class="required">*</span></label>
                <textarea id="alamat" name="alamat" required 
                          placeholder="Masukkan alamat lengkap"><?php echo htmlspecialchars($anggota['alamat']); ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telepon">No. Telepon <span class="required">*</span></label>
                    <input type="tel" id="telepon" name="telepon" required 
                           value="<?php echo htmlspecialchars($anggota['telepon']); ?>"
                           placeholder="Contoh: 08123456789">
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="Aktif" <?php echo ($anggota['status'] == 'Aktif') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="Non Aktif" <?php echo ($anggota['status'] == 'Non Aktif') ? 'selected' : ''; ?>>Non Aktif</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required 
                       value="<?php echo htmlspecialchars($anggota['email']); ?>"
                       placeholder="contoh@email.com">
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Simpan Perubahan</button>
                <a href="anggota.php" class="btn btn-secondary">❌ Batal</a>
                <a href="hapus_anggota.php?id=<?php echo $id; ?>" class="btn btn-danger" 
                   onclick="return confirm('Apakah Anda yakin ingin menghapus anggota ini?')">🗑️ Hapus Anggota</a>
            </div>
        </form>
    </div>
</div>

<script>
// Validasi form di sisi client
document.getElementById('editAnggotaForm').addEventListener('submit', function(e) {
    const nama = document.getElementById('nama').value.trim();
    const alamat = document.getElementById('alamat').value.trim();
    const telepon = document.getElementById('telepon').value.trim();
    const email = document.getElementById('email').value.trim();
    const status = document.getElementById('status').value;
    
    let errors = [];
    
    if (!nama) errors.push('Nama lengkap harus diisi');
    if (!alamat) errors.push('Alamat harus diisi');
    if (!telepon) errors.push('No. telepon harus diisi');
    if (!email) errors.push('Email harus diisi');
    if (!status) errors.push('Status harus dipilih');
    
    // Validasi format email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email && !emailRegex.test(email)) {
        errors.push('Format email tidak valid');
    }
    
    // Validasi format telepon
    const phoneRegex = /^[0-9+\-\s()]+$/;
    if (telepon && !phoneRegex.test(telepon)) {
        errors.push('Format no. telepon tidak valid');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Error:\n' + errors.join('\n'));
    }
});

// Format input telepon
document.getElementById('telepon').addEventListener('input', function(e) {
    let value = e.target.value.replace(/[^0-9+\-\s()]/g, '');
    e.target.value = value;
});

// Auto-focus ke field pertama
document.getElementById('nama').focus();

// Konfirmasi sebelum meninggalkan halaman jika ada perubahan
let originalData = {
    nama: document.getElementById('nama').value,
    alamat: document.getElementById('alamat').value,
    telepon: document.getElementById('telepon').value,
    email: document.getElementById('email').value,
    status: document.getElementById('status').value
};

function hasChanges() {
    return (
        document.getElementById('nama').value !== originalData.nama ||
        document.getElementById('alamat').value !== originalData.alamat ||
        document.getElementById('telepon').value !== originalData.telepon ||
        document.getElementById('email').value !== originalData.email ||
        document.getElementById('status').value !== originalData.status
    );
}

// Peringatan jika ada perubahan yang belum disimpan
window.addEventListener('beforeunload', function(e) {
    if (hasChanges()) {
        e.preventDefault();
        e.returnValue = 'Anda memiliki perubahan yang belum disimpan. Apakah Anda yakin ingin meninggalkan halaman ini?';
    }
});

// Hapus peringatan saat form disubmit
document.getElementById('editAnggotaForm').addEventListener('submit', function() {
    window.removeEventListener('beforeunload', arguments.callee);
});
</script>

</body>
</html>