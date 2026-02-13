-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 13, 2026 at 05:58 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `affiliate_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `commissions`
--

CREATE TABLE `commissions` (
  `id` int(10) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED NOT NULL,
  `beneficiary_id` int(10) UNSIGNED NOT NULL,
  `source_user_id` int(10) UNSIGNED NOT NULL,
  `level` tinyint(3) UNSIGNED NOT NULL,
  `sale_amount` decimal(12,2) NOT NULL,
  `commission_rate` decimal(5,2) NOT NULL,
  `commission_amount` decimal(12,2) NOT NULL,
  `status` enum('pending','paid','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `paid_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `commissions`
--

INSERT INTO `commissions` (`id`, `sale_id`, `beneficiary_id`, `source_user_id`, `level`, `sale_amount`, `commission_rate`, `commission_amount`, `status`, `created_at`, `paid_at`) VALUES
(17, 6, 7, 8, 1, 1000.00, 10.00, 100.00, 'pending', '2026-02-13 22:21:17', NULL),
(18, 6, 6, 8, 2, 1000.00, 5.00, 50.00, 'pending', '2026-02-13 22:21:17', NULL),
(19, 6, 5, 8, 3, 1000.00, 3.00, 30.00, 'pending', '2026-02-13 22:21:17', NULL),
(20, 6, 4, 8, 4, 1000.00, 2.00, 20.00, 'pending', '2026-02-13 22:21:17', NULL),
(21, 6, 3, 8, 5, 1000.00, 1.00, 10.00, 'pending', '2026-02-13 22:21:17', NULL),
(22, 7, 5, 6, 1, 500.00, 10.00, 50.00, 'pending', '2026-02-13 22:21:17', NULL),
(23, 7, 4, 6, 2, 500.00, 5.00, 25.00, 'pending', '2026-02-13 22:21:17', NULL),
(24, 7, 3, 6, 3, 500.00, 3.00, 15.00, 'pending', '2026-02-13 22:21:17', NULL),
(25, 7, 2, 6, 4, 500.00, 2.00, 10.00, 'pending', '2026-02-13 22:21:17', NULL),
(26, 7, 1, 6, 5, 500.00, 1.00, 5.00, 'pending', '2026-02-13 22:21:17', NULL),
(27, 8, 2, 9, 1, 200.00, 10.00, 20.00, 'pending', '2026-02-13 22:21:17', NULL),
(28, 8, 1, 9, 2, 200.00, 5.00, 10.00, 'pending', '2026-02-13 22:21:17', NULL),
(29, 10, 6, 8, 2, 1000.00, 5.00, 50.00, 'pending', '2026-02-13 22:21:17', NULL),
(30, 10, 5, 8, 3, 1000.00, 3.00, 30.00, 'pending', '2026-02-13 22:21:17', NULL),
(31, 10, 4, 8, 4, 1000.00, 2.00, 20.00, 'pending', '2026-02-13 22:21:17', NULL),
(32, 10, 3, 8, 5, 1000.00, 1.00, 10.00, 'pending', '2026-02-13 22:21:17', NULL),
(33, 11, 1, 2, 1, 800.00, 10.00, 80.00, 'pending', '2026-02-13 22:21:17', NULL),
(34, 12, 1, 10, 1, 300.00, 10.00, 30.00, 'pending', '2026-02-13 22:21:17', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `description` varchar(500) DEFAULT NULL,
  `status` enum('pending','completed','refunded','cancelled') NOT NULL DEFAULT 'completed',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `user_id`, `amount`, `description`, `status`, `created_at`) VALUES
(6, 8, 1000.00, 'Test Hank', 'completed', '2026-02-13 22:21:17'),
(7, 6, 500.00, NULL, 'completed', '2026-02-13 22:21:17'),
(8, 9, 200.00, NULL, 'completed', '2026-02-13 22:21:17'),
(9, 1, 100.00, NULL, 'completed', '2026-02-13 22:21:17'),
(10, 8, 1000.00, NULL, 'completed', '2026-02-13 22:21:17'),
(11, 2, 800.00, NULL, 'completed', '2026-02-13 22:21:17'),
(12, 10, 300.00, NULL, 'completed', '2026-02-13 22:21:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL DEFAULT '',
  `parent_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `full_name`, `parent_id`, `status`, `created_at`) VALUES
(1, 'arjun', 'arjun@example.com', 'Arjun Kumar', NULL, 'active', '2026-02-13 16:37:49'),
(2, 'vijay', 'vijay@example.com', 'Vijay Raj', 1, 'active', '2026-02-13 16:37:49'),
(3, 'ajith', 'ajith@example.com', 'Ajith Kumar', 2, 'active', '2026-02-13 16:37:49'),
(4, 'priya', 'priya@example.com', 'Priya Devi', 3, 'active', '2026-02-13 16:37:49'),
(5, 'deepa', 'deepa@example.com', 'Deepa Nair', 4, 'active', '2026-02-13 16:37:49'),
(6, 'kavya', 'kavya@example.com', 'Kavya Iyer', 5, 'active', '2026-02-13 16:37:49'),
(7, 'nithya', 'nithya@example.com', 'Nithya Reddy', 6, 'active', '2026-02-13 16:37:49'),
(8, 'suresh', 'suresh@example.com', 'Suresh Menon', 7, 'active', '2026-02-13 16:37:49'),
(9, 'ganesh', 'ganesh@example.com', 'Ganesh Rao', 2, 'active', '2026-02-13 16:37:49'),
(10, 'lakshmi', 'lakshmi@example.com', 'Lakshmi Priya', 1, 'active', '2026-02-13 16:37:49'),
(11, 'ravi', 'ravi@example.com', 'Ravi Shankar', 10, 'active', '2026-02-13 16:52:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `commissions`
--
ALTER TABLE `commissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_sale_beneficiary` (`sale_id`,`beneficiary_id`),
  ADD KEY `idx_beneficiary` (`beneficiary_id`),
  ADD KEY `idx_source` (`source_user_id`),
  ADD KEY `idx_beneficiary_status` (`beneficiary_id`,`status`),
  ADD KEY `idx_sale_level` (`sale_id`,`level`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_user_status` (`user_id`,`status`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_username` (`username`),
  ADD UNIQUE KEY `uq_email` (`email`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_parent_status` (`parent_id`,`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `commissions`
--
ALTER TABLE `commissions`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `commissions`
--
ALTER TABLE `commissions`
  ADD CONSTRAINT `fk_comm_beneficiary` FOREIGN KEY (`beneficiary_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comm_sale` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comm_source` FOREIGN KEY (`source_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `fk_sale_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_user_parent` FOREIGN KEY (`parent_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
