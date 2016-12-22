CREATE DATABASE `lethal_assassins` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;

CREATE TABLE `player` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(191) NOT NULL,
  `rank` int(11) NOT NULL,
  `joindate` date DEFAULT NULL,
  `discordId` varchar(255) DEFAULT NULL,
  `active` tinyint(4) DEFAULT '1',
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name_UNIQUE` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4;
