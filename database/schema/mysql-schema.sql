/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `alur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alur` (
  `id_alur` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id_alur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `dtks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dtks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `Id_DTKS` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Provinsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Kabupaten_Kota` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Kecamatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Desa_Kelurahan` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Alamat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Dusun` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RT` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `RW` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nokk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Tanggal_Lahir` date DEFAULT NULL,
  `Tempat_Lahir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Jenis_Kelamin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Pekerjaan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nama_Ibu_Kandung` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Hub_Keluarga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Keterangan_padan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Bansos_Bpnt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Bansos_Pkh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Bansos_Bnpnt_Ppkm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Pbi_Jni` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `dtks_id_dtks_unique` (`Id_DTKS`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `failed_jobs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indonesia_cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indonesia_cities` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_cities` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indonesia_cities_code_unique` (`code`),
  KEY `indonesia_cities_province_code_foreign` (`province_code`),
  CONSTRAINT `indonesia_cities_province_code_foreign` FOREIGN KEY (`province_code`) REFERENCES `indonesia_provinces` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indonesia_districts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indonesia_districts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city_code` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_districts` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indonesia_districts_code_unique` (`code`),
  KEY `indonesia_districts_city_code_foreign` (`city_code`),
  CONSTRAINT `indonesia_districts_city_code_foreign` FOREIGN KEY (`city_code`) REFERENCES `indonesia_cities` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indonesia_provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indonesia_provinces` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_prov` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indonesia_provinces_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `indonesia_villages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `indonesia_villages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district_code` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_village` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indonesia_villages_code_unique` (`code`),
  KEY `indonesia_villages_district_code_foreign` (`district_code`),
  CONSTRAINT `indonesia_villages_district_code_foreign` FOREIGN KEY (`district_code`) REFERENCES `indonesia_districts` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_bantuan_pendidikan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_bantuan_pendidikan` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_trx_log_bantuan_pendidikans` varchar(10) DEFAULT NULL,
  `id_alur_log_bantuan_pendidikans` varchar(10) DEFAULT NULL,
  `tujuan_log_bantuan_pendidikans` varchar(255) DEFAULT NULL,
  `petugas_log_bantuan_pendidikans` varchar(255) DEFAULT NULL,
  `catatan_log_bantuan_pendidikans` varchar(255) DEFAULT NULL,
  `file_permohonan_bantuan_pendidikans` longtext DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by_log_bantuan_pendidikans` varchar(255) DEFAULT NULL,
  `updated_by_log_bantuan_pendidikans` varchar(255) DEFAULT NULL,
  `draft_rekomendasi_log_bantuan_pendidikans` varchar(50) DEFAULT NULL,
  `created_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_biper`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_biper` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_biper` varchar(50) DEFAULT NULL,
  `id_alur_biper` varchar(50) DEFAULT NULL,
  `tujuan_biper` varchar(50) DEFAULT NULL,
  `petugas_biper` varchar(50) DEFAULT NULL,
  `catatan_biper` varchar(50) DEFAULT NULL,
  `file_pendukung_biper` varchar(50) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by_biper` varchar(50) DEFAULT NULL,
  `created_by_biper` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_minkep`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_minkep` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_trx_minkep` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_alur_minkep` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_minkep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_minkep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_minkep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_permohonan_minkep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at_minkep` datetime DEFAULT NULL,
  `created_by_minkep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_minkep` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `draft_rekomendasi` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime(6) DEFAULT NULL,
  `updated_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_pbbs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_pbbs` (
  `id` int(11) DEFAULT NULL,
  `id_trx_pbbs` varchar(255) DEFAULT NULL,
  `id_alur_pbbs` varchar(255) DEFAULT NULL,
  `tujuan_pbbs` varchar(255) DEFAULT NULL,
  `petugas_pbbs` varchar(255) DEFAULT NULL,
  `catatan_pbbs` varchar(255) DEFAULT NULL,
  `file_pendukung_pbbs` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by_pbbs` varchar(255) DEFAULT NULL,
  `created_by_pbbs` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_pbijk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_pbijk` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_pbijk` varchar(255) DEFAULT NULL,
  `id_alur_pbijk` varchar(255) DEFAULT NULL,
  `tujuan_pbijk` varchar(255) DEFAULT NULL,
  `petugas_pbijk` varchar(255) DEFAULT NULL,
  `catatan_pbijk` varchar(255) DEFAULT NULL,
  `file_pendukung_pbijk` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_by_pbijk` varchar(50) DEFAULT NULL,
  `updated_by_pbijk` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_pelaporanpub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_pelaporanpub` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_trx_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_alur_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_permohonan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_pengaduan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_pengaduan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_pengaduan` int(11) DEFAULT NULL,
  `id_alur` varchar(50) DEFAULT NULL,
  `tujuan` varchar(50) DEFAULT NULL,
  `petugas` varchar(50) DEFAULT NULL,
  `catatan` varchar(50) DEFAULT NULL,
  `file_pendukung` varchar(50) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `updated_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_pengan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_pengan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_pengan` varchar(255) DEFAULT NULL,
  `id_alur_pengan` varchar(255) DEFAULT NULL,
  `tujuan_pengan` varchar(255) DEFAULT NULL,
  `petugas_pengan` varchar(255) DEFAULT NULL,
  `catatan_pengan` varchar(255) DEFAULT NULL,
  `file_pendukung_pengan` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by_pengan` varchar(255) DEFAULT NULL,
  `created_by_pengan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_resos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_resos` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `id_trx_resos` varchar(255) DEFAULT NULL,
  `id_alur_resos` varchar(255) DEFAULT NULL,
  `tujuan_resos` varchar(255) DEFAULT NULL,
  `petugas_resos` varchar(255) DEFAULT NULL,
  `catatan_resos` varchar(255) DEFAULT NULL,
  `draft_rekomendasi_resos` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_by_resos` varchar(255) DEFAULT NULL,
  `created_by_resos` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_sudtks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_sudtks` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_trx_sudtks` varchar(10) DEFAULT NULL,
  `id_alur_sudtks` varchar(10) DEFAULT NULL,
  `tujuan_sudtks` varchar(255) DEFAULT NULL,
  `petugas_sudtks` varchar(255) DEFAULT NULL,
  `catatan_sudtks` varchar(255) DEFAULT NULL,
  `file_pendukung_sudtks` varchar(255) DEFAULT NULL,
  `updated_at_sudtks` datetime DEFAULT NULL,
  `created_by_sudtks` varchar(255) DEFAULT NULL,
  `updated_by_sudtks` varchar(255) DEFAULT NULL,
  `draft_rekomendasi_sudtks` varchar(50) DEFAULT NULL,
  `created_at` datetime(6) DEFAULT NULL,
  `updated_at` datetime(6) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_ulangyayasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_ulangyayasan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_trx_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_alur_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_permohonan_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_by_ulangYayasan` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `log_yayasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `log_yayasan` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `id_trx_yayasan` varchar(10) DEFAULT NULL,
  `id_alur` varchar(10) DEFAULT NULL,
  `tujuan` varchar(255) DEFAULT NULL,
  `petugas` varchar(255) DEFAULT NULL,
  `catatan` varchar(255) DEFAULT NULL,
  `file_permohonan` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `created_by` varchar(255) DEFAULT NULL,
  `updated_by` varchar(255) DEFAULT NULL,
  `draft_rekomendasi` varchar(50) DEFAULT NULL,
  `created_at` datetime(6) DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pelapor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pelapor` (
  `id_pelapor` int(11) NOT NULL AUTO_INCREMENT,
  `id_menu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_form` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_peelaporan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tanggal_lahir_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telepon_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `nik_pelapor` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pelapor`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pengaduans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengaduans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_alur` int(11) DEFAULT NULL,
  `id_provinsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hubungan_terlapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_program_sosial` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_peserta` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_penunjang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kepesertaan_program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kategori_pengaduan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level_program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sektor_program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kartu_program` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ringkasan_pengaduan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `detail_pengaduan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tl_catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tl_file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `status_dtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_alur` (`id_alur`),
  CONSTRAINT `pengaduans_ibfk_1` FOREIGN KEY (`id_alur`) REFERENCES `alur` (`id_alur`),
  CONSTRAINT `pengaduans_ibfk_2` FOREIGN KEY (`id_alur`) REFERENCES `alur` (`id_alur`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `permissions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `prelist`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prelist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_provinsi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kabkot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kecamatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kelurahan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_kk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tgl_lahir` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_data` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_admin_kependudukans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_admin_kependudukans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kk_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir_minkep` timestamp NULL DEFAULT NULL,
  `jenis_kelamin_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ktp_terlapor_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk_terlapor_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_keterangan_dtks_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_pendukung_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `catatan_minkep` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_bantuan_pendidikans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_bantuan_pendidikans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_provinsi_bantuan_pendidikans` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kabkot_bantuan_pendidikans` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kecamatan_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_pendaftaran_bantuan_pendidikans` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_pelapor_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_kelamin_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_bantuan_pendidikans` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ktp_terlapor_bantuan_pendidikans` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk_terlapor_bantuan_pendidikans` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_keterangan_dtks_bantuan_pendidikans` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_pendukung_bantuan_pendidikans` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_alur_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_bantuan_pendidikans` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_sekolah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_biaya_perawatans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_biaya_perawatans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kk_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir_biper` timestamp NULL DEFAULT NULL,
  `jenis_kelamin_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_rm_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_masuk_rs_biper` date DEFAULT NULL,
  `file_perawatan_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_lap_sosial_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kebutuhan_layanan_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan_biper` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_biper` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_biper` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_biper` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_biper` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_biper` varchar(55) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_rumah_sakit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `yth_biper` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kab_kota_rumah_sakit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_pelaporan_pubs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_pelaporan_pubs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_permohonan_pub` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_izin_terdaftar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_keterangan_domisili` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_pokok_wajib_pajak` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bukti_setor_pajak` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `norek_penampung_pub` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ktp_direktur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_keabsahan_dokumen` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_bermaterai_cukup` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_permohonan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proposal_pub` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_ubar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Sistem_Pengumpulan` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_pengangkatan_anaks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_pengangkatan_anaks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_anak_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_izin_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_sehat_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_sehat_jiwa_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `surat_kandungan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_cota_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sukel_persyaratan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skck_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_nikah_cota_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `KK_cota_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `KTP_cota_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `KK_ortuangkat_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `KTP_ortuangkat_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_canak_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_canak_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `berita_acara_lembaga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `izincota_suami_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `izincota_istri_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sudok_fakta_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_motivasi_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_notwalinikah_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_asalusul_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_notdiskriminasi_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_terbaik_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi_diri_motivasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_sosial_pengasuh` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_asuransi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `super_hibah` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_pengan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nama_ibu_angkat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nama_Bapak_angkat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `catatan_pengan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_pengumpulan_undian_berhadiahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_pengumpulan_undian_berhadiahs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_kk` int(11) NOT NULL,
  `nik` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_rehabilitasi_sosials`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_rehabilitasi_sosials` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kk_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir_resos` timestamp NULL DEFAULT NULL,
  `jenis_kelamin_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ktp_terlapor_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk_terlapor_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_keterangan_dtks_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_pendukung_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ttd_kepala_dinas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_resos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_rekativasi_pbi_jks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_rekativasi_pbi_jks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir_pbijk` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jenis_kelamin_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ktp_terlapor_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk_terlapor_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_keterangan_dtks_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_pendukung_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_pbijk` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_pbijk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `no_kk_pbijk` bigint(20) DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_terdaftar_dtks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_terdaftar_dtks` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `no_pendaftaran_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_provinsi_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kabkot_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kecamatan_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ada_nik_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kk_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tempat_lahir_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_lahir_sudtks` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `jenis_kelamin_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_ktp_terlapor_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_kk_terlapor_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_keterangan_dtks_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_pendukung_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_sudtks` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_aksi_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_dtks_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby_sudtks` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_terdaftar_yayasans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_terdaftar_yayasans` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_alur` int(11) DEFAULT NULL,
  `no_pendaftaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_provinsi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kabkot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kecamatan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_pel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_pel` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kepengurusan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_notaris` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lembaga` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_lembaga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_notaris` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notgl_akta` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ketua` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_ahu` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_mulai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_selesai` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_notarispendirian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adart` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `struktur_organisasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_ktp_pengurus` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_wajibpajak` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_terimalayanan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_keuangan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_kegiatan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_plang` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visi_misi` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proker_yayasan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_aset` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_sdm` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelengkapan_sarpras` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_kelengkapanberkas` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_permohonan` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_sk_sebelumnya` date DEFAULT NULL,
  `no_sk_sebelumnya` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sertifikat_akreditasi` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_sk_provinsi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan_daftar_ulang` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan_yayasan_provinsi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_alur` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `draft_rekomendasi` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ttd_kepala_dinas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `jenis_kesos` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lingkup_Wilayah_Kerja` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `rekomendasi_yayasans_provinsi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rekomendasi_yayasans_provinsi` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `id_alur` int(10) unsigned DEFAULT NULL,
  `no_pendaftaran` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_provinsi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kabkot` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_kecamatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_kelurahan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pelapor` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_pel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nik_pel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telp_pel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_kepengurusan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_pel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_notaris` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_lembaga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `alamat_lembaga` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_notaris` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notgl_akta` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_ketua` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipe` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_ahu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_mulai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_selesai` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `akta_notarispendirian` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `adart` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `struktur_organisasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_ktp_pengurus` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_wajibpajak` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_terimalayanan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_keuangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `laporan_kegiatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `foto_plang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visi_misi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proker_yayasan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_aset` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_sdm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelengkapan_sarpras` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `form_kelengkapanberkas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_permohonan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_sk_sebelumnya` date DEFAULT NULL,
  `no_sk_sebelumnya` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sertifikat_akreditasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_sk_provinsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan_daftar_ulang` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan_yayasan_provinsi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_alur` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tujuan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `petugas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `createdby` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updatedby` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `draft_rekomendasi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ttd_kepala_dinas` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `validasi_surat` tinyint(1) DEFAULT 0,
  `Nomor_Surat` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Lingkup_Wilayah_Kerja` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) unsigned NOT NULL,
  `role_id` bigint(20) unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wilayahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `wilayahs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `province_id` char(2) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kota_id` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kecamatan_id` char(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `kelurahan_id` char(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_wilayah` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `createdby` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `wilayahs_province_id_foreign` (`province_id`),
  KEY `wilayahs_kota_id_foreign` (`kota_id`),
  KEY `wilayahs_kecamatan_id_foreign` (`kecamatan_id`),
  KEY `wilayahs_kelurahan_id_foreign` (`kelurahan_id`),
  CONSTRAINT `wilayahs_kecamatan_id_foreign` FOREIGN KEY (`kecamatan_id`) REFERENCES `indonesia_districts` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `wilayahs_kelurahan_id_foreign` FOREIGN KEY (`kelurahan_id`) REFERENCES `indonesia_villages` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `wilayahs_kota_id_foreign` FOREIGN KEY (`kota_id`) REFERENCES `indonesia_cities` (`code`) ON UPDATE CASCADE,
  CONSTRAINT `wilayahs_province_id_foreign` FOREIGN KEY (`province_id`) REFERENCES `indonesia_provinces` (`code`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` VALUES (21,'2023_03_03_035050_create_wilayahs_table',3);
INSERT INTO `migrations` VALUES (44,'2014_10_12_000000_create_users_table',4);
INSERT INTO `migrations` VALUES (45,'2014_10_12_100000_create_password_resets_table',4);
INSERT INTO `migrations` VALUES (46,'2016_08_03_072729_create_provinces_table',4);
INSERT INTO `migrations` VALUES (47,'2016_08_03_072750_create_cities_table',4);
INSERT INTO `migrations` VALUES (48,'2016_08_03_072804_create_districts_table',4);
INSERT INTO `migrations` VALUES (49,'2016_08_03_072819_create_villages_table',4);
INSERT INTO `migrations` VALUES (50,'2019_08_19_000000_create_failed_jobs_table',4);
INSERT INTO `migrations` VALUES (51,'2019_12_14_000001_create_personal_access_tokens_table',4);
INSERT INTO `migrations` VALUES (52,'2022_04_15_130146_create_permission_tables',4);
INSERT INTO `migrations` VALUES (53,'2023_02_28_042542_create_pengaduans_table',4);
INSERT INTO `migrations` VALUES (54,'2023_02_28_062432_create_rekomendasi_pengangkatan_anaks_table',4);
INSERT INTO `migrations` VALUES (55,'2023_02_28_062610_create_rekomendasi_terdaftar_yayasans_table',4);
INSERT INTO `migrations` VALUES (56,'2023_02_28_062805_create_rekomendasi_pengumpulan_undian_berhadiahs_table',4);
INSERT INTO `migrations` VALUES (57,'2023_02_28_062932_create_rekomendasi_bantuan_pendidikans_table',4);
INSERT INTO `migrations` VALUES (58,'2023_02_28_063048_create_rekomendasi_rekativasi_pbi_jks_table',4);
INSERT INTO `migrations` VALUES (59,'2023_02_28_063147_create_rekomendasi_admin_kependudukans_table',4);
INSERT INTO `migrations` VALUES (60,'2023_02_28_063259_create_rekomendasi_rehabilitasi_sosials_table',4);
INSERT INTO `migrations` VALUES (61,'2023_02_28_063349_create_rekomendasi_terdaftar_dtks_table',4);
INSERT INTO `migrations` VALUES (62,'2023_02_28_063451_create_rekomendasi_biaya_perawatans_table',4);
INSERT INTO `migrations` VALUES (63,'2023_02_28_063601_create_rekomendasi_keringanan_pbbs_table',4);
INSERT INTO `migrations` VALUES (64,'2023_03_03_050428_create_wilayahs_table',4);
INSERT INTO `migrations` VALUES (65,'2023_05_27_041151_add__nomor_surat_to_rekomendasi_terdaftar_yayasans_table',5);
INSERT INTO `migrations` VALUES (66,'2023_05_28_080748_add_column_to_rekomendasi_pengangkatan_anaks_table',5);
INSERT INTO `migrations` VALUES (67,'2023_05_29_024943_add_column_to_rekomendasi_pelaporan_pubs_table',5);
INSERT INTO `migrations` VALUES (68,'2023_05_31_105835_add_rekomendasi_terdaftar_yayasans_to_table',6);
INSERT INTO `migrations` VALUES (69,'2023_05_31_113103_add_log_yayasan_to_table',7);
INSERT INTO `migrations` VALUES (70,'2023_06_05_061415_add_log_ulangyayasan_to_table',8);
INSERT INTO `migrations` VALUES (71,'2023_06_05_130800_add_log_pelaporanpub_to_table',9);
INSERT INTO `migrations` VALUES (72,'2023_06_07_053727_add_rekomendasi_rekativasi_pbi_jks_to_table',10);
INSERT INTO `migrations` VALUES (73,'2023_06_07_054405_add_rekomendasi_rekativasi_pbi_jks_to_table',11);
INSERT INTO `migrations` VALUES (74,'2023_06_07_054955_add_rekomendasi_rekativasi_pbi_jks_to_table',12);
INSERT INTO `migrations` VALUES (75,'2023_06_08_053516_add_rekomendasi_pelaporan_pubs_to_table',13);
INSERT INTO `migrations` VALUES (76,'2023_06_09_051311_create_rekomendasi_yayasans_provinsi',14);
INSERT INTO `migrations` VALUES (77,'2023_06_09_070557_add_rekomendasi_yayasans_provinsi_to_table',15);
INSERT INTO `migrations` VALUES (78,'2023_06_09_070630_add_log_yayasanprovinsi_to_table',15);
INSERT INTO `migrations` VALUES (79,'2023_06_21_022059_add_rekomendasi_admin_kependudukans_to_table',16);
INSERT INTO `migrations` VALUES (80,'2023_06_21_032754_add_rekomendasi_rehabilitasi_sosials_to_table',17);
INSERT INTO `migrations` VALUES (81,'2023_06_22_090747_add_rekomendasi_rekomendasi_bantuan_pendidikans_to_table',18);
INSERT INTO `migrations` VALUES (82,'2023_06_23_021250_add_rekomendasi_rekomendasi_bantuan_pendidikans_to_table',19);
INSERT INTO `migrations` VALUES (83,'2023_06_23_032649_add_rekomendasi_admin_kependudukans_to_table',20);
INSERT INTO `migrations` VALUES (84,'2023_06_23_062907_add_rekomendasi_biaya_perawatans_to_table',21);
