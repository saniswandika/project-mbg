/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE TABLE IF NOT EXISTS `log_ulangyayasan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_ulangYayasan` varchar(255) DEFAULT NULL,
  `id_alur_ulangYayasan` varchar(255) DEFAULT NULL,
  `petugas_ulangYayasan` varchar(255) DEFAULT NULL,
  `catatan_ulangYayasan` varchar(255) DEFAULT NULL,
  `file_permohonan_ulangYayasan` varchar(255) DEFAULT NULL,
  `tujuan_ulangYayasan` varchar(255) DEFAULT NULL,
  `created_by_ulangYayasan` varchar(255) DEFAULT NULL,
  `updated_by_ulangYayasan` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

INSERT INTO `log_ulangyayasan` (`id`, `id_trx_ulangYayasan`, `id_alur_ulangYayasan`, `petugas_ulangYayasan`, `catatan_ulangYayasan`, `file_permohonan_ulangYayasan`, `tujuan_ulangYayasan`, `created_by_ulangYayasan`, `updated_by_ulangYayasan`, `created_at`, `updated_at`) VALUES
	(1, '1', NULL, '18', NULL, NULL, '6', '20', NULL, '2023-05-06 03:25:46', NULL),
	(2, '1', NULL, '18', NULL, NULL, '6', '20', NULL, '2023-05-07 06:55:54', NULL),
	(3, '1', NULL, '18', NULL, NULL, '6', '20', NULL, '2023-05-07 06:59:34', NULL),
	(4, '1', '2', '16', NULL, NULL, '8', '18', NULL, '2023-05-07 07:09:59', NULL),
	(5, '1', '2', '14', NULL, NULL, '9', '16', NULL, '2023-05-07 07:11:33', NULL);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
