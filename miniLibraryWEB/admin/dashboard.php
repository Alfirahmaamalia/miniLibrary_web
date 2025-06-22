<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: loginadmin.php");
    exit;
}

include '../koneksi/koneksi.php';

// Tampilkan pesan jika ada
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $message_type = $_SESSION['message_type'];
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Update tabel request_buku jika belum ada kolom yang diperlukan
$cek_kolom = mysqli_query($koneksi, "SHOW COLUMNS FROM request_buku LIKE 'approved_by'");
if (mysqli_num_rows($cek_kolom) === 0) {
    mysqli_query($koneksi, "ALTER TABLE request_buku ADD COLUMN approved_by VARCHAR(100) DEFAULT NULL");
}

// Statistik Dashboard
$total_buku_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM buku");
$total_buku = $total_buku_query ? mysqli_fetch_assoc($total_buku_query)['total'] : 0;

$total_anggota_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM anggota");
$total_anggota = $total_anggota_query ? mysqli_fetch_assoc($total_anggota_query)['total'] : 0;

$peminjaman_aktif_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'");
$peminjaman_aktif = $peminjaman_aktif_query ? mysqli_fetch_assoc($peminjaman_aktif_query)['total'] : 0;

$pending_requests_query = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM request_buku WHERE status = 'pending'");
$pending_requests = $pending_requests_query ? mysqli_fetch_assoc($pending_requests_query)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - MiniLibrary</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <a href="dashboard.php" class="navbar-brand">MiniLibrary Admin</a>
            <ul class="navbar-nav">
                <li class="nav-item"><a href="dashboard.php" class="nav-link active">Dashboard</a></li>
                <li class="nav-item"><a href="manajemen.php" class="nav-link">Manajemen Buku</a></li>
                <li class="nav-item"><a href="anggota.php" class="nav-link">Anggota</a></li>
                <li class="nav-item"><a href="kategori.php" class="nav-link">Kategori</a></li>
                <li class="nav-item"><a href="peminjaman.php" class="nav-link">Peminjaman</a></li>
                <li class="nav-item"><a href="profil_admin.php" class="nav-link">Profil</a></li>
                <li class="nav-item"><a href="logout.php" class="nav-link">Logout</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php
        // Tampilkan pesan jika ada
        if (isset($message)) {
            $message_class = ($message_type == 'success') ? 'success' : 'error';
            echo "<div class='alert alert-$message_class'>";
            echo ($message_class == 'success' ? '✅' : '❌') . " " . $message;
            echo "</div>";
        }
        ?>

        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-text">
                <h1>Selamat Datang, Admin!</h1>
                <p>Kelola perpustakaan digital Anda dengan mudah dan efisien melalui dashboard admin.</p>
                <a href="manajemen.php" class="btn">Kelola Buku</a>
                <a href="tambah_buku.php" class="btn" style="margin-left: 10px; background: #28a745;">Tambah Buku</a>
            </div>
            <div class="welcome-image"></div>
        </div>

        <!-- Stats Row -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon books">📚</div>
                <div class="stat-info">
                    <h3>Total Buku</h3>
                    <p><?php echo number_format($total_buku); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon members">👥</div>
                <div class="stat-info">
                    <h3>Total Anggota</h3>
                    <p><?php echo number_format($total_anggota); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon borrowed">📖</div>
                <div class="stat-info">
                    <h3>Sedang Dipinjam</h3>
                    <p><?php echo number_format($peminjaman_aktif); ?></p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon requests">⏳</div>
                <div class="stat-info">
                    <h3>Request Pending</h3>
                    <p><?php echo number_format($pending_requests); ?></p>
                </div>
            </div>
        </div>

        <!-- Request Management Section -->
        <div class="content-section">
            <div class="section-header">
                <h2>📝 Kelola Request Buku</h2>
                <?php if ($pending_requests > 0): ?>
                    <span class="badge badge-warning">⏳ <?php echo $pending_requests; ?> Request Menunggu</span>
                <?php endif; ?>
            </div>
            
            <?php
            // Ambil data request buku dengan prioritas pending di atas
            $query_requests = mysqli_query($koneksi, "SELECT r.*, 
                CASE 
                    WHEN r.kategori = 1 THEN 'Fiksi'
                    WHEN r.kategori = 2 THEN 'Non-Fiksi'
                    WHEN r.kategori = 3 THEN 'Sejarah'
                    WHEN r.kategori = 4 THEN 'Ilmiah'
                    WHEN r.kategori = 5 THEN 'Teknologi'
                    WHEN r.kategori = 6 THEN 'Biografi'
                    WHEN r.kategori = 7 THEN 'Pendidikan'
                    ELSE 'Lainnya'
                END as nama_kategori
                FROM request_buku r 
                ORDER BY 
                    CASE WHEN r.status = 'pending' THEN 1 ELSE 2 END,
                    r.tanggal_request DESC
                LIMIT 10");
            
            if (mysqli_num_rows($query_requests) > 0):
            ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID Request</th>
                                <th>Detail Pemohon</th>
                                <th>Informasi Buku</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($req = mysqli_fetch_assoc($query_requests)): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: #333; font-size: 16px;">
                                            #<?php echo str_pad($req['id'], 4, '0', STR_PAD_LEFT); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                            <?php echo date('d M Y, H:i', strtotime($req['tanggal_request'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: #333;">
                                            <?php echo htmlspecialchars($req['email_pemohon']); ?>
                                        </div>
                                        <div style="font-size: 12px; color: #666; margin-top: 5px;">
                                            📧 Email Pemohon
                                        </div>
                                    </td>
                                    <td>
                                        <div class="request-detail">
                                            <div style="font-weight: 600; color: #333; margin-bottom: 5px;">
                                                📖 <?php echo htmlspecialchars($req['judul_buku']); ?>
                                            </div>
                                            <div style="font-size: 12px; color: #666; margin-bottom: 3px;">
                                                ✍️ <?php echo htmlspecialchars($req['penulis']); ?>
                                            </div>
                                            <div style="font-size: 11px; color: #4e73df;">
                                                📂 <?php echo $req['nama_kategori']; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        switch($req['status']) {
                                            case 'pending':
                                                $status_class = 'badge-warning';
                                                $status_text = '⏳ Menunggu';
                                                break;
                                            case 'approved':
                                                $status_class = 'badge-success';
                                                $status_text = '✅ Disetujui';
                                                break;
                                            case 'rejected':
                                                $status_class = 'badge-danger';
                                                $status_text = '❌ Ditolak';
                                                break;
                                            case 'completed':
                                                $status_class = 'badge-info';
                                                $status_text = '📚 Selesai';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <?php echo $status_text; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($req['status'] == 'pending'): ?>
                                            <div style="display: flex; flex-direction: column; gap: 5px;">
                                                <button onclick="showApproveModal(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars($req['judul_buku'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($req['email_pemohon'], ENT_QUOTES); ?>')" 
                                                        class="action-btn btn-approve">
                                                    ✅ Setujui
                                                </button>
                                                <button onclick="showRejectModal(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars($req['judul_buku'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($req['email_pemohon'], ENT_QUOTES); ?>')" 
                                                        class="action-btn btn-reject">
                                                    ❌ Tolak
                                                </button>
                                            </div>
                                        <?php elseif ($req['status'] == 'approved'): ?>
                                            <button onclick="showCompleteModal(<?php echo $req['id']; ?>, '<?php echo htmlspecialchars($req['judul_buku'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($req['email_pemohon'], ENT_QUOTES); ?>')" 
                                                    class="action-btn btn-complete">
                                                📚 Selesai
                                            </button>
                                        <?php else: ?>
                                            <div style="text-align: center; color: #666; font-size: 12px;">
                                                ✅ Sudah Diproses
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">📝</div>
                    <p>Belum ada permintaan buku dari anggota perpustakaan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; <?php echo date('Y'); ?> MiniLibrary. Hak Cipta Dilindungi.</p>
    </div>

    <!-- Modal untuk Approve Request -->
    <div id="approveModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>✅ Setujui Request Buku</h3>
                <span class="close" onclick="closeModal('approveModal')">&times;</span>
            </div>
            <form method="POST" action="proses_request_admin_fixed.php">
                <input type="hidden" id="approveRequestId" name="request_id">
                <input type="hidden" name="action" value="approve">
                <div class="modal-body">
                    <div class="request-detail">
                        <div style="font-weight: 600; margin-bottom: 5px;">📖 Judul Buku:</div>
                        <div id="approveBookTitle" style="margin-bottom: 10px;"></div>
                        <div style="font-weight: 600; margin-bottom: 5px;">👤 Pemohon:</div>
                        <div id="approveRequester"></div>
                    </div>
                    <div class="form-group">
                        <label>📝 Keterangan Persetujuan:</label>
                        <textarea name="keterangan" class="form-control" required rows="3"
                                  placeholder="Contoh: Request buku telah disetujui. Buku akan segera dipesan dan ditambahkan ke koleksi perpustakaan."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('approveModal')" class="btn secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn success">
                        ✅ Setujui Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Reject Request -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>❌ Tolak Request Buku</h3>
                <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            </div>
            <form method="POST" action="proses_request_admin_fixed.php">
                <input type="hidden" id="rejectRequestId" name="request_id">
                <input type="hidden" name="action" value="reject">
                <div class="modal-body">
                    <div class="request-detail">
                        <div style="font-weight: 600; margin-bottom: 5px;">📖 Judul Buku:</div>
                        <div id="rejectBookTitle" style="margin-bottom: 10px;"></div>
                        <div style="font-weight: 600; margin-bottom: 5px;">👤 Pemohon:</div>
                        <div id="rejectRequester"></div>
                    </div>
                    <div class="form-group">
                        <label>📝 Alasan Penolakan:</label>
                        <textarea name="keterangan" class="form-control" required rows="3"
                                  placeholder="Contoh: Mohon maaf, request buku tidak dapat disetujui karena budget perpustakaan terbatas bulan ini."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('rejectModal')" class="btn secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn danger">
                        ❌ Tolak Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal untuk Complete Request -->
    <div id="completeModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>📚 Tandai Sebagai Selesai</h3>
                <span class="close" onclick="closeModal('completeModal')">&times;</span>
            </div>
            <form method="POST" action="proses_request_admin_fixed.php">
                <input type="hidden" id="completeRequestId" name="request_id">
                <input type="hidden" name="action" value="complete">
                <div class="modal-body">
                    <div class="request-detail">
                        <div style="font-weight: 600; margin-bottom: 5px;">📖 Judul Buku:</div>
                        <div id="completeBookTitle" style="margin-bottom: 10px;"></div>
                        <div style="font-weight: 600; margin-bottom: 5px;">👤 Pemohon:</div>
                        <div id="completeRequester"></div>
                    </div>
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #f6c23e;">
                        <strong>⚠️ Perhatian:</strong><br>
                        Dengan menandai sebagai selesai, buku akan otomatis ditambahkan ke koleksi perpustakaan dengan stok 5 unit.
                    </div>
                    <div class="form-group">
                        <label>📝 Keterangan Penyelesaian:</label>
                        <textarea name="keterangan" class="form-control" required rows="3"
                                  placeholder="Contoh: Buku telah berhasil dipesan dan sudah tersedia di perpustakaan."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeModal('completeModal')" class="btn secondary">
                        Batal
                    </button>
                    <button type="submit" class="btn info">
                        📚 Tandai Selesai
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Modal functions
    function showApproveModal(id, bookTitle, requester) {
        document.getElementById('approveRequestId').value = id;
        document.getElementById('approveBookTitle').textContent = bookTitle;
        document.getElementById('approveRequester').textContent = requester;
        document.getElementById('approveModal').style.display = 'block';
    }

    function showRejectModal(id, bookTitle, requester) {
        document.getElementById('rejectRequestId').value = id;
        document.getElementById('rejectBookTitle').textContent = bookTitle;
        document.getElementById('rejectRequester').textContent = requester;
        document.getElementById('rejectModal').style.display = 'block';
    }

    function showCompleteModal(id, bookTitle, requester) {
        document.getElementById('completeRequestId').value = id;
        document.getElementById('completeBookTitle').textContent = bookTitle;
        document.getElementById('completeRequester').textContent = requester;
        document.getElementById('completeModal').style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }

    // Auto-hide alert messages
    <?php if (isset($message)): ?>
    setTimeout(function() {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-20px)';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
    <?php endif; ?>

    // Add loading animation to buttons
    document.addEventListener('DOMContentLoaded', function() {
        const buttons = document.querySelectorAll('.action-btn');
        buttons.forEach(button => {
            button.addEventListener('click', function() {
                this.classList.add('loading');
                setTimeout(() => {
                    this.classList.remove('loading');
                }, 2000);
            });
        });
    });
    </script>
</body>
</html>
