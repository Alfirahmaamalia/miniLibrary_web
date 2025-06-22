<?php
session_start();
include 'koneksi.php';

$error = "";
$success = "";
$valid_token = false;

// Cek token
if (isset($_GET['token'])) {
    $token = mysqli_real_escape_string($koneksi, $_GET['token']);
    
    // Cek apakah token valid dan belum expired
    $query = "SELECT * FROM password_resets WHERE token = '$token' AND expires > NOW()";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        $valid_token = true;
        $reset_data = mysqli_fetch_assoc($result);
    } else {
        $error = "Link reset password tidak valid atau sudah expired.";
    }
}

// Proses reset password
if (isset($_POST['update_password']) && $valid_token) {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (strlen($new_password) < 6) {
        $error = "Password minimal 6 karakter.";
    } elseif ($new_password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $email = $reset_data['email'];
        
        // Update password di tabel anggota
        $update_query = "UPDATE anggota SET password = '$hashed_password' WHERE email = '$email'";
        
        if (mysqli_query($koneksi, $update_query)) {
            // Hapus token yang sudah digunakan
            $delete_token = "DELETE FROM password_resets WHERE token = '$token'";
            mysqli_query($koneksi, $delete_token);
            
            $success = "Password berhasil diubah! Silakan login dengan password baru.";
        } else {
            $error = "Terjadi kesalahan saat mengubah password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - MiniLibrary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-image: url('assets/buku.png');
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

        .reset-container {
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
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 
                2px 2px 4px rgba(0, 0, 0, 0.8),
                0 0 10px rgba(0, 0, 0, 0.6),
                0 0 20px rgba(0, 0, 0, 0.4);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .reset-container h2 {
            color: #fff;
            text-shadow: 
                1px 1px 3px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(0, 0, 0, 0.6);
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .reset-container input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.25);
            color: #333;
            font-weight: 500;
        }

        .reset-container input[type="password"]::placeholder {
            color: rgba(0, 0, 0, 0.6);
            font-weight: 500;
        }

        .reset-container input[type="password"]:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.35);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }

        .reset-container button {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.25);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.6);
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .reset-container button:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .back-link {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .back-link:hover {
            text-decoration: underline;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(255, 255, 255, 0.5);
        }

        .success {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border: 2px solid rgba(100, 255, 100, 0.6);
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
            font-weight: 500;
        }

        .error {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid rgba(255, 100, 100, 0.6);
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .reset-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .logo {
                font-size: 2rem;
            }

            .reset-container h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">MiniLibrary</div>
        
        <?php if (!$valid_token && !$success): ?>
            <h2>Link Tidak Valid</h2>
            <div class="error"><?php echo $error; ?></div>
            <a href="login_anggota.php" class="back-link">← Kembali ke Login</a>
        
        <?php elseif ($success): ?>
            <h2>Password Berhasil Diubah!</h2>
            <div class="success"><?php echo $success; ?></div>
            <a href="login_anggota.php" class="back-link">← Login Sekarang</a>
        
        <?php else: ?>
            <h2>Buat Password Baru</h2>
            
            <?php if (!empty($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="password" name="new_password" placeholder="Password Baru (min. 6 karakter)" required>
                <input type="password" name="confirm_password" placeholder="Konfirmasi Password Baru" required>
                <button type="submit" name="update_password">Ubah Password</button>
            </form>
            
            <a href="login_anggota.php" class="back-link">← Kembali ke Login</a>
        <?php endif; ?>
    </div>
</body>
</html>
