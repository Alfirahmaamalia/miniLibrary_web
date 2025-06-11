<?php
session_start();
include 'koneksi.php';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Ambil error dan success dari session jika ada
$error = "";
$success = "";
$old_email = "";

if (isset($_SESSION['register_error'])) {
    $error = $_SESSION['register_error'];
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['register_success'])) {
    $success = $_SESSION['register_success'];
    unset($_SESSION['register_success']);
}

if (isset($_SESSION['old_email'])) {
    $old_email = $_SESSION['old_email'];
    unset($_SESSION['old_email']);
}

// Proses registrasi langsung di halaman ini
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    // Sanitasi input
    $email = mysqli_real_escape_string($koneksi, trim($_POST["email"]));
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    // Validasi input
    if (empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Semua field harus diisi!";
        $old_email = $email;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid!";
        $old_email = $email;
    } elseif (strlen($password) < 6) {
        $error = "Password minimal 6 karakter!";
        $old_email = $email;
    } elseif ($password !== $confirm_password) {
        $error = "Konfirmasi password tidak cocok!";
        $old_email = $email;
    } elseif (!isset($_POST['terms'])) {
        $error = "Anda harus menyetujui Ketentuan Layanan dan Kebijakan Privasi!";
        $old_email = $email;
    } else {
        // Cek apakah email sudah terdaftar
        $check_email = mysqli_query($koneksi, "SELECT email FROM users WHERE email = '$email'");
        
        if (!$check_email) {
            $error = "Error database: " . mysqli_error($koneksi);
            $old_email = $email;
        } elseif (mysqli_num_rows($check_email) > 0) {
            $error = "Email sudah terdaftar!";
            $old_email = $email;
        } else {
            // Hash password dan simpan ke database
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $result = mysqli_query($koneksi, "INSERT INTO users (email, password) VALUES ('$email', '$password_hash')");
            
            if ($result) {
                $success = "Registrasi berhasil! Silakan login.";
                // Redirect ke login setelah 2 detik
                header("refresh:2;url=login.php");
            } else {
                $error = "Registrasi gagal: " . mysqli_error($koneksi);
                $old_email = $email;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MiniLibrary - Daftar</title>
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
        }

        /* Form container yang sangat transparan */
        .register-container {
            background: rgba(255, 255, 255, 0.1); /* 10% opacity - sangat transparan */
            backdrop-filter: blur(5px); /* Blur ringan */
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }

        /* Logo dengan text shadow untuk keterbacaan */
        .logo {
            font-size: 2.5rem;
            font-weight: bold;
            color: #fff;
            text-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            margin-bottom: 20px;
        }

        /* Heading dengan text shadow */
        .register-container h2 {
            color: #fff;
            text-shadow: 0 0 8px rgba(0, 0, 0, 0.5);
            margin-bottom: 30px;
            font-size: 1.5rem;
        }

        .register-container form {
            width: 100%;
        }

        /* Input fields transparan */
        .register-container input[type="email"],
        .register-container input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            font-size: 16px;
            transition: all 0.3s;
            background: rgba(255, 255, 255, 0.2); /* 20% opacity */
            color: #fff;
        }

        .register-container input[type="email"]::placeholder,
        .register-container input[type="password"]::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .register-container input[type="email"]:focus,
        .register-container input[type="password"]:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.5);
            background: rgba(255, 255, 255, 0.3); /* Sedikit lebih solid saat focus */
        }

        /* Checkbox styling */
        .checkbox {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            text-align: left;
            color: #fff;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        .checkbox input[type="checkbox"] {
            margin-top: 3px;
            margin-right: 10px;
            transform: scale(1.2);
        }

        .checkbox label {
            font-size: 14px;
            line-height: 1.4;
        }

        .terms a {
            color: #fff;
            text-decoration: underline;
            font-weight: bold;
        }

        /* Button transparan dengan border */
        .register-container button {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 20px;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }

        .register-container button:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Link dengan text shadow */
        .secondary {
            color: #fff;
            text-decoration: none;
            font-size: 14px;
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        .secondary:hover {
            text-decoration: underline;
        }

        /* Error message transparan */
        .error {
            background: rgba(255, 0, 0, 0.2);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(255, 0, 0, 0.3);
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        /* Success message transparan */
        .success {
            background: rgba(0, 255, 0, 0.2);
            color: #fff;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            border: 1px solid rgba(0, 255, 0, 0.3);
            text-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .register-container {
                margin: 20px;
                padding: 30px 20px;
            }

            .logo {
                font-size: 2rem;
            }

            .register-container h2 {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">MiniLibrary</div>
        <h2>Buat akunmu</h2>
        
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <form method="POST" action="">
            <input type="email" name="email" placeholder="Masukan email" value="<?php echo htmlspecialchars($old_email); ?>" required>
            <input type="password" name="password" placeholder="Masukan Kata Sandi" required>
            <input type="password" name="confirm_password" placeholder="Konfirmasi Kata Sandi" required>
            <div class="checkbox">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Saya menyetujui <span class="terms"><a href="#" onclick="showTerms()">Ketentuan Layanan</a></span> dan <span class="terms"><a href="#" onclick="showPrivacy()">Kebijakan Privasi</a></span></label>
            </div>
            <button type="submit" name="register">Daftar</button>
        </form>
        <a href="login.php" class="secondary">Sudah punya akun</a>
    </div>

    <script>
        function showTerms() {
            alert('Ketentuan Layanan:\n\n1. Pengguna bertanggung jawab atas keamanan akun\n2. Dilarang menyalahgunakan sistem\n3. Data pribadi akan dijaga kerahasiaannya');
        }
        
        function showPrivacy() {
            alert('Kebijakan Privasi:\n\n1. Data email hanya digunakan untuk login\n2. Password di-enkripsi dengan aman\n3. Data tidak akan dibagikan ke pihak ketiga');
        }
    </script>
</body>
</html>
