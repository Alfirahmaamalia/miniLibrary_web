<?php
include 'koneksi.php';

echo "<h2>🔧 Setup Database untuk Request Buku</h2>";

// 1. Buat tabel admin
$create_admin = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(255) DEFAULT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($koneksi, $create_admin)) {
    echo "✅ Tabel admin berhasil dibuat/sudah ada<br>";
} else {
    echo "❌ Error membuat tabel admin: " . mysqli_error($koneksi) . "<br>";
}

// 2. Buat tabel admin_log
$create_log = "CREATE TABLE IF NOT EXISTS admin_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admin_email VARCHAR(100) NOT NULL,
    action VARCHAR(50) NOT NULL,
    target_id INT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if (mysqli_query($koneksi, $create_log)) {
    echo "✅ Tabel admin_log berhasil dibuat/sudah ada<br>";
} else {
    echo "❌ Error membuat tabel admin_log: " . mysqli_error($koneksi) . "<br>";
}

// 3. Update tabel request_buku
$alter_request = "ALTER TABLE request_buku 
    ADD COLUMN IF NOT EXISTS approved_by VARCHAR(100) DEFAULT NULL,
    ADD COLUMN IF NOT EXISTS approved_at DATETIME DEFAULT NULL";

if (mysqli_query($koneksi, $alter_request)) {
    echo "✅ Tabel request_buku berhasil diupdate<br>";
} else {
    echo "❌ Error update tabel request_buku: " . mysqli_error($koneksi) . "<br>";
}

// 4. Cek apakah ada user yang bisa dijadikan admin
$users_query = mysqli_query($koneksi, "SELECT email FROM users LIMIT 5");
echo "<h3>👥 User yang tersedia untuk dijadikan admin:</h3>";
echo "<form method='POST' action='make_admin.php'>";
echo "<select name='user_email' required>";
echo "<option value=''>Pilih user untuk dijadikan admin</option>";
while ($user = mysqli_fetch_assoc($users_query)) {
    echo "<option value='{$user['email']}'>{$user['email']}</option>";
}
echo "</select>";
echo "<button type='submit' style='margin-left: 10px; padding: 5px 15px; background: #007bff; color: white; border: none; border-radius: 4px;'>Jadikan Admin</button>";
echo "</form>";

// 5. Tampilkan admin yang sudah ada
$admin_query = mysqli_query($koneksi, "SELECT email, nama, role, created_at FROM admin");
if (mysqli_num_rows($admin_query) > 0) {
    echo "<h3>👨‍💼 Admin yang sudah terdaftar:</h3>";
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Email</th><th>Nama</th><th>Role</th><th>Dibuat</th></tr>";
    while ($admin = mysqli_fetch_assoc($admin_query)) {
        echo "<tr>";
        echo "<td>{$admin['email']}</td>";
        echo "<td>{$admin['nama']}</td>";
        echo "<td>{$admin['role']}</td>";
        echo "<td>{$admin['created_at']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>⚠️ Belum ada admin yang terdaftar</p>";
}

echo "<br><a href='dashboard.php' style='padding: 10px 20px; background: #28a745; color: white; text-decoration: none; border-radius: 4px;'>Kembali ke Dashboard</a>";
?>
