# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 91.208.99.2 (MySQL 5.6.21)
# Database: invoices
# Generation Time: 2015-05-31 17:45:26 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `account`;

CREATE TABLE `account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(80) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `account` WRITE;
/*!40000 ALTER TABLE `account` DISABLE KEYS */;

INSERT INTO `account` (`id`, `username`, `password`)
VALUES
	(1,'steveedson','$2y$10$HdmxHV98rIasddpUIBWw9ukIDAaqeEERhvSnLL.Yp91jcVlnr2Vy.');

/*!40000 ALTER TABLE `account` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table invoice
# ------------------------------------------------------------

DROP TABLE IF EXISTS `invoice`;

CREATE TABLE `invoice` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `account_id` int(11) NOT NULL,
  `title` varchar(80) DEFAULT NULL,
  `amount` int(11) NOT NULL,
  `description` varchar(400) DEFAULT NULL,
  `issue_date` datetime DEFAULT NULL,
  `status` enum('PAID','UNPAID') NOT NULL DEFAULT 'UNPAID',
  `stripe_charge` varchar(28) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

LOCK TABLES `invoice` WRITE;
/*!40000 ALTER TABLE `invoice` DISABLE KEYS */;

INSERT INTO `invoice` (`id`, `account_id`, `title`, `amount`, `description`, `issue_date`, `status`, `stripe_charge`, `payment_date`)
VALUES
	(1,1,'Website',2124,'Awesome website','2015-05-31 16:20:00','PAID','ch_168ZjfLnZMJRPIZaEeDn0NPG','2015-05-31 17:56:32'),
	(2,1,'Domain',1400,'google.com','2015-05-31 16:20:00','UNPAID',NULL,NULL);

/*!40000 ALTER TABLE `invoice` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
