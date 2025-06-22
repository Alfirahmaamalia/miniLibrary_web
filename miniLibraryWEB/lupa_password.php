<?php
session_start();
include 'koneksi.php';

$message = "";
$error = "";

if (isset($_POST['reset_password'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    
    // Cek apakah email ada di database anggota
    $query = "SELECT * FROM anggota WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
    
    if (mysqli_num_rows($result) > 0) {
        // Generate token reset password
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        // Simpan token ke database (buat tabel password_resets jika belum ada)
        $create_table = "CREATE TABLE IF NOT EXISTS password_resets (
            id INT AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            token VARCHAR(255) NOT NULL,
            expires DATETIME NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        mysqli_query($koneksi, $create_table);
        
        // Insert token
        $insert_token = "INSERT INTO password_resets (email, token, expires) VALUES ('$email', '$token', '$expires')";
        
        if (mysqli_query($koneksi, $insert_token)) {
            // Dalam implementasi nyata, kirim email dengan link reset
            // Untuk demo, tampilkan link reset
            $reset_link = "reset_password.php?token=" . $token;
            $message = "Link reset password telah dibuat! <br><br>
                       <strong>Demo Link:</strong><br>
                       <a href='$reset_link' style='color: #fff; text-decoration: underline;'>
                       Klik disini untuk reset password
                       </a><br><br>
                       <small>Link akan expired dalam 1 jam</small>";
        } else {
            $error = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    } else {
        $error = "Email tidak ditemukan dalam sistem.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - MiniLibrary</title>
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
            margin-bottom: 15px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .reset-container p {
            color: #fff;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            font-size: 14px;
            line-height: 1.5;
            opacity: 0.9;
        }

        .reset-container form {
            width: 100%;
        }

        .reset-container input[type="email"] {
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

        .reset-container input[type="email"]::placeholder {
            color: rgba(0, 0, 0, 0.6);
            font-weight: 500;
        }

        .reset-container input[type="email"]:focus {
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
            display: inline-block;
            margin-top: 10px;
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
            text-align: left;
            border: 2px solid rgba(100, 255, 100, 0.6);
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
            font-weight: 500;
            line-height: 1.6;
        }

        .error {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
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
        <h2>Lupa Kata Sandi?</h2>
        <p>Masukkan alamat email Anda dan kami akan mengirimkan link untuk mereset kata sandi.</p>
        
        <?php if (!empty($message)): ?>
            <div class="success"><?php echo $message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if (empty($message)): ?>
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Masukkan alamat email Anda" required>
            <button type="submit" name="reset_password">Kirim Link Reset</button>
        </form>
        <?php endif; ?>
        
        <a href="login_anggota.php" class="back-link">← Kembali ke Login</a>
    </div>
</body>
</html>
