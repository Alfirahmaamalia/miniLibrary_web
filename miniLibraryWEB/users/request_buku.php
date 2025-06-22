<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit;
}

include 'koneksi.php';

// Error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Buat tabel admin jika belum ada
$create_admin_table = "CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_admin_table);

// Buat tabel request_buku jika belum ada
$create_request_table = "CREATE TABLE IF NOT EXISTS request_buku (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul_buku VARCHAR(255) NOT NULL,
    penulis VARCHAR(255) NOT NULL,
    kategori INT NOT NULL,
    alasan TEXT NOT NULL,
    email_pemohon VARCHAR(255) NOT NULL,
    tanggal_request DATETIME NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'completed') DEFAULT 'pending',
    keterangan TEXT,
    approved_by VARCHAR(255),
    approved_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
mysqli_query($koneksi, $create_request_table);

// Cek status admin
$user_email = $_SESSION['email'];
$is_admin = false;

try {
    $admin_check = mysqli_query($koneksi, "SELECT email FROM admin WHERE email = '$user_email'");
    if ($admin_check) {
        $is_admin = mysqli_num_rows($admin_check) > 0;
    }
} catch (Exception $e) {
    $is_admin = false;
}

// Redirect admin ke halaman kelola request
if ($is_admin && file_exists('kelola_request.php')) {
    header("Location: kelola_request.php");
    exit;
}

