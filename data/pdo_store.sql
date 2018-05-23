SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

DROP TABLE IF EXISTS `KVStore`;
CREATE TABLE `KVStore` (
  `realm` tinytext NOT NULL,
  `key` tinytext NOT NULL,
  `value` text NOT NULL,
  KEY `realm_key` (`realm`(255),`key`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
