<?php
session_start();



// Fungsi helper (jika belum ada di session.php)
function isLoggedIn() {
    return isset($_SESSION['user_role']);
}

// Redirect jika user sudah login
if (isLoggedIn() && $_SESSION['user_role'] === "admin") {
    header("Location: users/beranda_pengguna.php");
    exit();
}
if (isLoggedIn() && $_SESSION['user_role'] === "users") {
    header("Location: users/beranda_pengguna.php");
    exit();
}

// Logout jika ada parameter logout
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Minilibrary - Pilih Role</title>
  <link rel="stylesheet" href="./css/style.css" />
</head>
<body>
  <nav class="navbar">
    <div class="navbar-inner">
      <h1 class="navbar-brand">MiniLibrary</h1>
    </div>
  </nav>

  <section class="full-screen-section">
    <div class="form-container" style="margin-top: 50px;">
      <div class="form-inner">
        <div>
          <h1 class="heading-bold" style="text-align:center;">Selamat Datang!</h1>
          <h1 class="heading" style="text-align:center;">Silakan pilih role Anda</h1>
        </div>
        <div class="role-selection" style="display:flex;gap:32px;justify-content:center;flex-wrap:wrap;margin-top:32px;">
          <!-- Card Admin -->
          <div class="role-card" style="width:300px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border-radius:16px;padding:2rem 1.5rem;text-align:center;">
            <h2 style="margin-bottom:1rem;">Admin</h2>
            <p style="margin-bottom:2rem;">Login sebagai administrator untuk mengelola sistem</p>
            <a href="../admin/loginadmin.php" class="btn btn-primary" style="width:70%;margin:0 auto;">Login Admin</a>
          </div>
          <!-- Card Petugas -->
          <div class="role-card" style="width:300px;box-shadow:0 4px 16px rgba(0,0,0,0.08);border-radius:16px;padding:2rem 1.5rem;text-align:center;">
            <h2 style="margin-bottom:1rem;">Pengguna</h2>
            <p style="margin-bottom:2rem;">Login sebagai pengguna untuk melihat perpustakaan</p>
            <a href="../users/login_anggota.php" class="btn btn-secondary" style="width:70%;margin:0 auto;">Login Pengguna</a>
          </div>
        </div>
      </div>
    </div>
  </section>
</body>
</html>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda Anggota - MiniLibrary</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f8f9fc;
            background-image : url('../assets/buku.png')
            color: #333;
            line-height: 1.6;
        }

        /* Navbar */
        .navbar {
            background: #4e73df;
            padding: 15px 20px;
            color: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }

        .navbar-nav {
            display: flex;
            list-style: none;
        }

        .nav-item {
            margin-left: 20px;
        }

        .nav-link {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 15px;
            transition: color 0.3s;
        }

        .nav-link:hover {
            color: white;
        }

        .nav-link.active {
            color: white;
            font-weight: 600;
        }

        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        <?php
        // Tampilkan pesan jika ada
        if (isset($_SESSION['message'])) {
            $message_class = ($_SESSION['message_type'] == 'success') ? 'success' : 'error';
            echo "<div style='background: " . ($message_class == 'success' ? '#d4edda' : '#f8d7da') . "; color: " . ($message_class == 'success' ? '#155724' : '#721c24') . "; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid " . ($message_class == 'success' ? '#c3e6cb' : '#f5c6cb') . "; display: flex; align-items: center; gap: 10px;'>";
            echo ($message_class == 'success' ? '✅' : '❌') . " " . $_SESSION['message'];
            echo "</div>";
            unset($_SESSION['message']);
            unset($_SESSION['message_type']);
        }
        ?>

        .welcome-section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .welcome-text h1 {
            font-size: 24px;
            margin-bottom: 10px;
            color: #333;
        }

        .welcome-text p {
            color: #666;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #4e73df;
            color: white;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn:hover {
            background: #2e59d9;
            transform: translateY(-2px);
        }

        .welcome-image {
            width: 200px;
            height: 200px;
            background-image: url('assets/reading.svg');
            background-size: contain;
            background-repeat: no-repeat;
            background-position: center;
        }

        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .stat-icon.books {
            background: rgba(78, 115, 223, 0.1);
            color: #4e73df;
        }

        .stat-icon.borrowed {
            background: rgba(54, 185, 204, 0.1);
            color: #36b9cc;
        }

        .stat-icon.overdue {
            background: rgba(231, 74, 59, 0.1);
            color: #e74a3b;
        }

        .stat-info h3 {
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }

        .stat-info p {
            font-size: 20px;
            font-weight: 600;
            color: #333;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .section-header h2 {
            font-size: 18px;
            color: #333;
        }

        .section-header a {
            color: #4e73df;
            text-decoration: none;
            font-size: 14px;
        }

        .section-header a:hover {
            text-decoration: underline;
        }

        /* Book Cards */
        .book-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .book-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s;
        }

        .book-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .book-cover {
            height: 200px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            font-size: 40px;
        }

        .book-info {
            padding: 15px;
        }

        .book-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 5px;
            color: #333;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: 48px;
        }

        .book-author {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }

        .book-category {
            display: inline-block;
            padding: 3px 8px;
            background: #f8f9fc;
            border-radius: 4px;
            font-size: 12px;
            color: #4e73df;
        }

        /* Loan Table */
        .table-responsive {
            overflow-x: auto;
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th,
        .table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .table th {
            background-color: #f8f9fc;
            color: #666;
            font-weight: 600;
            font-size: 14px;
        }

        .table tr:hover {
            background-color: #f8f9fc;
        }

        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        .empty-state-icon {
            font-size: 40px;
            margin-bottom: 10px;
            color: #ddd;
        }

        /* Footer */
        .footer {
            background: white;
            padding: 20px;
            text-align: center;
            color: #666;
            font-size: 14px;
            border-top: 1px solid #eee;
            margin-top: 40px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .welcome-section {
                flex-direction: column;
                text-align: center;
            }

            .welcome-image {
                margin-top: 20px;
            }

            .navbar-nav {
                display: none;
            }

            .mobile-menu-btn {
                display: block;
            }
        }

        @media (max-width: 576px) {
            .stats-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>