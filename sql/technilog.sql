-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2026 at 04:50 PM
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
-- Database: `technilog`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin_logins`
--

CREATE TABLE `admin_logins` (
  `id` int(11) NOT NULL,
  `session_token` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `login_time` datetime NOT NULL,
  `status` enum('success','failed') NOT NULL DEFAULT 'failed',
  `failure_reason` varchar(100) DEFAULT NULL,
  `device_info` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`device_info`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admin_logins`
--

INSERT INTO `admin_logins` (`id`, `session_token`, `ip_address`, `user_agent`, `login_time`, `status`, `failure_reason`, `device_info`) VALUES
(1, '1778600888463tr01fm5xi', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 17:49:51', 'success', NULL, NULL),
(2, '1778650464735689x0p1xh', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 07:34:52', 'success', NULL, NULL),
(3, '1778650495466houqqs9sp', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 07:34:58', 'failed', 'incorrect_code', NULL),
(4, '1778650961377yuplclzh1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-13 07:42:57', 'success', NULL, NULL),
(5, '1778927618542n777hw2aa', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-16 12:33:56', 'success', NULL, NULL),
(6, '17789980051730xmt12gab', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 08:07:47', 'success', NULL, NULL),
(7, '1778998222448m9151jrk1', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 08:10:28', 'success', NULL, NULL),
(8, '1778999012567awg2al4ug', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 08:23:37', 'success', NULL, NULL),
(9, '1778999914953353by439e', '::1', 'Mozilla/5.0 (Linux; Android 10; K) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Mobile Safari/537.36', '2026-05-17 08:38:38', 'success', NULL, NULL),
(10, '177901366787041p36ltou', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 12:27:57', 'success', NULL, NULL),
(11, '1779013890507anzm541dv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 12:31:33', 'success', NULL, NULL),
(12, '17790185547395krt4dba5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-17 13:49:26', 'success', NULL, NULL),
(13, '1779068842861imikx9cs4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 03:47:44', 'success', NULL, NULL),
(14, '17790708837662f5h1llx5', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 04:21:30', 'success', NULL, NULL),
(15, '1779080840832esomty0z9', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 07:07:25', 'success', NULL, NULL),
(16, '1779081249623cimm8tmvv', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-18 07:14:22', 'success', NULL, NULL),
(17, '1779164027370izt6fyxv7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 06:14:11', 'success', NULL, NULL),
(18, '1779164027370izt6fyxv7', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-19 06:14:11', 'success', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cancelled_records`
--

CREATE TABLE `cancelled_records` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_ref` varchar(64) DEFAULT NULL,
  `user_id` int(11) DEFAULT 0,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(64) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `service` varchar(100) DEFAULT NULL,
  `installation_fee` decimal(10,2) DEFAULT 0.00,
  `items_json` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `shipping` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `cancelled_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cancelled_records`
--

INSERT INTO `cancelled_records` (`id`, `order_id`, `order_ref`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `address`, `payment_method`, `service`, `installation_fee`, `items_json`, `subtotal`, `shipping`, `total`, `cancelled_at`) VALUES
(1, 13, 'TL-40D064A3', 0, 'Venice Angel Garna', 'garnavenice9@gmail.com', '09543712676', '38, San Carlos, Binangonan, Rizal 1940', 'cod', 'self', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 6000.00, 0.00, 6000.00, '2026-05-13 16:49:28'),
(2, 32, 'TL-7604F972', 0, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', '09934534048', '38, San Carlos, Binangonan, Rizal 1940', 'gcash', 'with_installation', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"..\\/uploads\\/products\\/prod_69f6bc1ab91d32.88208511.jpg\",\"variant\":\"hshshs\",\"price\":2000,\"category\":\"Smart Fire Alarm\",\"qty\":1}]', 2000.00, 120.00, 3620.00, '2026-05-17 13:26:34'),
(3, 39, 'TL-3EF8B8D3', 0, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', '09934534048', '38, San Carlos, Binangonan, Rizal 1940', 'cod', 'self', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"Basic Variants\",\"price\":2385,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 2385.00, 120.00, 2505.00, '2026-05-19 21:41:23');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('Pending','Replied') DEFAULT 'Pending',
  `admin_reply` text DEFAULT NULL,
  `user_reply` text DEFAULT NULL,
  `user_replied_at` datetime DEFAULT NULL,
  `user_reply_unread` tinyint(1) NOT NULL DEFAULT 0,
  `replied_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `user_id`, `email`, `subject`, `message`, `status`, `admin_reply`, `user_reply`, `user_replied_at`, `user_reply_unread`, `replied_at`, `created_at`) VALUES
(1, 5, 'garnavenice9@gmail.com', 'Hi', 'Hello Nigga', 'Pending', NULL, NULL, NULL, 0, NULL, '2026-05-03 13:01:30'),
(2, 5, 'ronlouiemagsipoc210@gmail.com', 'HI', 'HI NIGGA', 'Replied', 'hello nigga', NULL, NULL, 0, '2026-05-03 21:30:16', '2026-05-03 13:12:15'),
(3, 4, 'ronlouiemagsipoc210@gmail.com', 'Hi', 'HELLO PO', 'Replied', 'hi', 'hello', '2026-05-18 09:08:13', 0, '2026-05-17 18:28:06', '2026-05-04 04:18:02'),
(4, 17, 'rlamagsipoc@gmail.com', 'FIRE EXTINGUISHER', 'Expired na po yung nabili kong fire extinguisher.', 'Replied', 'hindi po', NULL, NULL, 0, '2026-05-17 14:14:15', '2026-05-17 06:14:03'),
(5, 5, 'garnavenice9@gmail.com', 'secret', 'bato po dumating sakin bakit ganon 😢😢', 'Replied', 'karma na bahala sakin', NULL, NULL, 0, '2026-05-17 14:21:43', '2026-05-17 06:17:19'),
(6, 4, 'ronlouiemagsipoc210@gmail.com', 'Maintenance', 'Please', 'Replied', 'Hi', 'hi', '2026-05-21 14:31:30', 0, '2026-05-21 14:31:15', '2026-05-21 06:30:58');

-- --------------------------------------------------------

--
-- Table structure for table `deleted_forum_posts`
--

CREATE TABLE `deleted_forum_posts` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `tag` varchar(50) DEFAULT 'general',
  `deleted_by` int(11) NOT NULL,
  `deleted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deleted_forum_posts`
--

INSERT INTO `deleted_forum_posts` (`id`, `post_id`, `user_id`, `title`, `content`, `tag`, `deleted_by`, `deleted_at`) VALUES
(1, 2, 4, 'aa', 'aa', 'general', 4, '2026-05-14 09:01:50'),
(2, 1, 4, 'aa', 'aa', 'tips', 4, '2026-05-14 09:01:52');

-- --------------------------------------------------------

--
-- Table structure for table `deliveries`
--

CREATE TABLE `deliveries` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `courier` varchar(100) DEFAULT 'Standard Delivery',
  `tracking_number` varchar(100) DEFAULT NULL,
  `status` enum('processing','to_ship','shipped','delayed','arrived','received') NOT NULL DEFAULT 'processing',
  `estimated_date` date DEFAULT NULL,
  `shipped_at` datetime DEFAULT NULL,
  `arrived_at` datetime DEFAULT NULL,
  `received_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deliveries`
--

INSERT INTO `deliveries` (`id`, `order_id`, `username`, `email`, `courier`, `tracking_number`, `status`, `estimated_date`, `shipped_at`, `arrived_at`, `received_at`, `created_at`, `updated_at`) VALUES
(5, 5, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-05-02 08:57:40', '2026-05-02 08:57:40'),
(6, 6, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', NULL, NULL, '', NULL, NULL, NULL, NULL, '2026-05-02 09:03:27', '2026-05-02 09:03:27'),
(15, 16, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-13 08:55:06', '2026-05-13 08:55:06'),
(16, 17, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-14 02:54:42', '2026-05-14 02:54:42'),
(17, 18, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-14 05:31:39', '2026-05-14 05:31:39'),
(20, 21, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-14 05:47:20', '2026-05-14 05:47:20'),
(24, 25, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-14 07:25:56', '2026-05-14 07:25:56'),
(26, 27, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-14 07:53:37', '2026-05-14 07:53:37'),
(27, 28, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-15 04:30:08', '2026-05-15 04:30:08'),
(28, 29, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-15 04:36:54', '2026-05-15 04:36:54'),
(32, 33, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-17 06:14:56', '2026-05-17 06:14:56'),
(33, 34, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-17 06:16:25', '2026-05-17 06:16:25'),
(34, 35, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-17 06:17:58', '2026-05-17 06:17:58'),
(36, 37, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-17 10:57:46', '2026-05-17 10:57:46'),
(37, 38, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-17 11:47:35', '2026-05-17 11:47:35'),
(39, 40, '', '', 'Standard Delivery', NULL, 'processing', NULL, NULL, NULL, NULL, '2026-05-21 06:27:13', '2026-05-21 06:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_logs`
--

CREATE TABLE `delivery_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `delivery_id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL DEFAULT '',
  `email` varchar(150) NOT NULL DEFAULT '',
  `status` varchar(50) NOT NULL,
  `note` text DEFAULT NULL,
  `logged_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery_logs`
--

INSERT INTO `delivery_logs` (`id`, `delivery_id`, `username`, `email`, `status`, `note`, `logged_at`) VALUES
(5, 5, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', 'pending', 'Order placed successfully.', '2026-05-02 08:57:40'),
(6, 6, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', 'pending', 'Order placed successfully.', '2026-05-02 09:03:27');

-- --------------------------------------------------------

--
-- Table structure for table `device_history`
--

CREATE TABLE `device_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `model_no` varchar(50) NOT NULL,
  `device_model` varchar(150) NOT NULL,
  `category` varchar(100) NOT NULL,
  `order_ref` varchar(50) NOT NULL,
  `action` enum('turned_off','turned_on') NOT NULL,
  `actioned_at` datetime NOT NULL DEFAULT current_timestamp(),
  `note` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `device_history`
--

INSERT INTO `device_history` (`id`, `user_id`, `model_no`, `device_model`, `category`, `order_ref`, `action`, `actioned_at`, `note`) VALUES
(1, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-15 09:56:59', 'User manually turned off the device.'),
(2, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-15 09:57:16', 'User manually turned off the device.'),
(3, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-15 09:57:16', 'User manually turned on the device.'),
(4, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-15 10:54:21', 'TEST: Fire alert triggered by user via Test Alert button.'),
(5, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-15 11:07:11', 'TEST: Fire alert triggered by user via Test Alert button.'),
(6, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-15 11:15:22', 'TEST: Fire alert triggered by user via Test Alert button.'),
(7, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-15 11:27:50', 'TEST: Fire alert triggered by user via Test Alert button.'),
(8, 5, 'TL-CAM-1685', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-0699E35A', '', '2026-05-15 11:31:39', 'TEST: Burglar alert triggered by user via Test Alert button.'),
(9, 5, 'TL-FAL-1740', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-C0845E60', '', '2026-05-15 11:37:17', 'TEST: Fire alert triggered by user via Test Alert button.'),
(10, 5, 'TL-FAL-1740', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-C0845E60', 'turned_off', '2026-05-15 11:37:42', 'User manually turned off the device.'),
(11, 5, 'TL-FAL-1740', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-C0845E60', 'turned_off', '2026-05-15 11:39:31', 'User manually turned off the device.'),
(12, 5, 'TL-FAL-1740', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-C0845E60', 'turned_on', '2026-05-15 11:39:34', 'User manually turned on the device.'),
(13, 5, 'TL-CAM-1840', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-73414B56', 'turned_off', '2026-05-15 12:05:33', 'User manually turned off the device.'),
(14, 5, 'TL-CAM-1840', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-73414B56', 'turned_on', '2026-05-15 12:05:38', 'User manually turned on the device.'),
(15, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-15 12:24:51', 'TEST: Fire alert triggered by user via Test Alert button.'),
(16, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-15 12:27:05', 'TEST: Fire alert triggered by user via Test Alert button.'),
(17, 10, 'TL-CAM-2057', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-C531E9A8', '', '2026-05-15 12:30:58', 'TEST: Burglar alert triggered by user via Test Alert button.'),
(18, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-15 13:14:38', 'User manually turned off the device.'),
(19, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-15 13:14:41', 'User manually turned on the device.'),
(20, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 18:28:01', 'TEST: Fire alert triggered by user via Test Alert button.'),
(21, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-16 19:11:42', 'User manually turned off the device.'),
(22, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-16 19:11:45', 'User manually turned on the device.'),
(23, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 20:05:36', 'TEST: Fire alert triggered by user via Test Alert button.'),
(24, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 20:05:56', 'TEST: Fire alert triggered by user via Test Alert button.'),
(25, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 20:06:17', 'TEST: Fire alert triggered by user via Test Alert button.'),
(26, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 20:09:32', 'TEST: Fire alert triggered by user via Test Alert button.'),
(27, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 20:09:43', 'TEST: Fire alert triggered by user via Test Alert button.'),
(28, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:11:34', 'TEST: Fire alert triggered by user via Test Alert button.'),
(29, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:14:56', 'TEST: Fire alert triggered by user via Test Alert button.'),
(30, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-16 23:15:36', 'User manually turned off the device.'),
(31, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-16 23:15:39', 'User manually turned on the device.'),
(32, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-16 23:15:40', 'User manually turned off the device.'),
(33, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:15:42', 'TEST: Fire alert triggered by user via Test Alert button.'),
(34, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-16 23:15:42', 'User manually turned on the device.'),
(35, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-16 23:17:23', 'User manually turned off the device.'),
(36, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-16 23:17:24', 'User manually turned on the device.'),
(37, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:17:33', 'TEST: Fire alert triggered by user via Test Alert button.'),
(38, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:18:58', 'TEST: Fire alert triggered by user via Test Alert button.'),
(39, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:21:20', 'TEST: Fire alert triggered by user via Test Alert button.'),
(40, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-16 23:24:17', 'TEST: Fire alert triggered by user via Test Alert button.'),
(41, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-16 23:27:26', 'User manually turned off the device.'),
(42, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-16 23:27:33', 'User manually turned on the device.'),
(43, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-16 23:27:34', 'User manually turned off the device.'),
(44, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-16 23:27:34', 'User manually turned on the device.'),
(45, 5, 'TL-CAM-1840', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-73414B56', 'turned_off', '2026-05-16 23:28:46', 'User manually turned off the device.'),
(46, 5, 'TL-CAM-1840', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-73414B56', 'turned_on', '2026-05-16 23:28:49', 'User manually turned on the device.'),
(47, 5, 'TL-FAL-1740', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-C0845E60', 'turned_off', '2026-05-16 23:50:28', 'User manually turned off the device.'),
(48, 5, 'TL-FAL-1740', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-C0845E60', 'turned_on', '2026-05-16 23:50:29', 'User manually turned on the device.'),
(49, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-17 13:51:11', 'TEST: Fire alert triggered by user via Test Alert button.'),
(50, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-17 14:04:08', 'TEST: Fire alert triggered by user via Test Alert button.'),
(51, 17, 'TL-FAL-2267', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-D2080464', '', '2026-05-17 14:19:16', 'TEST: Fire alert triggered by user via Test Alert button.'),
(52, 17, 'TL-CAM-2274', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-D2080464', '', '2026-05-17 14:21:53', 'TEST: Burglar alert triggered by user via Test Alert button.'),
(53, 17, 'TL-CAM-2274', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-D2080464', 'turned_off', '2026-05-17 14:22:26', 'User manually turned off the device.'),
(54, 17, 'TL-CAM-2274', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-D2080464', 'turned_on', '2026-05-17 14:22:29', 'User manually turned on the device.'),
(55, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-17 14:28:42', 'User manually turned off the device.'),
(56, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-17 14:28:44', 'User manually turned on the device.'),
(57, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-17 14:28:47', 'User manually turned off the device.'),
(58, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-17 14:28:49', 'User manually turned on the device.'),
(59, 20, 'TL-CAM-2336', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-F45090EE', '', '2026-05-17 19:01:03', 'TEST: Burglar alert triggered by user via Test Alert button.'),
(60, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-18 11:20:55', 'User manually turned off the device.'),
(61, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-18 11:20:59', 'User manually turned on the device.'),
(62, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_off', '2026-05-18 19:51:32', 'User manually turned off the device.'),
(63, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', 'turned_on', '2026-05-18 19:51:32', 'User manually turned on the device.'),
(64, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-19 15:41:47', 'TEST: Fire alert triggered by user via Test Alert button.'),
(65, 4, 'TL-CAM-2429', 'TECHNiLOG Smart CCTV Camera', 'Smart CCTV Camera', 'TL-F6E2DA35', '', '2026-05-21 14:28:32', 'TEST: Burglar alert triggered by user via Test Alert button.'),
(66, 4, 'TL-FAL-1709', 'TECHNiLOG Smart Fire Alarm', 'Smart Fire Alarm', 'TL-51BA3E32', '', '2026-05-21 14:30:16', 'TEST: Fire alert triggered by user via Test Alert button.');

-- --------------------------------------------------------

--
-- Table structure for table `forum_comments`
--

CREATE TABLE `forum_comments` (
  `comment_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `parent_comment_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `forum_comment_likes`
--

CREATE TABLE `forum_comment_likes` (
  `like_id` int(11) NOT NULL,
  `comment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_comment_likes`
--

INSERT INTO `forum_comment_likes` (`like_id`, `comment_id`, `user_id`, `created_at`) VALUES
(2, 8, 4, '2026-05-17 19:36:25'),
(3, 7, 5, '2026-05-17 19:38:40'),
(4, 9, 4, '2026-05-17 19:47:07'),
(5, 6, 4, '2026-05-17 19:48:01'),
(6, 7, 4, '2026-05-17 19:48:02'),
(9, 12, 4, '2026-05-18 14:57:24'),
(11, 14, 4, '2026-05-18 19:51:09');

-- --------------------------------------------------------

--
-- Table structure for table `forum_notifications`
--

CREATE TABLE `forum_notifications` (
  `notif_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `actor_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `type` enum('like','comment') NOT NULL,
  `post_title` varchar(255) NOT NULL DEFAULT '',
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_notifications`
--

INSERT INTO `forum_notifications` (`notif_id`, `owner_id`, `actor_id`, `post_id`, `type`, `post_title`, `is_read`, `created_at`) VALUES
(1, 4, 5, 3, 'like', 'ASASA', 1, '2026-05-14 12:04:36'),
(2, 4, 11, 3, 'like', 'ASASA', 1, '2026-05-15 12:09:04'),
(46, 4, 5, 9, 'comment', 'sas', 1, '2026-05-16 19:56:09'),
(121, 4, 18, 3, 'comment', 'ASASA', 1, '2026-05-17 14:31:54'),
(192, 4, 18, 3, 'like', 'ASASA', 1, '2026-05-17 19:19:32'),
(238, 18, 4, 3, '', 'ASASA', 1, '2026-05-17 19:30:55'),
(253, 18, 4, 12, 'like', 'SubSubject: In Memoriam: The Passing of Putol', 1, '2026-05-17 19:32:10'),
(254, 20, 4, 11, 'like', 'CCTV Features', 0, '2026-05-17 19:32:12'),
(295, 4, 5, 3, 'comment', 'ASASA', 1, '2026-05-17 19:36:08'),
(306, 5, 4, 3, '', 'ASASA', 1, '2026-05-17 19:36:25'),
(333, 18, 5, 12, 'comment', 'SubSubject: In Memoriam: The Passing of Putol', 1, '2026-05-17 19:38:29'),
(334, 4, 5, 3, '', 'ASASA', 1, '2026-05-17 19:38:40'),
(423, 5, 4, 12, '', 'SubSubject: In Memoriam: The Passing of Putol', 0, '2026-05-17 19:47:07'),
(755, 21, 4, 13, 'like', 'MINSAN NALANG KUMAIN WALA PANG ULAM', 1, '2026-05-18 10:08:54'),
(756, 21, 4, 13, 'comment', 'MINSAN NALANG KUMAIN WALA PANG ULAM', 1, '2026-05-18 10:08:59'),
(807, 21, 4, 14, 'like', 'LIBRE SAMPAK BY NOPETSALLOWED', 0, '2026-05-18 10:27:47'),
(810, 21, 4, 14, 'comment', 'LIBRE SAMPAK BY NOPETSALLOWED', 0, '2026-05-18 10:28:15'),
(1725, 4, 18, 35, 'comment', 'h', 1, '2026-05-18 14:36:42'),
(1726, 18, 4, 38, 'like', 'YJ', 0, '2026-05-18 14:57:20'),
(1727, 18, 4, 35, '', 'h', 0, '2026-05-18 14:57:24'),
(1736, 4, 5, 35, 'like', 'h', 1, '2026-05-18 19:44:02'),
(1737, 4, 5, 35, 'comment', 'h', 1, '2026-05-18 19:44:17'),
(1738, 5, 4, 35, '', 'h', 0, '2026-05-18 19:48:08'),
(1740, 20, 15, 11, 'like', 'CCTV Features', 0, '2026-05-21 19:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `forum_posts`
--

CREATE TABLE `forum_posts` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `media_path` varchar(500) DEFAULT NULL,
  `media_type` enum('image','video') DEFAULT NULL,
  `tag` varchar(50) NOT NULL DEFAULT 'general',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_posts`
--

INSERT INTO `forum_posts` (`post_id`, `user_id`, `title`, `content`, `media_path`, `media_type`, `tag`, `created_at`, `updated_at`) VALUES
(11, 20, 'CCTV Features', 'Which CCTV camera has the best night vision?\r\nCan CCTV record audio?\r\nHow long can CCTV footage be stored?', NULL, NULL, 'question', '2026-05-17 19:06:10', '2026-05-17 19:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `forum_post_likes`
--

CREATE TABLE `forum_post_likes` (
  `like_id` int(10) UNSIGNED NOT NULL,
  `post_id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `forum_post_likes`
--

INSERT INTO `forum_post_likes` (`like_id`, `post_id`, `user_id`, `created_at`) VALUES
(7, 11, 4, '2026-05-17 11:32:11'),
(23, 11, 15, '2026-05-21 11:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `forum_reports`
--

CREATE TABLE `forum_reports` (
  `id` int(11) NOT NULL,
  `reporter_id` int(11) NOT NULL COMMENT 'User who submitted the report',
  `reported_user_id` int(11) NOT NULL COMMENT 'User being reported',
  `post_id` int(11) NOT NULL COMMENT 'The post being reported',
  `reason` varchar(100) NOT NULL COMMENT 'Report category/reason',
  `details` text DEFAULT NULL COMMENT 'Optional extra details (future use)',
  `status` enum('Pending','Reviewed','Dismissed') NOT NULL DEFAULT 'Pending',
  `admin_note` text DEFAULT NULL COMMENT 'Admin resolution note',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `forum_reports`
--

INSERT INTO `forum_reports` (`id`, `reporter_id`, `reported_user_id`, `post_id`, `reason`, `details`, `status`, `admin_note`, `created_at`, `reviewed_at`) VALUES
(1, 4, 21, 14, 'Spam', NULL, 'Dismissed', '', '2026-05-18 13:17:16', '2026-05-18 13:38:32'),
(2, 4, 20, 11, 'Other', NULL, 'Dismissed', '', '2026-05-18 13:33:07', '2026-05-18 13:38:27'),
(3, 4, 21, 13, 'Other', NULL, 'Dismissed', '', '2026-05-18 13:38:43', '2026-05-18 13:39:17'),
(4, 4, 18, 12, 'Other', NULL, 'Reviewed', NULL, '2026-05-18 13:39:25', '2026-05-18 13:39:40'),
(5, 5, 18, 38, 'Harassment or Bullying', NULL, 'Reviewed', NULL, '2026-05-18 19:44:45', '2026-05-18 19:48:54');

-- --------------------------------------------------------

--
-- Table structure for table `online_payment`
--

CREATE TABLE `online_payment` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_ref` varchar(50) NOT NULL,
  `gcash_ref` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','verified','rejected') NOT NULL DEFAULT 'pending',
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `online_payment`
--

INSERT INTO `online_payment` (`id`, `user_id`, `order_id`, `order_ref`, `gcash_ref`, `amount`, `status`, `verified_at`, `created_at`) VALUES
(1, 4, 31, 'TL-B135A9D5', 'ronlouiemagsipoc210@gmail.com', 2120.00, 'pending', NULL, '2026-05-16 19:44:51'),
(2, 4, 32, 'TL-7604F972', 'ronlouiemagsipoc210@gmail.com', 3620.00, 'pending', NULL, '2026-05-17 13:26:15');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL DEFAULT 0,
  `order_ref` varchar(20) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `address` text NOT NULL,
  `notes` text DEFAULT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'cod',
  `service` varchar(50) NOT NULL DEFAULT 'self',
  `installation_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `items_json` longtext NOT NULL COMMENT 'JSON array of cart items',
  `subtotal` decimal(12,2) NOT NULL DEFAULT 0.00,
  `shipping` decimal(12,2) NOT NULL DEFAULT 0.00,
  `install_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Processing','Shipped','Delivered','Received','Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `order_ref`, `customer_name`, `customer_phone`, `customer_email`, `address`, `notes`, `payment_method`, `service`, `installation_fee`, `items_json`, `subtotal`, `shipping`, `install_fee`, `total`, `status`, `created_at`, `updated_at`) VALUES
(5, 0, 'TL-00005', 'RonLouie Magsipoc', '09934534048', 'ronlouiemagsipoc210@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"\",\"variant\":\"hshshs\",\"price\":2000,\"qty\":1}]', 2000.00, 120.00, 0.00, 2120.00, 'Received', '2026-05-02 16:57:40', '2026-05-04 19:47:40'),
(6, 0, 'TL-00006', 'RonLouie Magsipoc', '09934534048', 'ronlouiemagsipoc210@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"\",\"variant\":\"hshshs\",\"price\":2000,\"qty\":1}]', 2000.00, 120.00, 0.00, 2120.00, 'Delivered', '2026-05-02 17:03:27', '2026-05-13 14:02:55'),
(16, 0, 'TL-0699E35A', 'Venice Angel Garna', '09543712676', 'garnavenice9@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'with_installation', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 6000.00, 120.00, 0.00, 7620.00, 'Received', '2026-05-13 16:55:06', '2026-05-13 16:55:24'),
(17, 0, 'TL-51BA3E32', 'RonLouie Magsipoc', '09934534048', 'ronlouiemagsipoc210@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"..\\/uploads\\/products\\/prod_69f6bc1ab91d32.88208511.jpg\",\"variant\":\"hshshs\",\"price\":2000,\"category\":\"Smart Fire Alarm\",\"qty\":1}]', 2000.00, 120.00, 0.00, 2120.00, 'Received', '2026-05-14 10:54:42', '2026-05-14 10:55:30'),
(18, 0, 'TL-C0845E60', 'Venice Angel Garna', '09543712676', 'garnavenice9@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'with_installation', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"..\\/uploads\\/products\\/prod_69f6bc1ab91d32.88208511.jpg\",\"variant\":\"hshshs\",\"price\":2000,\"category\":\"Smart Fire Alarm\",\"qty\":1}]', 2000.00, 120.00, 0.00, 3620.00, 'Received', '2026-05-14 13:31:39', '2026-05-14 13:31:55'),
(21, 0, 'TL-73414B56', 'Venice Angel Garna', '09543712676', 'garnavenice9@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'with_installation', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 6000.00, 120.00, 0.00, 7620.00, 'Received', '2026-05-14 13:47:20', '2026-05-14 13:47:33'),
(25, 0, 'TL-A923C52F', 'RonLouie Magsipoc', '09934534048', 'magsipocronlouie@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'with_installation', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"..\\/uploads\\/products\\/prod_69f6bc1ab91d32.88208511.jpg\",\"variant\":\"hshshs\",\"price\":2000,\"category\":\"Smart Fire Alarm\",\"qty\":1}]', 2000.00, 120.00, 0.00, 3620.00, 'Received', '2026-05-14 15:25:56', '2026-05-14 15:26:20'),
(27, 0, 'TL-17160FCC', 'Venice Angel Garna', '09543712676', 'garnavenice9@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":16,\"name\":\"Fire Extinguisher\",\"image\":\"..\\/uploads\\/products\\/prod_6a042479ba7021.90340002.jpg\",\"variant\":\"ahaha\",\"price\":1000,\"category\":\"Accessories\",\"qty\":1}]', 1000.00, 120.00, 0.00, 1120.00, 'Received', '2026-05-14 15:53:37', '2026-05-14 15:54:01'),
(28, 0, 'TL-C531E9A8', 'RonLouie Magsipoc', '09934534048', 'magsipocronlouie@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'with_installation', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 6000.00, 120.00, 0.00, 7620.00, 'Received', '2026-05-15 12:30:08', '2026-05-15 12:30:35'),
(29, 0, 'TL-A9D0495D', 'RonLouie Magsipoc', '09934534048', 'magsipocronlouie@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":16,\"name\":\"Fire Extinguisher\",\"image\":\"..\\/uploads\\/products\\/prod_6a042479ba7021.90340002.jpg\",\"variant\":\"ahaha\",\"price\":1000,\"category\":\"Accessories\",\"qty\":1}]', 1000.00, 120.00, 0.00, 1120.00, 'Received', '2026-05-15 12:36:54', '2026-05-15 12:37:54'),
(33, 0, 'TL-A5D804C8', 'Angelo Rivera', '09946460901', 'bunzkageru@gmail.com', 'C.Valle St., Dolores, Taytay, Rizal 1920', '', 'cod', 'self', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 6000.00, 120.00, 0.00, 6120.00, 'Shipped', '2026-05-17 14:14:55', '2026-05-17 14:20:10'),
(34, 0, 'TL-739C26DD', 'Venice Garna', '09543712676', 'garnavenice9@gmail.com', 'Pagkakaisa St., San Roque, Lupao, Nueva Ecija 3122', 'sana po yung may ari mag deliver', 'cod', 'with_installation', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":2}]', 12000.00, 400.00, 0.00, 13900.00, 'Received', '2026-05-17 14:16:25', '2026-05-17 14:18:57'),
(35, 0, 'TL-D2080464', 'Rhona Ganda', '0999999999', 'rlamagsipoc@gmail.com', '38 Linaluz, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":16,\"name\":\"Fire Extinguisher\",\"image\":\"..\\/uploads\\/products\\/prod_6a042479ba7021.90340002.jpg\",\"variant\":\"ahaha\",\"price\":1000,\"category\":\"Accessories\",\"qty\":1},{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1},{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"..\\/uploads\\/products\\/prod_69f6bc1ab91d32.88208511.jpg\",\"variant\":\"hshshs\",\"price\":2000,\"category\":\"Smart Fire Alarm\",\"qty\":1}]', 9000.00, 120.00, 0.00, 9120.00, 'Received', '2026-05-17 14:17:58', '2026-05-17 14:18:50'),
(37, 0, 'TL-F45090EE', 'Lorie', '09066072932', 'magsipoc.lorieann@yahoo.com', 'Bahay ni Kuya, Brgy. Liko, Binangonan, Rizal 1940', '', 'cod', 'self', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"hahah\",\"price\":6000,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 6000.00, 120.00, 0.00, 6120.00, 'Received', '2026-05-17 18:57:46', '2026-05-17 18:58:45'),
(38, 0, 'TL-DE59BE06', 'MARC MAGSIPOC', '09203571698', 'anitoramv@gmail.com', '#14 M BORJA ST, Dolores, Taytay, Rizal 1920', '', 'cod', 'self', 0.00, '[{\"id\":16,\"name\":\"Fire Extinguisher\",\"image\":\"..\\/uploads\\/products\\/prod_6a042479ba7021.90340002.jpg\",\"variant\":\"ahaha\",\"price\":1000,\"category\":\"Accessories\",\"qty\":1}]', 1000.00, 120.00, 0.00, 1120.00, 'Received', '2026-05-17 19:47:35', '2026-05-17 20:00:47'),
(40, 0, 'TL-F6E2DA35', 'RonLouie Magsipoc', '09934534048', 'ronlouiemagsipoc210@gmail.com', '38, San Carlos, Binangonan, Rizal 1940', '', 'cod', 'with_installation', 0.00, '[{\"id\":14,\"name\":\"TECHNiLOG Smart CCTV Camera\",\"image\":\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\",\"variant\":\"Basic Variants\",\"price\":2385,\"category\":\"Smart CCTV Camera\",\"qty\":1}]', 2385.00, 120.00, 0.00, 4005.00, 'Received', '2026-05-21 14:27:13', '2026-05-21 14:27:59');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(10) UNSIGNED NOT NULL,
  `order_id` int(10) UNSIGNED NOT NULL,
  `product_id` int(10) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `quantity`, `unit_price`) VALUES
(10, 16, 14, 1, 6000.00),
(11, 17, 7, 1, 2000.00),
(12, 18, 7, 1, 2000.00),
(15, 21, 14, 1, 6000.00),
(19, 25, 7, 1, 2000.00),
(21, 27, 16, 1, 1000.00),
(22, 28, 14, 1, 6000.00),
(23, 29, 16, 1, 1000.00),
(27, 33, 14, 1, 6000.00),
(28, 34, 14, 2, 6000.00),
(29, 35, 16, 1, 1000.00),
(30, 35, 14, 1, 6000.00),
(31, 35, 7, 1, 2000.00),
(33, 37, 14, 1, 6000.00),
(34, 38, 16, 1, 1000.00),
(36, 40, 14, 1, 2385.00);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_log`
--

CREATE TABLE `password_reset_log` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `reset_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_log`
--

INSERT INTO `password_reset_log` (`id`, `user_id`, `reset_at`) VALUES
(1, 4, '2026-04-30 08:27:54'),
(2, 4, '2026-05-02 08:41:05'),
(3, 10, '2026-05-16 14:27:15'),
(4, 10, '2026-05-18 18:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text NOT NULL,
  `category` varchar(100) NOT NULL DEFAULT 'Other',
  `specifications` text DEFAULT NULL,
  `status` enum('Available','Out of Stock','Coming Soon','Deleted') NOT NULL DEFAULT 'Available',
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `images_json` longtext DEFAULT NULL COMMENT 'JSON array of image paths',
  `prices_json` text DEFAULT NULL COMMENT 'JSON array of {label, price, stock}',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `specifications`, `status`, `featured`, `images_json`, `prices_json`, `created_at`, `updated_at`) VALUES
(6, 'TECHNiLOG Smart Fire Alarm', 'ahahhah', 'Smart Fire Alarm', 'HAAHHAHA', 'Deleted', 1, '0', '[{\"label\":\"hhahaah\",\"price\":2000,\"stock\":501}]', '2026-05-02 07:29:51', '2026-05-02 08:33:13'),
(7, 'TECHNiLOG Smart Fire Alarm', 'Technilog Smart Fire Alarm System\r\n\r\nThe Technilog Smart Fire Alarm System is an advanced fire detection and safety solution designed to provide real-time monitoring, instant alerts, and intelligent emergency response for homes, buildings, schools, offices, and industrial facilities.\r\n\r\nUsing modern IoT (Internet of Things) technology, the system continuously monitors smoke, heat, and possible fire hazards through interconnected smart sensors. Once a threat is detected, the system immediately activates alarms and sends real-time notifications to authorized users through mobile devices, dashboards, or monitoring centers.\r\n\r\nKey Features\r\nReal-Time Monitoring – Continuously tracks fire alarm status and environmental conditions 24/7.\r\nInstant Notifications – Sends immediate alerts via mobile app, SMS, or email during emergencies.\r\nSmart Detection Technology – Detects smoke, heat, and abnormal conditions quickly and accurately.\r\nRemote Access & Control – Users can monitor and manage the system anytime and anywhere.\r\nAutomatic Emergency Response – Can trigger alarms, activate safety protocols, and notify emergency responders.\r\nCloud-Based Data Monitoring – Stores event logs and system activity for maintenance and reporting.\r\nReliable & Scalable – Suitable for residential, commercial, and industrial applications.\r\nSystem Benefits\r\n\r\nThe Technilog Smart Fire Alarm System improves fire safety by enabling early detection, faster response times, reduced risk of property damage, and enhanced protection of lives and assets. Its intelligent monitoring capabilities help facility managers and homeowners maintain a safer environment while ensuring continuous system reliability.', 'Smart Fire Alarm', 'Technilog Smart Fire Alarm System\r\nReal-time fire and smoke monitoring\r\nSmoke, heat, and flame detection sensors\r\nInstant alarm and mobile notifications\r\nWi-Fi / IoT connectivity\r\nRemote monitoring through mobile app\r\nAutomatic emergency alert system\r\nBattery backup support\r\nLCD/Digital display interface\r\nLow power consumption\r\nSuitable for homes, offices, and buildings', 'Available', 1, '[\"..\\/uploads\\/products\\/prod_69f6bc1ab91d32.88208511.jpg\"]', '[{\"label\":\"Basic Variant\",\"price\":1500,\"stock\":50}]', '2026-05-02 08:33:32', '2026-05-20 10:46:50'),
(8, 'Smart CCTV Camera', 'hahahah', 'Smart CCTV Camera', 'hahahahah', 'Deleted', 1, '0', '[{\"label\":\"hahahah\",\"price\":6000,\"stock\":500}]', '2026-05-03 10:30:30', '2026-05-03 10:50:39'),
(14, 'TECHNiLOG Smart CCTV Camera', 'Technilog Smart CCTV Camera System\r\n\r\nThe Technilog Smart CCTV Camera System is an intelligent security and surveillance solution designed to provide real-time monitoring, remote access, and advanced protection for homes, offices, schools, and commercial establishments.\r\n\r\nEquipped with high-definition cameras and smart monitoring technology, the system captures clear video footage day and night while allowing users to monitor their property remotely through mobile devices or computers. The system features motion detection, instant alerts, cloud recording, and live video streaming to ensure continuous security and fast response to suspicious activities.\r\n\r\nKey Features\r\nReal-time video monitoring\r\nHigh-definition (HD) camera quality\r\nNight vision capability\r\nMotion detection and instant alerts\r\nRemote viewing through mobile app\r\nCloud and local video storage\r\n24/7 surveillance recording\r\nWi-Fi / Internet connectivity\r\nEasy installation and user-friendly interface\r\nSuitable for homes, offices, schools, and businesses\r\nBenefits\r\n\r\nThe Technilog Smart CCTV Camera System enhances security by providing continuous surveillance, reducing security risks, improving incident monitoring, and allowing users to access live footage anytime and anywhere for better safety and protection.', 'Smart CCTV Camera', 'Technilog Smart CCTV Camera System\r\nHD real-time video monitoring\r\nNight vision camera support\r\nMotion detection alerts\r\nRemote mobile access\r\nWi-Fi / Internet connectivity\r\nCloud and local storage\r\n24/7 video recording\r\nWide-angle camera view\r\nLow power consumption\r\nIndoor and outdoor installation support', 'Available', 1, '[\"..\\/uploads\\/products\\/prod_69f70426dd9786.61944564.jpg\"]', '[{\"label\":\"Basic Variants\",\"price\":2385,\"stock\":49}]', '2026-05-03 11:45:59', '2026-05-21 14:27:13'),
(15, 'gahahaha', 'hahahah', 'Smart CCTV Camera', 'hahaha', 'Deleted', 1, '0', '[{\"label\":\"hahaha\",\"price\":200,\"stock\":3}]', '2026-05-03 16:19:41', '2026-05-03 16:19:57'),
(16, 'Fire Extinguisher', 'Technilog Fire Extinguisher\r\n\r\nThe Technilog Fire Extinguisher is a reliable fire safety device designed to quickly control and extinguish small fires before they spread. It is suitable for homes, offices, schools, commercial buildings, and industrial areas, providing fast emergency response and enhanced protection for people and property.\r\n\r\nBuilt with durable materials and easy-to-use operation, the extinguisher is effective against common fire types such as electrical fires, flammable liquids, and ordinary combustible materials. Its compact and portable design allows convenient installation and quick access during emergencies.\r\n\r\nKey Features\r\nFast and effective fire suppression\r\nEasy-to-use safety mechanism\r\nPortable and lightweight design\r\nDurable and corrosion-resistant cylinder\r\nSuitable for electrical and combustible fires\r\nSafety pressure indicator\r\nReliable emergency protection\r\nIdeal for residential and commercial use\r\nBenefits\r\n\r\nThe Technilog Fire Extinguisher helps reduce fire damage, improves emergency preparedness, and provides dependable first-response fire protection for safer environments.', 'Accessories', 'Technilog Fire Extinguisher\r\nPortable and lightweight design\r\nFast fire suppression capability\r\nDry chemical / CO₂ fire protection\r\nSafety pressure gauge indicator\r\nDurable steel cylinder body\r\nEasy-pull safety pin mechanism\r\nSuitable for Class A, B, and C fires\r\nCorrosion-resistant finish\r\nEasy wall-mount installation\r\nIdeal for home, office, and commercial use', 'Available', 1, '[\"..\\/uploads\\/products\\/prod_6a042479ba7021.90340002.jpg\"]', '[{\"label\":\"Basic Variants\",\"price\":1300,\"stock\":50}]', '2026-05-13 15:12:50', '2026-05-20 10:46:54'),
(17, 'chair', 'saasa', 'Accessories', 'asasa', 'Deleted', 1, '[\"..\\/uploads\\/products\\/prod_6a042964486377.51483792.jpg\"]', '[{\"label\":\"asa\",\"price\":222,\"stock\":222}]', '2026-05-13 15:33:47', '2026-05-13 15:45:22'),
(18, 'comp', 'sasaa', 'Smart Fire Alarm', 'asasasa', 'Deleted', 1, '0', '[{\"label\":\"sasa\",\"price\":22222,\"stock\":22}]', '2026-05-13 15:37:24', '2026-05-13 15:45:20');

-- --------------------------------------------------------

--
-- Table structure for table `product_edit_logs`
--

CREATE TABLE `product_edit_logs` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `edited_by` varchar(255) DEFAULT 'Admin',
  `changed_fields` text DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `edited_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_edit_logs`
--

INSERT INTO `product_edit_logs` (`id`, `product_id`, `product_name`, `edited_by`, `changed_fields`, `old_values`, `new_values`, `edited_at`) VALUES
(1, 14, 'TECHNiLOG Smart CCTV Camera', 'Admin', 'category', '{\"category\":\"Smart CCTV Camera\"}', '{\"category\":\"Other\"}', '2026-05-03 08:23:41'),
(2, 14, 'TECHNiLOG Smart CCTV Camera', 'Admin', 'category', '{\"category\":\"Other\"}', '{\"category\":\"Smart CCTV Camera\"}', '2026-05-03 08:24:11'),
(3, 14, 'TECHNiLOG Smart CCTV Camera', 'Admin', 'status, prices_json', '{\"status\":\"Available\",\"prices_json\":\"[{\\\"label\\\":\\\"hahah\\\",\\\"price\\\":6000,\\\"stock\\\":500}]\"}', '{\"status\":\"Out of Stock\",\"prices_json\":\"[{\\\"label\\\":\\\"hahah\\\",\\\"price\\\":6000,\\\"stock\\\":0}]\"}', '2026-05-03 08:28:43'),
(4, 14, 'TECHNiLOG Smart CCTV Camera', 'Admin', 'status, prices_json', '{\"status\":\"Out of Stock\",\"prices_json\":\"[{\\\"label\\\":\\\"hahah\\\",\\\"price\\\":6000,\\\"stock\\\":0}]\"}', '{\"status\":\"Available\",\"prices_json\":\"[{\\\"label\\\":\\\"hahah\\\",\\\"price\\\":6000,\\\"stock\\\":300}]\"}', '2026-05-03 08:29:02'),
(5, 16, 'Fire Extinguisher', 'Admin', 'images_json', '{\"images_json\":\"0\"}', '{\"images_json\":\"[\\\"..\\\\\\/uploads\\\\\\/products\\\\\\/prod_6a042479ba7021.90340002.jpg\\\"]\"}', '2026-05-13 07:12:57'),
(6, 17, 'chair', 'Admin', 'images_json', '{\"images_json\":\"0\"}', '{\"images_json\":\"[\\\"..\\\\\\/uploads\\\\\\/products\\\\\\/prod_6a042964486377.51483792.jpg\\\"]\"}', '2026-05-13 07:33:56'),
(7, 7, 'TECHNiLOG Smart Fire Alarm', 'Admin', '', '[]', '[]', '2026-05-13 08:38:29'),
(8, 7, 'TECHNiLOG Smart Fire Alarm', 'Admin', 'description, specifications, prices_json', '{\"description\":\"hahah\",\"specifications\":\"hshshs\",\"prices_json\":\"[{\\\"label\\\":\\\"hshshs\\\",\\\"price\\\":2000,\\\"stock\\\":297}]\"}', '{\"description\":\"Technilog Smart Fire Alarm System\\r\\n\\r\\nThe Technilog Smart Fire Alarm System is an advanced fire detection and safety solution designed to provide real-time monitoring, instant alerts, and intelligent emergency response for homes, buildings, schools, offices, and industrial facilities.\\r\\n\\r\\nUsing modern IoT (Internet of Things) technology, the system continuously monitors smoke, heat, and possible fire hazards through interconnected smart sensors. Once a threat is detected, the system immediately activates alarms and sends real-time notifications to authorized users through mobile devices, dashboards, or monitoring centers.\\r\\n\\r\\nKey Features\\r\\nReal-Time Monitoring \\u2013 Continuously tracks fire alarm status and environmental conditions 24\\/7.\\r\\nInstant Notifications \\u2013 Sends immediate alerts via mobile app, SMS, or email during emergencies.\\r\\nSmart Detection Technology \\u2013 Detects smoke, heat, and abnormal conditions quickly and accurately.\\r\\nRemote Access & Control \\u2013 Users can monitor and manage the system anytime and anywhere.\\r\\nAutomatic Emergency Response \\u2013 Can trigger alarms, activate safety protocols, and notify emergency responders.\\r\\nCloud-Based Data Monitoring \\u2013 Stores event logs and system activity for maintenance and reporting.\\r\\nReliable & Scalable \\u2013 Suitable for residential, commercial, and industrial applications.\\r\\nSystem Benefits\\r\\n\\r\\nThe Technilog Smart Fire Alarm System improves fire safety by enabling early detection, faster response times, reduced risk of property damage, and enhanced protection of lives and assets. Its intelligent monitoring capabilities help facility managers and homeowners maintain a safer environment while ensuring continuous system reliability.\",\"specifications\":\"Technilog Smart Fire Alarm System Specifications\\r\\nGeneral Specifications\\r\\nSystem Name: Technilog Smart Fire Alarm System\\r\\nSystem Type: Intelligent IoT-Based Fire Detection and Monitoring System\\r\\nApplication: Residential, Commercial, Industrial, and Institutional Buildings\\r\\nMonitoring Capability: 24\\/7 Real-Time Monitoring\\r\\nDetection Features\\r\\nSmoke Detection Sensor\\r\\nHeat Detection Sensor\\r\\nFlame Detection Capability\\r\\nAutomatic Fire Hazard Identification\\r\\nMulti-Sensor Integrated Detection\\r\\nAlarm Features\\r\\nHigh-Volume Audible Alarm\\r\\nLED Warning Indicators\\r\\nAutomatic Emergency Notifications\\r\\nInstant Alert Transmission\\r\\nManual Alarm Trigger Option\\r\\nConnectivity & Communication\\r\\nWi-Fi \\/ GSM \\/ Internet Connectivity\\r\\nMobile Application Integration\\r\\nReal-Time Cloud Monitoring\\r\\nSMS and Email Notification Support\\r\\nRemote Access and Control\\r\\nMonitoring System\\r\\nLive System Status Monitoring\\r\\nReal-Time Data Logging\\r\\nEvent History Recording\\r\\nSensor Health Monitoring\\r\\nFault and Error Detection System\\r\\nPower Specifications\\r\\nInput Power: 220V AC\\r\\nBackup Power Supply: Rechargeable Battery Backup\\r\\nLow Power Consumption Design\\r\\nAutomatic Power Failure Detection\\r\\nSmart Features\\r\\nIoT-Based Smart Integration\\r\\nAutomatic Emergency Response\\r\\nRemote System Configuration\\r\\nIntelligent Alert Prioritization\\r\\nScalable Multi-Zone Monitoring\\r\\nSafety & Reliability\\r\\nFast Response Time\\r\\nHigh Accuracy Detection\\r\\nContinuous System Self-Check\\r\\nFire-Resistant System Design\\r\\nReliable Network Communication\\r\\nUser Interface\\r\\nLCD \\/ Digital Display Panel\\r\\nMobile Monitoring Dashboard\\r\\nUser-Friendly Control Interface\\r\\nReal-Time Alert Display\\r\\nOptional Features\\r\\nCCTV Integration\\r\\nSprinkler System Activation\\r\\nVoice Evacuation Support\\r\\nIntegration with Building Management Systems (BMS)\\r\\nGPS Location Tracking for Emergency Response\\r\\nOperating Environment\\r\\nIndoor Installation\\r\\nOperating Temperature: 0\\u00b0C \\u2013 50\\u00b0C\\r\\nHumidity Resistance Capability\\r\\nSuitable for Offices, Schools, Warehouses, and Homes\\r\\nBenefits\\r\\nEarly Fire Detection\\r\\nFaster Emergency Response\\r\\nReduced Property Damage\\r\\nEnhanced Safety and Protection\\r\\nRemote Monitoring Convenience\",\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variant\\\",\\\"price\\\":2000,\\\"stock\\\":297}]\"}', '2026-05-18 11:36:31'),
(9, 7, 'TECHNiLOG Smart Fire Alarm', 'Admin', 'specifications', '{\"specifications\":\"Technilog Smart Fire Alarm System Specifications\\r\\nGeneral Specifications\\r\\nSystem Name: Technilog Smart Fire Alarm System\\r\\nSystem Type: Intelligent IoT-Based Fire Detection and Monitoring System\\r\\nApplication: Residential, Commercial, Industrial, and Institutional Buildings\\r\\nMonitoring Capability: 24\\/7 Real-Time Monitoring\\r\\nDetection Features\\r\\nSmoke Detection Sensor\\r\\nHeat Detection Sensor\\r\\nFlame Detection Capability\\r\\nAutomatic Fire Hazard Identification\\r\\nMulti-Sensor Integrated Detection\\r\\nAlarm Features\\r\\nHigh-Volume Audible Alarm\\r\\nLED Warning Indicators\\r\\nAutomatic Emergency Notifications\\r\\nInstant Alert Transmission\\r\\nManual Alarm Trigger Option\\r\\nConnectivity & Communication\\r\\nWi-Fi \\/ GSM \\/ Internet Connectivity\\r\\nMobile Application Integration\\r\\nReal-Time Cloud Monitoring\\r\\nSMS and Email Notification Support\\r\\nRemote Access and Control\\r\\nMonitoring System\\r\\nLive System Status Monitoring\\r\\nReal-Time Data Logging\\r\\nEvent History Recording\\r\\nSensor Health Monitoring\\r\\nFault and Error Detection System\\r\\nPower Specifications\\r\\nInput Power: 220V AC\\r\\nBackup Power Supply: Rechargeable Battery Backup\\r\\nLow Power Consumption Design\\r\\nAutomatic Power Failure Detection\\r\\nSmart Features\\r\\nIoT-Based Smart Integration\\r\\nAutomatic Emergency Response\\r\\nRemote System Configuration\\r\\nIntelligent Alert Prioritization\\r\\nScalable Multi-Zone Monitoring\\r\\nSafety & Reliability\\r\\nFast Response Time\\r\\nHigh Accuracy Detection\\r\\nContinuous System Self-Check\\r\\nFire-Resistant System Design\\r\\nReliable Network Communication\\r\\nUser Interface\\r\\nLCD \\/ Digital Display Panel\\r\\nMobile Monitoring Dashboard\\r\\nUser-Friendly Control Interface\\r\\nReal-Time Alert Display\\r\\nOptional Features\\r\\nCCTV Integration\\r\\nSprinkler System Activation\\r\\nVoice Evacuation Support\\r\\nIntegration with Building Management Systems (BMS)\\r\\nGPS Location Tracking for Emergency Response\\r\\nOperating Environment\\r\\nIndoor Installation\\r\\nOperating Temperature: 0\\u00b0C \\u2013 50\\u00b0C\\r\\nHumidity Resistance Capability\\r\\nSuitable for Offices, Schools, Warehouses, and Homes\\r\\nBenefits\\r\\nEarly Fire Detection\\r\\nFaster Emergency Response\\r\\nReduced Property Damage\\r\\nEnhanced Safety and Protection\\r\\nRemote Monitoring Convenience\"}', '{\"specifications\":\"Technilog Smart Fire Alarm System\\r\\nReal-time fire and smoke monitoring\\r\\nSmoke, heat, and flame detection sensors\\r\\nInstant alarm and mobile notifications\\r\\nWi-Fi \\/ IoT connectivity\\r\\nRemote monitoring through mobile app\\r\\nAutomatic emergency alert system\\r\\nBattery backup support\\r\\nLCD\\/Digital display interface\\r\\nLow power consumption\\r\\nSuitable for homes, offices, and buildings\"}', '2026-05-18 11:38:27'),
(10, 14, 'TECHNiLOG Smart CCTV Camera', 'Admin', 'description, specifications, prices_json', '{\"description\":\"Smart Camera\",\"specifications\":\"hahah\",\"prices_json\":\"[{\\\"label\\\":\\\"hahah\\\",\\\"price\\\":6000,\\\"stock\\\":293}]\"}', '{\"description\":\"Technilog Smart CCTV Camera System\\r\\n\\r\\nThe Technilog Smart CCTV Camera System is an intelligent security and surveillance solution designed to provide real-time monitoring, remote access, and advanced protection for homes, offices, schools, and commercial establishments.\\r\\n\\r\\nEquipped with high-definition cameras and smart monitoring technology, the system captures clear video footage day and night while allowing users to monitor their property remotely through mobile devices or computers. The system features motion detection, instant alerts, cloud recording, and live video streaming to ensure continuous security and fast response to suspicious activities.\\r\\n\\r\\nKey Features\\r\\nReal-time video monitoring\\r\\nHigh-definition (HD) camera quality\\r\\nNight vision capability\\r\\nMotion detection and instant alerts\\r\\nRemote viewing through mobile app\\r\\nCloud and local video storage\\r\\n24\\/7 surveillance recording\\r\\nWi-Fi \\/ Internet connectivity\\r\\nEasy installation and user-friendly interface\\r\\nSuitable for homes, offices, schools, and businesses\\r\\nBenefits\\r\\n\\r\\nThe Technilog Smart CCTV Camera System enhances security by providing continuous surveillance, reducing security risks, improving incident monitoring, and allowing users to access live footage anytime and anywhere for better safety and protection.\",\"specifications\":\"Technilog Smart CCTV Camera System\\r\\nHD real-time video monitoring\\r\\nNight vision camera support\\r\\nMotion detection alerts\\r\\nRemote mobile access\\r\\nWi-Fi \\/ Internet connectivity\\r\\nCloud and local storage\\r\\n24\\/7 video recording\\r\\nWide-angle camera view\\r\\nLow power consumption\\r\\nIndoor and outdoor installation support\",\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variants\\\",\\\"price\\\":6000,\\\"stock\\\":293}]\"}', '2026-05-18 11:42:53'),
(11, 16, 'Fire Extinguisher', 'Admin', 'description, specifications, prices_json', '{\"description\":\"hahaha\",\"specifications\":\"hahahah\",\"prices_json\":\"[{\\\"label\\\":\\\"ahaha\\\",\\\"price\\\":1000,\\\"stock\\\":200}]\"}', '{\"description\":\"Technilog Fire Extinguisher\\r\\n\\r\\nThe Technilog Fire Extinguisher is a reliable fire safety device designed to quickly control and extinguish small fires before they spread. It is suitable for homes, offices, schools, commercial buildings, and industrial areas, providing fast emergency response and enhanced protection for people and property.\\r\\n\\r\\nBuilt with durable materials and easy-to-use operation, the extinguisher is effective against common fire types such as electrical fires, flammable liquids, and ordinary combustible materials. Its compact and portable design allows convenient installation and quick access during emergencies.\\r\\n\\r\\nKey Features\\r\\nFast and effective fire suppression\\r\\nEasy-to-use safety mechanism\\r\\nPortable and lightweight design\\r\\nDurable and corrosion-resistant cylinder\\r\\nSuitable for electrical and combustible fires\\r\\nSafety pressure indicator\\r\\nReliable emergency protection\\r\\nIdeal for residential and commercial use\\r\\nBenefits\\r\\n\\r\\nThe Technilog Fire Extinguisher helps reduce fire damage, improves emergency preparedness, and provides dependable first-response fire protection for safer environments.\",\"specifications\":\"Technilog Fire Extinguisher\\r\\nPortable and lightweight design\\r\\nFast fire suppression capability\\r\\nDry chemical \\/ CO\\u2082 fire protection\\r\\nSafety pressure gauge indicator\\r\\nDurable steel cylinder body\\r\\nEasy-pull safety pin mechanism\\r\\nSuitable for Class A, B, and C fires\\r\\nCorrosion-resistant finish\\r\\nEasy wall-mount installation\\r\\nIdeal for home, office, and commercial use\",\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variants\\\",\\\"price\\\":1300,\\\"stock\\\":200}]\"}', '2026-05-18 11:47:10'),
(12, 14, 'TECHNiLOG Smart CCTV Camera', 'Admin', 'prices_json', '{\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variants\\\",\\\"price\\\":6000,\\\"stock\\\":293}]\"}', '{\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variants\\\",\\\"price\\\":2385,\\\"stock\\\":293}]\"}', '2026-05-18 11:47:17'),
(13, 7, 'TECHNiLOG Smart Fire Alarm', 'Admin', 'prices_json', '{\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variant\\\",\\\"price\\\":2000,\\\"stock\\\":297}]\"}', '{\"prices_json\":\"[{\\\"label\\\":\\\"Basic Variant\\\",\\\"price\\\":1500,\\\"stock\\\":297}]\"}', '2026-05-18 11:47:26'),
(14, 7, 'TECHNiLOG Smart Fire Alarm', 'Admin', '', '[]', '[]', '2026-05-19 07:37:24');

-- --------------------------------------------------------

--
-- Table structure for table `received_records`
--

CREATE TABLE `received_records` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_ref` varchar(50) DEFAULT NULL,
  `user_id` int(11) DEFAULT 0,
  `customer_name` varchar(100) DEFAULT NULL,
  `customer_email` varchar(150) DEFAULT NULL,
  `customer_phone` varchar(30) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `service` varchar(50) DEFAULT NULL,
  `installation_fee` decimal(10,2) DEFAULT 0.00,
  `items_json` text DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `shipping` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) DEFAULT 0.00,
  `received_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `received_records`
--

INSERT INTO `received_records` (`id`, `order_id`, `order_ref`, `user_id`, `customer_name`, `customer_email`, `customer_phone`, `address`, `payment_method`, `service`, `installation_fee`, `items_json`, `subtotal`, `shipping`, `total`, `received_at`) VALUES
(1, 5, 'TL-00005', 0, 'RonLouie Magsipoc', 'ronlouiemagsipoc210@gmail.com', '09934534048', '38, San Carlos, Binangonan, Rizal 1940', 'cod', 'self', 0.00, '[{\"id\":7,\"name\":\"TECHNiLOG Smart Fire Alarm\",\"image\":\"\",\"variant\":\"hshshs\",\"price\":2000,\"qty\":1}]', 2000.00, 120.00, 2120.00, '2026-05-04 19:47:40');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `order_ref` varchar(50) NOT NULL,
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL,
  `user_name` varchar(255) NOT NULL DEFAULT '',
  `rating` tinyint(1) NOT NULL,
  `comment` text NOT NULL,
  `image_path` varchar(512) DEFAULT NULL,
  `admin_reply` text DEFAULT NULL,
  `replied_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `order_id`, `order_ref`, `product_id`, `product_name`, `user_id`, `user_name`, `rating`, `comment`, `image_path`, `admin_reply`, `replied_at`, `created_at`) VALUES
(1, 27, 'TL-17160FCC', 16, '0', 5, 'Benis', 4, 'good', NULL, 'thank you', '2026-05-14 16:52:00', '2026-05-14 16:51:16'),
(2, 28, 'TL-C531E9A8', 14, '0', 10, 'Ron', 5, 'Effective and good', 'uploads/reviews/review_10_14_1778819836.jpg', NULL, NULL, '2026-05-15 12:37:16'),
(3, 29, 'TL-A9D0495D', 16, '0', 10, 'Ron', 4, 'this is good thank you', NULL, NULL, NULL, '2026-05-15 12:38:06'),
(4, 21, 'TL-73414B56', 14, '0', 5, 'Benis', 1, 'bato dumating 1 star ka sakin', NULL, 'im sorry po, karma na bahala sakin', '2026-05-17 14:29:20', '2026-05-17 14:21:10'),
(5, 37, 'TL-F45090EE', 14, '0', 20, 'Lorie Ann', 5, 'expected I can use to cook meal. \r\n\r\n\r\nCan detect ghost. Nice!', NULL, NULL, NULL, '2026-05-17 19:00:26'),
(6, 36, 'TL-1AAD022F', 16, '0', 18, 'Niagara', 5, 'Satisfied customer 😄', 'uploads/reviews/review_18_16_1779016228.jpg', NULL, NULL, '2026-05-17 19:10:28'),
(7, 38, 'TL-DE59BE06', 16, '0', 19, 'anitoramv@gmail.com', 5, 'Good naman ang products kaso mas nauna pato mawala kesa yung apoy', 'uploads/reviews/review_19_16_1779073496.jpg', NULL, NULL, '2026-05-18 11:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_verified` tinyint(1) NOT NULL DEFAULT 0,
  `is_banned` tinyint(1) NOT NULL DEFAULT 0,
  `role` varchar(20) NOT NULL DEFAULT 'user',
  `verify_code` char(6) DEFAULT NULL,
  `verify_expires` datetime DEFAULT NULL,
  `reset_code` char(6) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `address` varchar(512) DEFAULT NULL,
  `profile_pic` varchar(512) DEFAULT NULL,
  `auth_provider` varchar(20) NOT NULL DEFAULT 'local'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `is_verified`, `is_banned`, `role`, `verify_code`, `verify_expires`, `reset_code`, `reset_expires`, `created_at`, `updated_at`, `address`, `profile_pic`, `auth_provider`) VALUES
(4, 'Tetsuyash', 'ronlouiemagsipoc210@gmail.com', '$2y$10$hxM065OUfykS85P0Ou939OsKgvHyKhovws5W0RBOlVgCZh4Ft7aWO', 1, 0, 'admin', NULL, NULL, '296367', '2026-05-18 12:14:29', '2026-04-28 17:17:34', '2026-05-18 17:59:29', '{\"name\":\"RonLouie Magsipoc\",\"phone\":\"09934534048\",\"street\":\"38\",\"barangay\":\"San Carlos\",\"city\":\"Binangonan\",\"province\":\"Rizal\",\"zip\":\"1940\",\"notes\":\"\"}', 'uploads/avatars/avatar_4_1779074387.jpeg', 'local'),
(5, 'Benis', 'garnavenice9@gmail.com', '$2y$10$q0UN1VqDvuLZ.CV0vTVFgOKsTyp0LeOoZLGPX7a3CYa3iuswrg3mC', 1, 0, 'user', NULL, NULL, '382653', '2026-05-02 17:17:02', '2026-04-28 20:00:47', '2026-05-17 19:36:55', NULL, 'uploads/avatars/avatar_5_1779017815.jpeg', 'local'),
(10, 'Ron', 'magsipocronlouie@gmail.com', '$2y$10$fZCj8Vtr/5W4auT3ZLDmC.GyQDoAU7zveQ1gYOmKRl.hPqBm55u56', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-14 14:26:28', '2026-05-18 18:01:34', NULL, 'uploads/avatars/avatar_10_1778740122.jpeg', 'local'),
(11, 'Bunzkageru', 'bunzkageru@gmail.com', '$2y$10$lGWrsXmdPTqPHx6cgnFIKuAKxovJS.Yz8qh7m05s1n8Y5E.gxmdkq', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-15 12:06:47', '2026-05-15 12:08:32', NULL, 'uploads/avatars/avatar_11_1778818112.jpeg', 'local'),
(15, 'Tetsuya', 'tetsuyakuroko1518@gmail.com', '$2y$10$keilfpp9IdYarcwVBHMwMOFQyUMERBqZ2UF/R77ADrENe72f5OK4.', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-17 13:49:10', '2026-05-17 13:49:33', NULL, NULL, 'local'),
(16, 'binis', 'petilogarnavenice@gmail.com', '$2y$10$L5yMg7qG2sKdhcgahcOUD.A3M6jmKlhe0q5HKbGEFwpMdyQ2hqUjS', 1, 0, 'user', '722877', '2026-05-17 08:25:39', NULL, NULL, '2026-05-17 14:10:39', '2026-05-17 14:24:17', NULL, NULL, 'local'),
(17, 'rnmgspc', 'rlamagsipoc@gmail.com', '$2y$10$7g3t5hsEQmMxXTXjW/nzXO8Epc9rldFoxJznoRisv5By.A7m5ET4e', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-17 14:12:14', '2026-05-17 14:12:32', NULL, NULL, 'local'),
(18, 'Niagara', 'bimboyhoyo@gmail.com', '$2y$10$GoeDWdnVd9iYz0GcvFmXj.4FmC1Icnrdjb3qZgj21ORVXdkyVUj2W', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-17 14:15:08', '2026-05-17 14:20:02', '{\"name\":\"Marc jayvin magsipoc\",\"phone\":\"09942327622\",\"street\":\"38 linaluz\",\"barangay\":\"San carlos\",\"city\":\"Binangonan\",\"province\":\"Rizal\",\"zip\":\"1940\",\"notes\":\"Nigga\"}', NULL, 'local'),
(19, 'anitoramv@gmail.com', 'anitoramv@gmail.com', '$2y$10$QKodMoadnTwxb4wNA06XCOVAlo.YCIoxlXVnpU5QTKt.p7KTQ2WfS', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-17 14:43:09', '2026-05-17 14:43:27', NULL, NULL, 'local'),
(20, 'Lorie Ann', 'magsipoc.lorieann@yahoo.com', '$2y$10$j3ZwVJ9u9.qUytvGi6AZMeLOWY7FcpplwFbsJjZbt.O.7AcIv/4Zq', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-17 18:52:31', '2026-05-17 18:54:26', NULL, NULL, 'local'),
(21, 'hays', 'jpgarcia5208@gmail.com', '$2y$10$sVNNaDlfOU9fujfbxvaP.eYbN.drbZvPF9wHpcl0smvUSOMcoBgtK', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-18 10:00:51', '2026-05-18 10:01:24', NULL, NULL, 'local'),
(22, 'Dheznie May Libreja', 'dhezniemaylibreja@gmail.com', '$2y$10$/yUUBYp5cFTEKbrmtjVRoONBWqq5URz1EuWsAjYWCQFCh4xsFNY46', 1, 0, 'user', NULL, NULL, NULL, NULL, '2026-05-18 10:19:57', '2026-05-18 10:19:57', NULL, NULL, 'local'),
(24, 'Reynold', 'reynolddelacruz1025@gmail.com', '$2y$10$XGkBPAh/ArImeSI0cAvoS.8AX.quRaA5O/.922xEgInhsDrEmvQIy', 1, 0, 'admin', NULL, NULL, NULL, NULL, '2026-05-21 09:18:19', '2026-05-21 19:51:50', NULL, NULL, 'local');

-- --------------------------------------------------------

--
-- Table structure for table `user_devices`
--

CREATE TABLE `user_devices` (
  `id` int(11) NOT NULL,
  `order_ref` varchar(50) NOT NULL,
  `model_no` varchar(50) NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `device_type` varchar(100) NOT NULL,
  `device_model` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `received_date` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_warnings`
--

CREATE TABLE `user_warnings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `report_id` int(11) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_warnings`
--

INSERT INTO `user_warnings` (`id`, `user_id`, `report_id`, `message`, `is_read`, `created_at`) VALUES
(1, 21, 3, 'ganda ni sarina', 0, '2026-05-18 13:39:00'),
(2, 18, 4, 'ganda ni sarina', 1, '2026-05-18 13:39:40'),
(3, 18, 5, 'isa kanalang boi', 0, '2026-05-18 19:48:52'),
(4, 18, 5, 'isa kanalang boi', 0, '2026-05-18 19:48:54');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin_logins`
--
ALTER TABLE `admin_logins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_token` (`session_token`),
  ADD KEY `login_time` (`login_time`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `cancelled_records`
--
ALTER TABLE `cancelled_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_order_id` (`order_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_product` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `deleted_forum_posts`
--
ALTER TABLE `deleted_forum_posts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `delivery_logs`
--
ALTER TABLE `delivery_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `delivery_id` (`delivery_id`);

--
-- Indexes for table `device_history`
--
ALTER TABLE `device_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dh_user` (`user_id`),
  ADD KEY `idx_dh_model` (`model_no`);

--
-- Indexes for table `forum_comments`
--
ALTER TABLE `forum_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `idx_comment_post` (`post_id`),
  ADD KEY `idx_comment_user` (`user_id`);

--
-- Indexes for table `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `unique_comment_like` (`comment_id`,`user_id`);

--
-- Indexes for table `forum_notifications`
--
ALTER TABLE `forum_notifications`
  ADD PRIMARY KEY (`notif_id`),
  ADD UNIQUE KEY `uniq_like` (`owner_id`,`actor_id`,`post_id`,`type`);

--
-- Indexes for table `forum_posts`
--
ALTER TABLE `forum_posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `idx_post_user` (`user_id`);

--
-- Indexes for table `forum_post_likes`
--
ALTER TABLE `forum_post_likes`
  ADD PRIMARY KEY (`like_id`),
  ADD UNIQUE KEY `uq_post_user` (`post_id`,`user_id`),
  ADD KEY `idx_post_id` (`post_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `forum_reports`
--
ALTER TABLE `forum_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reporter` (`reporter_id`),
  ADD KEY `idx_reported_user` (`reported_user_id`),
  ADD KEY `idx_post` (`post_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `online_payment`
--
ALTER TABLE `online_payment`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_ref` (`order_ref`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `password_reset_log`
--
ALTER TABLE `password_reset_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `product_edit_logs`
--
ALTER TABLE `product_edit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `received_records`
--
ALTER TABLE `received_records`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_order_product` (`order_id`,`product_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_devices`
--
ALTER TABLE `user_devices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_warnings`
--
ALTER TABLE `user_warnings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin_logins`
--
ALTER TABLE `admin_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `cancelled_records`
--
ALTER TABLE `cancelled_records`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `deleted_forum_posts`
--
ALTER TABLE `deleted_forum_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `deliveries`
--
ALTER TABLE `deliveries`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `delivery_logs`
--
ALTER TABLE `delivery_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `device_history`
--
ALTER TABLE `device_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `forum_comments`
--
ALTER TABLE `forum_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `forum_comment_likes`
--
ALTER TABLE `forum_comment_likes`
  MODIFY `like_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `forum_notifications`
--
ALTER TABLE `forum_notifications`
  MODIFY `notif_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1741;

--
-- AUTO_INCREMENT for table `forum_posts`
--
ALTER TABLE `forum_posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `forum_post_likes`
--
ALTER TABLE `forum_post_likes`
  MODIFY `like_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `forum_reports`
--
ALTER TABLE `forum_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `online_payment`
--
ALTER TABLE `online_payment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `password_reset_log`
--
ALTER TABLE `password_reset_log`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `product_edit_logs`
--
ALTER TABLE `product_edit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `received_records`
--
ALTER TABLE `received_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `user_devices`
--
ALTER TABLE `user_devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_warnings`
--
ALTER TABLE `user_warnings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `deliveries`
--
ALTER TABLE `deliveries`
  ADD CONSTRAINT `deliveries_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery_logs`
--
ALTER TABLE `delivery_logs`
  ADD CONSTRAINT `delivery_logs_ibfk_1` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `password_reset_log`
--
ALTER TABLE `password_reset_log`
  ADD CONSTRAINT `password_reset_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
