-- MySQL dump 10.13  Distrib 8.4.13, for Linux (x86_64)
--
-- Host: localhost    Database: gestion_pedagogique
-- ------------------------------------------------------
-- Server version	8.4.13

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
-- Current Database: `gestion_pedagogique`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `gestion_pedagogique` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `gestion_pedagogique`;

--
-- Table structure for table `absences`
--

DROP TABLE IF EXISTS `absences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `absences` (
  `idAbsence` int NOT NULL AUTO_INCREMENT,
  `idEtudiant` int NOT NULL,
  `idSeance` int NOT NULL,
  `statut` enum('ABSENT','PRESENT') DEFAULT 'ABSENT',
  `justifiee` tinyint(1) DEFAULT '0',
  `motif` text,
  PRIMARY KEY (`idAbsence`),
  UNIQUE KEY `uq_etudiant_seance` (`idEtudiant`,`idSeance`),
  KEY `idSeance` (`idSeance`),
  CONSTRAINT `absences_ibfk_1` FOREIGN KEY (`idEtudiant`) REFERENCES `etudiants` (`idUtilisateur`) ON DELETE CASCADE,
  CONSTRAINT `absences_ibfk_2` FOREIGN KEY (`idSeance`) REFERENCES `seances` (`idSeance`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `absences`
--

LOCK TABLES `absences` WRITE;
/*!40000 ALTER TABLE `absences` DISABLE KEYS */;
INSERT INTO `absences` VALUES (4,11,5,'PRESENT',0,NULL),(5,12,5,'PRESENT',0,NULL),(6,13,5,'PRESENT',0,NULL),(7,11,6,'PRESENT',0,NULL),(8,12,6,'ABSENT',0,'Panne de transport en commun'),(9,13,6,'PRESENT',0,NULL),(10,11,7,'PRESENT',0,NULL),(11,12,7,'PRESENT',0,NULL),(12,13,7,'ABSENT',1,'Certificat médical validé par la scolarité'),(13,11,8,'PRESENT',0,NULL),(14,12,8,'PRESENT',0,NULL),(15,13,8,'PRESENT',0,NULL),(16,11,9,'PRESENT',0,NULL),(17,12,9,'PRESENT',0,NULL),(18,13,9,'PRESENT',0,NULL),(19,11,10,'PRESENT',0,NULL),(20,12,10,'PRESENT',0,NULL),(21,13,10,'PRESENT',0,NULL),(22,11,11,'PRESENT',0,NULL),(23,12,11,'PRESENT',0,NULL),(24,13,11,'PRESENT',0,NULL),(25,11,12,'PRESENT',0,NULL),(26,12,12,'PRESENT',0,NULL),(27,13,12,'PRESENT',0,NULL),(28,11,13,'PRESENT',0,NULL),(29,12,13,'PRESENT',0,NULL),(30,13,13,'PRESENT',0,NULL),(31,11,14,'PRESENT',0,NULL),(32,12,14,'PRESENT',0,NULL),(33,13,14,'PRESENT',0,NULL),(34,11,15,'PRESENT',0,NULL),(35,12,15,'PRESENT',0,NULL),(36,13,15,'PRESENT',0,NULL),(37,11,16,'PRESENT',0,NULL),(38,12,16,'PRESENT',0,NULL),(39,13,16,'PRESENT',0,NULL),(40,11,17,'PRESENT',0,NULL),(41,12,17,'PRESENT',0,NULL),(42,13,17,'PRESENT',0,NULL),(43,11,18,'PRESENT',0,NULL),(44,12,18,'PRESENT',0,NULL),(45,13,18,'PRESENT',0,NULL),(46,11,19,'PRESENT',0,NULL),(47,12,19,'PRESENT',0,NULL),(48,13,19,'PRESENT',0,NULL);
/*!40000 ALTER TABLE `absences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `annonces`
--