// Proses form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_request'])) {
    $judul_buku = trim(mysqli_real_escape_string($koneksi, $_POST['judul_buku']));
    $penulis = trim(mysqli_real_escape_string($koneksi, $_POST['penulis']));
    $kategori = (int)$_POST['kategori'];
    $alasan = trim(mysqli_real_escape_string($koneksi, $_POST['alasan']));
    $email_pemohon = $_SESSION['email'];
    $tanggal_request = date('Y-m-d H:i:s');
    
    // Validasi input
    $errors = [];
    if (empty($judul_buku)) $errors[] = "Judul buku tidak boleh kosong";
    if (empty($penulis)) $errors[] = "Nama penulis tidak boleh kosong";
    if ($kategori < 1 || $kategori > 7) $errors[] = "Kategori tidak valid";
    if (empty($alasan)) $errors[] = "Alasan permintaan tidak boleh kosong";
    if (strlen($alasan) < 20) $errors[] = "Alasan permintaan minimal 20 karakter";
    
    // Cek duplikasi request dalam 24 jam terakhir
    $check_duplicate = mysqli_query($koneksi, "SELECT id FROM request_buku 
                                              WHERE email_pemohon = '$email_pemohon' 
                                              AND judul_buku = '$judul_buku' 
                                              AND penulis = '$penulis'
                                              AND tanggal_request >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    if (mysqli_num_rows($check_duplicate) > 0) {
        $errors[] = "Anda sudah mengajukan request untuk buku yang sama dalam 24 jam terakhir";
    }
    
    if (empty($errors)) {
        $query = "INSERT INTO request_buku (judul_buku, penulis, kategori, alasan, email_pemohon, tanggal_request, status) 
                  VALUES ('$judul_buku', '$penulis', $kategori, '$alasan', '$email_pemohon', '$tanggal_request', 'pending')";
        
        if (mysqli_query($koneksi, $query)) {
            $_SESSION['message'] = "Permintaan buku berhasil diajukan! Menunggu persetujuan admin.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . mysqli_error($koneksi);
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = implode("<br>", $errors);
        $_SESSION['message_type'] = "error";
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Tampilkan pesan
if (isset($_SESSION['message'])) {
    $message_class = ($_SESSION['message_type'] == 'success') ? 'success-message' : 'error-message';
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

function getStatusRequest($status) {
    switch($status) {
        case 'pending':
            return ['text' => 'Menunggu Persetujuan', 'class' => 'pending', 'icon' => '⏳'];
        case 'approved':
            return ['text' => 'Disetujui', 'class' => 'approved', 'icon' => '✅'];
        case 'rejected':
            return ['text' => 'Ditolak', 'class' => 'rejected', 'icon' => '❌'];
        case 'completed':
            return ['text' => 'Buku Sudah Tersedia', 'class' => 'completed', 'icon' => '📚'];
        default:
            return ['text' => 'Tidak Diketahui', 'class' => 'unknown', 'icon' => '❓'];
    }
}

function getKategoriName($kategori_id) {
    $kategori_names = [
        1 => 'Fiksi', 2 => 'Non-Fiksi', 3 => 'Sejarah', 4 => 'Ilmiah',
        5 => 'Teknologi', 6 => 'Biografi', 7 => 'Pendidikan'
    ];
    return isset($kategori_names[$kategori_id]) ? $kategori_names[$kategori_id] : 'Tidak Diketahui';
}

// Statistik user
$user_stats = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT 
        COUNT(*) as total_request,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
        SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
        SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed
    FROM request_buku WHERE email_pemohon = '$user_email'
"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Buku - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            line-height: 1.6;
        }

        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 300;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .user-info {
            background: linear-gradient(135deg, #e3f2fd, #bbdefb);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 5px solid #2196f3;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-top: 4px solid;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }

        .stat-card:nth-child(1) { border-top-color: #007bff; }
        .stat-card:nth-child(2) { border-top-color: #ffc107; }
        .stat-card:nth-child(3) { border-top-color: #28a745; }
        .stat-card:nth-child(4) { border-top-color: #dc3545; }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .stat-card:nth-child(1) .stat-number { color: #007bff; }
        .stat-card:nth-child(2) .stat-number { color: #ffc107; }
        .stat-card:nth-child(3) .stat-number { color: #28a745; }
        .stat-card:nth-child(4) .stat-number { color: #dc3545; }

        .stat-label {
            color: #666;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .request-form {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f1f1f1;
        }

        .form-header h3 {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 10px;
        }

        .form-header p {
            color: #666;
            font-size: 1rem;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .char-counter {
            font-size: 0.8rem;
            color: #666;
            text-align: right;
            margin-top: 5px;
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
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .table-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .table-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 25px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .table-header h3 {
            font-size: 1.5rem;
            margin: 0;
            color: #333;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f1f1f1;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #666;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background: #f8f9fa;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-badge.approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .status-badge.completed {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }

        .success-message, .error-message {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .success-message {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .empty-state {
            text-align: center;
            padding: 50px 20px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state h4 {
            font-size: 1.3rem;
            margin-bottom: 10px;
            color: #333;
        }

        .empty-state p {
            font-size: 1rem;
            opacity: 0.8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header h1 {
                font-size: 2rem;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
            }

            th, td {
                padding: 10px 8px;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }

            .request-form {
                padding: 20px;
            }

            .stat-number {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>MiniLibrary</h2>
    <a href="dashboard.php">📊 Dashboard</a>
    <a href="katalog.php">📚 Katalog Buku</a>
    <a href="pinjaman_saya.php">📖 Pinjaman Saya</a>
    <a class="active" href="request_buku.php">📝 Request Buku</a>
    <a href="profil.php">👤 Profil</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="content">
    <div class="page-header">
        <h1>📝 Request Buku Baru</h1>
        <p>Ajukan permintaan buku yang belum tersedia di perpustakaan</p>
    </div>

    <div class="user-info">
        <strong>👤 Anggota:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?> | 
        <strong>🕒 Waktu:</strong> <?php echo date('d/m/Y H:i'); ?> WIB
    </div>

    <?php if (isset($message)): ?>
        <div class="<?php echo $message_class; ?>">
            <?php echo $message_class == 'success-message' ? '✅' : '❌'; ?>
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <!-- Statistik User -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_stats['total_request'] ?? 0; ?></div>
            <div class="stat-label">Total Request</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_stats['pending'] ?? 0; ?></div>
            <div class="stat-label">Menunggu</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_stats['approved'] ?? 0; ?></div>
            <div class="stat-label">Disetujui</div>
        </div>
        <div class="stat-card">
            <div class="stat-number"><?php echo $user_stats['completed'] ?? 0; ?></div>
            <div class="stat-label">Selesai</div>
        </div>
    </div>

    <!-- Form Request Buku -->
    <div class="request-form">
        <div class="form-header">
            <h3>📚 Ajukan Permintaan Buku Baru</h3>
            <p>Lengkapi form di bawah ini untuk mengajukan permintaan buku</p>
        </div>
        <form method="POST" id="requestForm">
            <div class="form-grid">
                <div class="form-group">
                    <label for="judul_buku">📖 Judul Buku *</label>
                    <input type="text" id="judul_buku" name="judul_buku" required 
                           placeholder="Masukkan judul buku yang diinginkan"
                           maxlength="255">
                </div>
                <div class="form-group">
                    <label for="penulis">✍️ Penulis *</label>
                    <input type="text" id="penulis" name="penulis" required 
                           placeholder="Nama penulis buku"
                           maxlength="255">
                </div>
                <div class="form-group">
                    <label for="kategori">📂 Kategori *</label>
                    <select id="kategori" name="kategori" required>
                        <option value="">Pilih Kategori Buku</option>
                        <option value="1">📚 Fiksi</option>
                        <option value="2">📰 Non-Fiksi</option>
                        <option value="3">🏛️ Sejarah</option>
                        <option value="4">🔬 Ilmiah</option>
                        <option value="5">💻 Teknologi</option>
                        <option value="6">👤 Biografi</option>
                        <option value="7">🎓 Pendidikan</option>
                    </select>
                </div>
                <div class="form-group full-width">
                    <label for="alasan">💭 Alasan Permintaan *</label>
                    <textarea id="alasan" name="alasan" required 
                              placeholder="Jelaskan mengapa buku ini diperlukan (untuk penelitian, tugas, minat baca, dll.) - minimal 20 karakter"
                              maxlength="1000"></textarea>
                    <div class="char-counter">
                        <span id="charCount">0</span>/1000 karakter (minimal 20)
                    </div>
                </div>
            </div>
            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">
                    🔄 Reset Form
                </button>
                <button type="submit" name="submit_request" class="btn btn-primary">
                    📤 Ajukan Request
                </button>
            </div>
        </form>
    </div>

    <!-- Riwayat Request -->
    <div class="table-card">
        <div class="table-header">
            <h3>📋 Riwayat Request Saya</h3>
        </div>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Detail Buku</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "SELECT * FROM request_buku WHERE email_pemohon = '$user_email' ORDER BY tanggal_request DESC LIMIT 10";
                    $result = mysqli_query($koneksi, $query);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $status_info = getStatusRequest($row['status']);
                            
                            echo "<tr>
                                <td>
                                    <strong>".date('d/m/Y', strtotime($row['tanggal_request']))."</strong><br>
                                    <small style='color: #666;'>".date('H:i', strtotime($row['tanggal_request']))." WIB</small>
                                </td>
                                <td>
                                    <strong style='color: #333;'>".htmlspecialchars($row['judul_buku'])."</strong><br>
                                    <small style='color: #666;'>oleh ".htmlspecialchars($row['penulis'])."</small>
                                </td>
                                <td>".getKategoriName($row['kategori'])."</td>
                                <td>
                                    <span class='status-badge {$status_info['class']}'>
                                        {$status_info['icon']} {$status_info['text']}
                                    </span>
                                </td>
                                <td>".htmlspecialchars($row['keterangan'] ?? 'Belum ada keterangan')."</td>
                            </tr>";
                        }
                    } else {
                        echo "<tr>
                                <td colspan='5'>
                                    <div class='empty-state'>
                                        <div class='empty-state-icon'>📝</div>
                                        <h4>Belum Ada Request</h4>
                                        <p>Anda belum pernah mengajukan permintaan buku. Mulai ajukan request buku pertama Anda!</p>
                                    </div>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Character counter untuk textarea
document.getElementById('alasan').addEventListener('input', function() {
    const charCount = this.value.length;
    const counter = document.getElementById('charCount');
    counter.textContent = charCount;
    
    if (charCount < 20) {
        counter.style.color = '#dc3545';
    } else if (charCount > 900) {
        counter.style.color = '#ffc107';
    } else {
        counter.style.color = '#28a745';
    }
});

// Form validation
document.getElementById('requestForm').addEventListener('submit', function(e) {
    const alasan = document.getElementById('alasan').value;
    if (alasan.length < 20) {
        e.preventDefault();
        alert('Alasan permintaan minimal 20 karakter!');
        document.getElementById('alasan').focus();
    }
});

// Auto-resize textarea
document.getElementById('alasan').addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = this.scrollHeight + 'px';
});
</script>

</body>
</html>
