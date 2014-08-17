-- MySQL dump 10.13  Distrib 5.5.37, for Linux (i686)
--
-- Host: localhost    Database: mk9_timeclock
-- ------------------------------------------------------
-- Server version	5.5.37-cll

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) unsigned NOT NULL,
  `dept_id` int(11) unsigned DEFAULT NULL,
  `username` varchar(32) NOT NULL,
  `first_name` varchar(32) NOT NULL,
  `last_name` varchar(32) NOT NULL,
  `password` varchar(128) NOT NULL,
  `email` varchar(120) NOT NULL,
  `phone` varchar(24) DEFAULT NULL,
  `signature` tinytext,
  `theme` varchar(16) DEFAULT NULL,
  `is_working` tinyint(1) unsigned DEFAULT NULL,
  `is_active` tinyint(1) unsigned NOT NULL,
  `is_freelance` tinyint(1) unsigned DEFAULT NULL,
  `is_visible` tinyint(1) unsigned NOT NULL,
  `is_admin` tinyint(1) unsigned NOT NULL,
  `hours_worked` int(11) unsigned DEFAULT NULL,
  `created` datetime NOT NULL,
  `schedule` varchar(256) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,1001,500,'mgutierrez','Marcus','Gutierrez','$2y$10$9PxIU6FzjqCvxFx2x0JXguXuZtiE1xZxpRroEGIHlYsEqDfMdhFUy','mgutierrez@loudservers.com',NULL,NULL,NULL,0,1,0,1,1,NULL,'2014-04-05 09:00:00',NULL),(2,1002,500,'hgarmon','Harper','Garmon','$2y$10$1ErMPOMjJr5vEAzAlpo7EOWD7PyQLTwFbkUOgvrNgiN4JfaHi8lJW','hgarmon@loudservers.com',NULL,NULL,'dark',0,1,0,1,0,NULL,'2014-04-05 12:00:00',NULL),(3,1003,500,'tborosko','Tomas','Borosko','$2y$10$r0FE/oh7YDkgPFXvvOFseetlV8FnohVGLBBrhzc2sMs0HzTiP86y.','tborosko@loudservers.com',NULL,NULL,NULL,0,1,1,1,0,NULL,'2014-07-19 12:00:00',NULL),(4,1004,500,'beaton','Ben','Eaton','$2y$10$jilW/yOIN4bRS0eO/GYGeeKP/7tnZ9FaT5CeVixoGnneROpI.YJ.6','beaton@loudservers.com',NULL,NULL,NULL,0,1,0,1,0,NULL,'2014-07-20 22:00:00',NULL);
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

-- Dump completed on 2014-07-26 23:39:22
