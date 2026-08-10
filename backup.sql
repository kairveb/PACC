-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: coor
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admissions`
--

DROP TABLE IF EXISTS `admissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admission_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `er_visit_id` bigint unsigned DEFAULT NULL,
  `attending_provider_id` bigint unsigned DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'REQUESTED',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `admitted_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `admissions_admission_number_unique` (`admission_number`),
  KEY `admissions_er_visit_id_foreign` (`er_visit_id`),
  KEY `admissions_attending_provider_id_foreign` (`attending_provider_id`),
  KEY `admissions_created_by_foreign` (`created_by`),
  KEY `admissions_patient_id_status_index` (`patient_id`,`status`),
  CONSTRAINT `admissions_attending_provider_id_foreign` FOREIGN KEY (`attending_provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_er_visit_id_foreign` FOREIGN KEY (`er_visit_id`) REFERENCES `er_visits` (`id`) ON DELETE SET NULL,
  CONSTRAINT `admissions_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admissions`
--

LOCK TABLES `admissions` WRITE;
/*!40000 ALTER TABLE `admissions` DISABLE KEYS */;
INSERT INTO `admissions` VALUES (1,'ADM-2026-000001',3,NULL,1,'ADMITTED','Observation','2026-08-06 13:38:04',7,'2026-08-06 08:38:04','2026-08-06 08:38:04');
/*!40000 ALTER TABLE `admissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `api_logs`
--

DROP TABLE IF EXISTS `api_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `api_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status_code` smallint unsigned NOT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `api_logs_user_id_foreign` (`user_id`),
  CONSTRAINT `api_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `api_logs`
--

