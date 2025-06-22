<?php
include 'koneksi.php';

echo "<h2>🔧 Setup Admin System</h2>";

// Buat tabel admin
$create_admin = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if (mysqli_query($koneksi, $create_admin)) {
    echo "✅ Tabel admin berhasil dibuat<br>";
} else {
    echo "❌ Error membuat tabel admin: " . mysqli_error($koneksi) . "<br>";
}

// Update tabel request_buku
$alter_request = "ALTER TABLE request_buku 
    ADD COLUMN IF NOT EXISTS approved_by VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME DEFAULT NULL";

if (mysqli_query($koneksi, $alter_request)) {
    echo "✅ Tabel request_buku berhasil diupdate<br>";
} else {
    echo "❌ Error update request_buku: " . mysqli_error($koneksi) . "<br>";
}

// Buat tabel log admin
$create_log = "CREATE TABLE IF NOT EXISTS admin_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_email VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    target_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($koneksi, $create_log)) {
    echo "✅ Tabel admin_log berhasil dibuat<br>";
} else {
    echo "❌ Error membuat tabel admin_log: " . mysqli_error($koneksi) . "<br>";
}

// Tambahkan admin default
$admin_email = 'admin@gmail.com';
$admin_password = password_hash('admin123', PASSWORD_DEFAULT);
$admin_nama = 'Super Admin';

$check_admin = mysqli_query($koneksi, "SELECT email FROM admin WHERE email = '$admin_email'");
if (mysqli_num_rows($check_admin) == 0) {
    $insert_admin = "INSERT INTO admin (email, password, nama, role) VALUES 
                     ('$admin_email', '$admin_password', '$admin_nama', 'super_admin')";
    
    if (mysqli_query($koneksi, $insert_admin)) {
        echo "✅ Admin default berhasil ditambahkan<br>";
        echo "📧 Email: admin@gmail.com<br>";
        echo "🔑 Password: admin123<br>";
    } else {
        echo "❌ Error menambah admin: " . mysqli_error($koneksi) . "<br>";
    }
} else {
    echo "ℹ️ Admin sudah ada<br>";
}

// Tambahkan admin dari users yang sudah ada
echo "<br><h3>🔄 Konversi Users ke Admin</h3>";
echo "<form method='POST'>";
echo "<p>Pilih user yang ingin dijadikan admin:</p>";

$users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY email");
while ($user = mysqli_fetch_assoc($users)) {
    $is_admin = mysqli_query($koneksi, "SELECT email FROM admin WHERE email = '{$user['email']}'");
    $admin_status = mysqli_num_rows($is_admin) > 0 ? " (Sudah Admin)" : "";
    
    echo "<label style='display: block; margin: 5px 0;'>";
    echo "<input type='checkbox' name='make_admin[]' value='{$user['email']}'> ";
    echo "{$user['email']}{$admin_status}";
    echo "</label>";
}

echo "<br><button type='submit' name='convert_users' style='background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;'>Jadikan Admin</button>";
echo "</form>";

// Proses konversi users ke admin
if (isset($_POST['convert_users']) && isset($_POST['make_admin'])) {
    echo "<br><h4>📝 Hasil Konversi:</h4>";
    foreach ($_POST['make_admin'] as $email) {
        $user_data = mysqli_query($koneksi, "SELECT * FROM users WHERE email = '$email'");
        if ($user = mysqli_fetch_assoc($user_data)) {
            $check_existing = mysqli_query($koneksi, "SELECT email FROM admin WHERE email = '$email'");
            if (mysqli_num_rows($check_existing) == 0) {
                $insert_new_admin = "INSERT INTO admin (email, password, nama, role) VALUES 
                                   ('$email', '{$user['password']}', '$email', 'admin')";
                if (mysqli_query($koneksi, $insert_new_admin)) {
                    echo "✅ $email berhasil dijadikan admin<br>";
                } else {
                    echo "❌ Error: $email - " . mysqli_error($koneksi) . "<br>";
                }
            } else {
                echo "ℹ️ $email sudah menjadi admin<br>";
            }
        }
    }
}

echo "<br><hr><h3>📋 Daftar Admin Saat Ini:</h3>";
$admins = mysqli_query($koneksi, "SELECT * FROM admin ORDER BY created_at DESC");
if (mysqli_num_rows($admins) > 0) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background: #f0f0f0;'><th>Email</th><th>Nama</th><th>Role</th><th>Dibuat</th></tr>";
    while ($admin = mysqli_fetch_assoc($admins)) {
        echo "<tr>";
        echo "<td>{$admin['email']}</td>";
        echo "<td>{$admin['nama']}</td>";
        echo "<td>{$admin['role']}</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($admin['created_at'])) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Belum ada admin.</p>";
}

echo "<br><p><strong>🎯 Langkah Selanjutnya:</strong></p>";
echo "<ol>";
echo "<li>Login menggunakan email admin yang sudah dibuat</li>";
echo "<li>Akses dashboard untuk mengelola request buku</li>";
echo "<li>Ganti password default jika diperlukan</li>";
echo "</ol>";
?>
