-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 20, 2025 at 01:34 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_enterprise`
--

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `barcode` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `payment_due_quantity` int(11) DEFAULT 0,
  `tax_percent` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `discount_percent` decimal(5,2) DEFAULT 0.00,
  `expiry_date` date DEFAULT NULL,
  `supplier_name` varchar(100) DEFAULT NULL,
  `supplier_address` varchar(255) DEFAULT NULL,
  `supplier_contact` varchar(100) DEFAULT NULL,
  `is_paid` tinyint(1) DEFAULT 0,
  `paid_at` datetime DEFAULT NULL,
  `cost_price` decimal(10,2) DEFAULT 0.00,
  `original_quantity` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `supplier_id`, `name`, `barcode`, `price`, `quantity`, `payment_due_quantity`, `tax_percent`, `created_at`, `image`, `discount_percent`, `expiry_date`, `supplier_name`, `supplier_address`, `supplier_contact`, `is_paid`, `paid_at`, `cost_price`, `original_quantity`) VALUES
(1, NULL, 'Sufi Washing liquid ', '12345', 200.00, 2, 0, 0.00, '2025-04-30 06:37:38', '1745996316_1020353-1.jpg', 0.00, '2025-05-02', NULL, NULL, NULL, 0, NULL, 0.00, 0),
(2, NULL, 'Sufi Washing powder', '123123', 250.00, 1, 0, 5.00, '2025-04-30 06:48:40', '1745995720_images.jpg', 2.00, '2025-05-01', NULL, NULL, NULL, 0, NULL, 0.00, 0),
(5, NULL, 'FFC logo', '12121', 250.00, 20, 0, 0.00, '2025-05-12 11:34:02', '1747049642_tt.png', 0.00, '2025-06-12', 'shakir bhai', 'dsds sdsd sdff', '03462671465', 0, NULL, 0.00, 0),
(6, NULL, 'BH LOGO', '123456645', 125.00, 15, 0, 0.00, '2025-05-12 11:35:10', '1747049710_1654494868815.jpg', 0.00, '2025-06-12', 'shakir bhai', 'ddd fe rfe erte ertre t r', '123456789', 0, NULL, 0.00, 0),
(7, 6, 'bhp name ', '147477', 100.00, 19, 0, 0.00, '2025-05-13 05:52:06', '1747115526_PP.png', 0.00, '2025-06-14', 'ahmed&company', 'qwdew wsd wedwedf wefefe', '123145678', 1, '2025-05-13 11:38:33', 0.00, 0),
(8, 6, 'screen', '564789', 150.00, 9, 0, 0.00, '2025-05-13 05:52:06', '1747115526_Screenshot 2025-05-02 100541.png', 50.00, '2025-05-14', 'ahmed&company', 'qwdew wsd wedwedf wefefe', '123145678', 1, '2025-05-13 16:04:33', 0.00, 0),
(9, 7, 'log of man', '123321', 200.00, 49, 0, 0.00, '2025-05-14 11:56:52', '1747223812_8b167af653c2399dd93b952a48740620.jpg', 0.12, '2025-06-14', 'HABIB & CO', 'qwe wedw', '12314567478', 0, NULL, 0.00, 0),
(10, 1, 'waseem ', '12321234', 100.00, 2, 0, 0.00, '2025-05-15 12:00:10', '1747310410_Muhammad Waseem Shah (2).jpg', 0.00, '2025-05-16', 'shakir bhai', 'dsds sdsd sdff', '03462671465', 0, NULL, 0.00, 0),
(11, 1, 'waseem', '454568', 100.00, 6, 0, 0.00, '2025-05-19 07:24:30', '1747639470_Muhammad Waseem Shah (2).jpg', 0.50, '2025-05-20', 'shakir bhai', 'dsds sdsd sdff', '03462671465', 0, NULL, 0.00, 0),
(12, 1, 'as', '1213234343', 10.00, 10, 0, 0.00, '2025-05-19 11:48:28', '1747655308_Muhammad Waseem Shah (2).jpg', 0.00, '0000-00-00', 'shakir bhai', 'dsds sdsd sdff', '03462671465', 0, NULL, NULL, 0),
(13, 1, 'dfd', '112343', 23.00, 12, 0, 0.00, '2025-05-19 11:56:16', '1747655776_Muhammad Waseem Shah (2).jpg', 0.00, '2025-05-20', 'shakir bhai', 'dsds sdsd sdff', '03462671465', 0, NULL, 24.00, 0),
(14, 5, 'waseem', '12121212', 45.00, 23, 0, 0.00, '2025-05-20 05:44:48', '1747719888_Muhammad Waseem Shah (2).jpg', 0.00, '0000-00-00', 'asif noor Company', 'st 178 b;lock 2 gul', '1234567', 0, NULL, 40.00, 0),
(15, 7, 'omer pic', '12343234', 150.00, 0, 0, 0.00, '2025-05-20 07:40:47', '1747726847_Omer.jpeg', 0.00, '2025-05-21', 'HABIB & CO', 'qwe wedw', '12314567478', 0, NULL, 100.00, 0),
(16, 7, 'HIKO Dates 250 gm', '124578', 350.00, 24, 0, 0.00, '2025-05-20 09:26:17', '1747733177_HIKO Premium Dates.png', 0.00, '2025-06-20', 'HABIB & CO', 'qwe wedw', '12314567478', 0, NULL, 300.00, 0),
(17, 7, 'nasif', '325466', 300.00, 20, 0, 0.00, '2025-05-20 09:47:23', '1747734443_20250514120038432.png', 0.00, '2025-06-20', 'HABIB & CO', 'qwe wedw', '12314567478', 1, '2025-05-20 16:15:35', 250.00, 0),
(18, 7, 'ss', '125', 250.00, 6, 0, 0.00, '2025-05-20 09:59:54', '1747735194_outllok.png', 0.00, '2025-05-22', 'HABIB & CO', 'qwe wedw', '12314567478', 0, NULL, 200.00, 10),
(19, 7, 'wwe', '434343', 150.00, 6, -2, 0.00, '2025-05-20 10:26:18', '1747736778_cert.png', 0.00, '2025-05-21', 'HABIB & CO', 'qwe wedw', '12314567478', 1, '2025-05-20 16:15:53', 100.00, 10),
(20, 7, 'outlook', '1213243', 100.00, 20, 20, 0.00, '2025-05-20 10:57:47', '1747738667_outtt.png', 0.00, '2025-05-21', 'HABIB & CO', 'qwe wedw', '12314567478', 1, '2025-05-20 16:15:32', 150.00, 0),
(21, 7, 'edfge', 'efre', 3234.00, 22, 22, 0.00, '2025-05-20 11:07:22', '1747739242_about op.png', 0.00, '2025-05-14', 'HABIB & CO', 'qwe wedw', '12314567478', 1, '2025-05-20 16:16:00', 242.00, 0),
(22, 7, 'tupi', '123334', 20.00, 10, 9, 0.00, '2025-05-20 11:14:15', '1747739655_jk.png', 0.00, '2025-05-22', 'HABIB & CO', 'qwe wedw', '12314567478', 0, NULL, 10.00, 0),
(23, 7, 'shahid', '123434', 20.00, 9, 9, 0.00, '2025-05-20 11:25:30', '1747740330_ssss.png', 0.00, '2025-05-22', 'HABIB & CO', 'qwe wedw', '12314567478', 0, NULL, 15.00, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_returns`
--

