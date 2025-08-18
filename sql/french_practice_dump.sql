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
-- Table structure for table `dialogue_lines`
--

DROP TABLE IF EXISTS `dialogue_lines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialogue_lines` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dialogue_id` int NOT NULL,
  `speaker` varchar(100) NOT NULL,
  `line_text` text NOT NULL,
  `line_order` int NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `dialogue_id` (`dialogue_id`),
  CONSTRAINT `dialogue_lines_ibfk_1` FOREIGN KEY (`dialogue_id`) REFERENCES `dialogues` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialogue_lines`
--

LOCK TABLES `dialogue_lines` WRITE;
/*!40000 ALTER TABLE `dialogue_lines` DISABLE KEYS */;
INSERT INTO `dialogue_lines` VALUES (1,1,'Student','Bonjour, je viens de lire votre annonce concernant la cueillette de fruits.',1,'2025-08-07 17:39:56'),(2,1,'Owner','Bonjour! Oui, absolument. Que souhaitez-vous savoir?',2,'2025-08-07 17:39:56'),(3,1,'Student','J’aurais quelques questions. D\'abord, où êtes-vous situé exactement?',3,'2025-08-07 17:39:56'),(4,1,'Owner','Nous sommes situés à 15 minutes de la ville, sur la route de campagne 12.',4,'2025-08-07 17:39:56'),(5,1,'Student','Parfait. Et est-ce que les enfants peuvent participer?',5,'2025-08-07 17:39:56'),(6,1,'Owner','Bien sûr, c\'est une activité familiale!',6,'2025-08-07 17:39:56'),(7,2,'Student','Bonjour, je vous appelle au sujet de l\'annonce pour les cours de poterie. J\'aimerais avoir quelques informations.',1,'2025-08-15 23:50:10'),(8,2,'Instructor','Bonjour ! Avec plaisir. Que voulez-vous savoir ?',2,'2025-08-15 23:50:10'),(9,2,'Student','Pour commencer, quels sont les horaires des cours pour débutants ?',3,'2025-08-15 23:50:10'),(10,2,'Instructor','Les cours pour débutants ont lieu le mardi soir de 18h à 20h et le samedi matin de 10h à 12h.',4,'2025-08-15 23:50:10'),(11,2,'Student','D\'accord. Et quel est le tarif pour une session ?',5,'2025-08-15 23:50:10'),(12,2,'Instructor','La session de 8 cours est à 250 $, matériel inclus.',6,'2025-08-15 23:50:10'),(13,2,'Student','Est-ce qu\'il faut apporter quelque chose de spécial ?',7,'2025-08-15 23:50:10'),(14,2,'Instructor','Non, nous fournissons tout. Venez simplement avec des vêtements que vous ne craignez pas de salir !',8,'2025-08-15 23:50:10'),(15,2,'Student','C\'est bon à savoir. Le cours est-il adapté aux vrais débutants ? Je n\'ai jamais fait de poterie.',9,'2025-08-15 23:50:10'),(16,2,'Instructor','Absolument, le cours est conçu pour les débutants. Nous commençons par les bases.',10,'2025-08-15 23:50:10'),(17,2,'Student','Y a-t-il une taille maximale pour les groupes ?',11,'2025-08-15 23:50:10'),(18,2,'Instructor','Oui, nous limitons les groupes à 8 personnes pour garantir une attention personnalisée.',12,'2025-08-15 23:50:10'),(19,2,'Student','Quelle est l\'adresse exacte du studio ?',13,'2025-08-15 23:50:10'),(20,2,'Instructor','Nous sommes au 45 Rue de la Créativité, dans le quartier des artisans.',14,'2025-08-15 23:50:10'),(21,2,'Student','Une dernière question, est-il possible de faire un cours d\'essai avant de s\'engager pour la session complète ?',15,'2025-08-15 23:50:10'),(22,2,'Instructor','Oui, nous proposons un cours d\'essai à 35$. Si vous vous inscrivez ensuite, le prix est déduit du total.',16,'2025-08-15 23:50:10'),(23,3,'Caller','Bonjour, je suis intéressé(e) par l\'appartement à louer que j\'ai vu sur internet. Est-il toujours disponible ?',1,'2025-08-15 23:50:10'),(24,3,'Agent','Bonjour. Oui, l\'appartement au 123 rue de la Paix est toujours disponible.',2,'2025-08-15 23:50:10'),(25,3,'Caller','Pourriez-vous me dire à quel étage il se situe ?',3,'2025-08-15 23:50:10'),(26,3,'Agent','Il est au troisième étage. Il y a un ascenseur dans l\'immeuble.',4,'2025-08-15 23:50:10'),(27,3,'Caller','Très bien. Le loyer inclut-il les charges comme le chauffage et l\'électricité ?',5,'2025-08-15 23:50:10'),(28,3,'Agent','Non, le chauffage et l\'électricité sont à la charge du locataire. L\'eau chaude est incluse.',6,'2025-08-15 23:50:10'),(29,3,'Caller','Est-ce que les animaux de compagnie sont autorisés ? J\'ai un petit chat.',7,'2025-08-15 23:50:10'),(30,3,'Agent','Oui, les petits animaux sont acceptés.',8,'2025-08-15 23:50:10'),(31,3,'Caller','L\'appartement dispose-t-il d\'un balcon ou d\'une terrasse ?',9,'2025-08-15 23:50:10'),(32,3,'Agent','Oui, il y a un petit balcon qui donne sur la cour intérieure.',10,'2025-08-15 23:50:10'),(33,3,'Caller','Y a-t-il une place de parking incluse avec l\'appartement ?',11,'2025-08-15 23:50:10'),(34,3,'Agent','Il n\'y a pas de place de parking attitrée, mais il est possible de louer une place dans le garage souterrain pour 100$ par mois.',12,'2025-08-15 23:50:10'),(35,3,'Caller','Quelle est la durée minimale du bail ?',13,'2025-08-15 23:50:10'),(36,3,'Agent','Le bail est d\'une durée minimale d\'un an.',14,'2025-08-15 23:50:10'),(37,3,'Caller','Quels sont les documents nécessaires pour déposer un dossier ?',15,'2025-08-15 23:50:10'),(38,3,'Agent','Il nous faudra une pièce d\'identité, vos trois derniers bulletins de salaire et une attestation de votre ancien propriétaire si possible.',16,'2025-08-15 23:50:10'),(39,4,'Volunteer','Bonjour, j\'ai vu votre appel à bénévoles pour le festival de musique et je suis très intéressé.',1,'2025-08-15 23:50:10'),(40,4,'Coordinator','Bonjour ! Merci de votre intérêt. Avez-vous des questions ?',2,'2025-08-15 23:50:10'),(41,4,'Volunteer','Oui. Quelles sont les dates exactes du festival ?',3,'2025-08-15 23:50:10'),(42,4,'Coordinator','Le festival se déroulera du 10 au 12 août.',4,'2025-08-15 23:50:10'),(43,4,'Volunteer','Et combien d\'heures de bénévolat sont demandées par jour ?',5,'2025-08-15 23:50:10'),(44,4,'Coordinator','Nous demandons un engagement de 6 heures par jour. En échange, vous avez un accès gratuit à tous les concerts.',6,'2025-08-15 23:50:10'),(45,4,'Volunteer','C\'est super ! Faut-il avoir une expérience particulière ?',7,'2025-08-15 23:50:10'),(46,4,'Coordinator','Aucune expérience n\'est requise pour les postes d\'accueil ou d\'information, mais une expérience en secourisme est un plus.',8,'2025-08-15 23:50:10'),(47,4,'Volunteer','Est-ce que la nourriture et les boissons sont fournies pour les bénévoles ?',9,'2025-08-15 23:50:10'),(48,4,'Coordinator','Oui, un repas par jour et des boissons sont offerts à tous nos bénévoles.',10,'2025-08-15 23:50:10'),(49,4,'Volunteer','Y a-t-il une formation obligatoire avant le festival ?',11,'2025-08-15 23:50:10'),(50,4,'Coordinator','Oui, une réunion d\'information et de formation est prévue le 8 août au soir.',12,'2025-08-15 23:50:10'),(51,4,'Volunteer','Comment les horaires sont-ils attribués ? Peut-on choisir nos postes ?',13,'2025-08-15 23:50:10'),(52,4,'Coordinator','Vous pouvez nous donner vos préférences pour les postes et les horaires, et nous faisons de notre mieux pour accommoder tout le monde.',14,'2025-08-15 23:50:10'),(53,4,'Volunteer','Recevons-nous un T-shirt ou un uniforme du festival ?',15,'2025-08-15 23:50:10'),(54,4,'Coordinator','Oui, chaque bénévole reçoit un T-shirt officiel du festival à porter pendant ses heures de travail.',16,'2025-08-15 23:50:10');
/*!40000 ALTER TABLE `dialogue_lines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dialogues`
--

DROP TABLE IF EXISTS `dialogues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dialogues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dialogue_name` varchar(255) NOT NULL,
  `theme` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dialogues`
--

LOCK TABLES `dialogues` WRITE;
/*!40000 ALTER TABLE `dialogues` DISABLE KEYS */;
INSERT INTO `dialogues` VALUES (1,'Cueillette de fruits','la_vie_de_tous_les_jours','section_a','2025-08-07 17:39:56'),(2,'Inscription à un cours de poterie','les_loisirs','section_a','2025-08-15 23:50:10'),(3,'Location d\'un appartement','le_logement','section_a','2025-08-15 23:50:10'),(4,'Bénévolat pour un festival de musique','la_culture','section_a','2025-08-15 23:50:10');
/*!40000 ALTER TABLE `dialogues` ENABLE KEYS */;
UNLOCK TABLES;

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (1,'ebenvanellewee@gmail.com','bf7c345d2d3ac8733a4800ad2cb9c4ed3a9390db27efba49fcc23254bdf1288a','2025-08-10 19:16:46','2025-08-10 18:16:46'),(2,'ebenvanellewee@gmail.com','7ec929ba34e6b5777f4b37d1d56c8dc79ce1fcc241dcea868c16326159900681','2025-08-10 19:17:27','2025-08-10 18:17:27'),(3,'ebenvanellewee@gmail.com','9cf49b443edfe8314ce07fb7836a6e84a4ac75df632261173d429f4b08d40dd5','2025-08-10 19:28:29','2025-08-10 18:28:29'),(4,'ebenvanellewee@gmail.com','b8087862855daeecce3a1ae8aee9c3ed7bbd4df89c5285915449486708ef2184','2025-08-10 19:28:56','2025-08-10 18:28:56');
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
) ENGINE=InnoDB AUTO_INCREMENT=126 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `phrases`
--

