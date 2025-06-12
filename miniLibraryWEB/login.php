<?php
session_start();
include 'koneksi.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = '$email'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau kata sandi salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniLibrary - Login</title>
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

        .login-container {
            background: rgba(255, 255, 255, 0.15); 
            backdrop-filter: blur(15px); 
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 400px;
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

        .login-container h2 {
            color: #fff;
            text-shadow: 
                1px 1px 3px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(0, 0, 0, 0.6);
            margin-bottom: 30px;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .login-container form {
            width: 100%;
        }

        .login-container input[type="email"],
        .login-container input[type="password"] {
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

        .login-container input[type="email"]::placeholder,
        .login-container input[type="password"]::placeholder {
            color: rgba(0, 0, 0, 0.6);
            font-weight: 500;
        }

        .login-container input[type="email"]:focus,
        .login-container input[type="password"]:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.7);
            background: rgba(255, 255, 255, 0.35);
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.3);
        }

        .login-container button {
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

        .login-container button:hover {
            background: rgba(255, 255, 255, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        }

        .secondary {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 5px rgba(0, 0, 0, 0.5);
        }

        .secondary:hover {
            text-decoration: underline;
            text-shadow: 
                1px 1px 2px rgba(0, 0, 0, 0.8),
                0 0 8px rgba(255, 255, 255, 0.5);
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
            .login-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .logo {
                font-size: 2rem;
            }

            .login-container h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">MiniLibrary</div>
        <h2>Hai! Selamat Datang Kembali!</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Masukan email" required>
            <input type="password" name="password" placeholder="Masukan Kata Sandi" required>
            <button type="submit" name="login">Masuk</button>
        </form>
        <a href="register.php" class="secondary">Buat akun</a>
    </div>
</body>
</html>