CREATE TABLE `product_returns` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `return_qty` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtract_qty` tinyint(1) DEFAULT 0,
  `resolved` tinyint(1) DEFAULT 0,
  `received` tinyint(4) DEFAULT 0,
  `received_qty` int(11) DEFAULT 0,
  `received_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_returns`
--

INSERT INTO `product_returns` (`id`, `product_id`, `return_qty`, `reason`, `return_date`, `created_at`, `subtract_qty`, `resolved`, `received`, `received_qty`, `received_at`) VALUES
(1, 7, 1, '', '2025-04-13', '2025-05-13 07:37:14', 0, 0, 0, 0, NULL),
(2, 7, 2, ' not good', '2025-05-13', '2025-05-13 07:38:09', 0, 0, 0, 0, NULL),
(3, 2, 1, 'asis', '2025-05-13', '2025-05-13 07:39:10', 0, 0, 0, 0, NULL),
(4, 2, 1, 'asai', '2025-05-13', '2025-05-13 07:41:16', 1, 0, 0, 0, NULL),
(5, 6, 1, '11', '2025-05-13', '2025-05-13 07:53:18', 1, 1, 1, 0, NULL),
(6, 6, 1, 'asai', '2025-05-13', '2025-05-13 07:59:12', 1, 0, 1, 0, NULL),
(7, 6, 1, 'asdai', '2025-05-13', '2025-05-13 08:04:34', 1, 0, 1, 0, NULL),
(8, 6, 5, 'not good', '2025-05-13', '2025-05-13 08:21:00', 0, 0, 0, 0, NULL),
(9, 6, 5, '', '2025-05-13', '2025-05-13 09:10:54', 1, 0, 0, 3, '2025-05-13 16:03:46'),
(10, 6, 6, 'awaien', '2025-05-13', '2025-05-13 09:22:25', 1, 1, 0, 6, '2025-05-13 14:31:44'),
(11, 6, 1, '', '2025-05-14', '2025-05-14 09:52:08', 1, 1, 0, 1, '2025-05-14 16:13:22'),
(12, 6, 1, '', '2025-05-14', '2025-05-14 10:16:10', 1, 1, 0, 1, '2025-05-14 16:13:20'),
(13, 6, 1, '', '2025-05-14', '2025-05-14 10:17:23', 1, 1, 0, 1, '2025-05-14 16:13:19'),
(14, 6, 1, '', '2025-05-14', '2025-05-14 11:03:24', 1, 1, 0, 1, '2025-05-14 16:13:15'),
(15, 6, 1, '', '2025-05-14', '2025-05-14 11:03:31', 1, 1, 0, 1, '2025-05-14 16:13:16'),
(16, 6, 1, 'asai', '2025-05-14', '2025-05-14 11:11:02', 1, 1, 0, 1, '2025-05-14 16:13:14'),
(17, 6, 1, 'asai', '2025-05-14', '2025-05-14 11:11:31', 1, 1, 0, 1, '2025-05-14 16:13:13'),
(18, 6, 1, 'asai', '2025-05-14', '2025-05-14 11:11:52', 1, 1, 0, 1, '2025-05-14 16:13:12'),
(19, 6, 1, '', '2025-05-14', '2025-05-14 11:12:35', 1, 1, 0, 1, '2025-05-14 16:13:11'),
(20, 6, 5, 'testing', '2025-05-14', '2025-05-14 11:16:17', 1, 0, 0, 0, NULL),
(21, 9, 2, '', '2025-05-14', '2025-05-14 11:57:23', 0, 0, 0, 1, '2025-05-14 16:58:19'),
(22, 11, 4, 'asai', '2025-05-20', '2025-05-20 05:46:13', 0, 0, 0, 0, NULL),
(23, 18, 2, '', '2025-05-20', '2025-05-20 10:01:35', 0, 0, 0, 0, NULL),
(24, 19, 2, '', '2025-05-20', '2025-05-20 10:27:45', 0, 0, 0, 0, NULL),
(25, 19, 2, '', '2025-05-20', '2025-05-20 10:34:30', 0, 0, 0, 0, NULL),
(26, 19, 2, '', '2025-05-20', '2025-05-20 10:35:53', 0, 0, 0, 0, NULL),
(27, 19, 2, '', '2025-05-20', '2025-05-20 10:36:54', 0, 0, 0, 0, NULL),
(28, 22, 2, '', '2025-05-20', '2025-05-20 11:14:44', 0, 1, 0, 2, '2025-05-20 16:24:25'),
(29, 23, 2, '', '2025-05-20', '2025-05-20 11:26:08', 0, 0, 0, 1, '2025-05-20 16:26:39');

-- --------------------------------------------------------

--
-- Table structure for table `return_received`
--

CREATE TABLE `return_received` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `received_by` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_received`
--

