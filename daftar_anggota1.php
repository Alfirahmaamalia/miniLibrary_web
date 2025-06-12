<?php
session_start();
include 'koneksi.php';

// Redirect jika sudah login
if (isset($_SESSION['anggota_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";
$success = "";

// Proses pendaftaran
if (isset($_POST['daftar'])) {
    $nama = mysqli_real_escape_string($koneksi, trim($_POST['nama']));
    $email = mysqli_real_escape_string($koneksi, trim($_POST['email']));
    $telepon = mysqli_real_escape_string($koneksi, trim($_POST['telepon']));
    $alamat = mysqli_real_escape_string($koneksi, trim($_POST['alamat']));
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];
    
    // Validasi input
    $errors = array();
    
    if (empty($nama)) {
        $errors[] = "Nama lengkap harus diisi!";
    }
    
    if (empty($email)) {
        $errors[] = "Email harus diisi!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid!";
    }
    
    if (empty($telepon)) {
        $errors[] = "Nomor telepon harus diisi!";
    }
    
    if (empty($alamat)) {
        $errors[] = "Alamat harus diisi!";
    }
    
    if (empty($password)) {
        $errors[] = "Password harus diisi!";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter!";
    }
    
    if ($password !== $konfirmasi_password) {
        $errors[] = "Konfirmasi password tidak sesuai!";
    }
    
    // Cek apakah email sudah terdaftar
    $check_email = mysqli_query($koneksi, "SELECT * FROM anggota WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        $errors[] = "Email sudah terdaftar! Silakan gunakan email lain.";
    }
    
    // Jika tidak ada error, simpan data anggota baru
    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Simpan ke database
        $query = "INSERT INTO anggota (nama, email, telepon, alamat, password, tanggal_daftar, status) 
                  VALUES ('$nama', '$email', '$telepon', '$alamat', '$hashed_password', NOW(), 'aktif')";
        
        if (mysqli_query($koneksi, $query)) {
            $success = "Pendaftaran berhasil! Silakan login dengan akun Anda.";
            // Reset form
            $nama = $email = $telepon = $alamat = "";
        } else {
            $error = "Error: " . mysqli_error($koneksi);
        }
    } else {
        $error = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Anggota - MiniLibrary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-image: url('assets/library-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            padding: 40px 0;
        }

        /* Overlay gelap untuk meningkatkan kontras */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: -1;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
            padding: 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .register-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(to right, #4e73df, #36b9cc);
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
        }

        .logo span {
            font-size: 14px;
            color: #666;
        }

        .register-container h2 {
            color: #333;
            margin-bottom: 25px;
            font-size: 20px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            background: #f9f9f9;
        }

        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.15);
            outline: none;
            background: #fff;
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: #4e73df;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn:hover {
            background: #2e59d9;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn:active {
            transform: translateY(0);
        }

        .links {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
        }

        .links a {
            color: #4e73df;
            text-decoration: none;
            transition: color 0.3s;
        }

        .links a:hover {
            color: #2e59d9;
            text-decoration: underline;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
            border-left: 4px solid #f5c6cb;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
            border-left: 4px solid #c3e6cb;
        }

        .form-row {
            display: flex;
            gap: 15px;
        }

        .form-row .form-group {
            flex: 1;
        }

        .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }

        @media (max-width: 600px) {
            .register-container {
                padding: 30px 20px;
                margin: 0 15px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <h1>MiniLibrary</h1>
            <span>Portal Anggota Perpustakaan</span>
        </div>
        
        <h2>Daftar Anggota Baru</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="nama">Nama Lengkap *</label>
                <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" value="<?php echo isset($nama) ? htmlspecialchars($nama) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="telepon">Nomor Telepon *</label>
                <input type="text" id="telepon" name="telepon" class="form-control" placeholder="Masukkan nomor telepon" value="<?php echo isset($telepon) ? htmlspecialchars($telepon) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat *</label>
                <textarea id="alamat" name="alamat" class="form-control" placeholder="Masukkan alamat lengkap" rows="3" required><?php echo isset($alamat) ? htmlspecialchars($alamat) : ''; ?></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    <div class="form-text">Password minimal 6 karakter</div>
                </div>
                
                <div class="form-group">
                    <label for="konfirmasi_password">Konfirmasi Password *</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" class="form-control" placeholder="Ulangi password" required>
                </div>
            </div>
            
            <button type="submit" name="daftar" class="btn">Daftar Sekarang</button>
            
            <div class="links">
                Sudah memiliki akun? <a href="login_anggota.php">Login di sini</a>
            </div>
        </form>
    </div>
</body>
</html>