LOCK TABLES `api_logs` WRITE;
/*!40000 ALTER TABLE `api_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `api_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_slots`
--

DROP TABLE IF EXISTS `appointment_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_slots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` bigint unsigned NOT NULL,
  `appointment_type_id` bigint unsigned DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AVAILABLE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointment_slots_provider_id_starts_at_unique` (`provider_id`,`starts_at`),
  KEY `appointment_slots_appointment_type_id_foreign` (`appointment_type_id`),
  CONSTRAINT `appointment_slots_appointment_type_id_foreign` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointment_slots_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_slots`
--

LOCK TABLES `appointment_slots` WRITE;
/*!40000 ALTER TABLE `appointment_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointment_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_status_histories`
--

DROP TABLE IF EXISTS `appointment_status_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_status_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint unsigned NOT NULL,
  `from_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `to_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `appointment_status_histories_appointment_id_foreign` (`appointment_id`),
  KEY `appointment_status_histories_changed_by_foreign` (`changed_by`),
  CONSTRAINT `appointment_status_histories_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appointment_status_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_status_histories`
--

LOCK TABLES `appointment_status_histories` WRITE;
/*!40000 ALTER TABLE `appointment_status_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `appointment_status_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointment_types`
--

DROP TABLE IF EXISTS `appointment_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointment_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_duration` smallint unsigned NOT NULL DEFAULT '30',
  `telehealth` tinyint(1) NOT NULL DEFAULT '0',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointment_types_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointment_types`
--

LOCK TABLES `appointment_types` WRITE;
/*!40000 ALTER TABLE `appointment_types` DISABLE KEYS */;
INSERT INTO `appointment_types` VALUES (1,'Outpatient',30,0,1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,'Telehealth',30,1,1,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `appointment_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `appointments`
--

DROP TABLE IF EXISTS `appointments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appointments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `appointment_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `appointment_type_id` bigint unsigned DEFAULT NULL,
  `starts_at` datetime NOT NULL,
  `ends_at` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'PENDING',
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `appointments_provider_id_starts_at_unique` (`provider_id`,`starts_at`),
  UNIQUE KEY `appointments_appointment_number_unique` (`appointment_number`),
  KEY `appointments_department_id_foreign` (`department_id`),
  KEY `appointments_appointment_type_id_foreign` (`appointment_type_id`),
  KEY `appointments_created_by_foreign` (`created_by`),
  KEY `appointments_patient_id_starts_at_index` (`patient_id`,`starts_at`),
  CONSTRAINT `appointments_appointment_type_id_foreign` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `appointments_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appointments`
--

LOCK TABLES `appointments` WRITE;
/*!40000 ALTER TABLE `appointments` DISABLE KEYS */;
INSERT INTO `appointments` VALUES (1,'APT-2026-000001',1,1,1,1,'2026-08-06 18:38:03','2026-08-06 19:08:03','CONFIRMED','Follow-up checkup',5,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `appointments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resource_id` bigint unsigned DEFAULT NULL,
  `result` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'SUCCESS',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_foreign` (`user_id`),
  KEY `audit_logs_resource_type_resource_id_index` (`resource_type`,`resource_id`),
  KEY `audit_logs_action_created_at_index` (`action`,`created_at`),
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bed_assignments`
--

DROP TABLE IF EXISTS `bed_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bed_assignments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admission_id` bigint unsigned NOT NULL,
  `bed_id` bigint unsigned NOT NULL,
  `assigned_by` bigint unsigned DEFAULT NULL,
  `assigned_at` datetime NOT NULL,
  `released_at` datetime DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bed_assignments_bed_id_status_unique` (`bed_id`,`status`),
  KEY `bed_assignments_assigned_by_foreign` (`assigned_by`),
  KEY `bed_assignments_admission_id_status_index` (`admission_id`,`status`),
  CONSTRAINT `bed_assignments_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bed_assignments_assigned_by_foreign` FOREIGN KEY (`assigned_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `bed_assignments_bed_id_foreign` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bed_assignments`
--

LOCK TABLES `bed_assignments` WRITE;
/*!40000 ALTER TABLE `bed_assignments` DISABLE KEYS */;
INSERT INTO `bed_assignments` VALUES (1,1,1,7,'2026-08-06 13:38:04',NULL,'ACTIVE','2026-08-06 08:38:04','2026-08-06 08:38:04');
/*!40000 ALTER TABLE `bed_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bed_reservations`
--

DROP TABLE IF EXISTS `bed_reservations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bed_reservations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `bed_id` bigint unsigned NOT NULL,
  `admission_id` bigint unsigned NOT NULL,
  `reserved_by` bigint unsigned DEFAULT NULL,
  `expires_at` datetime DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ACTIVE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `bed_reservations_bed_id_status_unique` (`bed_id`,`status`),
  KEY `bed_reservations_admission_id_foreign` (`admission_id`),
  KEY `bed_reservations_reserved_by_foreign` (`reserved_by`),
  CONSTRAINT `bed_reservations_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bed_reservations_bed_id_foreign` FOREIGN KEY (`bed_id`) REFERENCES `beds` (`id`),
  CONSTRAINT `bed_reservations_reserved_by_foreign` FOREIGN KEY (`reserved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bed_reservations`
--

LOCK TABLES `bed_reservations` WRITE;
/*!40000 ALTER TABLE `bed_reservations` DISABLE KEYS */;
/*!40000 ALTER TABLE `bed_reservations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `beds`
--

DROP TABLE IF EXISTS `beds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `beds` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `room_id` bigint unsigned NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'AVAILABLE',
  `status_updated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `beds_room_id_number_unique` (`room_id`,`number`),
  KEY `beds_status_index` (`status`),
  CONSTRAINT `beds_room_id_foreign` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `beds`
--

LOCK TABLES `beds` WRITE;
/*!40000 ALTER TABLE `beds` DISABLE KEYS */;
INSERT INTO `beds` VALUES (1,1,'A','OCCUPIED','2026-08-06 08:38:04','2026-08-06 08:38:03','2026-08-06 08:38:04'),(2,1,'B','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,1,'C','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,2,'A','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(5,2,'B','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(6,2,'C','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(7,3,'A','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(8,3,'B','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(9,3,'C','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(10,4,'A','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(11,4,'B','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(12,4,'C','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(13,5,'A','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(14,5,'B','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(15,5,'C','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(16,6,'A','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(17,6,'B','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(18,6,'C','AVAILABLE',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `beds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clinical_documents`
--

DROP TABLE IF EXISTS `clinical_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clinical_documents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `encounter_id` bigint unsigned DEFAULT NULL,
  `uploaded_by` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clinical_documents_patient_id_foreign` (`patient_id`),
  KEY `clinical_documents_encounter_id_foreign` (`encounter_id`),
  KEY `clinical_documents_uploaded_by_foreign` (`uploaded_by`),
  CONSTRAINT `clinical_documents_encounter_id_foreign` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `clinical_documents_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `clinical_documents_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clinical_documents`
--

LOCK TABLES `clinical_documents` WRITE;
/*!40000 ALTER TABLE `clinical_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `clinical_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `departments_code_unique` (`code`),
  UNIQUE KEY `departments_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'CAR','Cardiology','1234',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,'PED','Pediatrics','2345',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,'ORT','Orthopedics','3456',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,'GEN','General Medicine','4567',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(5,'EMG','Emergency Medicine','5678',1,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discharges`
--

DROP TABLE IF EXISTS `discharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discharges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admission_id` bigint unsigned NOT NULL,
  `authorized_by` bigint unsigned NOT NULL,
  `discharged_at` datetime NOT NULL,
  `reason` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disposition` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discharges_admission_id_unique` (`admission_id`),
  KEY `discharges_authorized_by_foreign` (`authorized_by`),
  CONSTRAINT `discharges_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `discharges_authorized_by_foreign` FOREIGN KEY (`authorized_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discharges`
--

LOCK TABLES `discharges` WRITE;
/*!40000 ALTER TABLE `discharges` DISABLE KEYS */;
/*!40000 ALTER TABLE `discharges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `emergency_contacts`
--

DROP TABLE IF EXISTS `emergency_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `emergency_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `relationship` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `emergency_contacts_patient_id_foreign` (`patient_id`),
  CONSTRAINT `emergency_contacts_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `emergency_contacts`
--

LOCK TABLES `emergency_contacts` WRITE;
/*!40000 ALTER TABLE `emergency_contacts` DISABLE KEYS */;
INSERT INTO `emergency_contacts` VALUES (1,1,'Emergency Contact','Spouse','09170000000','2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,2,'Emergency Contact','Spouse','09170000000','2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,3,'Emergency Contact','Spouse','09170000000','2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,4,'Emergency Contact','Spouse','09170000000','2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `emergency_contacts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encounter_notes`
--

DROP TABLE IF EXISTS `encounter_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `encounter_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `encounter_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned NOT NULL,
  `body` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `encounter_notes_encounter_id_foreign` (`encounter_id`),
  KEY `encounter_notes_author_id_foreign` (`author_id`),
  CONSTRAINT `encounter_notes_author_id_foreign` FOREIGN KEY (`author_id`) REFERENCES `users` (`id`),
  CONSTRAINT `encounter_notes_encounter_id_foreign` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encounter_notes`
--

LOCK TABLES `encounter_notes` WRITE;
/*!40000 ALTER TABLE `encounter_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `encounter_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `encounters`
--

DROP TABLE IF EXISTS `encounters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `encounters` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `encounter_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned NOT NULL,
  `appointment_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `started_at` datetime NOT NULL,
  `ended_at` datetime DEFAULT NULL,
  `chief_complaint` text COLLATE utf8mb4_unicode_ci,
  `assessment` text COLLATE utf8mb4_unicode_ci,
  `plan` text COLLATE utf8mb4_unicode_ci,
  `follow_up_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `encounters_encounter_number_unique` (`encounter_number`),
  UNIQUE KEY `encounters_appointment_id_unique` (`appointment_id`),
  KEY `encounters_patient_id_foreign` (`patient_id`),
  KEY `encounters_provider_id_foreign` (`provider_id`),
  CONSTRAINT `encounters_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `encounters_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `encounters_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `encounters`
--

LOCK TABLES `encounters` WRITE;
/*!40000 ALTER TABLE `encounters` DISABLE KEYS */;
/*!40000 ALTER TABLE `encounters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `er_queue`
--

DROP TABLE IF EXISTS `er_queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `er_queue` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `er_visit_id` bigint unsigned NOT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WAITING',
  `treatment_area` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_id` bigint unsigned DEFAULT NULL,
  `queued_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `er_queue_er_visit_id_unique` (`er_visit_id`),
  KEY `er_queue_provider_id_foreign` (`provider_id`),
  KEY `er_queue_status_priority_queued_at_index` (`status`,`priority`,`queued_at`),
  CONSTRAINT `er_queue_er_visit_id_foreign` FOREIGN KEY (`er_visit_id`) REFERENCES `er_visits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `er_queue_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `er_queue`
--

LOCK TABLES `er_queue` WRITE;
/*!40000 ALTER TABLE `er_queue` DISABLE KEYS */;
INSERT INTO `er_queue` VALUES (1,1,'Level 2','WAITING','Resuscitation',NULL,'2026-08-06 15:48:04','2026-08-06 08:38:04','2026-08-06 08:38:04');
/*!40000 ALTER TABLE `er_queue` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `er_visits`
--

DROP TABLE IF EXISTS `er_visits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `er_visits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `visit_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `arrived_at` datetime NOT NULL,
  `arrival_method` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `chief_complaint` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `referral_details` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ARRIVED',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `er_visits_visit_number_unique` (`visit_number`),
  KEY `er_visits_patient_id_foreign` (`patient_id`),
  KEY `er_visits_created_by_foreign` (`created_by`),
  KEY `er_visits_status_arrived_at_index` (`status`,`arrived_at`),
  CONSTRAINT `er_visits_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `er_visits_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `er_visits`
--

LOCK TABLES `er_visits` WRITE;
/*!40000 ALTER TABLE `er_visits` DISABLE KEYS */;
INSERT INTO `er_visits` VALUES (1,'ER-2026-000001',2,'2026-08-06 15:38:03','Ambulance','Chest pain',NULL,'TRIAGED',5,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `er_visits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `integration_logs`
--

DROP TABLE IF EXISTS `integration_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `integration_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `integration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `integration_logs`
--

LOCK TABLES `integration_logs` WRITE;
/*!40000 ALTER TABLE `integration_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `integration_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2019_12_14_000001_create_personal_access_tokens_table',1),(5,'2026_08_04_105847_create_hims_core_schema',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_addresses`
--

DROP TABLE IF EXISTS `patient_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `line1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_addresses_patient_id_foreign` (`patient_id`),
  CONSTRAINT `patient_addresses_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_addresses`
--

LOCK TABLES `patient_addresses` WRITE;
/*!40000 ALTER TABLE `patient_addresses` DISABLE KEYS */;
INSERT INTO `patient_addresses` VALUES (1,1,'123 Mabini St.',NULL,'Manila','Metro Manila','1000',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,2,'123 Mabini St.',NULL,'Manila','Metro Manila','1000',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,3,'123 Mabini St.',NULL,'Manila','Metro Manila','1000',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,4,'123 Mabini St.',NULL,'Manila','Metro Manila','1000',1,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `patient_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_consents`
--

DROP TABLE IF EXISTS `patient_consents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_consents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `granted` tinyint(1) NOT NULL,
  `recorded_at` timestamp NOT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_consents_patient_id_foreign` (`patient_id`),
  KEY `patient_consents_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `patient_consents_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_consents_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_consents`
--

LOCK TABLES `patient_consents` WRITE;
/*!40000 ALTER TABLE `patient_consents` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_consents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_identifiers`
--

DROP TABLE IF EXISTS `patient_identifiers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_identifiers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patient_identifiers_type_value_unique` (`type`,`value`),
  KEY `patient_identifiers_patient_id_foreign` (`patient_id`),
  CONSTRAINT `patient_identifiers_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_identifiers`
--

LOCK TABLES `patient_identifiers` WRITE;
/*!40000 ALTER TABLE `patient_identifiers` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_identifiers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_transfers`
--

DROP TABLE IF EXISTS `patient_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patient_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `admission_id` bigint unsigned NOT NULL,
  `from_bed_id` bigint unsigned DEFAULT NULL,
  `to_bed_id` bigint unsigned NOT NULL,
  `transferred_by` bigint unsigned DEFAULT NULL,
  `transferred_at` datetime NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `patient_transfers_admission_id_foreign` (`admission_id`),
  KEY `patient_transfers_from_bed_id_foreign` (`from_bed_id`),
  KEY `patient_transfers_to_bed_id_foreign` (`to_bed_id`),
  KEY `patient_transfers_transferred_by_foreign` (`transferred_by`),
  CONSTRAINT `patient_transfers_admission_id_foreign` FOREIGN KEY (`admission_id`) REFERENCES `admissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `patient_transfers_from_bed_id_foreign` FOREIGN KEY (`from_bed_id`) REFERENCES `beds` (`id`) ON DELETE SET NULL,
  CONSTRAINT `patient_transfers_to_bed_id_foreign` FOREIGN KEY (`to_bed_id`) REFERENCES `beds` (`id`),
  CONSTRAINT `patient_transfers_transferred_by_foreign` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_transfers`
--

LOCK TABLES `patient_transfers` WRITE;
/*!40000 ALTER TABLE `patient_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `patient_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `patients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `mrn` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `middle_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `suffix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date NOT NULL,
  `sex` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `civil_status` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nationality` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `allergies` text COLLATE utf8mb4_unicode_ci,
  `insurance_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `patients_mrn_unique` (`mrn`),
  UNIQUE KEY `patients_user_id_unique` (`user_id`),
  KEY `patients_last_name_first_name_index` (`last_name`,`first_name`),
  KEY `patients_date_of_birth_index` (`date_of_birth`),
  KEY `patients_phone_index` (`phone`),
  KEY `patients_email_index` (`email`),
  CONSTRAINT `patients_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES (1,8,'MRN-2026-000001','Maria',NULL,'Santos',NULL,'1987-04-18','Female','Single','Filipino','09171234567','maria.santos@example.test','Penicillin',NULL,1,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,NULL,'MRN-2026-000002','Juan',NULL,'Dela Cruz',NULL,'1975-11-02','Male','Single','Filipino','09173456789','juan.delacruz@example.test',NULL,NULL,1,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,NULL,'MRN-2026-000003','Liza',NULL,'Reyes',NULL,'1992-06-15','Female','Single','Filipino','09179876543','liza.reyes@example.test',NULL,NULL,1,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,NULL,'MRN-2026-000004','Pedro',NULL,'Garcia',NULL,'1968-02-28','Male','Single','Filipino','09171239876','pedro.garcia@example.test','Latex',NULL,1,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permission_role`
--

DROP TABLE IF EXISTS `permission_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_role` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `permission_role_role_id_foreign` (`role_id`),
  CONSTRAINT `permission_role_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permission_role_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_role`
--

LOCK TABLES `permission_role` WRITE;
/*!40000 ALTER TABLE `permission_role` DISABLE KEYS */;
INSERT INTO `permission_role` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(1,2),(2,2),(3,2),(4,2),(5,2),(6,2),(7,2),(8,2),(9,2),(10,2),(11,2),(12,2),(13,2),(14,2),(15,2),(16,2),(17,2),(18,2),(19,2),(20,2),(21,2),(1,3),(2,3),(3,3),(5,3),(6,3),(7,3),(1,4),(6,4),(10,4),(11,4),(12,4),(13,4),(15,4),(1,5),(6,5),(10,5),(11,5),(13,5),(14,5),(15,5),(16,5),(18,5),(1,6),(13,6),(14,6),(15,6),(16,6),(18,6),(1,7),(6,7),(16,7),(17,7),(18,7),(19,7),(6,8);
/*!40000 ALTER TABLE `permission_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'view-patients','View Patients','2026-08-06 08:37:59','2026-08-06 08:37:59'),(2,'create-patients','Create Patients','2026-08-06 08:37:59','2026-08-06 08:37:59'),(3,'update-patients','Update Patients','2026-08-06 08:37:59','2026-08-06 08:37:59'),(4,'delete-patients','Delete Patients','2026-08-06 08:37:59','2026-08-06 08:37:59'),(5,'verify-patients','Verify Patients','2026-08-06 08:37:59','2026-08-06 08:37:59'),(6,'view-appointments','View Appointments','2026-08-06 08:37:59','2026-08-06 08:37:59'),(7,'create-appointments','Create Appointments','2026-08-06 08:37:59','2026-08-06 08:37:59'),(8,'update-appointments','Update Appointments','2026-08-06 08:37:59','2026-08-06 08:37:59'),(9,'cancel-appointments','Cancel Appointments','2026-08-06 08:37:59','2026-08-06 08:37:59'),(10,'view-encounters','View Encounters','2026-08-06 08:37:59','2026-08-06 08:37:59'),(11,'create-encounters','Create Encounters','2026-08-06 08:38:00','2026-08-06 08:38:00'),(12,'update-encounters','Update Encounters','2026-08-06 08:38:00','2026-08-06 08:38:00'),(13,'view-er','View Er','2026-08-06 08:38:00','2026-08-06 08:38:00'),(14,'create-er-visits','Create Er Visits','2026-08-06 08:38:00','2026-08-06 08:38:00'),(15,'triage-patients','Triage Patients','2026-08-06 08:38:00','2026-08-06 08:38:00'),(16,'view-beds','View Beds','2026-08-06 08:38:00','2026-08-06 08:38:00'),(17,'manage-beds','Manage Beds','2026-08-06 08:38:00','2026-08-06 08:38:00'),(18,'view-admissions','View Admissions','2026-08-06 08:38:00','2026-08-06 08:38:00'),(19,'manage-admissions','Manage Admissions','2026-08-06 08:38:00','2026-08-06 08:38:00'),(20,'view-reports','View Reports','2026-08-06 08:38:00','2026-08-06 08:38:00'),(21,'view-audit-logs','View Audit Logs','2026-08-06 08:38:00','2026-08-06 08:38:00');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_schedules`
--

DROP TABLE IF EXISTS `provider_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `provider_id` bigint unsigned NOT NULL,
  `day_of_week` tinyint unsigned NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `slot_duration` smallint unsigned NOT NULL DEFAULT '30',
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `unavailable_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `provider_schedules_provider_id_day_of_week_index` (`provider_id`,`day_of_week`),
  CONSTRAINT `provider_schedules_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_schedules`
--

LOCK TABLES `provider_schedules` WRITE;
/*!40000 ALTER TABLE `provider_schedules` DISABLE KEYS */;
INSERT INTO `provider_schedules` VALUES (1,1,1,'08:00:00','17:00:00',30,NULL,NULL,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,1,2,'08:00:00','17:00:00',30,NULL,NULL,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,1,3,'08:00:00','17:00:00',30,NULL,NULL,NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `provider_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provider_specialties`
--

DROP TABLE IF EXISTS `provider_specialties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provider_specialties` (
  `provider_id` bigint unsigned NOT NULL,
  `specialty_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`provider_id`,`specialty_id`),
  KEY `provider_specialties_specialty_id_foreign` (`specialty_id`),
  CONSTRAINT `provider_specialties_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `provider_specialties_specialty_id_foreign` FOREIGN KEY (`specialty_id`) REFERENCES `specialties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provider_specialties`
--

LOCK TABLES `provider_specialties` WRITE;
/*!40000 ALTER TABLE `provider_specialties` DISABLE KEYS */;
INSERT INTO `provider_specialties` VALUES (1,1);
/*!40000 ALTER TABLE `provider_specialties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `providers`
--

DROP TABLE IF EXISTS `providers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `providers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `license_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `display_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `providers_user_id_unique` (`user_id`),
  UNIQUE KEY `providers_license_number_unique` (`license_number`),
  KEY `providers_department_id_foreign` (`department_id`),
  CONSTRAINT `providers_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `providers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `providers`
--

LOCK TABLES `providers` WRITE;
/*!40000 ALTER TABLE `providers` DISABLE KEYS */;
INSERT INTO `providers` VALUES (1,4,1,'LIC-001','Dr. Elena Santos',1,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `providers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_user`
--

DROP TABLE IF EXISTS `role_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_user` (
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`user_id`),
  KEY `role_user_user_id_foreign` (`user_id`),
  CONSTRAINT `role_user_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_user`
--

LOCK TABLES `role_user` WRITE;
/*!40000 ALTER TABLE `role_user` DISABLE KEYS */;
INSERT INTO `role_user` VALUES (1,1),(2,2),(3,3),(4,4),(5,5),(6,6),(7,7),(8,8);
/*!40000 ALTER TABLE `role_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super-admin','Super Admin','2026-08-06 08:37:59','2026-08-06 08:37:59'),(2,'hospital-admin','Hospital Admin','2026-08-06 08:37:59','2026-08-06 08:37:59'),(3,'registration','Registration Staff','2026-08-06 08:37:59','2026-08-06 08:37:59'),(4,'doctor','Doctor','2026-08-06 08:37:59','2026-08-06 08:37:59'),(5,'nurse','Nurse','2026-08-06 08:37:59','2026-08-06 08:37:59'),(6,'er-staff','ER Staff','2026-08-06 08:37:59','2026-08-06 08:37:59'),(7,'admission','Admission Staff','2026-08-06 08:37:59','2026-08-06 08:37:59'),(8,'patient','Patient','2026-08-06 08:37:59','2026-08-06 08:37:59');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rooms`
--

DROP TABLE IF EXISTS `rooms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rooms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `ward_id` bigint unsigned NOT NULL,
  `number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rooms_ward_id_number_unique` (`ward_id`,`number`),
  CONSTRAINT `rooms_ward_id_foreign` FOREIGN KEY (`ward_id`) REFERENCES `wards` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rooms`
--

LOCK TABLES `rooms` WRITE;
/*!40000 ALTER TABLE `rooms` DISABLE KEYS */;
INSERT INTO `rooms` VALUES (1,1,'201','Standard','2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,1,'202','Standard','2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,2,'201','Standard','2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,2,'202','Standard','2026-08-06 08:38:03','2026-08-06 08:38:03'),(5,3,'201','Standard','2026-08-06 08:38:03','2026-08-06 08:38:03'),(6,3,'202','Standard','2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `rooms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES ('Ce2FBaaMFM0HKsHYzg7gSJ2N14ruwu4jcJ7SGozv',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/151.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJxdmVYQUROSE1Lc0t6c2pFbnRSZ1VnTnRHQmNNWVVYWjBjTVEzTnQ0IiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Nvb3IudGVzdFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwidXJsIjp7ImludGVuZGVkIjoiaHR0cDpcL1wvY29vci50ZXN0XC9kYXNoYm9hcmQifX0=',1786034391),('cwuV9TB93GXwBvkCyzTVBbZFAt1rninb813wA0Tl',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-PH) PowerShell/7.6.4','eyJfdG9rZW4iOiJiTmZ6dkpveG40bnhEYmo5WGhXZlVjNlhpZVFVcHZDdjRIa0Z2TURVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Nvb3IudGVzdFwvbG9naW4iLCJyb3V0ZSI6ImxvZ2luIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=',1786034317),('qwPqWAL0ZHXJUmLdZoZJ2coXCiXuxApbp4gLwWc5',1,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Microsoft Windows 10.0.26200; en-PH) PowerShell/7.6.4','eyJfdG9rZW4iOiIzSHVscHNoa0xjcDVzMDQ4TTFlN2FJNlZLOHZxTFdmaFIyeVdNenRYIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2Nvb3IudGVzdFwvZGFzaGJvYXJkIiwicm91dGUiOiJkYXNoYm9hcmQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6MX0=',1786034348);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `specialties`
--

DROP TABLE IF EXISTS `specialties`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `specialties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `department_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `specialties_name_unique` (`name`),
  KEY `specialties_department_id_foreign` (`department_id`),
  CONSTRAINT `specialties_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `specialties`
--

LOCK TABLES `specialties` WRITE;
/*!40000 ALTER TABLE `specialties` DISABLE KEYS */;
INSERT INTO `specialties` VALUES (1,NULL,'Cardiology','2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,NULL,'Pediatrics','2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,NULL,'Orthopedics','2026-08-06 08:38:03','2026-08-06 08:38:03'),(4,NULL,'Internal Medicine','2026-08-06 08:38:03','2026-08-06 08:38:03'),(5,NULL,'Emergency Medicine','2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `specialties` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telehealth_participants`
--

DROP TABLE IF EXISTS `telehealth_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telehealth_participants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `telehealth_session_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `joined_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `telehealth_participants_telehealth_session_id_foreign` (`telehealth_session_id`),
  KEY `telehealth_participants_user_id_foreign` (`user_id`),
  CONSTRAINT `telehealth_participants_telehealth_session_id_foreign` FOREIGN KEY (`telehealth_session_id`) REFERENCES `telehealth_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `telehealth_participants_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telehealth_participants`
--

LOCK TABLES `telehealth_participants` WRITE;
/*!40000 ALTER TABLE `telehealth_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `telehealth_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `telehealth_sessions`
--

DROP TABLE IF EXISTS `telehealth_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `telehealth_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `appointment_id` bigint unsigned NOT NULL,
  `zoom_meeting_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `join_url` text COLLATE utf8mb4_unicode_ci,
  `host_start_url` text COLLATE utf8mb4_unicode_ci,
  `start_time` datetime NOT NULL,
  `duration` smallint unsigned NOT NULL DEFAULT '30',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'NOT_CONFIGURED',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `telehealth_sessions_appointment_id_unique` (`appointment_id`),
  UNIQUE KEY `telehealth_sessions_zoom_meeting_id_unique` (`zoom_meeting_id`),
  CONSTRAINT `telehealth_sessions_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `telehealth_sessions`
--

LOCK TABLES `telehealth_sessions` WRITE;
/*!40000 ALTER TABLE `telehealth_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `telehealth_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triage_assessments`
--

DROP TABLE IF EXISTS `triage_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `triage_assessments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `er_visit_id` bigint unsigned NOT NULL,
  `triage_nurse_id` bigint unsigned NOT NULL,
  `triaged_at` datetime NOT NULL,
  `chief_complaint` text COLLATE utf8mb4_unicode_ci,
  `pain_score` tinyint unsigned DEFAULT NULL,
  `priority` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'COMPLETE',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `triage_assessments_er_visit_id_unique` (`er_visit_id`),
  KEY `triage_assessments_triage_nurse_id_foreign` (`triage_nurse_id`),
  CONSTRAINT `triage_assessments_er_visit_id_foreign` FOREIGN KEY (`er_visit_id`) REFERENCES `er_visits` (`id`) ON DELETE CASCADE,
  CONSTRAINT `triage_assessments_triage_nurse_id_foreign` FOREIGN KEY (`triage_nurse_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triage_assessments`
--

LOCK TABLES `triage_assessments` WRITE;
/*!40000 ALTER TABLE `triage_assessments` DISABLE KEYS */;
INSERT INTO `triage_assessments` VALUES (1,1,5,'2026-08-06 15:48:03','Chest pain',7,'Level 2','Stable, monitoring','COMPLETE','2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `triage_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triage_vitals`
--

DROP TABLE IF EXISTS `triage_vitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `triage_vitals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `triage_assessment_id` bigint unsigned NOT NULL,
  `blood_pressure` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heart_rate` smallint unsigned DEFAULT NULL,
  `respiratory_rate` smallint unsigned DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `spo2` decimal(5,2) DEFAULT NULL,
  `weight` decimal(6,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `triage_vitals_triage_assessment_id_foreign` (`triage_assessment_id`),
  CONSTRAINT `triage_vitals_triage_assessment_id_foreign` FOREIGN KEY (`triage_assessment_id`) REFERENCES `triage_assessments` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triage_vitals`
--

LOCK TABLES `triage_vitals` WRITE;
/*!40000 ALTER TABLE `triage_vitals` DISABLE KEYS */;
INSERT INTO `triage_vitals` VALUES (1,1,'140/90',100,20,37.5,97.00,NULL,'2026-08-06 08:38:04','2026-08-06 08:38:04');
/*!40000 ALTER TABLE `triage_vitals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Admin','super.admin@coor.test','2026-08-06 08:38:01','$2y$12$dks0bKy1umxlzt9Qj3rj.eVHCdMYOPt9fAGBeJklDn8cSbsaIEIYm',NULL,'2026-08-06 08:38:01','2026-08-06 08:38:01'),(2,'Hospital Admin','hospital.admin@coor.test','2026-08-06 08:38:01','$2y$12$B8TmVYeRCE8hzutld1HwvuNaF.1RhHwq9ML2HOw1EPRhZepb6fS9W',NULL,'2026-08-06 08:38:01','2026-08-06 08:38:01'),(3,'Registration Staff','registration@coor.test','2026-08-06 08:38:01','$2y$12$qXRDKNYbnSD2atGKUzhpyOAk27wsF03hHQWsjgbgKRDNTOIXEkJHC',NULL,'2026-08-06 08:38:01','2026-08-06 08:38:01'),(4,'Dr. Elena Santos','doctor@coor.test','2026-08-06 08:38:01','$2y$12$iNDkOgvcNHpgubu2dgIehu9iBrEfS7V2tGhlX0NK4C2yNRf.5a3eC',NULL,'2026-08-06 08:38:01','2026-08-06 08:38:01'),(5,'Nurse Ana Reyes','nurse@coor.test','2026-08-06 08:38:02','$2y$12$pKZCRT9RjVrn.Otajn4rDO1LMPM5/rRzqnQjR4IL9NfpIaLI8G0iW',NULL,'2026-08-06 08:38:02','2026-08-06 08:38:02'),(6,'ER Staff','er.staff@coor.test','2026-08-06 08:38:02','$2y$12$YDBhQQ6Lvt3eBZLr614.IOATjmaP7KcaA7vXRCDfWjtwVjMMSgCOq',NULL,'2026-08-06 08:38:02','2026-08-06 08:38:02'),(7,'Admission Staff','admission@coor.test','2026-08-06 08:38:02','$2y$12$X8MvvEbK2OodRF/xi1Ott.DoAIa4v5CncXGnHZXq1fskumD2G4G26',NULL,'2026-08-06 08:38:02','2026-08-06 08:38:02'),(8,'Maria Santos','patient@coor.test','2026-08-06 08:38:03','$2y$12$68Y79Xv99Xu/3TIH9.0gWOSGXqelXHb8nQ5JcQLboez4RCgP7P/9W',NULL,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `vitals`
--

DROP TABLE IF EXISTS `vitals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `vitals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `encounter_id` bigint unsigned DEFAULT NULL,
  `patient_id` bigint unsigned NOT NULL,
  `recorded_by` bigint unsigned DEFAULT NULL,
  `blood_pressure` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `heart_rate` smallint unsigned DEFAULT NULL,
  `respiratory_rate` smallint unsigned DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `spo2` decimal(5,2) DEFAULT NULL,
  `weight` decimal(6,2) DEFAULT NULL,
  `pain_score` tinyint unsigned DEFAULT NULL,
  `recorded_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vitals_encounter_id_foreign` (`encounter_id`),
  KEY `vitals_patient_id_foreign` (`patient_id`),
  KEY `vitals_recorded_by_foreign` (`recorded_by`),
  CONSTRAINT `vitals_encounter_id_foreign` FOREIGN KEY (`encounter_id`) REFERENCES `encounters` (`id`) ON DELETE SET NULL,
  CONSTRAINT `vitals_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `vitals_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `vitals`
--

LOCK TABLES `vitals` WRITE;
/*!40000 ALTER TABLE `vitals` DISABLE KEYS */;
/*!40000 ALTER TABLE `vitals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `waitlists`
--

DROP TABLE IF EXISTS `waitlists`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `waitlists` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `patient_id` bigint unsigned NOT NULL,
  `provider_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `appointment_type_id` bigint unsigned DEFAULT NULL,
  `preferred_date` date DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'WAITING',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `waitlists_patient_id_foreign` (`patient_id`),
  KEY `waitlists_provider_id_foreign` (`provider_id`),
  KEY `waitlists_department_id_foreign` (`department_id`),
  KEY `waitlists_appointment_type_id_foreign` (`appointment_type_id`),
  CONSTRAINT `waitlists_appointment_type_id_foreign` FOREIGN KEY (`appointment_type_id`) REFERENCES `appointment_types` (`id`) ON DELETE SET NULL,
  CONSTRAINT `waitlists_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `waitlists_patient_id_foreign` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`),
  CONSTRAINT `waitlists_provider_id_foreign` FOREIGN KEY (`provider_id`) REFERENCES `providers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `waitlists`
--

LOCK TABLES `waitlists` WRITE;
/*!40000 ALTER TABLE `waitlists` DISABLE KEYS */;
/*!40000 ALTER TABLE `waitlists` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `wards`
--

DROP TABLE IF EXISTS `wards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wards_code_unique` (`code`),
  UNIQUE KEY `wards_name_unique` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `wards`
--

LOCK TABLES `wards` WRITE;
/*!40000 ALTER TABLE `wards` DISABLE KEYS */;
INSERT INTO `wards` VALUES (1,'MED','Medical Ward','General',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(2,'PED','Pediatric Ward','Pediatric',1,'2026-08-06 08:38:03','2026-08-06 08:38:03'),(3,'ICU','Intensive Care Unit','ICU',1,'2026-08-06 08:38:03','2026-08-06 08:38:03');
/*!40000 ALTER TABLE `wards` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-08-07  1:22:42
