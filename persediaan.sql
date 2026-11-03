-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 03, 2026 at 01:19 PM
-- Server version: 5.7.39
-- PHP Version: 8.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `persediaan`
--

-- --------------------------------------------------------

--
-- Table structure for table `bidangs`
--

CREATE TABLE `bidangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `nama_bidang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bidangs`
--

INSERT INTO `bidangs` (`id`, `team_id`, `nama_bidang`, `created_at`, `updated_at`) VALUES
(1, 1, 'Sekretariat', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(2, 1, 'Bidang TI', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(3, 1, 'Bidang Persandian', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(4, 1, 'Bidang Statistik', '2026-11-03 06:19:18', '2026-11-03 06:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `filament_exceptions_table`
--

CREATE TABLE `filament_exceptions_table` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line` int(11) NOT NULL,
  `trace` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `query` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `cookies` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `headers` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kode_rekenings`
--

CREATE TABLE `kode_rekenings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `akun` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelompok` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `objek` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rincian_objek` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_rincian_objek` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sub_sub_rincian_objek` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kode_lengkap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kode_rekenings`
--

INSERT INTO `kode_rekenings` (`id`, `akun`, `kelompok`, `jenis`, `objek`, `rincian_objek`, `sub_rincian_objek`, `sub_sub_rincian_objek`, `kode_lengkap`, `uraian`, `created_at`, `updated_at`) VALUES
(1, '1', '1', '7', '01', '01', '01', '000', '1.1.7.01.01.01.000', 'ALAT TULIS KANTOR', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(2, '1', '1', '7', '01', '02', '01', '000', '1.1.7.01.02.01.000', 'PERALATAN KOMPUTER', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(3, '1', '1', '7', '01', '03', '01', '000', '1.1.7.01.03.01.000', 'PERLENGKAPAN KANTOR', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(4, '1', '1', '7', '01', '04', '01', '000', '1.1.7.01.04.01.000', 'BAHAN CETAKAN', '2026-11-03 06:19:19', '2026-11-03 06:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `laporan_persediaans`
--

CREATE TABLE `laporan_persediaans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `bidang_id` bigint(20) UNSIGNED NOT NULL,
  `master_barang_id` bigint(20) UNSIGNED NOT NULL,
  `bulan` int(11) NOT NULL,
  `tahun` int(11) NOT NULL,
  `saldo_awal` int(11) NOT NULL DEFAULT '0',
  `jumlah_masuk` int(11) NOT NULL DEFAULT '0',
  `nilai_masuk` decimal(15,0) NOT NULL DEFAULT '0',
  `jumlah_keluar` int(11) NOT NULL DEFAULT '0',
  `nilai_keluar` decimal(15,0) NOT NULL DEFAULT '0',
  `jumlah_expired` int(11) NOT NULL DEFAULT '0',
  `nilai_expired` decimal(15,0) NOT NULL DEFAULT '0',
  `saldo_akhir` int(11) NOT NULL DEFAULT '0',
  `nilai_akhir` decimal(15,0) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `master_barangs`
--

CREATE TABLE `master_barangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `kode_rekening_id` bigint(20) UNSIGNED NOT NULL,
  `kode_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uraian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_barang` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan_id` bigint(20) UNSIGNED DEFAULT NULL,
  `harga_satuan` decimal(15,0) NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `master_barangs`
--

