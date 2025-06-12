<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Anggota Perpustakaan - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .search-section {
            display: flex;
            gap: 15px;
            align-items: center;
        }

        .search-box {
            position: relative;
        }

        .search-box input {
            padding: 10px 15px 10px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            width: 300px;
            font-size: 14px;
        }

        .search-box::before {
            content: "🔍";
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .status-filter {
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        .add-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }

        .add-btn:hover {
            background: #0056b3;
        }

        .table-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-header {
            margin-bottom: 20px;
            background : #f8f9fa;
            padding: 15px;
        }

        .table-header h3 {
            font-size: 18px;
            margin: 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
        }

        td {
            padding: 12px;
            border-bottom: 1px solid #f1f1f1;
            font-size: 14px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 500;
        }

        .status-badge.aktif {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.non-aktif {
            background: #fff3cd;
            color: #856404;
        }

        .actions {
            display: flex;
            gap: 5px;
        }

        .btn {
            padding: 6px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: white;
            color: #666;
            font-size: 11px;
            cursor: pointer;
            text-decoration: none;
        }

        .btn:hover {
            background: #f8f9fa;
        }

        .btn.primary {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .btn.primary:hover {
            background: #0056b3;
        }

        .btn.danger {
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn.danger:hover {
            background: #f5f5f5;
        }

       .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            border: 1px solid #f5c6cb;
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
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="topbar">
        <h1>Anggota Perpustakaan</h1>
        <div class="profile">
            <img src="assets/profile.png" alt="Profile" class="profile-img">
        </div>
    </div>

    <div class="header-section">
        <div class="search-section">
            <div class="search-box">
                <input type="text" placeholder="Cari Anggota atau ID" id="searchInput">
            </div>
            <select class="status-filter" id="statusFilter">
                <option>Semua Status</option>
                <option>Aktif</option>
                <option>Non Aktif</option>
            </select>
        </div>
        <button class="add-btn" onclick="tambahAnggota()">+ Tambah Anggota</button>
    </div>

    <div class="table-card">
        <div class="table-header">
            <h3>Daftar Anggota</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Lengkap</th>
                    <th>Alamat</th>
                    <th>No. Telepon</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="memberTable">
                <?php
                // Perbaikan: Include file koneksi dan error handling
                include 'koneksi.php';
                
                // Cek apakah koneksi berhasil
                if (!isset($koneksi) || mysqli_connect_error()) {
                    echo "<tr><td colspan='7' class='error-message'>Error: Koneksi database gagal!</td></tr>";
                } else {
                    // Perbaikan: Gunakan mysqli_query dengan variabel koneksi yang benar
                    $query = "SELECT * FROM anggota ORDER BY id ASC";
                    $result = mysqli_query($koneksi, $query);
                    
                    // Cek apakah query berhasil
                    if (!$result) {
                        echo "<tr><td colspan='7' class='error-message'>Error: " . mysqli_error($koneksi) . "</td></tr>";
                    } else if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $status_class = strtolower(str_replace(' ', '-', $row['status']));
                            echo "<tr>
                                <td>".str_pad($row['id'], 3, '0', STR_PAD_LEFT)."</td>
                                <td>".htmlspecialchars($row['nama'])."</td>
                                <td>".htmlspecialchars($row['alamat'])."</td>
                                <td>".htmlspecialchars($row['telepon'])."</td>
                                <td>".htmlspecialchars($row['email'])."</td>
                                <td><span class='status-badge {$status_class}'>".htmlspecialchars($row['status'])."</span></td>
                                <td>
                                    <div class='actions'>
                                        <a href='detail_anggota.php?id={$row['id']}' class='btn primary'>Detail</a>
                                        <a href='edit_anggota.php?id={$row['id']}' class='btn'>✏️</a>
                                        <a href='hapus_anggota.php?id={$row['id']}' class='btn danger' onclick='return confirm(\"Apakah Anda yakin ingin menghapus anggota ini?\")'>🗑️</a>
                                    </div>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align: center; padding: 20px; color: #666;'>Tidak ada data anggota</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
// Fungsi pencarian
document.getElementById('searchInput').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('#memberTable tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        if (text.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Fungsi filter status
document.getElementById('statusFilter').addEventListener('change', function(e) {
    const filterValue = e.target.value;
    const rows = document.querySelectorAll('#memberTable tr');
    
    rows.forEach(row => {
        if (filterValue === 'Semua Status') {
            row.style.display = '';
        } else {
            const statusElement = row.querySelector('.status-badge');
            if (statusElement) {
                const status = statusElement.textContent.trim();
                
                if (status === filterValue) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        }
    });
});

// Fungsi tambah anggota
function tambahAnggota() {
    window.location.href = 'tambah_anggota.php';
}
</script>

</body>
</html>
