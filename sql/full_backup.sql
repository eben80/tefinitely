-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: french_practice
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.24.04.2

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
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `phrases`
--

DROP TABLE IF EXISTS `phrases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `phrases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `french_text` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `english_translation` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `theme` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `section` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'general',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phrases`
--

LOCK TABLES `phrases` WRITE;
/*!40000 ALTER TABLE `phrases` DISABLE KEYS */;
INSERT INTO `phrases` VALUES (1,'Bonjour, je vous appelle au sujet de l’annonce.','Hello, I am calling about the advertisement.','daily_life','section_a'),(2,'Pourriez-vous me donner plus d’informations ?','Could you give me more information?','daily_life','section_a'),(3,'Quels sont les horaires d’ouverture ?','What are the opening hours?','daily_life','section_a'),(4,'Est-ce que le lieu est accessible en transport en commun ?','Is the place accessible by public transport?','daily_life','section_a'),(5,'Y a-t-il un parking à proximité ?','Is there parking nearby?','daily_life','section_a'),(6,'Quel est le prix du service ?','What is the price of the service?','daily_life','section_a'),(7,'Est-ce que je dois prendre rendez-vous ?','Do I need to make an appointment?','daily_life','section_a'),(8,'Combien de temps dure la séance ?','How long does the session last?','daily_life','section_a'),(9,'Acceptez-vous les paiements par carte ?','Do you accept card payments?','daily_life','section_a'),(10,'À quelle adresse se trouve l’entreprise ?','What is the company\'s address?','daily_life','section_a'),(11,'Puis-je parler à la personne responsable ?','May I speak to the person in charge?','daily_life','section_a'),(12,'Y a-t-il des réductions pour les étudiants ?','Are there discounts for students?','daily_life','section_a'),(13,'Quels documents dois-je apporter ?','What documents should I bring?','daily_life','section_a'),(14,'Est-ce que c’est possible de changer la date ?','Is it possible to change the date?','daily_life','section_a'),(15,'Puis-je avoir un numéro de téléphone pour vous joindre ?','Can I have a phone number to reach you?','daily_life','section_a'),(16,'Bonjour, je suis intéressé par l’appartement à louer.','Hello, I am interested in the apartment for rent.','housing','section_a'),(17,'Combien de chambres y a-t-il ?','How many bedrooms are there?','housing','section_a'),(18,'Est-ce que le loyer inclut les charges ?','Does the rent include utilities?','housing','section_a'),(19,'Y a-t-il une cuisine équipée ?','Is there an equipped kitchen?','housing','section_a'),(20,'Est-ce que les animaux domestiques sont acceptés ?','Are pets allowed?','housing','section_a'),(21,'Quel est le montant de la caution ?','What is the security deposit amount?','housing','section_a'),(22,'Puis-je visiter l’appartement ?','Can I visit the apartment?','housing','section_a'),(23,'À quelle date l’appartement sera-t-il disponible ?','When will the apartment be available?','housing','section_a'),(24,'Y a-t-il un ascenseur dans l’immeuble ?','Is there an elevator in the building?','housing','section_a'),(25,'Comment puis-je déposer ma candidature ?','How can I submit my application?','housing','section_a'),(26,'Le logement est-il meublé ?','Is the accommodation furnished?','housing','section_a'),(27,'Est-ce que le quartier est calme ?','Is the neighborhood quiet?','housing','section_a'),(28,'Quel est le temps de trajet jusqu’au centre-ville ?','What is the commute time to downtown?','housing','section_a'),(29,'Le chauffage est-il inclus ?','Is heating included?','housing','section_a'),(30,'Y a-t-il un parking disponible ?','Is parking available?','housing','section_a'),(31,'Bonjour, je voudrais des informations sur les cours.','Hello, I would like information about the courses.','courses','section_a'),(32,'Quels sont les horaires des cours ?','What are the course schedules?','courses','section_a'),(33,'Combien coûtent les frais d’inscription ?','How much are the registration fees?','courses','section_a'),(34,'Les cours sont-ils en présentiel ou en ligne ?','Are the courses in person or online?','courses','section_a'),(35,'Y a-t-il un certificat à la fin du cours ?','Is there a certificate at the end of the course?','courses','section_a'),(36,'Puis-je suivre un cours d’essai ?','Can I attend a trial class?','courses','section_a'),(37,'Quels sont les prérequis pour s’inscrire ?','What are the prerequisites for enrollment?','courses','section_a'),(38,'Comment puis-je m’inscrire ?','How can I register?','courses','section_a'),(39,'Y a-t-il un nombre limité de places ?','Is there a limited number of spots?','courses','section_a'),(40,'Est-ce que les cours sont adaptés aux débutants ?','Are the courses suitable for beginners?','courses','section_a'),(41,'Quels sont les moyens de paiement acceptés ?','What payment methods are accepted?','courses','section_a'),(42,'Le matériel est-il fourni ?','Is the material provided?','courses','section_a'),(43,'Puis-je changer de groupe si nécessaire ?','Can I change groups if necessary?','courses','section_a'),(44,'Quelle est la durée totale du cours ?','What is the total duration of the course?','courses','section_a'),(45,'Y a-t-il des aides financières disponibles ?','Are financial aids available?','courses','section_a'),(46,'Bonjour, je vous appelle concernant l’offre d’emploi.','Hello, I am calling about the job offer.','jobs','section_a'),(47,'Le poste est-il encore disponible ?','Is the position still available?','jobs','section_a'),(48,'Quelles sont les qualifications requises ?','What qualifications are required?','jobs','section_a'),(49,'Quel est le salaire proposé ?','What is the offered salary?','jobs','section_a'),(50,'Est-ce un contrat à durée déterminée ou indéterminée ?','Is it a fixed-term or permanent contract?','jobs','section_a'),(51,'Quelles sont les horaires de travail ?','What are the working hours?','jobs','section_a'),(52,'Y a-t-il des possibilités d’évolution ?','Are there opportunities for advancement?','jobs','section_a'),(53,'Où se situe le lieu de travail ?','Where is the workplace located?','jobs','section_a'),(54,'Quelles sont les responsabilités principales ?','What are the main responsibilities?','jobs','section_a'),(55,'Quand puis-je commencer ?','When can I start?','jobs','section_a'),(56,'Dois-je fournir des références ?','Do I need to provide references?','jobs','section_a'),(57,'Y a-t-il une période d’essai ?','Is there a trial period?','jobs','section_a'),(58,'Comment se déroule le processus de recrutement ?','How does the recruitment process work?','jobs','section_a'),(59,'Puis-je envoyer mon CV par email ?','Can I send my CV by email?','jobs','section_a'),(60,'À qui dois-je m’adresser pour plus d’informations ?','Who should I contact for more information?','jobs','section_a');
/*!40000 ALTER TABLE `phrases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `paypal_transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscription_start_date` datetime NOT NULL,
  `subscription_end_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_progress`
