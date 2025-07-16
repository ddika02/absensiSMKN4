-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2025 at 03:10 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi_smkn4`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `id` int(11) NOT NULL,
  `id_jadwal` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `pertemuan` int(11) NOT NULL,
  `materi` text DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`id`, `id_jadwal`, `tanggal`, `pertemuan`, `materi`, `catatan`, `created_at`, `updated_at`) VALUES
(29, 27, '2025-07-16', 1, 'verb', 'materi pertama', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(30, 28, '2025-07-16', 1, 'abcdf', 'pertemuan1', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(31, 30, '2025-07-16', 2, 'verb2', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `absensi_detail`
--

CREATE TABLE `absensi_detail` (
  `id` int(11) NOT NULL,
  `id_absensi` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `status` enum('Hadir','Izin','Sakit','Alpha') NOT NULL DEFAULT 'Hadir',
  `keterangan` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi_detail`
--

INSERT INTO `absensi_detail` (`id`, `id_absensi`, `id_siswa`, `status`, `keterangan`, `created_at`, `updated_at`) VALUES
(155, 29, 12, 'Sakit', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(156, 29, 13, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(157, 29, 14, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(158, 29, 15, 'Izin', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(159, 29, 16, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(160, 29, 17, 'Alpha', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(161, 29, 18, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(162, 29, 19, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(163, 29, 20, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(164, 29, 21, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(165, 29, 22, 'Hadir', '', '2025-07-16 02:15:02', '2025-07-16 02:15:02'),
(166, 30, 12, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(167, 30, 13, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(168, 30, 14, 'Sakit', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(169, 30, 15, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(170, 30, 16, 'Izin', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(171, 30, 17, 'Alpha', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(172, 30, 18, 'Alpha', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(173, 30, 19, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(174, 30, 20, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(175, 30, 21, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(176, 30, 22, 'Hadir', '', '2025-07-16 02:17:56', '2025-07-16 02:17:56'),
(177, 31, 12, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(178, 31, 13, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(179, 31, 14, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(180, 31, 15, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(181, 31, 16, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(182, 31, 17, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(183, 31, 18, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(184, 31, 19, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(185, 31, 20, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(186, 31, 21, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33'),
(187, 31, 22, 'Hadir', '', '2025-07-16 02:34:33', '2025-07-16 02:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `aktivitas_absensi`
--

CREATE TABLE `aktivitas_absensi` (
  `id` int(11) NOT NULL,
  `tanggal` date NOT NULL,
  `id_guru` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `keterangan` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `aktivitas_absensi`
--

INSERT INTO `aktivitas_absensi` (`id`, `tanggal`, `id_guru`, `id_kelas`, `keterangan`, `created_at`) VALUES
(11, '2025-07-16', 1, 2, 'Melakukan absensi kelas XPPLG-2 mata pelajaran Bahasa Inggris pertemuan ke-1', '2025-07-16 02:15:02'),
(12, '2025-07-16', 3, 2, 'Melakukan absensi kelas XPPLG-2 mata pelajaran Bahasa Indonesia pertemuan ke-1', '2025-07-16 02:17:56'),
(13, '2025-07-16', 1, 2, 'Melakukan absensi kelas XPPLG-3 mata pelajaran Bahasa Inggris pertemuan ke-2', '2025-07-16 02:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `id` int(11) NOT NULL,
  `nip` varchar(20) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `foto` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id`, `nip`, `nama`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_telp`, `email`, `user_id`, `foto`, `created_at`, `updated_at`) VALUES
(1, '19680830 199702 2 00', 'Dini Dartini, S.Pd.', 'P', 'Bandung', '1988-11-11', 'tasikmalaya', '23456', 'dini@gmail.com', 5, 'default.jpg', '2025-06-27 14:00:51', '2025-07-07 14:11:46'),
(2, '19681009 200312 2 00', 'Hj. Lilis Suryani, S.Pd.', 'P', 'Tasikmalaya', '1990-03-21', 'tasikmalaya', '234567', 'lilis@gmail.com', 6, 'default.jpg', '2025-06-27 14:04:03', '2025-07-07 14:11:55'),
(3, '19640815 198610 2 00', 'Ekawati, S.Pd.', 'P', 'Bandung', '1996-08-19', 'tasikmalaya', '8765', 'eka@gmail.com', 7, 'default.jpg', '2025-06-27 14:05:17', '2025-07-07 14:12:04');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE `jadwal` (
  `id` int(11) NOT NULL,
  `kelas_id` int(11) NOT NULL,
  `mapel_id` int(11) NOT NULL,
  `guru_id` int(11) NOT NULL,
  `hari` varchar(20) NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id`, `kelas_id`, `mapel_id`, `guru_id`, `hari`, `jam_mulai`, `jam_selesai`) VALUES
(21, 1, 2, 1, 'Senin', '08:58:00', '09:58:00'),
(22, 1, 3, 3, 'Senin', '09:10:00', '10:10:00'),
(23, 1, 1, 2, 'Senin', '10:10:00', '11:10:00'),
(24, 3, 2, 1, 'Selasa', '08:11:00', '09:11:00'),
(25, 3, 3, 3, 'Selasa', '09:11:00', '10:11:00'),
(26, 3, 1, 2, 'Selasa', '10:12:00', '11:12:00'),
(27, 2, 2, 1, 'Rabu', '08:12:00', '09:12:00'),
(28, 2, 3, 3, 'Rabu', '09:12:00', '10:13:00'),
(29, 2, 1, 2, 'Rabu', '10:13:00', '11:13:00'),
(30, 2, 2, 1, 'Rabu', '11:33:00', '00:33:00');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id` int(11) NOT NULL,
  `nama_kelas` varchar(20) NOT NULL,
  `tingkat` enum('X','XI','XII') NOT NULL,
  `jurusan` varchar(50) NOT NULL,
  `tahun_ajaran` varchar(10) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL DEFAULT 'Ganjil',
  `wali_kelas` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id`, `nama_kelas`, `tingkat`, `jurusan`, `tahun_ajaran`, `semester`, `wali_kelas`, `created_at`, `updated_at`) VALUES
(1, 'X PPLG-1', 'X', 'PPLG', '2025/2026', 'Ganjil', 3, '2025-06-27 13:40:17', '2025-06-27 14:21:35'),
(2, 'XPPLG-3', 'X', 'PPLG', '2025/2026', 'Ganjil', 2, '2025-06-27 13:40:17', '2025-07-16 02:32:59'),
(3, 'X PPLG-2', 'X', 'PPLG', '2025/2026', 'Ganjil', 1, '2025-06-27 13:40:17', '2025-07-16 02:32:47');

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id` int(11) NOT NULL,
  `kode` varchar(10) NOT NULL,
  `nama_mapel` varchar(100) NOT NULL,
  `jurusan` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id`, `kode`, `nama_mapel`, `jurusan`, `created_at`, `updated_at`) VALUES
(1, 'MTK01', 'Matematika', NULL, '2025-06-12 23:46:54', '2025-06-12 23:46:54'),
(2, 'BIG', 'Bahasa Inggris', NULL, '2025-06-13 00:57:30', '2025-06-13 00:57:30'),
(3, 'BIN', 'Bahasa Indonesia', NULL, '2025-06-13 00:57:30', '2025-06-13 00:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `id` int(11) NOT NULL,
  `nis` varchar(10) NOT NULL,
  `nisn` varchar(10) DEFAULT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `jenis_kelamin` enum('L','P') NOT NULL,
  `tempat_lahir` varchar(50) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(15) DEFAULT NULL,
  `id_kelas` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id`, `nis`, `nisn`, `nama_siswa`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_telp`, `id_kelas`, `created_at`, `updated_at`) VALUES
(1, '232410209', NULL, 'AI RISMAYANI', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(2, '232410210', NULL, 'AINI PUTRI', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(3, '232410211', NULL, 'ANGGI DWI PRATIWI', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(4, '232410212', NULL, 'ARIN', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(5, '232410213', NULL, 'AZMI YAZID MENDRA KURNIA', 'L', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(6, '232410215', NULL, 'DIKRI FAKIH ALKAFI', 'L', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(7, '232410216', NULL, 'DINDA SALSABILA', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(8, '232410217', NULL, 'FAIZ RAYADHYA PERMANA', 'L', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(9, '232410218', NULL, 'FAIZAL ABDUL HAKIM', 'L', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(10, '232410219', NULL, 'FATMA MAULIDA RAHMAWATI', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(11, '232410220', NULL, 'FITRI NURCAHYANI', 'P', NULL, NULL, NULL, NULL, 1, '2025-06-27 13:55:37', '2025-06-27 13:55:37'),
(12, '232410221', NULL, 'IRA APRILIANI', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(13, '232410223', NULL, 'KEYSA NURZABILA', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(14, '232410224', NULL, 'LULU SITI ALIPAH', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(15, '232410225', NULL, 'MILA NIRMALA', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(16, '232410226', NULL, 'NAUFAL TAUFIK ANDRIANA', 'L', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(17, '232410228', NULL, 'NIA KANIA', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(18, '232410229', NULL, 'NIDA KHOIRIYAH', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(19, '232410230', NULL, 'RAFLI TRIYADI', 'L', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(20, '232410231', NULL, 'RAHADU RAHMAN', 'L', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(21, '232410232', NULL, 'RAMA SEPTIA ARDANI', 'L', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(22, '232410233', NULL, 'RANI RAMADANI', 'P', NULL, NULL, NULL, NULL, 2, '2025-06-27 13:55:59', '2025-06-27 13:55:59'),
(23, '232410234', NULL, 'RAYA NUR INTAN', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(24, '232410235', NULL, 'RENA APRI', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(25, '232410236', NULL, 'RINA SILVIANA', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(26, '232410237', NULL, 'RISKA PEBRIANI SAPARIAH', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(27, '232410238', NULL, 'SALMA MAULIDA CAHYANI PUTRI', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(28, '232410239', NULL, 'SHAHARA APRILLIANI', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(29, '232410240', NULL, 'SINDI MEGA AMELIA', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(30, '232410241', NULL, 'SULIS PITRI ANDRIANI', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(31, '232410242', NULL, 'WINDI YULIANI', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(32, '232410243', NULL, 'YOLA DERMAWATI', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14'),
(33, '232410244', NULL, 'YUSRI NURFADILAH', 'P', NULL, NULL, NULL, NULL, 3, '2025-06-27 13:56:14', '2025-06-27 13:56:14');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','guru') NOT NULL,
  `foto_profil` varchar(255) DEFAULT 'default.jpg',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `nama`, `email`, `role`, `foto_profil`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$qU5RwwMT8pdY2CiMZ3HKc.3niBJ551CyO9fdM/Ovem1/aiC01lScW', 'Administrator', 'admin@smkn4tasikmalaya.sch.id', 'admin', 'assets/img/profile/user_1_1751032750.jpg', '2025-06-12 11:19:07', '2025-06-27 13:59:10'),
(5, 'dini@gmail.com', '$2y$10$lJlzgU6QdnUMZWNRC.3Qsenc3EEshVTSQKh0IQYvOzs6KR36oM57q', 'Dini Dartini, S.Pd.', NULL, 'guru', 'assets/img/profile/user_5_1751364000.jpg', '2025-06-27 14:00:51', '2025-07-16 01:57:22'),
(6, 'lilis@gmail.com', '$2y$10$Le9g9uiIz41OD.nmEhtjN.oKDznJ8XN3glI1JUyu4gg5cNA3k/1D2', 'Hj. Lilis Suryani, S.Pd.', NULL, 'guru', 'assets/img/profile/user_6_1751041834.jpg', '2025-06-27 14:04:03', '2025-07-16 01:57:27'),
(7, 'eka@gmail.com', '$2y$10$2O3J8.7Ec8I2FfZhd09XUOiGTQQ5Hyf9sJKVlXkjKPTqUXgPQoYUK', 'Ekawati, S.Pd.', NULL, 'guru', 'assets/img/profile/user_7_1751430956.jpg', '2025-06-27 14:05:17', '2025-07-16 01:57:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_jadwal` (`id_jadwal`);

--
-- Indexes for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_absensi_id_siswa` (`id_absensi`,`id_siswa`),
  ADD KEY `id_siswa` (`id_siswa`);

--
-- Indexes for table `aktivitas_absensi`
--
ALTER TABLE `aktivitas_absensi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_guru` (`id_guru`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD KEY `fk_user_id` (`user_id`);

--
-- Indexes for table `jadwal`
--
ALTER TABLE `jadwal`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kelas_id` (`kelas_id`),
  ADD KEY `mapel_id` (`mapel_id`),
  ADD KEY `guru_id` (`guru_id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nama_kelas` (`nama_kelas`,`tahun_ajaran`),
  ADD KEY `wali_kelas` (`wali_kelas`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_mapel` (`kode`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `id_kelas` (`id_kelas`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `aktivitas_absensi`
--
ALTER TABLE `aktivitas_absensi`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `jadwal`
--
ALTER TABLE `jadwal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `mapel`
--
ALTER TABLE `mapel`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `absensi_detail`
--
ALTER TABLE `absensi_detail`
  ADD CONSTRAINT `absensi_detail_ibfk_1` FOREIGN KEY (`id_absensi`) REFERENCES `absensi` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `absensi_detail_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `aktivitas_absensi`
--
ALTER TABLE `aktivitas_absensi`
  ADD CONSTRAINT `aktivitas_absensi_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aktivitas_absensi_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`wali_kelas`) REFERENCES `guru` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `siswa`
--
ALTER TABLE `siswa`
  ADD CONSTRAINT `siswa_ibfk_1` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
