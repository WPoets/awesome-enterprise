
SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;
SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `notification_log`;
CREATE TABLE `notification_log` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notification_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_provider` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_to` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cc` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bcc` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notification_from` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reply_to` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `object_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `object_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_stage` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tracking_set` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `message_to` (`notification_to`),
  KEY `message_from` (`notification_from`),
  KEY `message_type` (`notification_type`),
  KEY `object_id` (`object_id`),
  KEY `tracking_id` (`tracking_id`),
  KEY `subject` (`subject`),
  KEY `tracking_set` (`tracking_set`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;