--

DROP TABLE IF EXISTS `user_progress`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `phrase_id` int NOT NULL,
  `matching_quality` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `phrase_id` (`phrase_id`),
  CONSTRAINT `user_progress_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_progress_ibfk_2` FOREIGN KEY (`phrase_id`) REFERENCES `phrases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=226 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_progress`
--

LOCK TABLES `user_progress` WRITE;
/*!40000 ALTER TABLE `user_progress` DISABLE KEYS */;
INSERT INTO `user_progress` VALUES (1,1,3,0.67,'2025-08-03 11:24:37'),(2,2,31,0.75,'2025-08-03 15:47:04'),(3,2,32,0.57,'2025-08-03 15:47:16'),(4,2,32,0.57,'2025-08-03 15:47:23'),(5,2,32,0.71,'2025-08-03 15:47:30'),(6,2,33,0.50,'2025-08-03 15:47:47'),(7,2,33,0.50,'2025-08-03 15:47:53'),(8,2,33,0.50,'2025-08-03 15:48:02'),(9,2,33,0.33,'2025-08-03 15:48:07'),(10,2,33,0.33,'2025-08-03 15:48:13'),(11,2,33,0.33,'2025-08-03 15:48:22'),(12,2,33,0.33,'2025-08-03 15:48:27'),(13,2,33,0.33,'2025-08-03 15:48:37'),(14,2,33,0.33,'2025-08-03 15:48:46'),(15,2,33,0.33,'2025-08-03 15:48:50'),(16,2,34,0.33,'2025-08-03 15:49:00'),(17,2,34,0.33,'2025-08-03 15:49:09'),(18,2,34,0.78,'2025-08-03 15:49:20'),(19,2,35,0.80,'2025-08-03 15:49:33'),(20,2,36,0.33,'2025-08-03 15:49:44'),(21,2,36,0.33,'2025-08-03 15:49:49'),(22,2,36,0.67,'2025-08-03 15:49:56'),(23,2,37,0.71,'2025-08-03 15:50:10'),(24,2,38,0.50,'2025-08-03 15:50:35'),(25,2,38,0.50,'2025-08-03 15:50:42'),(26,2,38,0.50,'2025-08-03 15:50:46'),(27,2,38,0.50,'2025-08-03 15:50:50'),(28,2,38,0.50,'2025-08-03 15:50:57'),(29,2,39,0.63,'2025-08-03 15:51:08'),(30,2,40,0.22,'2025-08-03 15:53:07'),(31,2,40,0.33,'2025-08-03 15:53:12'),(32,2,40,0.44,'2025-08-03 15:53:18'),(33,2,40,0.56,'2025-08-03 15:53:29'),(34,2,40,0.89,'2025-08-03 15:53:34'),(35,2,41,0.75,'2025-08-03 15:53:56'),(36,2,42,0.40,'2025-08-03 15:54:13'),(37,2,42,0.40,'2025-08-03 15:54:22'),(38,2,42,0.80,'2025-08-03 15:54:26'),(39,2,43,0.86,'2025-08-03 15:54:40'),(40,2,44,0.63,'2025-08-03 15:54:49'),(41,2,45,0.86,'2025-08-03 15:55:08'),(42,2,1,0.63,'2025-08-03 15:56:00'),(43,2,2,0.50,'2025-08-03 15:56:14'),(44,2,2,0.67,'2025-08-03 15:56:20'),(45,2,3,0.67,'2025-08-03 15:56:36'),(46,2,4,0.82,'2025-08-03 15:56:56'),(47,2,5,0.86,'2025-08-03 15:57:09'),(48,2,6,0.71,'2025-08-03 15:57:27'),(49,2,7,0.86,'2025-08-03 15:57:38'),(50,2,8,0.86,'2025-08-03 15:57:52'),(51,2,9,0.50,'2025-08-03 15:58:06'),(52,2,9,0.50,'2025-08-03 15:58:11'),(53,2,9,0.83,'2025-08-03 15:58:16'),(54,2,10,0.71,'2025-08-03 15:58:26'),(55,2,11,0.86,'2025-08-03 15:58:37'),(56,2,12,0.88,'2025-08-03 15:58:46'),(57,2,13,0.40,'2025-08-03 15:58:56'),(58,2,13,0.40,'2025-08-03 15:59:01'),(59,2,13,0.40,'2025-08-03 15:59:06'),(60,2,13,0.40,'2025-08-03 15:59:13'),(61,2,14,0.78,'2025-08-03 15:59:24'),(62,2,15,0.60,'2025-08-03 15:59:33'),(63,2,15,0.70,'2025-08-03 15:59:39'),(64,2,16,0.63,'2025-08-03 16:00:13'),(65,2,17,0.67,'2025-08-03 16:00:21'),(66,2,18,0.38,'2025-08-03 16:00:32'),(67,2,18,0.38,'2025-08-03 16:00:37'),(68,2,18,0.13,'2025-08-03 16:00:45'),(69,2,18,0.25,'2025-08-03 16:00:52'),(70,2,19,0.83,'2025-08-03 16:01:05'),(71,2,20,0.88,'2025-08-03 16:01:15'),(72,2,21,0.38,'2025-08-03 16:01:25'),(73,2,21,0.50,'2025-08-03 16:01:30'),(74,2,21,0.88,'2025-08-03 16:01:37'),(75,2,22,0.50,'2025-08-03 16:01:46'),(76,2,22,0.50,'2025-08-03 16:01:50'),(77,2,22,0.50,'2025-08-03 16:01:55'),(78,2,22,0.00,'2025-08-03 16:02:03'),(79,2,22,0.25,'2025-08-03 16:02:07'),(80,2,23,0.71,'2025-08-03 16:02:15'),(81,2,24,0.57,'2025-08-03 16:02:23'),(82,2,24,0.71,'2025-08-03 16:02:27'),(83,2,25,0.83,'2025-08-03 16:02:35'),(84,2,26,0.00,'2025-08-03 16:02:43'),(85,2,26,0.20,'2025-08-03 16:02:47'),(86,2,26,0.20,'2025-08-03 16:02:52'),(87,2,26,0.40,'2025-08-03 16:02:58'),(88,2,27,0.29,'2025-08-03 16:03:05'),(89,2,27,0.29,'2025-08-03 16:03:10'),(90,2,27,0.29,'2025-08-03 16:03:17'),(91,2,27,0.86,'2025-08-03 16:03:21'),(92,2,28,0.78,'2025-08-03 16:03:30'),(93,2,29,0.40,'2025-08-03 16:03:42'),(94,2,29,0.20,'2025-08-03 16:03:46'),(95,2,29,0.20,'2025-08-03 16:04:07'),(96,2,30,0.83,'2025-08-03 16:04:15'),(97,2,46,0.57,'2025-08-03 16:05:07'),(98,2,46,0.57,'2025-08-03 16:05:13'),(99,2,46,0.57,'2025-08-03 16:05:24'),(100,2,46,0.57,'2025-08-03 16:05:30'),(101,2,46,0.57,'2025-08-03 16:05:38'),(102,2,46,0.57,'2025-08-03 16:05:48'),(103,2,47,0.50,'2025-08-03 16:05:56'),(104,2,47,0.50,'2025-08-03 16:06:01'),(105,2,47,0.50,'2025-08-03 16:06:07'),(106,2,47,0.67,'2025-08-03 16:06:16'),(107,2,48,0.83,'2025-08-03 16:06:24'),(108,2,49,0.67,'2025-08-03 16:06:31'),(109,2,50,0.33,'2025-08-03 16:06:42'),(110,2,50,0.56,'2025-08-03 16:06:53'),(111,2,50,0.78,'2025-08-03 16:07:00'),(112,2,51,0.71,'2025-08-03 16:07:08'),(113,2,52,0.67,'2025-08-03 16:07:17'),(114,2,53,0.75,'2025-08-03 16:07:28'),(115,2,54,0.67,'2025-08-03 16:07:35'),(116,2,55,0.75,'2025-08-03 16:07:42'),(117,2,56,0.60,'2025-08-03 16:07:50'),(118,2,56,0.80,'2025-08-03 16:07:54'),(119,2,57,0.67,'2025-08-03 16:08:01'),(120,2,58,0.25,'2025-08-03 16:08:10'),(121,2,58,0.63,'2025-08-03 16:08:16'),(122,2,59,0.57,'2025-08-03 16:08:24'),(123,2,59,0.86,'2025-08-03 16:08:29'),(124,2,60,0.63,'2025-08-03 16:08:39'),(125,2,1,0.63,'2025-08-05 18:53:08'),(126,2,2,0.67,'2025-08-05 18:53:21'),(127,2,3,0.67,'2025-08-05 18:53:38'),(128,2,4,0.45,'2025-08-05 18:53:51'),(129,2,4,0.27,'2025-08-05 18:53:58'),(130,2,4,0.18,'2025-08-05 18:54:15'),(131,2,4,0.45,'2025-08-05 18:54:22'),(132,2,4,0.45,'2025-08-05 18:54:39'),(133,2,4,0.73,'2025-08-05 18:54:48'),(134,2,5,0.71,'2025-08-05 18:54:56'),(135,2,6,0.71,'2025-08-05 18:55:03'),(136,2,7,0.29,'2025-08-05 18:55:10'),(137,2,7,0.86,'2025-08-05 18:55:18'),(138,2,8,0.71,'2025-08-05 18:55:26'),(139,2,9,0.50,'2025-08-05 18:55:35'),(140,2,9,0.83,'2025-08-05 18:55:41'),(141,2,10,0.43,'2025-08-05 18:55:50'),(142,2,10,0.57,'2025-08-05 18:55:59'),(143,2,10,0.71,'2025-08-05 18:56:13'),(144,2,11,0.71,'2025-08-05 18:56:20'),(145,2,12,0.88,'2025-08-05 18:56:27'),(146,2,13,0.40,'2025-08-05 18:56:35'),(147,2,13,0.40,'2025-08-05 18:56:43'),(148,2,13,0.40,'2025-08-05 18:56:51'),(149,2,13,0.40,'2025-08-05 18:56:59'),(150,2,14,0.78,'2025-08-05 18:57:07'),(151,2,15,0.70,'2025-08-05 18:57:16'),(152,2,13,0.40,'2025-08-05 18:58:39'),(153,2,13,0.40,'2025-08-05 18:58:50'),(154,2,13,0.40,'2025-08-05 18:59:02'),(155,2,13,0.40,'2025-08-05 18:59:32'),(156,2,13,0.40,'2025-08-05 18:59:37'),(157,2,31,0.75,'2025-08-05 19:00:08'),(158,2,32,0.71,'2025-08-05 19:00:58'),(159,2,33,0.50,'2025-08-05 19:01:14'),(160,2,33,0.67,'2025-08-05 19:01:21'),(161,2,34,0.22,'2025-08-05 19:01:40'),(162,2,34,0.11,'2025-08-05 19:01:54'),(163,2,34,0.78,'2025-08-05 19:02:02'),(164,2,35,0.70,'2025-08-05 19:02:32'),(165,2,36,0.33,'2025-08-05 19:03:00'),(166,2,36,0.33,'2025-08-05 19:03:06'),(167,2,36,0.67,'2025-08-05 19:03:13'),(168,2,37,0.71,'2025-08-05 19:03:29'),(169,2,37,0.57,'2025-08-05 19:04:36'),(170,2,37,0.43,'2025-08-05 19:04:43'),(171,2,37,0.71,'2025-08-05 19:04:51'),(172,2,38,0.50,'2025-08-05 19:05:11'),(173,2,38,0.50,'2025-08-05 19:05:15'),(174,2,38,0.50,'2025-08-05 19:05:23'),(175,2,38,0.50,'2025-08-05 19:05:26'),(176,2,39,0.75,'2025-08-05 19:05:39'),(177,2,40,0.56,'2025-08-05 19:05:54'),(178,2,40,0.78,'2025-08-05 19:06:02'),(179,2,41,0.75,'2025-08-05 19:06:20'),(180,2,42,0.20,'2025-08-05 19:07:30'),(181,2,42,0.40,'2025-08-05 19:07:35'),(182,2,42,0.40,'2025-08-05 19:07:45'),(183,2,43,0.86,'2025-08-05 19:07:54'),(184,2,44,0.63,'2025-08-05 19:08:08'),(185,2,45,0.86,'2025-08-05 19:08:30'),(186,2,46,0.43,'2025-08-05 19:08:56'),(187,2,46,0.57,'2025-08-05 19:09:04'),(188,2,46,0.57,'2025-08-05 19:09:14'),(189,2,46,0.57,'2025-08-05 19:09:20'),(190,2,46,0.43,'2025-08-05 19:09:31'),(191,2,47,0.50,'2025-08-05 19:09:40'),(192,2,47,0.67,'2025-08-05 19:09:46'),(193,2,47,0.50,'2025-08-05 19:09:56'),(194,2,47,0.00,'2025-08-05 19:10:06'),(195,2,47,0.67,'2025-08-05 19:10:12'),(196,2,48,0.33,'2025-08-05 19:10:21'),(197,2,48,0.67,'2025-08-05 19:10:26'),(198,2,49,0.33,'2025-08-05 19:10:43'),(199,2,49,0.50,'2025-08-05 19:10:50'),(200,2,49,0.50,'2025-08-05 19:10:57'),(201,2,49,0.50,'2025-08-05 19:11:02'),(202,2,49,0.83,'2025-08-05 19:11:07'),(203,2,50,0.56,'2025-08-05 19:11:23'),(204,2,50,0.33,'2025-08-05 19:11:33'),(205,2,50,0.11,'2025-08-05 19:11:40'),(206,2,50,0.67,'2025-08-05 19:11:48'),(207,2,51,0.71,'2025-08-05 19:11:56'),(208,2,52,0.67,'2025-08-05 19:12:04'),(209,2,52,0.67,'2025-08-05 19:12:13'),(210,2,52,0.67,'2025-08-05 19:12:22'),(211,2,52,0.67,'2025-08-05 19:12:35'),(212,2,53,0.63,'2025-08-05 19:12:54'),(213,2,54,0.67,'2025-08-05 19:13:11'),(214,2,55,0.50,'2025-08-05 19:13:25'),(215,2,55,0.75,'2025-08-05 19:13:29'),(216,2,56,0.80,'2025-08-05 19:13:42'),(217,2,57,0.67,'2025-08-05 19:14:00'),(218,2,57,0.67,'2025-08-05 19:14:11'),(219,2,58,0.75,'2025-08-05 19:14:35'),(220,2,59,0.29,'2025-08-05 19:14:46'),(221,2,59,0.29,'2025-08-05 19:14:52'),(222,2,59,0.71,'2025-08-05 19:15:02'),(223,2,60,0.13,'2025-08-05 19:15:18'),(224,2,60,0.63,'2025-08-05 19:15:25'),(225,2,17,0.67,'2025-08-05 19:43:35');
/*!40000 ALTER TABLE `user_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `subscription_status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `tour_completed` tinyint(1) NOT NULL DEFAULT '0',
  `last_topic` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_card_index` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Eben','van Ellewee','sbmail246@gmail.com','$2y$10$kvBLG4SMi3uPgdTXRpWPROebKm8xazh0ph9fa7CwQ92II2fcZjQZS','admin','active','2025-08-03 11:16:03',1,'section_a-daily_life',3),(2,'Eben','van Ellewee','ebenvanellewee@gmail.com','$2y$10$SRhdc9nBvPmldt11/eAZ0ec.MqzLeJ/NeYVWUn88tr7QfmhECsIVO','user','active','2025-08-03 11:26:52',1,'section_a-courses',0);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-08-07 10:34:45
