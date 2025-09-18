/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
	(1, 'pengaduan-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(2, 'pengaduan-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(3, 'pengaduan-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(4, 'pengaduan-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(5, 'rekomendasi-biaya-perawatans-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(6, 'rekomendasi-biaya-perawatans-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(7, 'rekomendasi-biaya-perawatans-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(8, 'rekomendasi-biaya-perawatans-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(9, 'rekomendasi-dtks-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(10, 'rekomendasi-dtks-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(11, 'rekomendasi-dtks-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(12, 'rekomendasi-dtks-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(13, 'role-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(14, 'role-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(15, 'role-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(16, 'role-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(17, 'user-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(18, 'user-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(19, 'user-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(20, 'user-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(21, 'rekomendasi-bantuan-pendidikan-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(22, 'rekomendasi-bantuan-pendidikan-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(23, 'rekomendasi-bantuan-pendidikan-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(24, 'rekomendasi-bantuan-pendidikan-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(25, 'rekomendasi-rehabilitasi-sosial-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(26, 'rekomendasi-rehabilitasi-sosial-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(27, 'rekomendasi-rehabilitasi-sosial-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(28, 'rekomendasi-rehabilitasi-sosial-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(29, 'rekomendasi-reaktivasi-pbijk-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(30, 'rekomendasi-reaktivasi-pbijk-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(31, 'rekomendasi-reaktivasi-pbijk-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(32, 'rekomendasi-reaktivasi-pbijk-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(33, 'rekomendasi-keringanan-pbbs-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(34, 'rekomendasi-keringanan-pbbs-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(35, 'rekomendasi-keringanan-pbbs-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(36, 'rekomendasi-keringanan-pbbs-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(37, 'rekomendasi-pengangangkatan-anak-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(38, 'rekomendasi-pengangangkatan-anak-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(39, 'rekomendasi-pengangangkatan-anak-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(40, 'rekomendasi-pengangangkatan-anak-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(41, 'rekomendasi-daftar-ulang-yayasan-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(42, 'rekomendasi-daftar-ulang-yayasan-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(43, 'rekomendasi-daftar-ulang-yayasan-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(44, 'rekomendasi-daftar-ulang-yayasan-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(45, 'rekomendasi-terdaftar-yayasan-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(46, 'rekomendasi-terdaftar-yayasan-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(47, 'rekomendasi-terdaftar-yayasan-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(48, 'rekomendasi-terdaftar-yayasan-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(49, 'rekomendasi-yayasan-provinsi-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(50, 'rekomendasi-yayasan-provinsi-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(51, 'rekomendasi-yayasan-provinsi-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(52, 'rekomendasi-yayasan-provinsi-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(53, 'rekomendasi-pelaporan-pub-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(54, 'rekomendasi-pelaporan-pub-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(55, 'rekomendasi-pelaporan-pub-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(56, 'rekomendasi-pelaporan-pub-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(57, 'prelist-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(58, 'prelist-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(59, 'prelist-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(60, 'prelist-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(61, 'rekomendasi-admin-kependudukan-list', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(62, 'rekomendasi-admin-kependudukan-create', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(63, 'rekomendasi-admin-kependudukan-edit', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21'),
	(64, 'rekomendasi-admin-kependudukan-delete', 'web', '2023-03-08 21:29:21', '2023-03-08 21:29:21');
/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