DROP TABLE IF EXISTS `annonces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `annonces` (
  `idAnnonce` int NOT NULL AUTO_INCREMENT,
  `idAuteur` int NOT NULL,
  `titre` varchar(200) NOT NULL,
  `contenu` text NOT NULL,
  `datePublication` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `portee` enum('GLOBALE','MODULE') NOT NULL DEFAULT 'GLOBALE',
  `idModule` int DEFAULT NULL,
  PRIMARY KEY (`idAnnonce`),
  KEY `idAuteur` (`idAuteur`),
  KEY `idModule` (`idModule`),
  CONSTRAINT `annonces_ibfk_1` FOREIGN KEY (`idAuteur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE CASCADE,
  CONSTRAINT `annonces_ibfk_2` FOREIGN KEY (`idModule`) REFERENCES `modules` (`idModule`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `annonces`
--

LOCK TABLES `annonces` WRITE;
/*!40000 ALTER TABLE `annonces` DISABLE KEYS */;
INSERT INTO `annonces` VALUES (3,2,'PROG-BACKEND : Consignes du TP Not?? Architecture MVC','Bonjour ?? tous les ??tudiants de Licence INFO-L3.\nLe TP Not?? de programmation backend portera sur la mise en place d\'un routeur personnalis?? et l\'impl??mentation de formulaires s??curis??s par jeton CSRF.\nLa date limite de d??p??t sur le d??p??t Git est fix??e au vendredi prochain ?? 23h59.','2026-10-27 00:35:04','MODULE',1),(4,7,'DATA-SCI : Donn??es du Projet de Classification Pr??dicitive','Chers ??tudiants, les jeux de donn??es CSV pour les travaux pratiques du mardi en Salle D5 sont d??sormais disponibles dans l\'espace ressources. Merci de pr??parer votre environnement Python/Pandas ?? l\'avance.','2026-10-27 00:35:04','MODULE',5),(5,8,'BDD-ADV : S??ance Mercredi en Salle D5 ??? Mod??lisation NoSQL','Rappel pour la s??ance de travaux dirig??s : nous aborderons l\'indexation B-Tree et la normalisation relationnelle avanc??e (3NF et BCNF). Pr??sence obligatoire.','2026-10-27 00:35:04','MODULE',6),(6,1,'D??partement Math-Info : Calendrier P??dagogique du Semestre S1','La scolarit?? rappelle ?? l\'ensemble des ??tudiants que les feuilles d\'??margement sont d??sormais cl??tur??es chaque semaine. Tout certificat d\'absence doit ??tre d??pos?? dans un d??lai strict de 48 heures ouvrables.','2026-10-27 00:35:04','GLOBALE',NULL);
/*!40000 ALTER TABLE `annonces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `enseignants`
--

DROP TABLE IF EXISTS `enseignants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `enseignants` (
  `idUtilisateur` int NOT NULL,
  `matricule` varchar(50) NOT NULL,
  `specialite` varchar(100) NOT NULL,
  PRIMARY KEY (`idUtilisateur`),
  UNIQUE KEY `matricule` (`matricule`),
  CONSTRAINT `enseignants_ibfk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `enseignants`
--

LOCK TABLES `enseignants` WRITE;
/*!40000 ALTER TABLE `enseignants` DISABLE KEYS */;
INSERT INTO `enseignants` VALUES (2,'ENS-2026-001','Programmation Backend & Architecture Logicielle'),(4,'ENS-2026-002','Syst??mes Distribu??s & Cloud'),(6,'ENS-2026-003','Comp??tences Num??riques & TICE'),(7,'ENS-2026-004','Sciences des Donn??es & Analyse Pr??dictive'),(8,'ENS-2026-005','Bases de Donn??es Avanc??es & NoSQL'),(9,'ENS-2026-006','R??seaux Informatiques & T??l??communications'),(10,'ENS-2026-007','Java & Interfaces Homme-Machine');
/*!40000 ALTER TABLE `enseignants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `etudiants`
--

DROP TABLE IF EXISTS `etudiants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `etudiants` (
  `idUtilisateur` int NOT NULL,
  `cne` varchar(50) NOT NULL,
  `dateNaissance` date NOT NULL,
  PRIMARY KEY (`idUtilisateur`),
  UNIQUE KEY `cne` (`cne`),
  CONSTRAINT `etudiants_ibfk_1` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `etudiants`
--

LOCK TABLES `etudiants` WRITE;
/*!40000 ALTER TABLE `etudiants` DISABLE KEYS */;
INSERT INTO `etudiants` VALUES (3,'M134098765','2004-03-15'),(5,'R130987654','2003-11-20'),(11,'G135089201','2004-05-12'),(12,'G138045612','2004-08-23'),(13,'G132078945','2003-11-04');
/*!40000 ALTER TABLE `etudiants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filieres`
--

DROP TABLE IF EXISTS `filieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filieres` (
  `idFiliere` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `intitule` varchar(150) NOT NULL,
  `description` text,
  PRIMARY KEY (`idFiliere`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filieres`
--

LOCK TABLES `filieres` WRITE;
/*!40000 ALTER TABLE `filieres` DISABLE KEYS */;
INSERT INTO `filieres` VALUES (1,'INFO-L3','Licence Informatique et Ing??nierie des Syst??mes','Formation approfondie en d??veloppement logiciel, syst??mes et sciences de donn??es.'),(3,'INFO-M1','Master Ing??nierie Logicielle & Syst??mes','Cycle sup??rieur ax?? sur le cloud computing et les architectures distribu??es.');
/*!40000 ALTER TABLE `filieres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `inscriptions`
--

DROP TABLE IF EXISTS `inscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `inscriptions` (
  `idInscription` int NOT NULL AUTO_INCREMENT,
  `idEtudiant` int NOT NULL,
  `idFiliere` int NOT NULL,
  `anneeUniversitaire` varchar(20) NOT NULL,
  `dateInscription` date NOT NULL,
  `statut` enum('EN_COURS','VALIDE','AJOURNE') DEFAULT 'EN_COURS',
  PRIMARY KEY (`idInscription`),
  UNIQUE KEY `uq_etudiant_annee` (`idEtudiant`,`anneeUniversitaire`),
  KEY `idFiliere` (`idFiliere`),
  CONSTRAINT `inscriptions_ibfk_1` FOREIGN KEY (`idEtudiant`) REFERENCES `etudiants` (`idUtilisateur`) ON DELETE CASCADE,
  CONSTRAINT `inscriptions_ibfk_2` FOREIGN KEY (`idFiliere`) REFERENCES `filieres` (`idFiliere`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `inscriptions`
--

LOCK TABLES `inscriptions` WRITE;
/*!40000 ALTER TABLE `inscriptions` DISABLE KEYS */;
INSERT INTO `inscriptions` VALUES (1,5,3,'2026-2027','2026-10-26','EN_COURS'),(2,3,1,'2026-2027','2026-10-26','EN_COURS'),(3,11,1,'2026-2027','2026-09-15','EN_COURS'),(4,12,1,'2026-2027','2026-09-15','EN_COURS'),(5,13,1,'2026-2027','2026-09-15','EN_COURS');
/*!40000 ALTER TABLE `inscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `journal_audit_notes`
--

DROP TABLE IF EXISTS `journal_audit_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `journal_audit_notes` (
  `idAudit` int NOT NULL AUTO_INCREMENT,
  `idNote` int NOT NULL,
  `idUtilisateur` int NOT NULL,
  `typeAction` enum('CREATION','MODIFICATION','SUPPRESSION') NOT NULL,
  `ancienneValeur` float DEFAULT NULL,
  `nouvelleValeur` float DEFAULT NULL,
  `dateAction` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idAudit`),
  KEY `idNote` (`idNote`),
  KEY `idUtilisateur` (`idUtilisateur`),
  CONSTRAINT `journal_audit_notes_ibfk_1` FOREIGN KEY (`idNote`) REFERENCES `notes` (`idNote`) ON DELETE CASCADE,
  CONSTRAINT `journal_audit_notes_ibfk_2` FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateurs` (`idUtilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `journal_audit_notes`
--

LOCK TABLES `journal_audit_notes` WRITE;
/*!40000 ALTER TABLE `journal_audit_notes` DISABLE KEYS */;
/*!40000 ALTER TABLE `journal_audit_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `modules`
--

DROP TABLE IF EXISTS `modules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `modules` (
  `idModule` int NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `intitule` varchar(150) NOT NULL,
  `semestre` enum('S1','S2','S3','S4') NOT NULL,
  `coefficient` float NOT NULL DEFAULT '1',
  `idFiliere` int NOT NULL,
  `idEnseignant` int DEFAULT NULL,
  PRIMARY KEY (`idModule`),
  UNIQUE KEY `code` (`code`),
  KEY `idFiliere` (`idFiliere`),
  KEY `idEnseignant` (`idEnseignant`),
  CONSTRAINT `modules_ibfk_1` FOREIGN KEY (`idFiliere`) REFERENCES `filieres` (`idFiliere`) ON DELETE CASCADE,
  CONSTRAINT `modules_ibfk_2` FOREIGN KEY (`idEnseignant`) REFERENCES `enseignants` (`idUtilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `modules`
--

LOCK TABLES `modules` WRITE;
/*!40000 ALTER TABLE `modules` DISABLE KEYS */;
INSERT INTO `modules` VALUES (1,'BACKEND','PROGRAMMATION BACKEND','S1',2,1,2),(4,'DIG-SKILLS','DIGITAL SKILLS','S1',1,1,6),(5,'DATA-SCI','INTRODUCTION AUX SCIENCES DE DONNEES','S1',1,1,7),(6,'BDD-ADV','BASE DE DONNEE AVANCEE','S1',1,1,8),(7,'RESEAUX','RESEAUX','S1',1,1,9),(8,'JAVA-IHM','JAVA-IHM','S1',1,1,10),(9,'ANGLAIS','ANGLAIS','S1',1,1,6);
/*!40000 ALTER TABLE `modules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notes`
--

DROP TABLE IF EXISTS `notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notes` (
  `idNote` int NOT NULL AUTO_INCREMENT,
  `idEtudiant` int NOT NULL,
  `idModule` int NOT NULL,
  `noteCC` float DEFAULT NULL,
  `noteExam` float DEFAULT NULL,
  `noteFinale` float DEFAULT NULL,
  `session` enum('NORMALE','RATTRAPAGE') DEFAULT 'NORMALE',
  `statutValidation` enum('V','RATT','NV') DEFAULT 'NV',
  `remarque` text,
  PRIMARY KEY (`idNote`),
  UNIQUE KEY `uq_etudiant_module_session` (`idEtudiant`,`idModule`,`session`),
  KEY `idModule` (`idModule`),
  CONSTRAINT `notes_ibfk_1` FOREIGN KEY (`idEtudiant`) REFERENCES `etudiants` (`idUtilisateur`) ON DELETE CASCADE,
  CONSTRAINT `notes_ibfk_2` FOREIGN KEY (`idModule`) REFERENCES `modules` (`idModule`) ON DELETE CASCADE,
  CONSTRAINT `notes_chk_1` CHECK ((`noteCC` between 0 and 20)),
  CONSTRAINT `notes_chk_2` CHECK ((`noteExam` between 0 and 20)),
  CONSTRAINT `notes_chk_3` CHECK ((`noteFinale` between 0 and 20))
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notes`
--

LOCK TABLES `notes` WRITE;
/*!40000 ALTER TABLE `notes` DISABLE KEYS */;
INSERT INTO `notes` VALUES (4,11,1,NULL,NULL,17.5,'NORMALE','V','Excellente ma??trise de l\'architecture MVC et des principes SOLID.'),(5,12,1,NULL,NULL,15.25,'NORMALE','V','Tr??s bon travail sur les API et la s??curit?? CSRF.'),(6,13,1,NULL,NULL,13,'NORMALE','V','Bonne assimilation des requ??tes PDO s??curis??es.'),(7,3,1,NULL,NULL,11.5,'NORMALE','V','Travail satisfaisant, approfondir la POO avanc??e.'),(8,5,1,NULL,NULL,9,'NORMALE','RATT','Rattrapage conseill?? pour consolider les sessions PHP.'),(9,11,4,NULL,NULL,16,'NORMALE','V','Excellente utilisation des outils collaboratifs.'),(10,12,4,NULL,NULL,14.5,'NORMALE','V','Tr??s bonne participation.'),(11,13,4,NULL,NULL,13.5,'NORMALE','V','Bon travail r??gulier.'),(12,3,4,NULL,NULL,15,'NORMALE','V','Acquis solides.'),(13,11,5,NULL,NULL,18,'NORMALE','V','Excellente mod??lisation statistique et analyse de donn??es.'),(14,12,5,NULL,NULL,14,'NORMALE','V','Tr??s bonne ma??trise des algorithmes de classification.'),(15,13,5,NULL,NULL,12.5,'NORMALE','V','Compr??hension satisfaisante des concepts de base.'),(16,11,6,NULL,NULL,16.5,'NORMALE','V','Tr??s bonne optimisation des index et transactions ACID.'),(17,12,6,NULL,NULL,15,'NORMALE','V','Projet de mod??lisation relationnelle bien structur??.'),(18,13,6,NULL,NULL,13.5,'NORMALE','V','Requ??tes SQL bien formul??es.'),(19,11,7,NULL,NULL,15.5,'NORMALE','V','Tr??s bonne compr??hension du mod??le TCP/IP et du routage.'),(20,12,7,NULL,NULL,13,'NORMALE','V','Bonne configuration des sous-r??seaux et masques.'),(21,13,7,NULL,NULL,14,'NORMALE','V','Travaux pratiques r??seau r??ussis avec succ??s.'),(22,11,8,NULL,NULL,17,'NORMALE','V','Interface Java Swing ergonomique et code propre.'),(23,12,8,NULL,NULL,16,'NORMALE','V','Tr??s bonne gestion des ??v??nements et ??couteurs IHM.'),(24,13,8,NULL,NULL,12,'NORMALE','V','Application fonctionnelle, peaufiner l\'esth??tique.'),(25,11,9,NULL,NULL,16,'NORMALE','V','Fluent communication and technical terminology.'),(26,12,9,NULL,NULL,15,'NORMALE','V','Good technical presentation and writing.'),(27,13,9,NULL,NULL,14,'NORMALE','V','Solid vocabulary in computer science.');
/*!40000 ALTER TABLE `notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seances`
--

DROP TABLE IF EXISTS `seances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `seances` (
  `idSeance` int NOT NULL AUTO_INCREMENT,
  `idModule` int NOT NULL,
  `dateSeance` date NOT NULL,
  `heureDebut` time NOT NULL,
  `heureFin` time NOT NULL,
  `typeSeance` enum('COURS','TD','TP') NOT NULL DEFAULT 'COURS',
  PRIMARY KEY (`idSeance`),
  KEY `idModule` (`idModule`),
  CONSTRAINT `seances_ibfk_1` FOREIGN KEY (`idModule`) REFERENCES `modules` (`idModule`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seances`
--

LOCK TABLES `seances` WRITE;
/*!40000 ALTER TABLE `seances` DISABLE KEYS */;
INSERT INTO `seances` VALUES (5,4,'2026-10-19','08:30:00','10:30:00','COURS'),(6,4,'2026-10-26','08:30:00','10:30:00','TD'),(7,5,'2026-10-20','08:30:00','10:30:00','COURS'),(8,5,'2026-10-27','08:30:00','10:30:00','TD'),(9,1,'2026-10-20','10:45:00','12:45:00','COURS'),(10,1,'2026-10-27','10:45:00','12:45:00','TD'),(11,1,'2026-11-03','10:45:00','12:45:00','TP'),(12,1,'2026-11-10','10:45:00','12:45:00','COURS'),(13,6,'2026-10-21','08:30:00','10:30:00','COURS'),(14,6,'2026-10-28','08:30:00','10:30:00','TD'),(15,7,'2026-10-22','08:30:00','10:30:00','COURS'),(16,7,'2026-10-29','08:30:00','10:30:00','TD'),(17,8,'2026-10-22','10:45:00','12:45:00','COURS'),(18,8,'2026-10-29','10:45:00','12:45:00','TP'),(19,9,'2026-10-24','09:00:00','11:00:00','COURS'),(20,1,'2026-10-27','08:30:00','10:30:00','COURS');
/*!40000 ALTER TABLE `seances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `idUtilisateur` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `motDePasse` varchar(255) NOT NULL,
  `role` enum('ADMIN','ENSEIGNANT','ETUDIANT') NOT NULL,
  `dateCreation` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUtilisateur`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Benjelloun','Karim','admin@departement.ma','$2y$12$o0IN7mzVjKb7/toAINT/geNXH.qYatVQJbnN/k4Xg1wcNEoOJJYGy','ADMIN','2026-10-26 21:02:46'),(2,'El Bahi','Hassan','h.elbahi@departement.ma','$2y$10$36uMRgOyrYlzsoHu3JlHKuTCzd073Rwvd4EuhU.fEfTjD0yQvxTEa','ENSEIGNANT','2026-10-26 21:02:46'),(3,'Idrissi','Youssef','y.idrissi@etudiant.ma','$2y$10$W.6owTWmQiza.wznPCBHQef6ssJ0mEEJSt4FGoHgCwGob29Ff08Wu','ETUDIANT','2026-10-26 21:02:46'),(4,'Zahraoui','Fatima','f.zahraoui@departement.ma','$2y$10$UQVJGvkydyCg/M5wW6VKh.KQWfM77rmqY32HXxxolZ6dfM5A2/Z9e','ENSEIGNANT','2026-10-26 21:26:36'),(5,'Bennani','Salma','s.bennani@etudiant.ma','$2y$10$W.6owTWmQiza.wznPCBHQef6ssJ0mEEJSt4FGoHgCwGob29Ff08Wu','ETUDIANT','2026-10-26 21:26:36'),(6,'Basmassi','Mohamed','m.basmassi@departement.ma','$2y$10$36uMRgOyrYlzsoHu3JlHKuTCzd073Rwvd4EuhU.fEfTjD0yQvxTEa','ENSEIGNANT','2026-10-27 00:35:04'),(7,'Nassiri','Nadia','n.nassiri@departement.ma','$2y$10$36uMRgOyrYlzsoHu3JlHKuTCzd073Rwvd4EuhU.fEfTjD0yQvxTEa','ENSEIGNANT','2026-10-27 00:35:04'),(8,'Bendarag','Abdelkrim','a.bendarag@departement.ma','$2y$10$36uMRgOyrYlzsoHu3JlHKuTCzd073Rwvd4EuhU.fEfTjD0yQvxTEa','ENSEIGNANT','2026-10-27 00:35:04'),(9,'Alioua','Tariq','t.alioua@departement.ma','$2y$10$36uMRgOyrYlzsoHu3JlHKuTCzd073Rwvd4EuhU.fEfTjD0yQvxTEa','ENSEIGNANT','2026-10-27 00:35:04'),(10,'Afilal','Mehdi','m.afilal@departement.ma','$2y$10$36uMRgOyrYlzsoHu3JlHKuTCzd073Rwvd4EuhU.fEfTjD0yQvxTEa','ENSEIGNANT','2026-10-27 00:35:04'),(11,'EL AZZAM','Bilal','b.elazzam@etudiant.ma','$2y$10$W.6owTWmQiza.wznPCBHQef6ssJ0mEEJSt4FGoHgCwGob29Ff08Wu','ETUDIANT','2026-10-27 00:35:04'),(12,'MIRI','Aymen','a.miri@etudiant.ma','$2y$10$W.6owTWmQiza.wznPCBHQef6ssJ0mEEJSt4FGoHgCwGob29Ff08Wu','ETUDIANT','2026-10-27 00:35:04'),(13,'Yafii','Oussama','o.yafii@etudiant.ma','$2y$10$W.6owTWmQiza.wznPCBHQef6ssJ0mEEJSt4FGoHgCwGob29Ff08Wu','ETUDIANT','2026-10-27 00:35:04');
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-27 18:23:25
