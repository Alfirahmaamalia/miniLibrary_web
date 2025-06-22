-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 21 Jun 2025 pada 13.24
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `projek_web`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(255) DEFAULT NULL,
  `role` enum('super_admin','admin') DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id`, `email`, `password`, `nama`, `role`, `created_at`, `updated_at`, `telepon`, `alamat`, `foto_profil`) VALUES
(1, 'haha@gmail.com', '$2y$10$xHzGI4vnkBUaNlR6OSL8L.O/vwa2uLpx843w5a3YxpTQto2SnPLrS', 'haha@gmail.com', 'admin', '2025-06-20 10:45:02', '2025-06-21 10:22:54', '082211221122', 'Gedong Meneng', 'uploads/admin/admin_a89ef47d53d683554c9399c75998e4f6_1750501374.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin_log`
--

CREATE TABLE `admin_log` (
  `id` int(11) NOT NULL,
  `admin_email` varchar(100) NOT NULL,
  `action` varchar(50) NOT NULL,
  `target_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin_log`
--

INSERT INTO `admin_log` (`id`, `admin_email`, `action`, `target_id`, `description`, `created_at`) VALUES
(1, 'haha@gmail.com', 'approve', 1, 'Request buku: Universitas Lampung ', '2025-06-20 05:49:46'),
(2, 'haha@gmail.com', 'complete', 2, 'Request buku: Football', '2025-06-20 05:50:02'),
(3, 'haha@gmail.com', 'complete', 1, 'Request buku: Universitas Lampung ', '2025-06-20 05:50:43'),
(4, 'haha@gmail.com', 'approve', 3, 'Request buku: Apa ya', '2025-06-20 06:02:07'),
(5, 'haha@gmail.com', 'complete', 3, 'Request buku: Apa ya (Buku ID: 9)', '2025-06-20 06:02:23'),
(6, 'haha@gmail.com', 'approve', 4, 'Request buku: kanper', '2025-06-20 06:16:27'),
(7, 'haha@gmail.com', 'complete', 4, 'Request buku: kanper (Buku ID: 10)', '2025-06-20 06:16:40'),
(8, 'haha@gmail.com', 'delete_request', 4, 'Menghapus request buku: kanper dari rull@gmail.com', '2025-06-20 06:28:02'),
(9, 'haha@gmail.com', 'approve', 5, 'Request buku: Ilmu Komputer', '2025-06-20 12:37:32'),
(10, 'haha@gmail.com', 'complete', 5, 'Request buku: Ilmu Komputer (Buku ID: 11)', '2025-06-20 12:37:48');

-- --------------------------------------------------------

--
-- Struktur dari tabel `anggota`
--

CREATE TABLE `anggota` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `alamat` text NOT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `telepon` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `status` enum('Aktif','Non Aktif') DEFAULT 'Aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `password` varchar(255) NOT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp(),
  `foto` varchar(255) DEFAULT NULL,
  `terakhir_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `anggota`
--

INSERT INTO `anggota` (`id`, `nama`, `alamat`, `foto_profil`, `telepon`, `email`, `status`, `created_at`, `password`, `tanggal_daftar`, `foto`, `terakhir_login`) VALUES
(5, 'haha', 'jalan 123', NULL, '121222', 'aa2@gmail.com', 'Aktif', '2025-06-10 16:28:47', '', '2025-06-12 23:30:56', NULL, NULL),
(6, 'soraa', 'jalan prof', NULL, '08221122', 'sora@gmail.com', 'Aktif', '2025-06-10 16:29:17', '', '2025-06-12 23:30:56', NULL, NULL),
(7, 'Mira', 'Untung Senopati', NULL, '12121212', 'mir@gmail.com', 'Aktif', '2025-06-11 02:19:09', '', '2025-06-12 23:30:56', NULL, NULL),
(8, 'famous', 'lamsel 1', NULL, '8989', 'lam@gmail.com', 'Non Aktif', '2025-06-11 08:11:05', '', '2025-06-12 23:30:56', NULL, NULL),
(9, 'serana', 'balam ujung', NULL, '123456', 'serana@gmail.com', 'Aktif', '2025-06-11 15:42:37', '', '2025-06-12 23:30:56', NULL, NULL),
(10, 'KhomarulH', 'Rajabasa', NULL, '081278786432', 'khomarulhidayat9@gmail.com', 'Aktif', '2025-06-11 16:17:33', '', '2025-06-12 23:30:56', NULL, NULL),
(11, 'Khomarul', 'rajabasa raya', NULL, '08228282', 'rull@gmail.com', 'Aktif', '2025-06-12 16:34:37', '$2y$10$x4Hw2AOqfLVyiXxEOx07Vef9dz95FRN4OwzLSP37eAW623Bhr7zq2', '2025-06-12 23:34:37', NULL, '2025-06-20 17:52:40'),
(12, 'fandri', 'Pedalaman', NULL, '123123', 'fandri@gmail.com', 'Aktif', '2025-06-12 16:46:46', '$2y$10$r7Uh7nh9RdXAQtIcmiRuxeZbinu97/wNreTNxP0eMNfGlXY.vCTGq', '2025-06-12 23:46:46', NULL, '2025-06-12 23:57:24'),
(13, 'Khomarul Hidayat', 'Kedaton', 'uploads/profile/profile_13_1750430026.jpg', '123123123', 'asrama@gmail.com', 'Aktif', '2025-06-20 11:33:37', '$2y$10$12Y.ija9j12TsVOqKXKvD.gIVPN5A3ZNdNzCrLvwVdl6TdTDqrzt6', '2025-06-20 18:33:37', NULL, '2025-06-21 17:38:50');

-- --------------------------------------------------------

--
-- Struktur dari tabel `buku`
--

CREATE TABLE `buku` (
  `id` int(11) NOT NULL,
  `judul buku` varchar(255) NOT NULL,
  `penulis` varchar(255) NOT NULL,
  `stok` varchar(255) DEFAULT NULL,
  `kategori` int(11) DEFAULT NULL,
  `tahun_terbit` varchar(100) DEFAULT NULL,
  `status` int(11) DEFAULT 0,
  `id_kategori` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `buku`
--

INSERT INTO `buku` (`id`, `judul buku`, `penulis`, `stok`, `kategori`, `tahun_terbit`, `status`, `id_kategori`) VALUES
(1, 'malin kundang', 'Serti', '10', 1, '2020', 1, NULL),
(2, 'Dasar Pemrograman', '11', '10', 2, '2020', 1, NULL),
(3, 'apa', 'saya', '10', 5, '90', 1, NULL),
(4, 'Sangkuriang', 'BARA', '12', 5, '1921', 1, NULL),
(5, 'Lampung Sai Batin dan Pepadun', 'Khomarul', '100', 7, '2019', 1, NULL),
(6, 'Kampung Halaman', 'Rull', '149', 9, '1990', 1, NULL),
(7, 'Raja Batin', 'Senala', '12', 6, '2000', 1, NULL),
(8, 'Football', 'Marimas', '121', 3, '2001', 1, NULL),
(9, 'Apa ya', 'karna', '5', 1, '2025', 1, NULL),
(10, 'kanper', 'apa ha', '5', 2, '2025', 1, NULL),
(11, 'Ilmu Komputer', 'kajur', '5', 3, '2025', 1, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori`
--

CREATE TABLE `kategori` (
  `id` int(11) NOT NULL,
  `nama_kategori` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori`
--

INSERT INTO `kategori` (`id`, `nama_kategori`) VALUES
(1, 'Fiksi'),
(2, 'Non-Fiksi'),
(3, 'Sejarah'),
(4, 'Ilmiah'),
(5, 'Teknologi'),
(6, 'Biografi'),
(7, 'Pendidikan'),
(8, 'non sejarah'),
(9, 'Jadul'),
(10, 'komic'),
(11, 'apa saja');

-- --------------------------------------------------------

--
-- Struktur dari tabel `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expires` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires`, `created_at`) VALUES
(1, 'asrama@gmail.com', '399d953fbab284bbd9f2c823da622b37f963ae9604676b1144127dcf9709951b', '2025-06-20 14:38:13', '2025-06-20 11:38:13'),
(2, 'asrama@gmail.com', 'aa940fa9381508f1689b43a106081b6a3d529a4d55c14695a3d105c9cfe5b886', '2025-06-20 14:39:01', '2025-06-20 11:39:01'),
(3, 'asrama@gmail.com', '166e055d9d4cff5c84800819f2d8c8e1745201c82b545b86bd7c066d2278b250', '2025-06-20 14:39:21', '2025-06-20 11:39:21'),
(4, 'asrama@gmail.com', 'cb257e2d24095ce97a708db667d843fc4e4d60c6cfb397cae10c03944696bed4', '2025-06-20 17:14:42', '2025-06-20 14:14:42');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peminjaman`
--

CREATE TABLE `peminjaman` (
  `id` int(11) NOT NULL,
  `id_anggota` int(11) NOT NULL,
  `id_buku` int(11) NOT NULL,
  `tanggal_pinjam` date NOT NULL,
  `tanggal_kembali` date NOT NULL,
  `tanggal_dikembalikan` date DEFAULT NULL,
  `status` enum('dipinjam','dikembalikan') DEFAULT 'dipinjam',
  `denda` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `perpanjangan` int(11) DEFAULT 0 COMMENT 'Jumlah perpanjangan (maksimal 1)',
  `keterangan` text DEFAULT NULL COMMENT 'Catatan tambahan'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peminjaman`
--

INSERT INTO `peminjaman` (`id`, `id_anggota`, `id_buku`, `tanggal_pinjam`, `tanggal_kembali`, `tanggal_dikembalikan`, `status`, `denda`, `created_at`, `updated_at`, `perpanjangan`, `keterangan`) VALUES
(1, 5, 1, '2025-06-11', '2025-06-19', NULL, 'dipinjam', 0.00, '2025-06-11 15:35:06', '2025-06-11 15:35:06', 0, NULL),
(2, 9, 2, '2025-06-11', '2025-06-10', NULL, 'dipinjam', 0.00, '2025-06-11 15:43:01', '2025-06-11 15:43:01', 0, NULL),
(3, 6, 1, '2025-06-11', '2025-06-02', '2025-06-11', 'dikembalikan', 9000.00, '2025-06-11 15:47:32', '2025-06-11 16:00:11', 0, NULL),
(4, 8, 3, '2025-06-11', '2025-06-18', NULL, 'dipinjam', 0.00, '2025-06-11 16:16:26', '2025-06-11 16:16:26', 0, NULL),
(5, 11, 3, '2025-06-12', '2025-06-19', '2025-06-14', 'dikembalikan', 0.00, '2025-06-12 16:44:33', '2025-06-14 17:27:43', 0, NULL),
(6, 11, 4, '2025-06-14', '2025-06-21', '2025-06-14', 'dikembalikan', 0.00, '2025-06-14 15:48:33', '2025-06-14 17:26:04', 0, NULL),
(7, 11, 3, '2025-06-14', '2025-06-21', '2025-06-14', 'dikembalikan', 0.00, '2025-06-14 17:29:25', '2025-06-14 17:29:35', 0, NULL),
(8, 11, 1, '2025-06-14', '2025-06-28', '2025-06-14', 'dikembalikan', 0.00, '2025-06-14 17:30:41', '2025-06-14 17:46:47', 1, NULL),
(9, 11, 2, '2025-06-14', '2025-06-21', NULL, 'dipinjam', 0.00, '2025-06-14 17:48:31', '2025-06-14 17:48:31', 0, NULL),
(10, 11, 6, '2025-06-14', '2025-06-21', NULL, 'dipinjam', 0.00, '2025-06-14 18:09:51', '2025-06-14 18:09:51', 0, NULL),
(11, 11, 1, '2025-06-19', '2025-06-26', NULL, 'dipinjam', 0.00, '2025-06-19 17:26:07', '2025-06-19 17:26:07', 0, NULL),
(12, 13, 10, '2025-06-20', '2025-06-27', '2025-06-20', 'dikembalikan', 0.00, '2025-06-20 12:03:47', '2025-06-20 12:15:20', 0, NULL),
(13, 13, 9, '2025-06-20', '2025-06-27', '2025-06-20', 'dikembalikan', 0.00, '2025-06-20 12:04:31', '2025-06-20 12:16:33', 0, NULL),
(14, 13, 8, '2025-06-20', '2025-06-27', '2025-06-20', 'dikembalikan', 0.00, '2025-06-20 12:17:34', '2025-06-20 12:22:37', 0, NULL),
(15, 13, 10, '2025-06-20', '2025-06-27', '2025-06-20', 'dikembalikan', 0.00, '2025-06-20 12:24:25', '2025-06-20 12:24:37', 0, NULL),
(16, 13, 6, '2025-06-20', '2025-06-27', '2025-06-20', 'dikembalikan', 0.00, '2025-06-20 12:32:11', '2025-06-20 12:32:18', 0, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `request_buku`
--

CREATE TABLE `request_buku` (
  `id` int(11) NOT NULL,
  `judul_buku` varchar(255) NOT NULL,
  `penulis` varchar(255) NOT NULL,
  `kategori` int(11) NOT NULL,
  `alasan` text NOT NULL,
  `email_pemohon` varchar(255) NOT NULL,
  `tanggal_request` datetime NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `keterangan` text DEFAULT NULL,
  `tanggal_update` datetime DEFAULT NULL,
  `approved_by` varchar(100) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `buku_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `request_buku`
--

INSERT INTO `request_buku` (`id`, `judul_buku`, `penulis`, `kategori`, `alasan`, `email_pemohon`, `tanggal_request`, `status`, `keterangan`, `tanggal_update`, `approved_by`, `approved_at`, `buku_id`) VALUES
(1, 'Universitas Lampung ', 'Rektor', 6, 'Saya ingin mengetahui sisi unila', 'haha@gmail.com', '2025-06-14 20:16:00', 'completed', 'terima kasih yaa', NULL, 'haha@gmail.com', '2025-06-20 12:50:43', NULL),
(2, 'Football', 'Marimas', 5, 'Karena saya ingin mengetahui tentang sepak bola', 'rull@gmail.com', '2025-06-20 12:35:41', 'completed', 'terima kasih ya', NULL, 'haha@gmail.com', '2025-06-20 12:50:02', NULL),
(3, 'Apa ya', 'karna', 1, 'karena saya ingin ini itu apa saja yang ada di kolom buku, terima kasih yaaa', 'rull@gmail.com', '2025-06-20 13:01:36', 'completed', 'okee terima kasih', NULL, 'haha@gmail.com', '2025-06-20 13:02:23', 9),
(5, 'Ilmu Komputer', 'kajur', 3, 'karena saya ingin mengetahui apa saja di dalam prodi ilmu komputer.', 'asrama@gmail.com', '2025-06-20 19:36:58', 'completed', 'Buku telah disetujui', NULL, 'haha@gmail.com', '2025-06-20 19:37:48', 11);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `email`, `password`) VALUES
(1, 'mirasepira71@gmail.com', 'mmm'),
(3, 'miraaa@gmail.com', '111'),
(4, 'mwwa@gmail.com', 'kkk'),
(5, 'tes@gmail.com', 'ttt'),
(6, 'ngetes@gmail.com', '000'),
(7, 'miraa@gmail.com', '000'),
(8, 'rull@gmail.com', '123123'),
(9, 'haha@gmail.com', '$2y$10$xHzGI4vnkBUaNlR6OSL8L.O/vwa2uLpx843w5a3YxpTQto2SnPLrS'),
(10, 'baraganteng@gmail.com', '$2y$10$YZfU5Grzb7U1Mj9oam6DNeo6BUKA2gqePyVTSnDdVD/qvX4Nr5D2C'),
(11, 'hidayat91@gmail.com', '$2y$10$M6FoGbXhxdl5PwOHKbuTCu123rpwP/FbMEI1R.xVZkwatgnbiETJW'),
(12, 'saya@gmail.com', '$2y$10$33Tiwuom0pyaRe0UekP2JOutRnCnYIbZLN7D30Ei65McIrzK/Zn1a'),
(13, 'arul@gmail.com', '$2y$10$3n/Q2n5AMtSJizCWBPpw0eAwmQblV.S6cKetdFGPe8weAs2Uw1HO6'),
(14, 'hara@gmail.com', '$2y$10$ORClJIgq0KLDaA2U6M2eKOXZXn/0RQGPkJ0v4wHCxuOu0LIVeFZqa'),
(15, 'anjay@gmail.com', '$2y$10$i308KuDazA9PF5hcmvwLf.AcZCPCYHllRh7.ecH89I2CFSz7ur14S');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `admin_log`
--
ALTER TABLE `admin_log`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `anggota`
--
ALTER TABLE `anggota`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_buku_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_anggota` (`id_anggota`),
  ADD KEY `idx_buku` (`id_buku`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tanggal_pinjam` (`tanggal_pinjam`);

--
-- Indeks untuk tabel `request_buku`
--
ALTER TABLE `request_buku`
  ADD PRIMARY KEY (`id`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `admin_log`
--
ALTER TABLE `admin_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `anggota`
--
ALTER TABLE `anggota`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `buku`
--
ALTER TABLE `buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT untuk tabel `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT untuk tabel `request_buku`
--
ALTER TABLE `request_buku`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `buku`
--
ALTER TABLE `buku`
  ADD CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Ketidakleluasaan untuk tabel `peminjaman`
--
ALTER TABLE `peminjaman`
  ADD CONSTRAINT `peminjaman_ibfk_1` FOREIGN KEY (`id_anggota`) REFERENCES `anggota` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `peminjaman_ibfk_2` FOREIGN KEY (`id_buku`) REFERENCES `buku` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
