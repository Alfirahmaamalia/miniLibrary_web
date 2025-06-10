<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$message_type = '';

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include 'koneksi.php';
    
    // Ambil data dari form - sesuaikan dengan nama kolom di database
    $judul_buku = mysqli_real_escape_string($conn, trim($_POST['judul_buku']));
    $penulis = mysqli_real_escape_string($conn, trim($_POST['penulis']));
    $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
    $tahun_terbit = mysqli_real_escape_string($conn, $_POST['tahun_terbit']);
    $stok = mysqli_real_escape_string($conn, $_POST['stok']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    // Validasi input
    $errors = [];
    
    if (empty($judul_buku)) {
        $errors[] = "Judul buku harus diisi";
    }
    
    if (empty($penulis)) {
        $errors[] = "Penulis harus diisi";
    }
    
    if (empty($kategori)) {
        $errors[] = "Kategori harus dipilih";
    }
    
    if (empty($tahun_terbit)) {
        $errors[] = "Tahun terbit harus diisi";
    } elseif (!is_numeric($tahun_terbit) || $tahun_terbit < 1000 || $tahun_terbit > date('Y')) {
        $errors[] = "Tahun terbit tidak valid";
    }
    
    if (empty($stok)) {
        $errors[] = "Stok harus diisi";
    } elseif (!is_numeric($stok) || $stok < 0) {
        $errors[] = "Stok harus berupa angka positif";
    }
    
    if (empty($status)) {
        $errors[] = "Status harus dipilih";
    }
    
    // Jika tidak ada error, simpan ke database
    if (empty($errors)) {
        // Query sesuai dengan nama kolom yang ada di database
        $query = "INSERT INTO buku (judul_buku, penulis, kategori, tahun_terbit, stok, status) 
                  VALUES ('$judul_buku', '$penulis', '$kategori', '$tahun_terbit', '$stok', '$status')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['message'] = "Buku '$judul_buku' berhasil ditambahkan!";
            $_SESSION['message_type'] = "success";
            header("Location: manajemen.php");
            exit;
        } else {
            $message = "Error: " . mysqli_error($conn);
            $message_type = "error";
        }
    } else {
        $message = implode("<br>", $errors);
        $message_type = "error";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Buku - MiniLibrary</title>
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
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #007bff;
            box-shadow: 0 0 0 2px rgba(0,123,255,0.25);
        }

        .required {
            color: #dc3545;
        }

        .form-row {
            display: flex;
            gap: 20px;
        }

        .form-row .form-group {
            flex: 1;
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
    <a class="active" href="manajemen.php">📚 Manajemen Buku</a>
    <a href="anggota.php">👤 Anggota Perpustakaan</a>
    <a href="kategori.php">📂 Kategori Buku</a>
    <a href="peminjaman.php">🔒 Peminjaman</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Tambah Buku Baru</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <a href="manajemen.php" class="back-link">Kembali ke Manajemen Buku</a>

    <div class="form-container">
        <div class="form-header">
            <h2>Form Tambah Buku</h2>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="tambahBukuForm">
            <div class="form-group">
                <label for="judul_buku">Judul Buku <span class="required">*</span></label>
                <input type="text" id="judul_buku" name="judul_buku" required 
                       value="<?php echo isset($_POST['judul_buku']) ? htmlspecialchars($_POST['judul_buku']) : ''; ?>"
                       placeholder="Masukkan judul buku">
            </div>

            <div class="form-group">
                <label for="penulis">Penulis <span class="required">*</span></label>
                <input type="text" id="penulis" name="penulis" required 
                       value="<?php echo isset($_POST['penulis']) ? htmlspecialchars($_POST['penulis']) : ''; ?>"
                       placeholder="Nama penulis">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="kategori">Kategori <span class="required">*</span></label>
                    <select id="kategori" name="kategori" required>
                        <option value="">Pilih Kategori</option>
                        <option value="1" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '1') ? 'selected' : ''; ?>>Fiksi</option>
                        <option value="2" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '2') ? 'selected' : ''; ?>>Non-Fiksi</option>
                        <option value="3" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '3') ? 'selected' : ''; ?>>Sejarah</option>
                        <option value="4" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '4') ? 'selected' : ''; ?>>Ilmiah</option>
                        <option value="5" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '5') ? 'selected' : ''; ?>>Teknologi</option>
                        <option value="6" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '6') ? 'selected' : ''; ?>>Biografi</option>
                        <option value="7" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == '7') ? 'selected' : ''; ?>>Pendidikan</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tahun_terbit">Tahun Terbit <span class="required">*</span></label>
                    <input type="text" id="tahun_terbit" name="tahun_terbit" required 
                           value="<?php echo isset($_POST['tahun_terbit']) ? htmlspecialchars($_POST['tahun_terbit']) : ''; ?>"
                           placeholder="<?php echo date('Y'); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="stok">Stok <span class="required">*</span></label>
                    <input type="text" id="stok" name="stok" required 
                           value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : ''; ?>"
                           placeholder="Jumlah stok">
                </div>

                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="">Pilih Status</option>
                        <option value="1" <?php echo (isset($_POST['status']) && $_POST['status'] == '1') ? 'selected' : ''; ?>>Tersedia</option>
                        <option value="0" <?php echo (isset($_POST['status']) && $_POST['status'] == '0') ? 'selected' : ''; ?>>Tidak Tersedia</option>
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary">💾 Simpan Buku</button>
                <a href="manajemen.php" class="btn btn-secondary">❌ Batal</a>
            </div>
        </form>
    </div>
</div>

<script>
// Validasi form di sisi client
document.getElementById('tambahBukuForm').addEventListener('submit', function(e) {
    const judul_buku = document.getElementById('judul_buku').value.trim();
    const penulis = document.getElementById('penulis').value.trim();
    const kategori = document.getElementById('kategori').value;
    const tahun_terbit = document.getElementById('tahun_terbit').value;
    const stok = document.getElementById('stok').value;
    const status = document.getElementById('status').value;
    
    let errors = [];
    
    if (!judul_buku) errors.push('Judul buku harus diisi');
    if (!penulis) errors.push('Penulis harus diisi');
    if (!kategori) errors.push('Kategori harus dipilih');
    if (!tahun_terbit) errors.push('Tahun terbit harus diisi');
    if (!stok) errors.push('Stok harus diisi');
    if (!status) errors.push('Status harus dipilih');
    
    // Validasi tahun terbit
    const currentYear = new Date().getFullYear();
    if (tahun_terbit && (isNaN(tahun_terbit) || tahun_terbit < 1000 || tahun_terbit > currentYear)) {
        errors.push('Tahun terbit tidak valid');
    }
    
    // Validasi stok
    if (stok && (isNaN(stok) || stok < 0)) {
        errors.push('Stok harus berupa angka positif');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Error:\n' + errors.join('\n'));
    }
});

// Auto update status berdasarkan stok
document.getElementById('stok').addEventListener('input', function(e) {
    const stok = parseInt(e.target.value);
    const statusSelect = document.getElementById('status');
    
    if (stok > 0) {
        statusSelect.value = '1'; // Tersedia
    } else if (stok === 0) {
        statusSelect.value = '0'; // Tidak Tersedia
    }
});

// Auto-focus ke field pertama
document.getElementById('judul_buku').focus();
</script>

</body>
</html>