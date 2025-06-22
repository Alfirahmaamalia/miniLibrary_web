<?php
session_start();
include '../koneksi/koneksi.php';

// Redirect jika sudah login sebagai anggota
if (isset($_SESSION['anggota_id'])) {
    header("Location: beranda_pengguna.php");
    exit;
}

$error = "";
$success = "";

// Proses login
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Validasi input
    if (empty($email) || empty($password)) {
        $error = "Email dan password harus diisi!";
    } else {
        // Cek user di database
        $query = "SELECT * FROM anggota WHERE email = '$email'";
        $result = mysqli_query($koneksi, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $anggota = mysqli_fetch_assoc($result);
            
            // Verifikasi password
            if (password_verify($password, $anggota['password'])) {
                // Set session
                $_SESSION['anggota_id'] = $anggota['id'];
                $_SESSION['anggota_nama'] = $anggota['nama'];
                $_SESSION['anggota_email'] = $anggota['email'];
                
                // Update terakhir login jika kolom ada
                $check_column = mysqli_query($koneksi, "SHOW COLUMNS FROM anggota LIKE 'terakhir_login'");
                if (mysqli_num_rows($check_column) > 0) {
                    mysqli_query($koneksi, "UPDATE anggota SET terakhir_login = NOW() WHERE id = {$anggota['id']}");
                }
                
                // Redirect ke halaman utama anggota
                header("Location: beranda_pengguna.php");
                exit;
            } else {
                $error = "Password yang Anda masukkan salah!";
            }
        } else {
            $error = "Email tidak terdaftar!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Anggota - MiniLibrary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('../assets/buku.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        /* Overlay gelap untuk meningkatkan kontras */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            z-index: -1;
        }

        .login-container {
            background: rgba(255, 255, 255, 0.15); 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        .logo {
            margin-bottom: 30px;
        }

        .logo h1 {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 
                2px 2px 4px rgba(0, 0, 0, 0.8),
                0 0 10px rgba(0, 0, 0, 0.6),
                0 0 20px rgba(0, 0, 0, 0.4);
            margin-bottom: 5px;
            letter-spacing: 1px;
        }

        .logo span {
            font-size: 14px;
            color: #fff;
            text-shadow: 
                1px 1px 3px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(0, 0, 0, 0.6);
            opacity: 0.9;
        }

        .login-container h2 {
            color: #fff;
            text-shadow: 
                1px 1px 3px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(0, 0, 0, 0.6);
            margin-bottom: 25px;
            font-size: 1.5rem;
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
            color: #fff;
            font-weight: 500;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.25);
            color: #333;
            font-weight: 500;
        }

        .form-control::placeholder {
            color: rgba(0, 0, 0, 0.6);
            font-weight: 500;
        }

        .form-control:focus {
            border-color: rgba(255, 255, 255, 0.7);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
            outline: none;
            background: rgba(255, 255, 255, 0.35);
        }

        .btn {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .links {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 14px;
        }

        .links a {
            color: #fff;
            text-decoration: none;
            font-weight: 500;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .links a:hover {
            text-decoration: underline;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(255, 255, 255, 0.5);
        }

        .error-message {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
            border: 2px solid rgba(255, 100, 100, 0.6);
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
            font-weight: 500;
        }

        .success-message {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
            border: 2px solid rgba(100, 255, 100, 0.6);
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
            font-weight: 500;
        }

        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }

        .remember-me input {
            margin-right: 8px;
            transform: scale(1.2);
        }

        .remember-me label {
            font-size: 14px;
            color: #fff;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .divider {
            display: flex;
            align-items: center;
            margin: 25px 0;
            color: #fff;
            font-size: 14px;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.4);
        }

        .divider::before {
            margin-right: 10px;
        }

        .divider::after {
            margin-left: 10px;
        }

        .admin-link {
            margin-top: 20px;
            font-size: 13px;
        }

        .admin-link a {
            color: #fff;
            text-decoration: none;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .admin-link a:hover {
            text-decoration: underline;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(255, 255, 255, 0.5);
        }

        @media (max-width: 768px) {
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .logo h1 {
                font-size: 2rem;
            }

            .login-container h2 {
                font-size: 1.3rem;
            }

            .links {
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }
        }
</style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>MiniLibrary</h1>
            <span>Portal Anggota Perpustakaan</span>
        </div>
        
        <h2>Login Anggota</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message"><?php echo $success; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email Anda" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Masukkan password Anda" required>
            </div>
            
            <div class="remember-me">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Ingat saya</label>
            </div>
            
            <button type="submit" name="login" class="btn">Masuk</button>
            
            <div class="links">
                <a href="penggunabaru.php">Daftar Anggota Baru</a>
            </div>
            
            
            
           
        </form>
    </div>
</body>
</html>
