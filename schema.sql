-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.3.39-MariaDB-0+deb10u2 - Debian 10
-- Server OS:                    debian-linux-gnu
-- HeidiSQL Version:             12.15.0.7171
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table yno_stage.actors
CREATE TABLE IF NOT EXISTS `actors` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `login` varchar(32) NOT NULL,
  `password` char(60) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uuid` (`uuid`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table yno_stage.actors: ~1 rows (approximately)
INSERT INTO `actors` (`id`, `uuid`, `active`, `name`, `login`, `password`) VALUES
	(1, 'fcb68880-8d82-4085-8122-636d6b9d2849', 0, 'Администратор', 'admin', '$2a$12$BZ3TFbLIsBLaqRTkCFfq8O66AkGRjHW87QP7IXzgRktYJRD0b6zRa');

-- Dumping structure for table yno_stage.messages
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `token_id` int(10) unsigned NOT NULL,
  `actor_id` int(10) unsigned DEFAULT NULL,
  `value` text NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  KEY `FK_messages_tokens` (`token_id`),
  KEY `FK_messages_actors` (`actor_id`),
  CONSTRAINT `FK_messages_actors` FOREIGN KEY (`actor_id`) REFERENCES `actors` (`id`),
  CONSTRAINT `FK_messages_tokens` FOREIGN KEY (`token_id`) REFERENCES `tokens` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table yno_stage.messages: ~0 rows (approximately)

-- Dumping structure for table yno_stage.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `token` char(64) NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`token`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table yno_stage.sessions: ~0 rows (approximately)

-- Dumping structure for table yno_stage.tokens
CREATE TABLE IF NOT EXISTS `tokens` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) NOT NULL,
  `value` varchar(16) NOT NULL,
  `active` tinyint(3) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uuid` (`uuid`),
  UNIQUE KEY `value` (`value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- Dumping data for table yno_stage.tokens: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