LOCK TABLES `phrases` WRITE;
/*!40000 ALTER TABLE `phrases` DISABLE KEYS */;
INSERT INTO `phrases` VALUES (1,'Bonjour, je vous appelle au sujet de l’annonce.','Hello, I am calling about the advertisement.','daily_life','section_a'),(2,'Pourriez-vous me donner plus d’informations ?','Could you give me more information?','daily_life','section_a'),(3,'Quels sont les horaires d’ouverture ?','What are the opening hours?','daily_life','section_a'),(4,'Est-ce que le lieu est accessible en transport en commun ?','Is the place accessible by public transport?','daily_life','section_a'),(5,'Y a-t-il un parking à proximité ?','Is there parking nearby?','daily_life','section_a'),(6,'Quel est le prix du service ?','What is the price of the service?','daily_life','section_a'),(7,'Est-ce que je dois prendre rendez-vous ?','Do I need to make an appointment?','daily_life','section_a'),(8,'Combien de temps dure la séance ?','How long does the session last?','daily_life','section_a'),(9,'Acceptez-vous les paiements par carte ?','Do you accept card payments?','daily_life','section_a'),(10,'À quelle adresse se trouve l’entreprise ?','What is the company\'s address?','daily_life','section_a'),(11,'Puis-je parler à la personne responsable ?','May I speak to the person in charge?','daily_life','section_a'),(12,'Y a-t-il des réductions pour les étudiants ?','Are there discounts for students?','daily_life','section_a'),(13,'Quels documents dois-je apporter ?','What documents should I bring?','daily_life','section_a'),(14,'Est-ce que c’est possible de changer la date ?','Is it possible to change the date?','daily_life','section_a'),(15,'Puis-je avoir un numéro de téléphone pour vous joindre ?','Can I have a phone number to reach you?','daily_life','section_a'),(16,'Bonjour, je suis intéressé par l’appartement à louer.','Hello, I am interested in the apartment for rent.','housing','section_a'),(17,'Combien de chambres y a-t-il ?','How many bedrooms are there?','housing','section_a'),(18,'Est-ce que le loyer inclut les charges ?','Does the rent include utilities?','housing','section_a'),(19,'Y a-t-il une cuisine équipée ?','Is there an equipped kitchen?','housing','section_a'),(20,'Est-ce que les animaux domestiques sont acceptés ?','Are pets allowed?','housing','section_a'),(21,'Quel est le montant de la caution ?','What is the security deposit amount?','housing','section_a'),(22,'Puis-je visiter l’appartement ?','Can I visit the apartment?','housing','section_a'),(23,'À quelle date l’appartement sera-t-il disponible ?','When will the apartment be available?','housing','section_a'),(24,'Y a-t-il un ascenseur dans l’immeuble ?','Is there an elevator in the building?','housing','section_a'),(25,'Comment puis-je déposer ma candidature ?','How can I submit my application?','housing','section_a'),(26,'Le logement est-il meublé ?','Is the accommodation furnished?','housing','section_a'),(27,'Est-ce que le quartier est calme ?','Is the neighborhood quiet?','housing','section_a'),(28,'Quel est le temps de trajet jusqu’au centre-ville ?','What is the commute time to downtown?','housing','section_a'),(29,'Le chauffage est-il inclus ?','Is heating included?','housing','section_a'),(30,'Y a-t-il un parking disponible ?','Is parking available?','housing','section_a'),(31,'Bonjour, je voudrais des informations sur les cours.','Hello, I would like information about the courses.','courses','section_a'),(32,'Quels sont les horaires des cours ?','What are the course schedules?','courses','section_a'),(33,'Combien coûtent les frais d’inscription ?','How much are the registration fees?','courses','section_a'),(34,'Les cours sont-ils en présentiel ou en ligne ?','Are the courses in person or online?','courses','section_a'),(35,'Y a-t-il un certificat à la fin du cours ?','Is there a certificate at the end of the course?','courses','section_a'),(36,'Puis-je suivre un cours d’essai ?','Can I attend a trial class?','courses','section_a'),(37,'Quels sont les prérequis pour s’inscrire ?','What are the prerequisites for enrollment?','courses','section_a'),(38,'Comment puis-je m’inscrire ?','How can I register?','courses','section_a'),(39,'Y a-t-il un nombre limité de places ?','Is there a limited number of spots?','courses','section_a'),(40,'Est-ce que les cours sont adaptés aux débutants ?','Are the courses suitable for beginners?','courses','section_a'),(41,'Quels sont les moyens de paiement acceptés ?','What payment methods are accepted?','courses','section_a'),(42,'Le matériel est-il fourni ?','Is the material provided?','courses','section_a'),(43,'Puis-je changer de groupe si nécessaire ?','Can I change groups if necessary?','courses','section_a'),(44,'Quelle est la durée totale du cours ?','What is the total duration of the course?','courses','section_a'),(45,'Y a-t-il des aides financières disponibles ?','Are financial aids available?','courses','section_a'),(46,'Bonjour, je vous appelle concernant l’offre d’emploi.','Hello, I am calling about the job offer.','jobs','section_a'),(47,'Le poste est-il encore disponible ?','Is the position still available?','jobs','section_a'),(48,'Quelles sont les qualifications requises ?','What qualifications are required?','jobs','section_a'),(49,'Quel est le salaire proposé ?','What is the offered salary?','jobs','section_a'),(50,'Est-ce un contrat à durée déterminée ou indéterminée ?','Is it a fixed-term or permanent contract?','jobs','section_a'),(51,'Quelles sont les horaires de travail ?','What are the working hours?','jobs','section_a'),(52,'Y a-t-il des possibilités d’évolution ?','Are there opportunities for advancement?','jobs','section_a'),(53,'Où se situe le lieu de travail ?','Where is the workplace located?','jobs','section_a'),(54,'Quelles sont les responsabilités principales ?','What are the main responsibilities?','jobs','section_a'),(55,'Quand puis-je commencer ?','When can I start?','jobs','section_a'),(56,'Dois-je fournir des références ?','Do I need to provide references?','jobs','section_a'),(57,'Y a-t-il une période d’essai ?','Is there a trial period?','jobs','section_a'),(58,'Comment se déroule le processus de recrutement ?','How does the recruitment process work?','jobs','section_a'),(59,'Puis-je envoyer mon CV par email ?','Can I send my CV by email?','jobs','section_a'),(60,'À qui dois-je m’adresser pour plus d’informations ?','Who should I contact for more information?','jobs','section_a'),(61,'Combien coûte l’entrée pour une personne adulte ?','How much is entry for an adult?','events','section_a'),(62,'Est-ce que l’événement a lieu en intérieur ou en extérieur ?','Is the event held indoors or outdoors?','events','section_a'),(63,'Est-ce qu’il y a un parking à proximité ?','Is there parking nearby?','events','section_a'),(64,'Peut-on venir avec un animal de compagnie ?','Can we bring a pet?','events','section_a'),(65,'L’entrée est-elle gratuite pour les enfants ?','Is entrance free for children?','events','section_a'),(66,'Y aura-t-il de la restauration sur place ?','Will there be food available on-site?','events','section_a'),(67,'Combien de temps dure l’événement ?','How long does the event last?','events','section_a'),(68,'Faut-il imprimer les billets ou peut-on les présenter sur téléphone ?','Do we need to print the tickets or can we show them on a phone?','events','section_a'),(69,'Y a-t-il des activités prévues pour les familles ?','Are there activities planned for families?','events','section_a'),(70,'Quel est le programme de l’événement ?','What is the event schedule?','events','section_a'),(71,'Faut-il réserver à l’avance ?','Do we need to book in advance?','events','section_a'),(72,'Combien de personnes peut-on inscrire ?','How many people can be registered?','events','section_a'),(73,'L’événement est-il accessible aux personnes handicapées ?','Is the event accessible to people with disabilities?','events','section_a'),(74,'Quel est l’adresse exacte de l’événement ?','What is the exact address of the event?','events','section_a'),(75,'Peut-on obtenir un remboursement en cas d’annulation ?','Can we get a refund in case of cancellation?','events','section_a'),(76,'Le bénévolat est-il ponctuel ou régulier ?','Is the volunteering temporary or regular?','volunteering','section_a'),(77,'Est-ce qu’on peut choisir ses horaires ?','Can we choose our schedule?','volunteering','section_a'),(78,'Où se déroule l’activité de bénévolat ?','Where does the volunteering take place?','volunteering','section_a'),(79,'Combien de bénévoles recherchez-vous ?','How many volunteers are you looking for?','volunteering','section_a'),(80,'Quel âge minimum faut-il avoir pour participer ?','What is the minimum age required to participate?','volunteering','section_a'),(81,'Y a-t-il une assurance pour les bénévoles ?','Is there insurance for volunteers?','volunteering','section_a'),(82,'Quelles compétences sont utiles pour cette activité ?','What skills are helpful for this activity?','volunteering','section_a'),(83,'Doit-on apporter du matériel personnel ?','Do we need to bring any personal equipment?','volunteering','section_a'),(84,'Est-ce que les frais de déplacement sont remboursés ?','Are travel expenses reimbursed?','volunteering','section_a'),(85,'Faut-il signer un contrat ou une convention ?','Do we need to sign a contract or agreement?','volunteering','section_a'),(86,'Y a-t-il une formation prévue ?','Is there a training session planned?','volunteering','section_a'),(87,'Les repas sont-ils fournis ?','Are meals provided?','volunteering','section_a'),(88,'Combien d’heures par semaine doit-on s’engager ?','How many hours per week are required?','volunteering','section_a'),(89,'Puis-je choisir l’activité qui me plaît ?','Can I choose the activity I like?','volunteering','section_a'),(90,'Est-ce que c’est ouvert aux étudiants étrangers ?','Is it open to foreign students?','volunteering','section_a'),(91,'Depuis combien de temps possédez-vous cet objet ?','How long have you owned this item?','second_hand','section_a'),(92,'Y a-t-il des défauts ou des réparations à prévoir ?','Are there any defects or repairs needed?','second_hand','section_a'),(93,'Pourquoi le vendez-vous ?','Why are you selling it?','second_hand','section_a'),(94,'Est-ce que l’article est encore sous garantie ?','Is the item still under warranty?','second_hand','section_a'),(95,'Peut-on payer en espèces ?','Can we pay in cash?','second_hand','section_a'),(96,'Faites-vous une livraison ou faut-il venir le chercher ?','Do you deliver or must it be picked up?','second_hand','section_a'),(97,'Est-ce que le prix est ferme ou ouvert à négociation ?','Is the price firm or negotiable?','second_hand','section_a'),(98,'Est-ce que l’article a beaucoup servi ?','Has the item been used a lot?','second_hand','section_a'),(99,'Y a-t-il des accessoires inclus ?','Are there any accessories included?','second_hand','section_a'),(100,'Est-il possible de venir le voir aujourd’hui ?','Is it possible to come see it today?','second_hand','section_a'),(101,'Quel est l’état général de l’objet ?','What is the general condition of the item?','second_hand','section_a'),(102,'Peut-on tester l’objet avant l’achat ?','Can we test the item before buying?','second_hand','section_a'),(103,'Acceptez-vous les paiements par virement ?','Do you accept bank transfers?','second_hand','section_a'),(104,'L’objet est-il encore disponible ?','Is the item still available?','second_hand','section_a'),(105,'Avez-vous la facture originale ?','Do you have the original invoice?','second_hand','section_a'),(106,'Bonjour, [Votre nom] à l\'appareil.','Hello, [Your Name] speaking.','General','section_a'),(107,'Je vous appelle au sujet de...','I am calling you about...','General','section_a'),(108,'Serait-il possible de parler à [Nom de la personne]?','Would it be possible to speak to [Person\'s Name]?','General','section_a'),(109,'Est-ce que je parle bien à la personne qui a posté l\'annonce ?','Am I speaking with the person who posted the ad?','General','section_a'),(110,'C\'est bien vous qui avez publié l\'annonce pour [sujet de l\'annonce]?','Are you the one who published the ad for [subject of the ad]?','General','section_a'),(111,'Auriez-vous quelques instants à m\'accorder pour quelques questions ?','Would you have a few moments for a few questions?','General','section_a'),(112,'Seriez-vous disponible pour répondre à quelques questions ?','Would you be available to answer a few questions?','General','section_a'),(113,'Cela ne vous prendra que quelques minutes.','It will only take a few minutes.','General','section_a'),(114,'D\'accord, je vois.','Okay, I see.','General','section_a'),(115,'C\'est noté.','Noted.','General','section_a'),(116,'Parfait.','Perfect.','General','section_a'),(117,'Intéressant.','Interesting.','General','section_a'),(118,'Absolument.','Absolutely.','General','section_a'),(119,'Ça a l\'air bien.','That sounds good.','General','section_a'),(120,'Ça semble intéressant.','That seems interesting.','General','section_a'),(121,'Je vous remercie pour votre temps et pour les informations.','Thank you for your time and for the information.','General','section_a'),(122,'Merci beaucoup pour votre aide.','Thank you very much for your help.','General','section_a'),(123,'Je vais étudier la question et je vous recontacterai.','I will consider the matter and get back to you.','General','section_a'),(124,'Passez une excellente journée.','Have an excellent day.','General','section_a'),(125,'Au revoir, Madame/Monsieur.','Goodbye, Madam/Sir.','General','section_a');
/*!40000 ALTER TABLE `phrases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `question_drills`
--

DROP TABLE IF EXISTS `question_drills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `question_drills` (
  `id` int NOT NULL AUTO_INCREMENT,
  `theme` varchar(100) NOT NULL,
  `section` varchar(100) NOT NULL,
  `english_question` text NOT NULL,
  `french_vocab_hints` text NOT NULL,
  `expected_answer` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `question_drills`
--

LOCK TABLES `question_drills` WRITE;
/*!40000 ALTER TABLE `question_drills` DISABLE KEYS */;
INSERT INTO `question_drills` VALUES (1,'la_vie_de_tous_les_jours','section_a','Where exactly are you located?','adresse, où, situé','Où êtes-vous situé exactement?','2025-08-07 17:39:56'),(2,'la_vie_de_tous_les_jours','section_a','Is there parking available?','parking, sur place, y a-t-il','Y a-t-il un parking sur place?','2025-08-07 17:39:56'),(3,'le_travail','section_a','What are the working hours?','horaires, garde, quels sont','Quels sont les horaires de garde?','2025-08-07 17:39:56'),(4,'le_logement','section_a','Is the apartment still available?','appartement, toujours, disponible','Est-ce que l\'appartement est toujours disponible?','2025-08-07 18:07:08'),(5,'le_logement','section_a','How much is the rent per month?','loyer, par, mois, combien','Combien coûte le loyer par mois?','2025-08-07 18:07:08'),(6,'le_logement','section_a','Are utilities included?','charges, comprises, est-ce que','Est-ce que les charges sont comprises?','2025-08-07 18:07:08'),(7,'le_logement','section_a','Is there a washing machine?','machine à laver, y a-t-il, dans','Y a-t-il une machine à laver dans l\'appartement?','2025-08-07 18:07:08'),(8,'le_travail','section_a','What type of contract is it?','type, contrat, quel','Quel type de contrat proposez-vous?','2025-08-07 18:07:08'),(9,'le_travail','section_a','Is it possible to work from home?','télétravail, possible, faire','Est-ce qu\'il est possible de faire du télétravail?','2025-08-07 18:07:08'),(10,'le_travail','section_a','What are the benefits?','avantages, sociaux, quels sont','Quels sont les avantages sociaux?','2025-08-07 18:07:08'),(11,'les_transports','section_a','How do I get to the city center?','comment, aller, centre-ville','Comment puis-je aller au centre-ville?','2025-08-07 18:07:08'),(12,'les_transports','section_a','Does this bus go to the airport?','bus, va, aéroport','Est-ce que ce bus va à l\'aéroport?','2025-08-07 18:07:08'),(13,'les_transports','section_a','Where can I buy a ticket?','où, acheter, billet','Où est-ce que je peux acheter un billet?','2025-08-07 18:07:08'),(14,'la_vie_de_tous_les_jours','section_a','Can you recommend a good restaurant nearby?','recommander, bon, restaurant, près d\'ici','Pouvez-vous me recommander un bon restaurant près d\'ici?','2025-08-07 18:07:08'),(15,'la_vie_de_tous_les_jours','section_a','What time do you open?','à, quelle, heure, ouvrez-vous','À quelle heure ouvrez-vous?','2025-08-07 18:07:08'),(16,'la_vie_de_tous_les_jours','section_a','Do you accept credit cards?','acceptez-vous, cartes, crédit','Acceptez-vous les cartes de crédit?','2025-08-07 18:07:08');
/*!40000 ALTER TABLE `question_drills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roleplay_scenarios`
--

DROP TABLE IF EXISTS `roleplay_scenarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roleplay_scenarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` text,
  `cloze_script_json` json DEFAULT NULL,
  `english_prompts_json` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roleplay_scenarios`
--

LOCK TABLES `roleplay_scenarios` WRITE;
/*!40000 ALTER TABLE `roleplay_scenarios` DISABLE KEYS */;
INSERT INTO `roleplay_scenarios` VALUES (1,'Fruit Picking Ad','You are calling a farm about a fruit-picking activity you saw advertised.','{\"lines\": [{\"line\": \"Bonjour! Ferme de la Rivière, que puis-je pour vous?\", \"speaker\": \"Owner\"}, {\"line\": \"Bonjour, je vous appelle au sujet de votre annonce pour la cueillette de fruits. Est-ce que c\'est toujours d\'actualité?\", \"speaker\": \"Student\", \"expected\": \"Bonjour, je vous appelle au sujet de votre annonce pour la cueillette de fruits. Est-ce que c\'est toujours d\'actualité?\", \"is_blank\": true}, {\"line\": \"Oui, tout à fait! La saison des fraises bat son plein.\", \"speaker\": \"Owner\"}, {\"line\": \"Super! J\'aurais quelques questions. Premièrement, quels sont vos horaires?\", \"speaker\": \"Student\", \"expected\": \"Super! J\'aurais quelques questions. Premièrement, quels sont vos horaires?\", \"is_blank\": true}, {\"line\": \"Nous sommes ouverts de 9h à 18h, tous les jours sauf le lundi.\", \"speaker\": \"Owner\"}, {\"line\": \"D\'accord. Et combien ça coûte?\", \"speaker\": \"Student\", \"expected\": \"D\'accord. Et combien ça coûte?\", \"is_blank\": true}, {\"line\": \"L\'entrée est gratuite, vous ne payez que les fruits que vous cueillez, au poids.\", \"speaker\": \"Owner\"}, {\"line\": \"Parfait, merci beaucoup pour les informations.\", \"speaker\": \"Student\", \"expected\": \"Parfait, merci beaucoup pour les informations.\", \"is_blank\": true}], \"title\": \"Calling the Fruit Farm\"}','{\"title\": \"Fruit Picking Inquiry (Prompts)\", \"prompts\": [{\"prompt\": \"Greet the person and say you are calling about their ad for fruit picking.\", \"keywords\": [\"bonjour\", \"annonce\", \"cueillette\"]}, {\"prompt\": \"Ask where they are located.\", \"keywords\": [\"où\", \"situé\", \"adresse\", \"se trouve\"]}, {\"prompt\": \"Ask about the opening hours.\", \"keywords\": [\"quand\", \"heures\", \"ouvert\"]}, {\"prompt\": \"Ask if there is a place to park.\", \"keywords\": [\"parking\", \"stationner\", \"garer\"]}, {\"prompt\": \"Thank them for the information.\", \"keywords\": [\"merci\", \"remercie\", \"informations\"]}]}','2025-08-07 17:39:56'),(2,'Babysitter Ad','You are calling a parent who is looking for a babysitter.','{\"lines\": [{\"line\": \"Allô, bonjour.\", \"speaker\": \"Parent\"}, {\"line\": \"Bonjour Madame/Monsieur, je me permets de vous appeler concernant votre annonce pour du baby-sitting.\", \"speaker\": \"Student\", \"expected\": \"Bonjour Madame/Monsieur, je me permets de vous appeler concernant votre annonce pour du baby-sitting.\", \"is_blank\": true}, {\"line\": \"Ah oui, bonjour. Oui, c\'est bien moi. Vous êtes intéressé(e)?\", \"speaker\": \"Parent\"}, {\"line\": \"Oui, tout à fait. Pourriez-vous me dire combien d\'enfants vous avez?\", \"speaker\": \"Student\", \"expected\": \"Oui, tout à fait. Pourriez-vous me dire combien d\'enfants vous avez?\", \"is_blank\": true}, {\"line\": \"J\'ai deux enfants, un garçon de 5 ans et une fille de 8 ans.\", \"speaker\": \"Parent\"}, {\"line\": \"D\'accord. Et quelles seraient les tâches principales?\", \"speaker\": \"Student\", \"expected\": \"D\'accord. Et quelles seraient les tâches principales?\", \"is_blank\": true}, {\"line\": \"Il faudrait aller les chercher à l\'école, leur préparer le goûter et les aider avec leurs devoirs.\", \"speaker\": \"Parent\"}, {\"line\": \"Entendu. Je vous remercie.\", \"speaker\": \"Student\", \"expected\": \"Entendu. Je vous remercie.\", \"is_blank\": true}], \"title\": \"Inquiring about Babysitting\"}','{\"title\": \"Babysitting Inquiry (Prompts)\", \"prompts\": [{\"prompt\": \"Greet the person and state the reason for your call (their ad for a babysitter).\", \"keywords\": [\"bonjour\", \"annonce\", \"baby-sitting\", \"garder\"]}, {\"prompt\": \"Ask how many children they have.\", \"keywords\": [\"combien\", \"enfants\"]}, {\"prompt\": \"Ask for the ages of the children.\", \"keywords\": [\"âge\", \"quel âge\"]}, {\"prompt\": \"Ask what the main tasks would be.\", \"keywords\": [\"tâches\", \"faire\", \"occuper\"]}, {\"prompt\": \"Ask if the school is nearby.\", \"keywords\": [\"école\", \"proche\", \"près\", \"loin\"]}]}','2025-08-07 17:39:56');
/*!40000 ALTER TABLE `roleplay_scenarios` ENABLE KEYS */;
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
  `paypal_subscription_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `subscription_start_date` datetime NOT NULL,
  `subscription_end_date` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id_unique` (`user_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (2,4,'I-L34HUA11TA04','active','2025-08-11 17:00:46','2025-09-11 10:00:00','2025-08-11 17:01:29','2025-08-11 17:01:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=437 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_progress`
--

LOCK TABLES `user_progress` WRITE;
/*!40000 ALTER TABLE `user_progress` DISABLE KEYS */;
INSERT INTO `user_progress` VALUES (1,1,3,0.67,'2025-08-03 11:24:37'),(254,2,1,0.63,'2025-08-09 17:10:54'),(255,2,1,0.75,'2025-08-09 17:11:01'),(256,2,2,0.67,'2025-08-09 17:11:19'),(257,2,5,0.86,'2025-08-10 14:24:43'),(258,2,6,0.71,'2025-08-10 15:54:07'),(259,2,1,0.63,'2025-08-10 15:54:27'),(260,2,2,0.50,'2025-08-10 15:54:36'),(261,2,2,0.50,'2025-08-10 15:54:43'),(262,2,2,0.67,'2025-08-10 15:54:54'),(263,2,3,0.67,'2025-08-10 15:55:01'),(264,2,4,0.64,'2025-08-10 15:55:12'),(265,2,5,0.86,'2025-08-10 15:55:20'),(266,2,6,0.86,'2025-08-10 15:55:28'),(267,2,7,0.29,'2025-08-10 15:55:37'),(268,2,7,0.86,'2025-08-10 15:55:45'),(269,2,8,0.71,'2025-08-10 15:55:53'),(270,2,9,0.83,'2025-08-10 15:56:01'),(271,2,10,0.43,'2025-08-10 15:56:10'),(272,2,10,0.43,'2025-08-10 15:56:16'),(273,2,10,0.71,'2025-08-10 15:56:24'),(274,2,11,0.43,'2025-08-10 15:56:32'),(275,2,11,0.57,'2025-08-10 15:56:38'),(276,2,11,0.86,'2025-08-10 15:56:46'),(277,2,12,0.88,'2025-08-10 15:56:55'),(278,2,13,0.40,'2025-08-10 15:57:02'),(279,2,13,0.40,'2025-08-10 15:57:07'),(280,2,13,0.40,'2025-08-10 15:57:15'),(281,2,13,0.40,'2025-08-10 15:57:20'),(282,2,14,0.67,'2025-08-10 15:57:32'),(283,2,15,0.60,'2025-08-10 15:57:42'),(284,2,15,0.80,'2025-08-10 15:57:48'),(285,2,16,0.50,'2025-08-10 15:58:24'),(286,2,16,0.63,'2025-08-10 15:58:32'),(287,2,17,0.83,'2025-08-10 15:58:38'),(288,2,18,0.25,'2025-08-10 15:58:45'),(289,2,18,0.25,'2025-08-10 15:58:49'),(290,2,18,0.38,'2025-08-10 15:58:59'),(291,2,18,0.25,'2025-08-10 15:59:03'),(292,2,18,0.25,'2025-08-10 15:59:10'),(293,2,18,0.25,'2025-08-10 15:59:33'),(294,2,19,0.83,'2025-08-10 15:59:41'),(295,2,20,0.88,'2025-08-10 15:59:50'),(296,2,21,0.50,'2025-08-10 15:59:57'),(297,2,21,0.38,'2025-08-10 16:00:01'),(298,2,21,0.88,'2025-08-10 16:00:10'),(299,2,22,0.50,'2025-08-10 16:00:18'),(300,2,22,0.50,'2025-08-10 16:00:22'),(301,2,22,0.25,'2025-08-10 16:00:30'),(302,2,22,0.50,'2025-08-10 16:00:34'),(303,2,23,0.57,'2025-08-10 16:00:42'),(304,2,23,0.71,'2025-08-10 16:00:47'),(305,2,24,0.57,'2025-08-10 16:00:54'),(306,2,24,0.43,'2025-08-10 16:00:59'),(307,2,24,0.57,'2025-08-10 16:01:03'),(308,2,24,0.71,'2025-08-10 16:01:13'),(309,2,25,0.83,'2025-08-10 16:01:20'),(310,2,26,0.20,'2025-08-10 16:01:27'),(311,2,26,0.20,'2025-08-10 16:01:33'),(312,2,26,0.40,'2025-08-10 16:01:40'),(313,2,26,0.40,'2025-08-10 16:01:45'),(314,2,27,0.57,'2025-08-10 16:01:52'),(315,2,27,0.71,'2025-08-10 16:01:56'),(316,2,28,0.78,'2025-08-10 16:02:04'),(317,2,29,0.40,'2025-08-10 16:02:11'),(318,2,29,0.60,'2025-08-10 16:02:14'),(319,2,29,0.40,'2025-08-10 16:02:23'),(320,2,29,0.60,'2025-08-10 16:02:27'),(321,2,30,0.83,'2025-08-10 16:02:34'),(322,2,32,0.43,'2025-08-10 16:34:10'),(323,2,32,0.71,'2025-08-10 16:34:19'),(324,2,33,0.50,'2025-08-10 16:34:35'),(325,2,34,0.22,'2025-08-10 16:34:49'),(326,2,34,0.78,'2025-08-10 16:35:06'),(327,2,35,0.90,'2025-08-10 16:35:45'),(328,2,36,0.00,'2025-08-10 16:35:57'),(329,2,36,0.17,'2025-08-10 16:36:04'),(330,2,36,0.67,'2025-08-10 16:36:14'),(331,2,38,0.50,'2025-08-10 16:37:22'),(332,2,38,0.50,'2025-08-10 16:37:27'),(333,2,38,0.50,'2025-08-10 16:37:40'),(334,2,38,0.50,'2025-08-10 16:37:47'),(335,2,39,0.63,'2025-08-10 16:37:56'),(336,2,40,0.56,'2025-08-10 16:38:06'),(337,2,40,0.44,'2025-08-10 16:38:13'),(338,2,40,0.78,'2025-08-10 16:38:22'),(339,2,41,0.75,'2025-08-10 16:38:39'),(340,2,42,0.40,'2025-08-10 16:38:48'),(341,2,42,0.60,'2025-08-10 16:38:53'),(342,2,42,0.80,'2025-08-10 16:38:59'),(343,2,43,0.86,'2025-08-10 16:39:08'),(344,2,44,0.13,'2025-08-10 16:39:23'),(345,2,44,0.75,'2025-08-10 16:39:29'),(346,2,45,0.86,'2025-08-10 16:39:44'),(347,2,32,0.86,'2025-08-10 16:40:31'),(348,2,33,0.50,'2025-08-10 16:41:00'),(349,2,33,0.50,'2025-08-10 16:41:07'),(350,2,33,0.50,'2025-08-10 16:41:17'),(351,2,33,0.50,'2025-08-10 16:41:23'),(352,2,33,0.50,'2025-08-10 16:41:29'),(353,2,32,0.57,'2025-08-10 16:41:39'),(354,2,32,0.71,'2025-08-10 16:41:45'),(355,2,31,0.63,'2025-08-10 16:41:55'),(356,2,34,0.44,'2025-08-10 16:42:26'),(357,2,34,0.67,'2025-08-10 16:42:32'),(358,2,35,0.80,'2025-08-10 16:43:06'),(359,2,36,0.00,'2025-08-10 16:43:18'),(360,2,36,0.17,'2025-08-10 16:43:24'),(361,2,36,0.67,'2025-08-10 16:43:43'),(362,2,37,0.71,'2025-08-10 16:44:18'),(363,2,38,0.25,'2025-08-10 16:44:26'),(364,2,46,0.57,'2025-08-10 20:32:13'),(365,2,46,0.57,'2025-08-10 20:32:21'),(366,2,46,0.57,'2025-08-10 20:32:35'),(367,2,46,0.57,'2025-08-10 20:32:42'),(368,2,46,0.57,'2025-08-10 20:32:49'),(369,2,46,0.57,'2025-08-10 20:32:59'),(370,2,46,0.57,'2025-08-10 20:33:11'),(371,2,47,0.50,'2025-08-10 20:33:33'),(372,2,47,0.67,'2025-08-10 20:33:38'),(373,2,48,0.50,'2025-08-10 20:34:00'),(374,2,48,0.67,'2025-08-10 20:34:10'),(375,2,49,0.83,'2025-08-10 20:34:34'),(376,2,50,0.33,'2025-08-10 20:35:01'),(377,2,50,0.22,'2025-08-10 20:35:07'),(378,2,50,0.33,'2025-08-10 20:35:20'),(379,2,51,0.71,'2025-08-10 20:35:46'),(380,2,52,0.67,'2025-08-10 20:36:11'),(381,2,53,0.75,'2025-08-10 20:36:29'),(382,2,54,0.83,'2025-08-10 20:36:47'),(383,2,55,0.50,'2025-08-10 20:37:01'),(384,2,55,0.75,'2025-08-10 20:37:05'),(385,2,56,0.40,'2025-08-10 20:37:22'),(386,2,56,0.80,'2025-08-10 20:37:31'),(387,2,57,0.33,'2025-08-10 20:37:48'),(388,2,57,0.50,'2025-08-10 20:37:57'),(389,2,57,0.50,'2025-08-10 20:38:05'),(390,2,57,0.67,'2025-08-10 20:38:09'),(391,2,58,0.50,'2025-08-10 20:38:29'),(392,2,58,0.38,'2025-08-10 20:38:34'),(393,2,58,0.38,'2025-08-10 20:38:41'),(394,2,59,0.29,'2025-08-10 20:44:28'),(395,2,59,0.71,'2025-08-10 20:44:40'),(396,2,60,0.63,'2025-08-10 20:45:03'),(397,4,106,0.00,'2025-08-11 17:12:27'),(398,4,106,0.20,'2025-08-11 17:12:39'),(399,4,107,0.83,'2025-08-11 17:12:54'),(400,4,108,0.67,'2025-08-11 17:13:10'),(401,4,109,0.85,'2025-08-11 17:13:30'),(402,4,110,0.82,'2025-08-11 17:13:43'),(403,4,111,0.11,'2025-08-11 17:14:01'),(404,4,111,0.44,'2025-08-11 17:14:09'),(405,4,111,0.11,'2025-08-11 17:14:21'),(406,4,111,0.22,'2025-08-11 17:14:28'),(407,4,111,0.44,'2025-08-11 17:14:36'),(408,4,112,0.88,'2025-08-11 17:14:46'),(409,4,113,0.14,'2025-08-11 17:15:03'),(410,4,113,0.71,'2025-08-11 17:15:12'),(411,4,114,0.33,'2025-08-11 17:15:20'),(412,4,114,0.33,'2025-08-11 17:15:23'),(413,4,114,0.33,'2025-08-11 17:15:30'),(414,4,114,0.33,'2025-08-11 17:15:33'),(415,4,114,0.33,'2025-08-11 17:15:36'),(416,4,115,0.50,'2025-08-11 17:15:42'),(417,4,115,0.50,'2025-08-11 17:15:48'),(418,4,116,0.00,'2025-08-11 17:15:53'),(419,4,116,0.00,'2025-08-11 17:15:58'),(420,4,116,0.00,'2025-08-11 17:16:01'),(421,4,117,0.00,'2025-08-11 17:16:07'),(422,4,117,0.00,'2025-08-11 17:16:10'),(423,4,118,0.00,'2025-08-11 17:16:20'),(424,4,118,0.00,'2025-08-11 17:16:23'),(425,4,119,0.75,'2025-08-11 17:16:36'),(426,4,120,0.67,'2025-08-11 17:16:49'),(427,4,121,0.80,'2025-08-11 17:17:02'),(428,4,122,0.80,'2025-08-11 17:17:16'),(429,4,123,0.78,'2025-08-11 17:17:32'),(430,4,124,0.00,'2025-08-11 17:17:41'),(431,4,124,0.25,'2025-08-11 17:17:54'),(432,4,124,0.00,'2025-08-11 17:18:00'),(433,4,125,0.00,'2025-08-11 17:18:08'),(434,4,125,0.33,'2025-08-11 17:18:17'),(435,4,31,0.75,'2025-08-15 10:43:10'),(436,4,3,0.67,'2025-08-15 12:39:16');
/*!40000 ALTER TABLE `user_progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_scripts`
--

DROP TABLE IF EXISTS `user_scripts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_scripts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `script_name` varchar(255) NOT NULL,
  `script_content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `user_scripts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_scripts`
--

LOCK TABLES `user_scripts` WRITE;
/*!40000 ALTER TABLE `user_scripts` DISABLE KEYS */;
INSERT INTO `user_scripts` VALUES (1,2,'First','Bonjour Monsieur, excusez-moi de vous déranger.','2025-08-08 18:04:12','2025-08-08 18:04:12'),(3,2,'Second','Et je suis intéressé par votre annonce. Est-elle toujours d’actualité ?','2025-08-08 18:05:15','2025-08-08 18:05:15'),(4,2,'Third','Avez-vous quelques minutes à m’accorder ?','2025-08-08 18:15:15','2025-08-08 18:15:15'),(5,2,'Fourth','Est-ce que l\'appartement est toujours disponible?','2025-08-09 15:57:51','2025-08-09 15:57:51'),(7,4,'Combo','Où\nQui\nQuand\nComment\nCombien\nQuoi\n\nEst-ce que\nY a-t-il\nIl y a\nEst-ce que c\'est\nEst-ce que vous pouvez m’en dire plus ?\n\nÇa a l’air bien\nÇa a l’air intéressant\nÇa semble intéressant\nC’est parfait ! \nParfait ! \nça me convient. \nSuper !','2025-08-11 18:01:54','2025-08-11 18:01:54'),(8,4,'Introduction','Bonjour Madame/Monsieur, excusez-moi de vous déranger. \nBonjour, suis-je bien au ....(numéro) ? if there is a telephon number on the advertisement\nC’est bien le.......? \nJ’ai vu votre annonce au sujet de__ dans le journal. \nJe viens de lire votre annonce concernant le..... sur Internet \nJe viens de tomber sur votre annonce au sujet de..... dans le journal\n\nEt je suis intéressé par votre annonce. Est-elle toujours d’actualité ? \nJ’ai plusieurs questions à vous poser. \nJe voudrais vous poser quelques questions \nJ’aurais quelques questions à vous poser (s’il vous plaît) \nJ’ai une question à propos de…. /concernant….. \nJ’aimerais en savoir plus. \nJe voudrais avoir plus de renseignements. \nJe vous contacte pour avoir quelques renseignements.\n\n3/ Avez-vous quelques minutes à m’accorder ? \nPourriez-vous répondre à quelques questions ?','2025-08-11 18:05:56','2025-08-11 18:05:56'),(9,4,'Conclusion','D’accord, je vous remercie de m’avoir renseigné…\nJe vous remercie pour ces informations/ces renseignements… \nJe vous recontacte\nJe vais réfléchir et si je suis intéressé, je vous rappellerai/recontacterai. \nJ’attends votre confirmation (pour la réservation), \nMadame/Monsieur Je vais en parler/en discuter avec mon mari et je vous rappelle/recontacte.\n\nBonne journée/Au revoir','2025-08-11 18:06:26','2025-08-11 18:06:26');
/*!40000 ALTER TABLE `user_scripts` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Eben','van Ellewee','sbmail246@gmail.com','$2y$10$AbsHV5/uDhBM6PdSIkhF5.Cj5LwI.XdPAGOy2ONHJBHrFxAYswG1e','admin','active','2025-08-03 11:16:03',1,'section_a-daily_life',0),(2,'Eben','van Ellewee','ebenvanellewee@gmail.com','$2y$10$SRhdc9nBvPmldt11/eAZ0ec.MqzLeJ/NeYVWUn88tr7QfmhECsIVO','user','active','2025-08-03 11:26:52',1,'section_a-General',0),(3,'Milana','Gracheva','gracheva1441@gmail.com','$2y$10$QPX59DevXU8sxNvOLm.mseFKVXvuHrv8endJehxc.r50tQjjwxDjK','user','active','2025-08-10 16:57:20',0,NULL,NULL),(4,'Boris','Johnson','evanelledev@gmail.com','$2y$10$cqQ2.mwIIQs1udGOr2NLjetfaINbjbausnc8t55bqiJ.sUFUnTkkS','user','active','2025-08-10 20:14:11',1,'section_a-daily_life',0);
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

-- Dump completed on 2025-08-16 12:06:21