INSERT INTO `master_barangs` (`id`, `kode_rekening_id`, `kode_barang`, `uraian`, `nama_barang`, `satuan_id`, `harga_satuan`, `keterangan`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, '1.1.7.01.01.01.001', 'ALAT TULIS KANTOR', 'Pena Signo Uniball', 2, '23000', 'Pena Signo warna hitam', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(2, 1, '1.1.7.01.01.01.002', 'ALAT TULIS KANTOR', 'Kertas HVS A4 80 gram', 7, '55000', 'Kertas HVS A4 merk Sinar Dunia', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(3, 1, '1.1.7.01.01.01.003', 'ALAT TULIS KANTOR', 'Spidol Whiteboard', 5, '75000', 'Spidol whiteboard merk Snowman isi 12', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(4, 1, '1.1.7.01.01.01.004', 'ALAT TULIS KANTOR', 'Tinta Printer', 10, '85000', 'Tinta printer Epson warna hitam', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(5, 2, '1.1.7.01.02.01.001', 'PERALATAN KOMPUTER', 'Flashdisk 32GB', 2, '95000', 'Flashdisk merk Sandisk', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(6, 2, '1.1.7.01.02.01.002', 'PERALATAN KOMPUTER', 'Mouse Wireless', 3, '150000', 'Mouse wireless merk Logitech', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(7, 2, '1.1.7.01.02.01.003', 'PERALATAN KOMPUTER', 'Keyboard Mechanical', 3, '550000', 'Keyboard mechanical gaming', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(8, 2, '1.1.7.01.02.01.004', 'PERALATAN KOMPUTER', 'Harddisk External 1TB', 3, '850000', 'Harddisk external merk WD 1TB', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(9, 3, '1.1.7.01.03.01.001', 'PERLENGKAPAN KANTOR', 'Kursi Kerja', 3, '750000', 'Kursi kerja ergonomis', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(10, 3, '1.1.7.01.03.01.002', 'PERLENGKAPAN KANTOR', 'Lemari Arsip', 3, '1500000', 'Lemari arsip 2 pintu', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(11, 3, '1.1.7.01.03.01.003', 'PERLENGKAPAN KANTOR', 'Papan Tulis', 3, '450000', 'Papan tulis whiteboard 120x80cm', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(12, 3, '1.1.7.01.03.01.004', 'PERLENGKAPAN KANTOR', 'Dispenser Air', 3, '350000', 'Dispenser air panas dan dingin', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(13, 4, '1.1.7.01.04.01.001', 'BAHAN CETAKAN', 'Kop Surat', 7, '150000', 'Kop surat dinas', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(14, 4, '1.1.7.01.04.01.002', 'BAHAN CETAKAN', 'Amplop Dinas', 6, '75000', 'Amplop dinas ukuran A4', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(15, 4, '1.1.7.01.04.01.003', 'BAHAN CETAKAN', 'Kartu Nama', 6, '100000', 'Kartu nama dinas isi 100', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL),
(16, 4, '1.1.7.01.04.01.004', 'BAHAN CETAKAN', 'Stempel Dinas', 2, '120000', 'Stempel dinas otomatis', '2026-11-03 06:19:19', '2026-11-03 06:19:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_teams_table', 1),
(2, '2014_10_12_000001_create_bidangs_table', 1),
(3, '2014_10_12_000003_create_users_table', 1),
(4, '2014_10_12_100000_create_password_reset_tokens_table', 1),
(5, '2019_08_19_000000_create_failed_jobs_table', 1),
(6, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(7, '2024_01_24_073231_create_filament_exceptions_table', 1),
(8, '2024_01_24_073705_create_permission_tables', 1),
(9, '2024_02_04_131050_add_themes_settings_to_users_table', 1),
(10, '2024_02_04_135511_create_route_statistics_table', 1),
(11, '2025_01_24_070454_create_sessions_table', 1),
(12, '2025_01_24_080425_create_satuans_table', 1),
(13, '2025_01_24_080432_create_kode_rekenings_table', 1),
(14, '2025_01_24_082438_create_master_barangs_table', 1),
(15, '2025_01_24_082815_create_stok_barangs_table', 1),
(16, '2025_01_24_083241_create_transaksi_barangs_table', 1),
(17, '2025_01_25_201821_create_laporan_persediaans_table', 1),
(18, '2025_02_07_020152_add_custom_fields_to_users_table', 1),
(19, '2025_02_07_020153_add_avatar_url_to_users_table', 1),
(20, '2025_04_22_081125_create_pengaturans_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(2, 'App\\Models\\User', 2),
(3, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengaturans`
--

CREATE TABLE `pengaturans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `nama_instansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_kepala` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip_kepala` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_bendahara_pengelola_barang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip_bendahara_pengelola_barang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pengaturans`
--

INSERT INTO `pengaturans` (`id`, `team_id`, `nama_instansi`, `nama_kepala`, `jabatan`, `nip_kepala`, `nama_bendahara_pengelola_barang`, `nip_bendahara_pengelola_barang`, `created_at`, `updated_at`) VALUES
(1, 1, 'Dinas Komunikasi dan Informatika', 'David Rinaldo, SSTP', 'Kepala Dinas', '198509282003121002', 'Muharis', '197809252005011005', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(2, 4, 'Dinas Kesehatan', 'dr. Hendra Wijaya, M.Kes.', 'Kepala Dinas', '196812101995031002', 'Dewi Purnama, Amd.Kep.', '198710052012012004', '2026-11-03 06:19:18', '2026-11-03 06:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'view_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(2, 'view_any_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(3, 'create_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(4, 'update_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(5, 'restore_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(6, 'restore_any_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(7, 'replicate_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(8, 'reorder_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(9, 'delete_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(10, 'delete_any_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(11, 'force_delete_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(12, 'force_delete_any_jabatan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(13, 'view_role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(14, 'view_any_role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(15, 'create_role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(16, 'update_role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(17, 'delete_role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(18, 'delete_any_role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(19, 'view_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(20, 'view_any_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(21, 'create_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(22, 'update_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(23, 'restore_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(24, 'restore_any_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(25, 'replicate_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(26, 'reorder_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(27, 'delete_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(28, 'delete_any_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(29, 'force_delete_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(30, 'force_delete_any_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(31, 'view_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(32, 'view_any_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(33, 'create_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(34, 'update_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(35, 'restore_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(36, 'restore_any_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(37, 'replicate_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(38, 'reorder_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(39, 'delete_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(40, 'delete_any_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(41, 'force_delete_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(42, 'force_delete_any_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(43, 'view_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(44, 'view_any_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(45, 'create_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(46, 'update_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(47, 'restore_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(48, 'restore_any_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(49, 'replicate_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(50, 'reorder_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(51, 'delete_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(52, 'delete_any_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(53, 'force_delete_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(54, 'force_delete_any_kode', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(55, 'view_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(56, 'view_any_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(57, 'create_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(58, 'update_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(59, 'restore_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(60, 'restore_any_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(61, 'replicate_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(62, 'reorder_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(63, 'delete_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(64, 'delete_any_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(65, 'force_delete_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(66, 'force_delete_any_laporan::persediaan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(67, 'view_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(68, 'view_any_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(69, 'create_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(70, 'update_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(71, 'restore_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(72, 'restore_any_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(73, 'replicate_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(74, 'reorder_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(75, 'delete_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(76, 'delete_any_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(77, 'force_delete_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(78, 'force_delete_any_master::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(79, 'view_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(80, 'view_any_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(81, 'create_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(82, 'update_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(83, 'restore_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(84, 'restore_any_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(85, 'replicate_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(86, 'reorder_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(87, 'delete_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(88, 'delete_any_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(89, 'force_delete_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(90, 'force_delete_any_satuan', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(91, 'view_shield::role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(92, 'view_any_shield::role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(93, 'create_shield::role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(94, 'update_shield::role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(95, 'delete_shield::role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(96, 'delete_any_shield::role', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(97, 'view_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(98, 'view_any_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(99, 'create_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(100, 'update_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(101, 'restore_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(102, 'restore_any_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(103, 'replicate_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(104, 'reorder_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(105, 'delete_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(106, 'delete_any_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(107, 'force_delete_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(108, 'force_delete_any_stok::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(109, 'view_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(110, 'view_any_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(111, 'create_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(112, 'update_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(113, 'restore_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(114, 'restore_any_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(115, 'replicate_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(116, 'reorder_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(117, 'delete_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(118, 'delete_any_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(119, 'force_delete_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(120, 'force_delete_any_team', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(121, 'view_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(122, 'view_any_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(123, 'create_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(124, 'update_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(125, 'restore_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(126, 'restore_any_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(127, 'replicate_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(128, 'reorder_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(129, 'delete_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(130, 'delete_any_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(131, 'force_delete_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(132, 'force_delete_any_transaksi::barang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(133, 'page_Themes', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(134, 'view_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(135, 'view_any_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(136, 'create_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(137, 'update_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(138, 'restore_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(139, 'restore_any_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(140, 'replicate_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(141, 'reorder_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(142, 'delete_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(143, 'delete_any_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(144, 'force_delete_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(145, 'force_delete_any_identitas', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(146, 'view_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(147, 'view_any_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(148, 'create_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(149, 'update_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(150, 'restore_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(151, 'restore_any_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(152, 'replicate_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(153, 'reorder_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(154, 'delete_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(155, 'delete_any_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(156, 'force_delete_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(157, 'force_delete_any_instansi', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(158, 'view_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(159, 'view_any_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(160, 'create_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(161, 'update_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(162, 'restore_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(163, 'restore_any_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(164, 'replicate_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(165, 'reorder_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(166, 'delete_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(167, 'delete_any_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(168, 'force_delete_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(169, 'force_delete_any_kode::surat', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(170, 'view_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(171, 'view_any_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(172, 'create_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(173, 'update_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(174, 'restore_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(175, 'restore_any_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(176, 'replicate_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(177, 'reorder_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(178, 'delete_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(179, 'delete_any_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(180, 'force_delete_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(181, 'force_delete_any_pegawai', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(182, 'view_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(183, 'view_any_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(184, 'create_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(185, 'update_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(186, 'restore_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(187, 'restore_any_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(188, 'replicate_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(189, 'reorder_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(190, 'delete_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(191, 'delete_any_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(192, 'force_delete_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(193, 'force_delete_any_sk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(194, 'view_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(195, 'view_any_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(196, 'create_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(197, 'update_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(198, 'restore_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(199, 'restore_any_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(200, 'replicate_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(201, 'reorder_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(202, 'delete_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(203, 'delete_any_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(204, 'force_delete_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(205, 'force_delete_any_spt', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(206, 'view_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(207, 'view_any_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(208, 'create_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(209, 'update_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(210, 'restore_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(211, 'restore_any_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(212, 'replicate_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(213, 'reorder_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(214, 'delete_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(215, 'delete_any_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(216, 'force_delete_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(217, 'force_delete_any_surat::keluar', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(218, 'view_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(219, 'view_any_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(220, 'create_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(221, 'update_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(222, 'restore_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(223, 'restore_any_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(224, 'replicate_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(225, 'reorder_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(226, 'delete_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(227, 'delete_any_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(228, 'force_delete_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(229, 'force_delete_any_surat::masuk', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(230, 'view_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(231, 'view_any_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(232, 'create_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(233, 'update_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(234, 'restore_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(235, 'restore_any_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(236, 'replicate_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(237, 'reorder_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(238, 'delete_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(239, 'delete_any_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(240, 'force_delete_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(241, 'force_delete_any_unit', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'super_admin', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(2, 'admin_opd', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(3, 'user_bidang', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(4, 'panel_user', 'web', '2026-11-03 06:19:18', '2026-11-03 06:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(112, 1),
(113, 1),
(114, 1),
(115, 1),
(116, 1),
(117, 1),
(118, 1),
(119, 1),
(120, 1),
(121, 1),
(122, 1),
(123, 1),
(124, 1),
(125, 1),
(126, 1),
(127, 1),
(128, 1),
(129, 1),
(130, 1),
(131, 1),
(132, 1),
(133, 1),
(134, 1),
(135, 1),
(136, 1),
(137, 1),
(138, 1),
(139, 1),
(140, 1),
(141, 1),
(142, 1),
(143, 1),
(144, 1),
(145, 1),
(146, 1),
(147, 1),
(148, 1),
(149, 1),
(150, 1),
(151, 1),
(152, 1),
(153, 1),
(154, 1),
(155, 1),
(156, 1),
(157, 1),
(158, 1),
(159, 1),
(160, 1),
(161, 1),
(162, 1),
(163, 1),
(164, 1),
(165, 1),
(166, 1),
(167, 1),
(168, 1),
(169, 1),
(170, 1),
(171, 1),
(172, 1),
(173, 1),
(174, 1),
(175, 1),
(176, 1),
(177, 1),
(178, 1),
(179, 1),
(180, 1),
(181, 1),
(182, 1),
(183, 1),
(184, 1),
(185, 1),
(186, 1),
(187, 1),
(188, 1),
(189, 1),
(190, 1),
(191, 1),
(192, 1),
(193, 1),
(194, 1),
(195, 1),
(196, 1),
(197, 1),
(198, 1),
(199, 1),
(200, 1),
(201, 1),
(202, 1),
(203, 1),
(204, 1),
(205, 1),
(206, 1),
(207, 1),
(208, 1),
(209, 1),
(210, 1),
(211, 1),
(212, 1),
(213, 1),
(214, 1),
(215, 1),
(216, 1),
(217, 1),
(218, 1),
(219, 1),
(220, 1),
(221, 1),
(222, 1),
(223, 1),
(224, 1),
(225, 1),
(226, 1),
(227, 1),
(228, 1),
(229, 1),
(230, 1),
(231, 1),
(232, 1),
(233, 1),
(234, 1),
(235, 1),
(236, 1),
(237, 1),
(238, 1),
(239, 1),
(240, 1),
(241, 1),
(1, 2),
(2, 2),
(3, 2),
(4, 2),
(5, 2),
(6, 2),
(7, 2),
(8, 2),
(9, 2),
(10, 2),
(11, 2),
(12, 2),
(19, 2),
(20, 2),
(21, 2),
(22, 2),
(23, 2),
(24, 2),
(25, 2),
(26, 2),
(27, 2),
(28, 2),
(29, 2),
(30, 2),
(31, 2),
(32, 2),
(33, 2),
(34, 2),
(35, 2),
(36, 2),
(37, 2),
(38, 2),
(39, 2),
(40, 2),
(41, 2),
(42, 2),
(43, 2),
(44, 2),
(45, 2),
(46, 2),
(47, 2),
(48, 2),
(49, 2),
(50, 2),
(51, 2),
(52, 2),
(53, 2),
(54, 2),
(55, 2),
(56, 2),
(57, 2),
(58, 2),
(59, 2),
(60, 2),
(61, 2),
(62, 2),
(63, 2),
(64, 2),
(65, 2),
(66, 2),
(67, 2),
(68, 2),
(69, 2),
(70, 2),
(71, 2),
(72, 2),
(73, 2),
(74, 2),
(75, 2),
(76, 2),
(77, 2),
(78, 2),
(79, 2),
(80, 2),
(81, 2),
(82, 2),
(83, 2),
(84, 2),
(85, 2),
(86, 2),
(87, 2),
(88, 2),
(89, 2),
(90, 2),
(97, 2),
(98, 2),
(99, 2),
(100, 2),
(101, 2),
(102, 2),
(103, 2),
(104, 2),
(105, 2),
(106, 2),
(107, 2),
(108, 2),
(109, 2),
(110, 2),
(111, 2),
(112, 2),
(113, 2),
(114, 2),
(115, 2),
(116, 2),
(117, 2),
(118, 2),
(119, 2),
(120, 2),
(121, 2),
(122, 2),
(123, 2),
(124, 2),
(125, 2),
(126, 2),
(127, 2),
(128, 2),
(129, 2),
(130, 2),
(131, 2),
(132, 2),
(133, 2),
(134, 2),
(135, 2),
(136, 2),
(137, 2),
(138, 2),
(139, 2),
(140, 2),
(141, 2),
(142, 2),
(143, 2),
(144, 2),
(145, 2),
(146, 2),
(147, 2),
(148, 2),
(149, 2),
(150, 2),
(151, 2),
(152, 2),
(153, 2),
(154, 2),
(155, 2),
(156, 2),
(157, 2),
(158, 2),
(159, 2),
(160, 2),
(161, 2),
(162, 2),
(163, 2),
(164, 2),
(165, 2),
(166, 2),
(167, 2),
(168, 2),
(169, 2),
(170, 2),
(171, 2),
(172, 2),
(173, 2),
(174, 2),
(175, 2),
(176, 2),
(177, 2),
(178, 2),
(179, 2),
(180, 2),
(181, 2),
(182, 2),
(183, 2),
(184, 2),
(185, 2),
(186, 2),
(187, 2),
(188, 2),
(189, 2),
(190, 2),
(191, 2),
(192, 2),
(193, 2),
(194, 2),
(195, 2),
(196, 2),
(197, 2),
(198, 2),
(199, 2),
(200, 2),
(201, 2),
(202, 2),
(203, 2),
(204, 2),
(205, 2),
(206, 2),
(207, 2),
(208, 2),
(209, 2),
(210, 2),
(211, 2),
(212, 2),
(213, 2),
(214, 2),
(215, 2),
(216, 2),
(217, 2),
(218, 2),
(219, 2),
(220, 2),
(221, 2),
(222, 2),
(223, 2),
(224, 2),
(225, 2),
(226, 2),
(227, 2),
(228, 2),
(229, 2),
(230, 2),
(231, 2),
(232, 2),
(233, 2),
(234, 2),
(235, 2),
(236, 2),
(237, 2),
(238, 2),
(239, 2),
(240, 2),
(241, 2),
(1, 3),
(2, 3),
(3, 3),
(4, 3),
(5, 3),
(6, 3),
(7, 3),
(8, 3),
(9, 3),
(10, 3),
(11, 3),
(12, 3),
(19, 3),
(20, 3),
(21, 3),
(22, 3),
(23, 3),
(24, 3),
(25, 3),
(26, 3),
(27, 3),
(28, 3),
(29, 3),
(30, 3),
(31, 3),
(32, 3),
(33, 3),
(34, 3),
(35, 3),
(36, 3),
(37, 3),
(38, 3),
(39, 3),
(40, 3),
(41, 3),
(42, 3),
(43, 3),
(44, 3),
(45, 3),
(46, 3),
(47, 3),
(48, 3),
(49, 3),
(50, 3),
(51, 3),
(52, 3),
(53, 3),
(54, 3),
(55, 3),
(56, 3),
(57, 3),
(58, 3),
(59, 3),
(60, 3),
(61, 3),
(62, 3),
(63, 3),
(64, 3),
(65, 3),
(66, 3),
(67, 3),
(68, 3),
(69, 3),
(70, 3),
(71, 3),
(72, 3),
(73, 3),
(74, 3),
(75, 3),
(76, 3),
(77, 3),
(78, 3),
(79, 3),
(80, 3),
(81, 3),
(82, 3),
(83, 3),
(84, 3),
(85, 3),
(86, 3),
(87, 3),
(88, 3),
(89, 3),
(90, 3),
(97, 3),
(98, 3),
(99, 3),
(100, 3),
(101, 3),
(102, 3),
(103, 3),
(104, 3),
(105, 3),
(106, 3),
(107, 3),
(108, 3),
(109, 3),
(110, 3),
(111, 3),
(112, 3),
(113, 3),
(114, 3),
(115, 3),
(116, 3),
(117, 3),
(118, 3),
(119, 3),
(120, 3),
(121, 3),
(122, 3),
(123, 3),
(124, 3),
(125, 3),
(126, 3),
(127, 3),
(128, 3),
(129, 3),
(130, 3),
(131, 3),
(132, 3),
(133, 3),
(134, 3),
(135, 3),
(136, 3),
(137, 3),
(138, 3),
(139, 3),
(140, 3),
(141, 3),
(142, 3),
(143, 3),
(144, 3),
(145, 3),
(146, 3),
(147, 3),
(148, 3),
(149, 3),
(150, 3),
(151, 3),
(152, 3),
(153, 3),
(154, 3),
(155, 3),
(156, 3),
(157, 3),
(158, 3),
(159, 3),
(160, 3),
(161, 3),
(162, 3),
(163, 3),
(164, 3),
(165, 3),
(166, 3),
(167, 3),
(168, 3),
(169, 3),
(170, 3),
(171, 3),
(172, 3),
(173, 3),
(174, 3),
(175, 3),
(176, 3),
(177, 3),
(178, 3),
(179, 3),
(180, 3),
(181, 3),
(182, 3),
(183, 3),
(184, 3),
(185, 3),
(186, 3),
(187, 3),
(188, 3),
(189, 3),
(190, 3),
(191, 3),
(192, 3),
(193, 3),
(194, 3),
(195, 3),
(196, 3),
(197, 3),
(198, 3),
(199, 3),
(200, 3),
(201, 3),
(202, 3),
(203, 3),
(204, 3),
(205, 3),
(206, 3),
(207, 3),
(208, 3),
(209, 3),
(210, 3),
(211, 3),
(212, 3),
(213, 3),
(214, 3),
(215, 3),
(216, 3),
(217, 3),
(218, 3),
(219, 3),
(220, 3),
(221, 3),
(222, 3),
(223, 3),
(224, 3),
(225, 3),
(226, 3),
(227, 3),
(228, 3),
(229, 3),
(230, 3),
(231, 3),
(232, 3),
(233, 3),
(234, 3),
(235, 3),
(236, 3),
(237, 3),
(238, 3),
(239, 3),
(240, 3),
(241, 3);

-- --------------------------------------------------------

--
-- Table structure for table `route_statistics`
--

CREATE TABLE `route_statistics` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `route` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime NOT NULL,
  `counter` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `satuans`
--

CREATE TABLE `satuans` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `satuans`
--

INSERT INTO `satuans` (`id`, `team_id`, `nama`, `created_at`, `updated_at`) VALUES
(1, NULL, 'Lusin', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(2, NULL, 'Buah', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(3, NULL, 'Unit', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(4, NULL, 'Lembar', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(5, NULL, 'Pack', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(6, NULL, 'Box', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(7, NULL, 'Rim', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(8, NULL, 'Set', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(9, NULL, 'Roll', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(10, NULL, 'Botol', '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(11, NULL, 'Pcs', '2026-11-03 06:19:19', '2026-11-03 06:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stok_barangs`
--

CREATE TABLE `stok_barangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `bidang_id` bigint(20) UNSIGNED NOT NULL,
  `master_barang_id` bigint(20) UNSIGNED NOT NULL,
  `stok_tersedia` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `slug`, `created_at`, `updated_at`) VALUES
(1, 'Dinas Komunikasi dan Informatika', 'dinas-komunikasi-dan-informatika', '2026-11-03 06:19:17', '2026-11-03 06:19:17'),
(2, 'Badan Perencanaan,Penelitian dan Pengembangan Daerah', 'badan-perencanaan-penelitian-dan-pengembangan-daerah', '2026-11-03 06:19:17', '2026-11-03 06:19:17'),
(3, 'Dinas Kependudukan dan Pencatatan Sipil', 'dinas-kependudukan-dan-pencatatan-sipil', '2026-11-03 06:19:17', '2026-11-03 06:19:17'),
(4, 'Dinas Kesehatan', 'dinas-kesehatan', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(5, 'Dinas Ketenagakerjaan dan Transmigrasi', 'dinas-ke tenagakerjaan-dan-transmigrasi', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(6, 'Dinas Pariwisata,Pemuda dan Olahraga', 'dinas-pariwisata-pemuda-dan-olahraga', '2026-11-03 06:19:18', '2026-11-03 06:19:18'),
(7, 'Dinas Pekerjaan Umum dan Penataan Ruang', 'dinas-pekerjaan-umum-dan-penataan-ruang', '2026-11-03 06:19:18', '2026-11-03 06:19:18');

-- --------------------------------------------------------

--
-- Table structure for table `team_user`
--

CREATE TABLE `team_user` (
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_user`
--

INSERT INTO `team_user` (`team_id`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(1, 2, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(1, 3, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(1, 4, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(2, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(3, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(4, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(5, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(6, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19'),
(7, 1, '2026-11-03 06:19:19', '2026-11-03 06:19:19');

-- --------------------------------------------------------

--
-- Table structure for table `transaksi_barangs`
--

CREATE TABLE `transaksi_barangs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `bidang_id` bigint(20) UNSIGNED NOT NULL,
  `master_barang_id` bigint(20) UNSIGNED NOT NULL,
  `jenis_transaksi` enum('masuk','keluar') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int(11) NOT NULL,
  `harga_satuan` decimal(15,0) NOT NULL,
  `nilai_barang` decimal(15,0) NOT NULL,
  `tanggal_transaksi` date NOT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bidang_id` bigint(20) UNSIGNED DEFAULT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'default',
  `theme_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `custom_fields` json DEFAULT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `bidang_id`, `jabatan`, `nip`, `is_active`, `remember_token`, `created_at`, `updated_at`, `theme`, `theme_color`, `custom_fields`, `avatar_url`) VALUES
(1, 'DON BORLAND', 'superadmin@gmail.com', NULL, '$2y$12$ScwPhw9ONokBMQuxiNLqbOraPIyAvesgkd0S/GBEi19VWwOZaMXaq', 2, NULL, NULL, 1, NULL, '2026-11-03 06:19:19', '2026-11-03 06:19:19', 'default', NULL, NULL, NULL),
(2, 'Muharis', 'adminkominfo@gmail.com', NULL, '$2y$12$3b9EATluhXCL4A0Vy0vhne401Z6n24QFFL0MGQeTzUZsPxMef7J6q', 1, NULL, '123453453535', 1, NULL, '2026-11-03 06:19:19', '2026-11-03 06:19:19', 'default', NULL, NULL, NULL),
(3, 'User Bidang TI', 'userbidangti@gmail.com', NULL, '$2y$12$lBczT2ahMsDjfL3jZ0.QGO6cLnmXW/VY9iMoA0MMucLBrUoaQpdm6', 2, NULL, '12345634343', 1, NULL, '2026-11-03 06:19:19', '2026-11-03 06:19:19', 'default', NULL, NULL, NULL),
(4, 'User Bidang Sekretariat', 'userbidangsekretariat@gmail.com', NULL, '$2y$12$E/otoBdGkQkTMh76pQPB6OQZjSGZnkOAofyORRpQJ8koHtsGcye6u', 1, NULL, NULL, 1, NULL, '2026-11-03 06:19:19', '2026-11-03 06:19:19', 'default', NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bidangs`
--
ALTER TABLE `bidangs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `bidangs_team_id_nama_bidang_unique` (`team_id`,`nama_bidang`),
  ADD KEY `bidangs_team_id_nama_bidang_index` (`team_id`,`nama_bidang`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `filament_exceptions_table`
--
ALTER TABLE `filament_exceptions_table`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kode_rekenings`
--
ALTER TABLE `kode_rekenings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `kode_rekenings_akun_index` (`akun`),
  ADD KEY `kode_rekenings_kelompok_index` (`kelompok`),
  ADD KEY `kode_rekenings_jenis_index` (`jenis`),
  ADD KEY `kode_rekenings_objek_index` (`objek`),
  ADD KEY `kode_rekenings_rincian_objek_index` (`rincian_objek`),
  ADD KEY `kode_rekenings_sub_rincian_objek_index` (`sub_rincian_objek`),
  ADD KEY `kode_rekenings_sub_sub_rincian_objek_index` (`sub_sub_rincian_objek`),
  ADD KEY `kode_rekenings_kode_lengkap_index` (`kode_lengkap`),
  ADD KEY `kode_rekenings_uraian_index` (`uraian`);

--
-- Indexes for table `laporan_persediaans`
--
ALTER TABLE `laporan_persediaans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lap_idx` (`team_id`,`master_barang_id`,`bulan`,`tahun`),
  ADD KEY `laporan_persediaans_bidang_id_foreign` (`bidang_id`),
  ADD KEY `laporan_persediaans_master_barang_id_foreign` (`master_barang_id`);

--
-- Indexes for table `master_barangs`
--
ALTER TABLE `master_barangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `master_barangs_satuan_id_foreign` (`satuan_id`),
  ADD KEY `master_barangs_kode_rekening_id_nama_barang_index` (`kode_rekening_id`,`nama_barang`),
  ADD KEY `master_barangs_nama_barang_kode_barang_index` (`nama_barang`,`kode_barang`),
  ADD KEY `master_barangs_kode_barang_index` (`kode_barang`),
  ADD KEY `master_barangs_uraian_index` (`uraian`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengaturans`
--
ALTER TABLE `pengaturans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pengaturans_team_id_unique` (`team_id`),
  ADD KEY `pengaturans_team_id_index` (`team_id`),
  ADD KEY `pengaturans_nama_instansi_index` (`nama_instansi`),
  ADD KEY `pengaturans_nama_kepala_index` (`nama_kepala`),
  ADD KEY `pengaturans_jabatan_index` (`jabatan`),
  ADD KEY `pengaturans_nip_kepala_index` (`nip_kepala`),
  ADD KEY `pengaturans_nama_bendahara_pengelola_barang_index` (`nama_bendahara_pengelola_barang`),
  ADD KEY `pengaturans_nip_bendahara_pengelola_barang_index` (`nip_bendahara_pengelola_barang`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `route_statistics`
--
ALTER TABLE `route_statistics`
  ADD PRIMARY KEY (`id`),
  ADD KEY `route_statistics_date_index` (`date`),
  ADD KEY `route_statistics_user_id_date_route_method_index` (`user_id`,`date`,`route`,`method`),
  ADD KEY `route_statistics_team_id_date_route_method_index` (`team_id`,`date`,`route`,`method`),
  ADD KEY `route_statistics_route_method_date_index` (`route`,`method`,`date`);

--
-- Indexes for table `satuans`
--
ALTER TABLE `satuans`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `satuans_nama_unique` (`nama`),
  ADD KEY `satuans_team_id_foreign` (`team_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `stok_barangs`
--
ALTER TABLE `stok_barangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `stok_barangs_team_id_index` (`team_id`),
  ADD KEY `stok_barangs_bidang_id_index` (`bidang_id`),
  ADD KEY `stok_barangs_master_barang_id_index` (`master_barang_id`),
  ADD KEY `stok_barangs_team_id_bidang_id_index` (`team_id`,`bidang_id`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `team_user`
--
ALTER TABLE `team_user`
  ADD UNIQUE KEY `team_user_team_id_user_id_unique` (`team_id`,`user_id`),
  ADD KEY `team_user_team_id_index` (`team_id`),
  ADD KEY `team_user_user_id_index` (`user_id`);

--
-- Indexes for table `transaksi_barangs`
--
ALTER TABLE `transaksi_barangs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaksi_barangs_bidang_id_foreign` (`bidang_id`),
  ADD KEY `transaksi_barangs_master_barang_id_foreign` (`master_barang_id`),
  ADD KEY `transaksi_barangs_created_by_foreign` (`created_by`),
  ADD KEY `transaksi_barangs_updated_by_foreign` (`updated_by`),
  ADD KEY `trx_tim_brg_tgl_idx` (`team_id`,`master_barang_id`,`tanggal_transaksi`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_nip_unique` (`nip`),
  ADD KEY `users_bidang_id_is_active_index` (`bidang_id`,`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bidangs`
--
ALTER TABLE `bidangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `filament_exceptions_table`
--
ALTER TABLE `filament_exceptions_table`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kode_rekenings`
--
ALTER TABLE `kode_rekenings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `laporan_persediaans`
--
ALTER TABLE `laporan_persediaans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `master_barangs`
--
ALTER TABLE `master_barangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `pengaturans`
--
ALTER TABLE `pengaturans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `route_statistics`
--
ALTER TABLE `route_statistics`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `satuans`
--
ALTER TABLE `satuans`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `stok_barangs`
--
ALTER TABLE `stok_barangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `transaksi_barangs`
--
ALTER TABLE `transaksi_barangs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bidangs`
--
ALTER TABLE `bidangs`
  ADD CONSTRAINT `bidangs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `laporan_persediaans`
--
ALTER TABLE `laporan_persediaans`
  ADD CONSTRAINT `laporan_persediaans_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidangs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `laporan_persediaans_master_barang_id_foreign` FOREIGN KEY (`master_barang_id`) REFERENCES `master_barangs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `laporan_persediaans_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `master_barangs`
--
ALTER TABLE `master_barangs`
  ADD CONSTRAINT `master_barangs_kode_rekening_id_foreign` FOREIGN KEY (`kode_rekening_id`) REFERENCES `kode_rekenings` (`id`),
  ADD CONSTRAINT `master_barangs_satuan_id_foreign` FOREIGN KEY (`satuan_id`) REFERENCES `satuans` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pengaturans`
--
ALTER TABLE `pengaturans`
  ADD CONSTRAINT `pengaturans_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `route_statistics`
--
ALTER TABLE `route_statistics`
  ADD CONSTRAINT `route_statistics_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `satuans`
--
ALTER TABLE `satuans`
  ADD CONSTRAINT `satuans_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stok_barangs`
--
ALTER TABLE `stok_barangs`
  ADD CONSTRAINT `stok_barangs_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidangs` (`id`),
  ADD CONSTRAINT `stok_barangs_master_barang_id_foreign` FOREIGN KEY (`master_barang_id`) REFERENCES `master_barangs` (`id`),
  ADD CONSTRAINT `stok_barangs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `transaksi_barangs`
--
ALTER TABLE `transaksi_barangs`
  ADD CONSTRAINT `transaksi_barangs_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidangs` (`id`),
  ADD CONSTRAINT `transaksi_barangs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `transaksi_barangs_master_barang_id_foreign` FOREIGN KEY (`master_barang_id`) REFERENCES `master_barangs` (`id`),
  ADD CONSTRAINT `transaksi_barangs_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `transaksi_barangs_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_bidang_id_foreign` FOREIGN KEY (`bidang_id`) REFERENCES `bidangs` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