INSERT INTO `return_received` (`id`, `product_id`, `quantity`, `reason`, `return_date`, `received_by`, `created_at`, `resolved`) VALUES
(1, 6, 2, '', '2025-05-13', '5', '2025-05-13 08:19:02', 0),
(2, 7, 1, '', '2025-05-13', '5', '2025-05-13 08:19:53', 0),
(3, 6, 2, '', '2025-05-13', '5', '2025-05-13 09:11:46', 0),
(4, 6, 2, '', '2025-05-13', '5', '2025-05-13 09:23:08', 0);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` enum('cash','card','qr') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `invoice_no`, `user_id`, `total_amount`, `payment_mode`, `created_at`) VALUES
(1, NULL, 2, 200.00, 'cash', '2025-04-30 06:38:17'),
(2, NULL, 2, 250.00, 'cash', '2025-04-30 06:59:22'),
(3, NULL, 2, 450.00, 'cash', '2025-04-30 07:09:06'),
(4, NULL, 2, 200.00, 'cash', '2025-04-30 07:11:55'),
(5, NULL, 2, 700.00, 'cash', '2025-04-30 07:19:40'),
(6, NULL, 2, 900.00, 'cash', '2025-04-30 07:47:05'),
(7, NULL, 2, 1800.00, 'cash', '2025-04-30 07:48:23'),
(8, NULL, 2, 200.00, 'cash', '2025-04-30 07:49:12'),
(9, NULL, 6, 700.00, 'cash', '2025-04-30 08:03:20'),
(10, NULL, 6, 2000.00, 'cash', '2025-04-30 08:20:55'),
(11, NULL, 2, 500.00, 'qr', '2025-04-30 08:43:30'),
(12, NULL, 6, 1200.00, 'cash', '2025-04-30 09:15:17'),
(13, NULL, 6, 250.00, 'cash', '2025-04-30 11:17:52'),
(14, NULL, 6, 450.00, 'cash', '2025-04-30 11:47:52'),
(15, NULL, 2, 250.00, 'cash', '2025-04-30 13:15:25'),
(16, NULL, 6, 600.00, 'cash', '2025-04-30 13:21:29'),
(17, NULL, 5, 125.00, 'cash', '2025-05-13 06:41:19'),
(18, NULL, 5, 200.00, 'cash', '2025-05-13 11:18:13'),
(19, NULL, 5, 850.00, 'cash', '2025-05-13 11:34:12'),
(20, NULL, 5, 300.00, 'cash', '2025-05-20 09:22:23'),
(21, NULL, 5, 150.00, 'cash', '2025-05-20 09:22:51'),
(22, NULL, 5, 700.00, 'cash', '2025-05-20 09:27:32'),
(23, NULL, 5, 150.00, 'cash', '2025-05-20 09:31:15'),
(24, NULL, 5, 150.00, 'cash', '2025-05-20 09:33:09'),
(25, NULL, 5, 150.00, 'cash', '2025-05-20 09:33:19'),
(26, NULL, 5, 700.00, 'cash', '2025-05-20 09:33:58'),
(27, NULL, 5, 500.00, 'cash', '2025-05-20 10:00:34');

-- --------------------------------------------------------

--
-- Table structure for table `sales_returns`
--

CREATE TABLE `sales_returns` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `return_qty` int(11) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_returns`
--

