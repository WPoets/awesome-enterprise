-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 07, 2021 at 11:42 PM
-- Server version: 10.5.5-MariaDB-1:10.5.5+maria~bionic
-- PHP Version: 7.0.33-7+ubuntu18.04.1+deb.sury.org+1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `v4test_loantap_in`
--

-- --------------------------------------------------------

--
-- Table structure for table `awesome_exceptions`
--

CREATE TABLE IF NOT EXISTS `awesome_exceptions_old` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `exception_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `post_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `app_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sc` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int(11) DEFAULT NULL,
  `link` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `errno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errfile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errline` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trace` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_of_times` int(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


CREATE TABLE IF NOT EXISTS `awesome_exceptions` (
  `ID` bigint(20) NOT NULL AUTO_INCREMENT,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `modified_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
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
   `sql_query` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `request_url` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errno` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errfile` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `errline` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `call_stack` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trace` longtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `conditional` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `php7_result` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lhs_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lhs_datatype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rhs_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rhs_datatype` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_lhs_dt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_rhs_dt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_match` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
   `func` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_of_times` int(11) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;
