SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `v4test_loantap_in`
--

-- --------------------------------------------------------

--
-- Table structure for table `datatype_mismatch`
--

CREATE TABLE `datatype_mismatch` (
  `id` bigint(20) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `module_slug` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `conditional` varchar(20) DEFAULT NULL,
  `lhs_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lhs_datatype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rhs_value` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rhs_datatype` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_lhs_dt` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_rhs_dt` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invalid_match` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `extras` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `log_problems`
--

CREATE TABLE `log_problems` (
  `id` bigint(20) NOT NULL,
  `problem_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `module_slug` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template` varchar(100) DEFAULT NULL,
  `post_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_number` int(11) DEFAULT NULL,
  `func` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `class` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `method` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `extras` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `datatype_mismatch` ADD `php7_result` VARCHAR(15) NULL AFTER `conditional`;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `datatype_mismatch`
--
ALTER TABLE `datatype_mismatch`
  ADD PRIMARY KEY (`id`),
  ADD KEY `invalid_lhs_dt` (`invalid_lhs_dt`),
  ADD KEY `invalid_rhs_dt` (`invalid_rhs_dt`),
  ADD KEY `module_slug` (`module_slug`),
  ADD KEY `template_name` (`template_name`),
  ADD KEY `conditional` (`conditional`);

--
-- Indexes for table `log_problems`
--
ALTER TABLE `log_problems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `problem_type` (`problem_type`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `datatype_mismatch`
--
ALTER TABLE `datatype_mismatch`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `log_problems`
--
ALTER TABLE `log_problems`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;
  
COMMIT;
