-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 26, 2025 at 05:00 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `swiftpay_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `transaction_type` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `related_user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `transaction_type`, `amount`, `related_user_id`, `created_at`) VALUES
(1, 3, 'top-up', 10000.00, NULL, '2025-07-22 14:20:19'),
(2, 3, 'withdraw', 5000.00, NULL, '2025-07-22 14:20:25'),
(3, 1, 'top-up', 10000.00, NULL, '2025-07-22 14:57:11'),
(4, 1, 'transfer-out', 5000.00, 4, '2025-07-22 14:57:20'),
(5, 4, 'transfer-in', 5000.00, 1, '2025-07-22 14:57:20'),
(6, 1, 'transfer-out', 2000.00, 4, '2025-07-22 15:08:20'),
(7, 4, 'transfer-in', 2000.00, 1, '2025-07-22 15:08:20'),
(8, 1, 'transfer-out', 1000.00, 4, '2025-07-22 15:21:56'),
(9, 4, 'transfer-in', 1000.00, 1, '2025-07-22 15:21:56'),
(10, 1, 'transfer-out', 10.00, 4, '2025-07-22 15:22:41'),
(11, 4, 'transfer-in', 10.00, 1, '2025-07-22 15:22:41'),
(12, 1, 'transfer-out', 11.00, 4, '2025-07-22 15:34:55'),
(13, 4, 'transfer-in', 11.00, 1, '2025-07-22 15:34:55'),
(14, 1, '', 12.00, 4, '2025-07-22 15:49:23'),
(15, 1, '', 13.00, 4, '2025-07-22 15:54:37'),
(16, 1, '', 14.00, 4, '2025-07-22 16:11:54'),
(17, 1, '', 15.00, 4, '2025-07-22 16:15:47'),
(18, 5, 'top-up', 100.00, NULL, '2025-07-22 16:17:29'),
(19, 5, '', 10.00, 4, '2025-07-22 16:17:36'),
(20, 5, '', 11.00, 4, '2025-07-22 16:20:42'),
(21, 5, '', 12.00, 4, '2025-07-22 16:21:46'),
(22, 5, '', 13.00, 4, '2025-07-22 16:23:13'),
(23, 5, 'transfer', 14.00, 4, '2025-07-22 16:26:00'),
(24, 5, 'withdraw', 100.00, NULL, '2025-07-22 16:28:16'),
(25, 5, 'top-up', 100.11, NULL, '2025-07-25 11:04:25'),
(26, 5, 'transfer', 1000.00, 4, '2025-07-25 11:21:13'),
(27, 5, 'top-up', 5000.00, NULL, '2025-07-25 11:24:29'),
(28, 5, 'top-up', 100000.00, NULL, '2025-07-25 13:36:18'),
(29, 5, 'top-up', 333.00, NULL, '2025-07-25 13:37:52'),
(30, 5, 'top-up', 111.00, NULL, '2025-07-25 13:38:02'),
(31, 5, 'top-up', 111.00, NULL, '2025-07-25 13:42:24'),
(32, 5, 'top-up', 100.00, NULL, '2025-07-25 13:42:29'),
(33, 5, 'top-up', 10.00, NULL, '2025-07-25 13:46:54'),
(34, 5, 'top-up', 100.00, NULL, '2025-07-25 13:47:01'),
(35, 5, 'withdraw', 1000.00, NULL, '2025-07-25 13:50:38'),
(36, 5, 'withdraw', 100.00, NULL, '2025-07-25 14:03:09'),
(37, 5, 'transfer', 100.00, 4, '2025-07-26 14:08:09'),
(38, 5, 'top-up', 100000.00, NULL, '2025-07-25 14:14:09'),
(39, 5, 'top-up', 100.00, NULL, '2025-07-25 14:25:18'),
(40, 5, 'top-up', 1000000.00, NULL, '2025-07-25 14:30:50'),
(41, 5, 'top-up', 100000.00, NULL, '2025-07-25 14:35:05'),
(42, 5, 'top-up', 100000.00, NULL, '2025-07-25 14:38:46'),
(43, 5, 'top-up', 1.00, NULL, '2025-07-03 14:42:43');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `account_number` varchar(20) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `avatar` varchar(255) DEFAULT 'default.png',
  `status` enum('active','deactivated') DEFAULT 'active',
  `deleted_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `email`, `password`, `account_number`, `balance`, `avatar`, `status`, `deleted_at`, `created_at`) VALUES
(1, 'Sean Audric R. Salvador', 'sean.salvador@ciit.edu.ph', '$2y$10$B2eJ5QqRw9MhYErJW5/fGuB.Zff2cy54AO.IM1iJ1A0uu4Zgfn0XW', NULL, 1897.00, '687f9801b1b78_images.jpg', 'active', NULL, '2025-07-22 13:07:33'),
(2, 'Mister Test', 'tester@gmail.com', '$2y$10$bxrOMMvNDLSd4aEYNPDq7efsFwFoPQXIgdi4cS18JKjqiSpWjLT0S', NULL, 0.00, 'default.png', 'active', NULL, '2025-07-22 13:08:50'),
(3, 'Bruh Bro', 'bruh@gmail.com', '$2y$10$TSroBo8t2PoRp1GHAI..Fu37bHU.B7rU.7s3eNSvmAMj3d.K/G4oO', NULL, 5000.00, '687f9e01a7773_loading.png', 'active', NULL, '2025-07-22 14:19:45'),
(4, 'brow', 'brow@gmail.com', '$2y$10$vzD87t4JiKGm/ndkrAjOB.A5W.PeA7WQcxLRq934tgmXTyGm8TfM.', '1229172968', 9263.00, '687fa4a40d2dc_latest-removebg-preview.png', 'active', NULL, '2025-07-22 14:48:04'),
(5, 'bruhhh', 'bruhhh@gmail.com', '$2y$10$jU3DmIrb0t22U0yW53uFouPaL9c7vD83b/aHYcihaCsQcOimaWYJy', '9327000943', 1404706.11, '6883922a19a05_SwiftPay.png', 'active', NULL, '2025-07-22 16:16:54'),
(6, 'Sean Audric R. Salvador', 'sean@gmail.com', '$2y$10$8QXonKHgWbEXSl3aWShaJOGR/Gt0QvFkW1LthmFKziiKBwWgEFspO', '8399073985', 0.00, '68839a0587464_images.jpg', 'active', NULL, '2025-07-25 14:51:49'),
(7, 'huh', 'huh@gmail.com', '$2y$10$mW9TdsInGGWbkfQycrJJ/O7CzzkyLVmV4m.8QlLa7CC.Pb.Z8YAaG', '4402458985', 0.00, '6883a6ee4f39e_image-removebg-preview-removebg-preview.png', 'deactivated', '2025-07-26 00:08:39', '2025-07-25 15:46:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `related_user_id` (`related_user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `account_number` (`account_number`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`related_user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
