
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `awesome_log`
--

-- --------------------------------------------------------

--
-- Table structure for table `awesome_exceptions`
--

CREATE TABLE IF NOT EXISTS `awesome_exceptions` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `exception_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `post_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sc` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `header_data` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_data` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sql_query` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errfile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errline` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `call_stack` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trace` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `func` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_of_times` int(11) DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `datatype_mismatch`
--

CREATE TABLE IF NOT EXISTS `datatype_mismatch` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `app_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_type` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sc` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `position` int(10) DEFAULT NULL,
  `request_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditional` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php7_result` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lhs_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lhs_datatype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rhs_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rhs_datatype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_lhs_dt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_rhs_dt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_match` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extras` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invalid_lhs_dt` (`invalid_lhs_dt`),
  KEY `invalid_rhs_dt` (`invalid_rhs_dt`),
  KEY `module_slug` (`module_slug`),
  KEY `template_name` (`template_name`),
  KEY `conditional` (`conditional`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=COMPACT;
COMMIT;