INSERT INTO `sales_returns` (`id`, `sale_id`, `product_id`, `return_qty`, `reason`, `return_date`, `created_at`) VALUES
(1, 19, 2, 2, '', '2025-05-13', '2025-05-13 11:35:02'),
(2, 19, 7, 2, '', '2025-05-13', '2025-05-13 11:35:02'),
(3, 19, 8, 1, '', '2025-05-13', '2025-05-13 11:35:02'),
(4, 18, 1, 1, '', '2025-05-13', '2025-05-13 11:35:59'),
(5, 4, 1, 1, '', '2025-05-13', '2025-05-13 11:50:24'),
(6, 16, 1, 1, 'expired', '2025-05-13', '2025-05-13 11:53:17');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `product_id`, `quantity`, `unit_price`, `total_price`) VALUES
(1, 1, 1, 1, 200.00, 200.00),
(2, 2, 2, 1, 250.00, 250.00),
(3, 3, 1, 1, 200.00, 200.00),
(4, 3, 2, 1, 250.00, 250.00),
(5, 4, 1, 1, 200.00, 200.00),
(6, 5, 2, 2, 250.00, 500.00),
(7, 5, 1, 1, 200.00, 200.00),
(8, 6, 2, 2, 250.00, 500.00),
(9, 6, 1, 2, 200.00, 400.00),
(10, 7, 1, 9, 200.00, 1800.00),
(11, 8, 1, 1, 200.00, 200.00),
(12, 9, 1, 1, 200.00, 200.00),
(13, 9, 2, 2, 250.00, 500.00),
(14, 10, 1, 10, 200.00, 2000.00),
(15, 11, 2, 2, 250.00, 500.00),
(16, 12, 1, 6, 200.00, 1200.00),
(17, 13, 2, 1, 250.00, 250.00),
(18, 14, 2, 1, 250.00, 250.00),
(19, 14, 1, 1, 200.00, 200.00),
(20, 15, 2, 1, 250.00, 250.00),
(21, 16, 1, 3, 200.00, 600.00),
(22, 17, 6, 1, 125.00, 125.00),
(23, 18, 1, 1, 200.00, 200.00),
(24, 19, 2, 2, 250.00, 500.00),
(25, 19, 7, 2, 100.00, 200.00),
(26, 19, 8, 1, 150.00, 150.00),
(27, 20, 15, 2, 150.00, 300.00),
(28, 21, 15, 1, 150.00, 150.00),
(29, 22, 16, 2, 350.00, 700.00),
(30, 23, 15, 1, 150.00, 150.00),
(31, 24, 15, 1, 150.00, 150.00),
(32, 25, 15, 1, 150.00, 150.00),
(33, 26, 16, 2, 350.00, 700.00),
(34, 27, 18, 2, 250.00, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `sale_returns`
--

CREATE TABLE `sale_returns` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `return_qty` int(11) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_returns`
--

INSERT INTO `sale_returns` (`id`, `sale_id`, `product_id`, `return_qty`, `reason`, `return_date`, `created_at`) VALUES
(1, 16, 1, 1, 'change the product', '2025-05-13', '2025-05-13 11:10:07');

-- --------------------------------------------------------

--
-- Table structure for table `stock_avail`
--

CREATE TABLE `stock_avail` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_avail`
--

INSERT INTO `stock_avail` (`id`, `name`, `quantity`) VALUES
(1, 'efe', 1);

-- --------------------------------------------------------

--
-- Table structure for table `stock_entries`
--

CREATE TABLE `stock_entries` (
  `id` int(11) NOT NULL,
  `stock_id` varchar(100) DEFAULT NULL,
  `stock_name` varchar(100) DEFAULT NULL,
  `stock_supplier_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `company_price` decimal(10,2) DEFAULT NULL,
  `selling_price` decimal(10,2) DEFAULT NULL,
  `opening_stock` int(11) DEFAULT NULL,
  `closing_stock` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `mode` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `count1` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `stock_entries`
--

INSERT INTO `stock_entries` (`id`, `stock_id`, `stock_name`, `stock_supplier_name`, `quantity`, `company_price`, `selling_price`, `opening_stock`, `closing_stock`, `date`, `username`, `type`, `total`, `mode`, `description`, `subtotal`, `count1`) VALUES
(1, 'PR001', 'efe', 'shakir bhai', 1, 1.00, 1.00, 0, 1, '2025-05-12 12:51:09', 'admin', 'entry', 1.00, 'Cash', '', 1.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `name`, `contact_person`, `phone`, `email`, `address`, `created_at`) VALUES
(1, 'shakir bhai', 'Noman', '03462671465', 'habib.rehman@barretthodgson.com', 'dsds sdsd sdff', '2025-05-12 15:03:32'),
(2, 'shakir bhai', 'Noman', '03462671465', 'habib.rehman@barretthodgson.com', 'dsds sdsd sdff', '2025-05-12 15:03:43'),
(3, 'noman bhai & co', 'nomi', '123456789', 'habib.rehman@barretthodgson.com', 'ddd fe rfe erte ertre t r', '2025-05-12 16:21:06'),
(4, 'noman @ com', NULL, 'wdw', 'dww@gg.com', 'dw', '2025-05-13 10:17:42'),
(5, 'asif noor Company', 'Nasif', '1234567', 'er@fb.com', 'st 178 b;lock 2 gul', '2025-05-13 10:23:01'),
(6, 'ahmed&company', 'Omer', '123145678', 'ahmed@company.com', 'qwdew wsd wedwedf wefefe', '2025-05-13 10:36:45'),
(7, 'HABIB & CO', 'Hasseb', '12314567478', 'habib.rehman@barretthodgson.com', 'qwe wedw', '2025-05-14 16:55:51');

-- --------------------------------------------------------

--
-- Table structure for table `supplier_details`
--

CREATE TABLE `supplier_details` (
  `id` int(11) NOT NULL,
  `supplier_name` varchar(100) DEFAULT NULL,
  `supplier_address` varchar(255) DEFAULT NULL,
  `supplier_contact1` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payments`
--

CREATE TABLE `supplier_payments` (
  `id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `payment_date` datetime DEFAULT current_timestamp(),
  `amount` decimal(10,2) DEFAULT NULL,
  `payment_mode` varchar(50) DEFAULT NULL,
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_returns`
--

CREATE TABLE `supplier_returns` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `return_qty` int(11) NOT NULL,
  `received_qty` int(11) DEFAULT 0,
  `reason` varchar(255) DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `resolved` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','cashier') DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `full_name`, `created_at`, `status`) VALUES
(2, 'admin', 'admin123', 'admin', 'System Administrator', '2025-05-15 09:42:57', 'active'),
(5, 'Habib', '12345jJ', 'admin', 'Habib Ur Rehman', '2025-05-15 09:42:57', 'active'),
(6, 'Shakir', '12345s', 'cashier', 'Shakir Khan', '2025-05-15 09:42:57', 'active'),
(7, 'Nasif', '12345j', 'cashier', 'Nasif', '2025-05-15 10:09:58', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `product_returns`
--
ALTER TABLE `product_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `return_received`
--
ALTER TABLE `return_received`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_returns`
--
ALTER TABLE `sale_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_avail`
--
ALTER TABLE `stock_avail`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_entries`
--
ALTER TABLE `stock_entries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_details`
--
ALTER TABLE `supplier_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `supplier_returns`
--
ALTER TABLE `supplier_returns`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `product_returns`
--
ALTER TABLE `product_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `return_received`
--
ALTER TABLE `return_received`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `sales_returns`
--
ALTER TABLE `sales_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `sale_returns`
--
ALTER TABLE `sale_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_avail`
--
ALTER TABLE `stock_avail`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_entries`
--
ALTER TABLE `stock_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `supplier_details`
--
ALTER TABLE `supplier_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_returns`
--
ALTER TABLE `supplier_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `product_returns`
--
ALTER TABLE `product_returns`
  ADD CONSTRAINT `product_returns_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `return_received`
--
ALTER TABLE `return_received`
  ADD CONSTRAINT `return_received_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `sales_returns`
--
ALTER TABLE `sales_returns`
  ADD CONSTRAINT `sales_returns_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_returns_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD CONSTRAINT `supplier_payments_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
