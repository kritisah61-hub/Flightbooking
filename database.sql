-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: flightms
-- ------------------------------------------------------
-- Server version	8.0.45

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
-- Table structure for table `airlines`
--

DROP TABLE IF EXISTS `airlines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `airlines` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `iata_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `airlines_iata_code_unique` (`iata_code`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `airlines`
--

LOCK TABLES `airlines` WRITE;
/*!40000 ALTER TABLE `airlines` DISABLE KEYS */;
INSERT INTO `airlines` VALUES (1,'INDIGO','6E','INDIA','2026-03-01 01:37:18','2026-03-01 01:37:18'),(2,'AIR INDIA','AI','INDIA','2026-03-01 01:37:34','2026-03-01 01:37:34'),(3,'SPICEJET','SG','INDIA','2026-03-01 01:37:59','2026-03-01 01:37:59'),(4,'EMIRATES','EX','UAE','2026-04-22 08:04:14','2026-04-22 08:04:14'),(6,'SKY JET','AD','INDIA','2026-04-22 09:23:59','2026-04-22 09:24:17');
/*!40000 ALTER TABLE `airlines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bookings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `passenger_id` bigint unsigned NOT NULL,
  `flight_id` bigint unsigned NOT NULL,
  `booking_date` date NOT NULL,
  `status` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `seat_no` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_tickets` int NOT NULL DEFAULT '1',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `passenger_id` (`passenger_id`),
  KEY `flight_id` (`flight_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES (1,2,1,'2026-04-22','Pending','4C',1,5000.00,'2026-04-22 08:47:04','2026-04-22 08:47:04'),(2,5,3,'2026-04-22','paid','1A',1,30000.00,'2026-04-22 08:47:31','2026-04-22 08:47:52'),(3,1,2,'2026-04-22','Cancelled','3C',1,32000.00,'2026-04-22 09:09:55','2026-04-22 16:34:55'),(4,3,1,'2026-04-22','paid','2B',1,5000.00,'2026-04-22 09:21:07','2026-04-22 09:21:44'),(5,7,1,'2026-04-22','paid','4A',1,5000.00,'2026-04-22 09:21:26','2026-04-22 09:21:34'),(6,2,1,'2026-04-26','paid','1B',1,5000.00,'2026-04-26 16:26:26','2026-04-26 16:27:00');
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `flights`
--

DROP TABLE IF EXISTS `flights`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `flights` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `flight_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `airline_id` bigint unsigned NOT NULL,
  `source_airport` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `destination_airport` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `scheduled_departure` datetime NOT NULL,
  `scheduled_arrival` datetime NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Scheduled',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `flights_flight_number_unique` (`flight_number`),
  KEY `flights_airline_id_foreign` (`airline_id`),
  CONSTRAINT `flights_airline_id_foreign` FOREIGN KEY (`airline_id`) REFERENCES `airlines` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `flights`
--

LOCK TABLES `flights` WRITE;
/*!40000 ALTER TABLE `flights` DISABLE KEYS */;
INSERT INTO `flights` VALUES (1,'12345',1,'PUNE','DELHI',5000.00,'2026-06-25 10:00:00','2026-06-25 12:00:00','On Time','2026-03-01 00:37:43','2026-04-22 08:45:39'),(2,'23456',1,'DELHI','WASHINGTON DC',32000.00,'2026-04-25 10:00:00','2026-04-26 08:00:00','On Time','2026-02-28 23:17:39','2026-04-22 08:42:25'),(3,'67890',3,'MUMBAI','SYDNEY',30000.00,'2026-04-25 17:01:00','2026-04-26 01:38:00','On Time','2026-02-28 23:19:10','2026-04-22 08:42:32');
/*!40000 ALTER TABLE `flights` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `logs`
--

DROP TABLE IF EXISTS `logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `description` text COLLATE utf8mb4_unicode_ci,
  `model` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `document_id` bigint unsigned DEFAULT NULL,
  `document_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `logs`
--

LOCK TABLES `logs` WRITE;
/*!40000 ALTER TABLE `logs` DISABLE KEYS */;
INSERT INTO `logs` VALUES (1,'SYSTEM','2026-04-22 08:00:05','Passenger updated','UPDATE',1,'passengers','2026-04-22 08:00:05','2026-04-22 08:00:05'),(2,'SYSTEM','2026-04-22 08:02:10','Passenger created','INSERT',7,'passengers','2026-04-22 08:02:10','2026-04-22 08:02:10'),(3,'SYSTEM','2026-04-22 08:08:47','Booking status changed from \"Pending\" to \"Cancelled\"','UPDATE',33,'bookings','2026-04-22 08:08:47','2026-04-22 08:08:47'),(4,'SYSTEM','2026-04-22 08:10:55','Booking status changed from \"Pending\" to \"paid\"','UPDATE',39,'bookings','2026-04-22 08:10:55','2026-04-22 08:10:55'),(5,'SYSTEM','2026-04-22 08:11:18','Booking status changed from \"Pending\" to \"paid\"','UPDATE',19,'bookings','2026-04-22 08:11:18','2026-04-22 08:11:18'),(6,'SYSTEM','2026-04-24 11:48:35','Passenger updated','UPDATE',1,'passengers','2026-04-24 11:48:35','2026-04-24 11:48:35'),(7,'SYSTEM','2026-04-24 11:48:45','Passenger updated','UPDATE',2,'passengers','2026-04-24 11:48:45','2026-04-24 11:48:45'),(8,'SYSTEM','2026-04-24 11:48:54','Passenger updated','UPDATE',3,'passengers','2026-04-24 11:48:54','2026-04-24 11:48:54'),(9,'SYSTEM','2026-04-24 11:49:09','Passenger updated','UPDATE',5,'passengers','2026-04-24 11:49:09','2026-04-24 11:49:09'),(10,'SYSTEM','2026-04-24 11:49:19','Passenger updated','UPDATE',7,'passengers','2026-04-24 11:49:19','2026-04-24 11:49:19');
/*!40000 ALTER TABLE `logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passengers`
--

DROP TABLE IF EXISTS `passengers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `passengers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `passport_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `passengers_email_unique` (`email`),
  UNIQUE KEY `passengers_passport_number_unique` (`passport_number`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `passengers`
--

LOCK TABLES `passengers` WRITE;
/*!40000 ALTER TABLE `passengers` DISABLE KEYS */;
INSERT INTO `passengers` VALUES (1,'MS','Dhoni','msd@gmail.com','09423508743','2005-11-05','A123B456C','$2y$12$bMUueBDvBQlTqXrnd32i/ecjCYE5RlBdlsG1vEI2gKr/R1r1naX4O','2026-03-04 23:11:08','2026-04-24 11:48:35'),(2,'Hardik','Pandya','hardik@gmail.com','2038472984','2002-04-18','sdjfhih329u','$2y$12$0pcNLBCXrwdiCfMHpb1ureQ06vNQwj2D.gtL3g29yXzNATv.qtD5a','2026-03-05 05:38:00','2026-04-24 11:48:45'),(3,'Riya','Patil','riya@gmail.com','9370641932','1982-06-30','ABC1231','$2y$12$aMX/k7uCk74d/BkVC42FjOAcKLLa2PNAZes2Svw450gQC/h9Wm3Xm','2026-03-09 20:30:54','2026-04-24 11:48:54'),(5,'Tejaswi','Singh','tejaswi@gmail.com','8797190702','2005-11-05','123456','$2y$12$YWlsChic.l4SDM6arGOMau5B6G8W0uGjVvwF/JPlKeYpCdo3B21x.','2026-03-14 20:46:13','2026-04-24 11:49:09'),(7,'Sachin','Tendulkar','sachin@gmail.com','9699015964','2006-02-16','A123B456D','$2y$10$n35vVWH/cT0rlMPDYy2Ok./Fiezlmc52KEroPiO7pJ/CU7gL2jBMy','2026-04-22 08:02:10','2026-04-24 11:49:19');
/*!40000 ALTER TABLE `passengers` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_log_passenger_insert` AFTER INSERT ON `passengers` FOR EACH ROW BEGIN
    INSERT INTO `logs` (`link`, `time`, `description`, `model`, `document_id`, `document_type`, `created_at`, `updated_at`)
    VALUES ('SYSTEM', NOW(), 'Passenger created', 'INSERT', NEW.id, 'passengers', NOW(), NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_log_passenger_update` AFTER UPDATE ON `passengers` FOR EACH ROW BEGIN
    INSERT INTO `logs` (`link`, `time`, `description`, `model`, `document_id`, `document_type`, `created_at`, `updated_at`)
    VALUES ('SYSTEM', NOW(), 'Passenger updated', 'UPDATE', NEW.id, 'passengers', NOW(), NOW());
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `amount` decimal(8,2) NOT NULL,
  `payment_date` datetime NOT NULL,
  `payment_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Completed',
  `payment_reference` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `payments_payment_reference_unique` (`payment_reference`),
  KEY `payments_booking_id_foreign` (`booking_id`),
  CONSTRAINT `payments_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES (31,2,30000.00,'2026-04-22 14:17:52','Card','Completed','VC561DHBPS34','2026-04-22 08:47:52','2026-04-22 08:47:52'),(32,5,5000.00,'2026-04-22 14:51:34','Online','Completed','NY3U2L0RBW5X','2026-04-22 09:21:34','2026-04-22 09:21:34'),(33,4,5000.00,'2026-04-22 14:51:44','Card','Completed','L7ZVT91JRA34','2026-04-22 09:21:44','2026-04-22 09:21:44'),(34,6,5000.00,'2026-04-26 21:57:00','Cash','Completed','WDGBFC56NM7R','2026-04-26 16:27:00','2026-04-26 16:27:00');
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;
/*!50003 SET @saved_cs_client      = @@character_set_client */ ;
/*!50003 SET @saved_cs_results     = @@character_set_results */ ;
/*!50003 SET @saved_col_connection = @@collation_connection */ ;
/*!50003 SET character_set_client  = cp850 */ ;
/*!50003 SET character_set_results = cp850 */ ;
/*!50003 SET collation_connection  = cp850_general_ci */ ;
/*!50003 SET @saved_sql_mode       = @@sql_mode */ ;
/*!50003 SET sql_mode              = 'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION' */ ;
DELIMITER ;;
/*!50003 CREATE*/ /*!50017 DEFINER=`root`@`localhost`*/ /*!50003 TRIGGER `trg_payment_update_booking` AFTER INSERT ON `payments` FOR EACH ROW BEGIN
    IF NEW.status = 'Completed' THEN
        UPDATE `bookings`
        SET    `status`     = 'paid',
               `updated_at` = NOW()
        WHERE  `id`         = NEW.booking_id;
    END IF;
END */;;
DELIMITER ;
/*!50003 SET sql_mode              = @saved_sql_mode */ ;
/*!50003 SET character_set_client  = @saved_cs_client */ ;
/*!50003 SET character_set_results = @saved_cs_results */ ;
/*!50003 SET collation_connection  = @saved_col_connection */ ;

--
-- Table structure for table `staff`
--

DROP TABLE IF EXISTS `staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `staff` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `assigned_flight_id` bigint unsigned DEFAULT NULL,
  `employee_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `staff_employee_number_unique` (`employee_number`),
  KEY `staff_assigned_flight_id_foreign` (`assigned_flight_id`),
  CONSTRAINT `staff_assigned_flight_id_foreign` FOREIGN KEY (`assigned_flight_id`) REFERENCES `flights` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `staff`
--

LOCK TABLES `staff` WRITE;
/*!40000 ALTER TABLE `staff` DISABLE KEYS */;
INSERT INTO `staff` VALUES (1,'Sunil','Gavaskar','Pilot',1,'7862','2026-03-10 01:59:02','2026-04-24 11:49:31'),(4,'Kapil','Dev','Co-Pilot',2,'6868','2026-03-09 23:20:52','2026-04-24 11:49:39'),(5,'Rishab','Pant','Ground Staff',2,'112233','2026-03-09 18:38:21','2026-04-24 11:49:51');
/*!40000 ALTER TABLE `staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tickets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `booking_id` bigint unsigned NOT NULL,
  `seat_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fare` decimal(8,2) NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Issued',
  `issued_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tickets_booking_id_foreign` (`booking_id`),
  CONSTRAINT `tickets_booking_id_foreign` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-08 23:46:56
