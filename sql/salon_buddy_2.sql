-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 13, 2025 at 07:55 AM
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
-- Database: `salon_buddy_2`
--

-- --------------------------------------------------------

--
-- Table structure for table `aboutus_pages`
--

CREATE TABLE `aboutus_pages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `section_1_heading` varchar(100) DEFAULT NULL,
  `section_1_description` text DEFAULT NULL,
  `section_1_btn_link` varchar(250) DEFAULT NULL,
  `section_1_image` varchar(100) DEFAULT NULL,
  `section_1_image_2` varchar(100) DEFAULT NULL,
  `section_1_experience` varchar(100) DEFAULT NULL,
  `section_play_title` varchar(100) DEFAULT NULL,
  `section_play_link` varchar(250) DEFAULT NULL,
  `section_play_image` varchar(100) DEFAULT NULL,
  `section_discover_heading` varchar(100) DEFAULT NULL,
  `section_discover_description` varchar(250) DEFAULT NULL,
  `section_discover_bg_image` varchar(100) DEFAULT NULL,
  `section_discover_front_image` varchar(100) DEFAULT NULL,
  `section_discover_item_1_heading` varchar(100) DEFAULT NULL,
  `section_discover_item_1_description` varchar(250) DEFAULT NULL,
  `section_discover_item_1_image` varchar(100) DEFAULT NULL,
  `section_discover_item_2_heading` varchar(100) DEFAULT NULL,
  `section_discover_item_2_description` varchar(250) DEFAULT NULL,
  `section_discover_item_2_image` varchar(100) DEFAULT NULL,
  `section_discover_item_3_heading` varchar(100) DEFAULT NULL,
  `section_discover_item_3_description` varchar(250) DEFAULT NULL,
  `section_discover_item_3_image` varchar(100) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `aboutus_pages`
--

INSERT INTO `aboutus_pages` (`id`, `section_1_heading`, `section_1_description`, `section_1_btn_link`, `section_1_image`, `section_1_image_2`, `section_1_experience`, `section_play_title`, `section_play_link`, `section_play_image`, `section_discover_heading`, `section_discover_description`, `section_discover_bg_image`, `section_discover_front_image`, `section_discover_item_1_heading`, `section_discover_item_1_description`, `section_discover_item_1_image`, `section_discover_item_2_heading`, `section_discover_item_2_description`, `section_discover_item_2_image`, `section_discover_item_3_heading`, `section_discover_item_3_description`, `section_discover_item_3_image`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'Keeping your hair smooth and stylish, just like always', 'There\'s things people say in the barbershop they won\'t even say in their own living room, because it\'s just one of those zones where There\'s things people say in the barbershop they won\'t even say in their own living room, because it\'s just one of those zones where nobody\'s going. Come to the corner for a great cut.', NULL, NULL, NULL, '25', 'Experience the ultimate luxury for your hair like never before', NULL, NULL, 'Discover the Magic of Gorgeous Haircut & Styles', 'Pulvinar sagittis maximus posuere nec erat sit quisque inceptos mollis hac. Diam mattis lacus platea fusce quam fringilla metus.', NULL, NULL, 'Haircare Services', 'Porta conubia sollicitudin nostra risus fusce. Penatibus morbi felis efficitur ornare mi habitasse.', NULL, 'Professional Style', 'Porta conubia sollicitudin nostra risus fusce. Penatibus morbi felis efficitur ornare mi habitasse.', NULL, 'Comfortable Place', 'Porta conubia sollicitudin nostra risus fusce. Penatibus morbi felis efficitur ornare mi habitasse.', NULL, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` varchar(55) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `in_time` varchar(255) DEFAULT NULL,
  `out_time` varchar(255) DEFAULT NULL,
  `total_time` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `banner_tag` varchar(25) DEFAULT NULL,
  `banner_title` varchar(55) DEFAULT NULL,
  `banner_description` varchar(255) DEFAULT NULL,
  `banner_image` varchar(55) DEFAULT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(11) DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `date` varchar(25) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Accepted','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `reference_no`, `customer_id`, `branch_id`, `date`, `note`, `status`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, '000001', 1, 1, '2025-08-26', NULL, 'Pending', 1, 1, 'Live', '2025-08-25 12:25:36', '2025-08-25 12:25:36');

-- --------------------------------------------------------

--
-- Table structure for table `booking_details`
--

CREATE TABLE `booking_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `start_time` varchar(25) DEFAULT NULL,
  `end_time` varchar(25) DEFAULT NULL,
  `service_seller_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `booking_details`
--

INSERT INTO `booking_details` (`id`, `booking_id`, `item_id`, `start_time`, `end_time`, `service_seller_id`, `quantity`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 1, 1, '12:00', '12:05', 3, 1, 'Live', '2025-08-25 12:25:36', '2025-08-25 12:25:36');

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `branch_name` varchar(55) DEFAULT NULL,
  `branch_code` varchar(10) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `open_day_start` varchar(25) DEFAULT NULL,
  `open_day_end` varchar(25) DEFAULT NULL,
  `open_day_start_time` varchar(25) DEFAULT NULL,
  `open_day_end_time` varchar(25) DEFAULT NULL,
  `active_status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `branch_name`, `branch_code`, `address`, `phone`, `email`, `photo`, `open_day_start`, `open_day_end`, `open_day_start_time`, `open_day_end_time`, `active_status`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'ABC', '000001', 'Uttara, Dhaka', '01709-098765', 'abc@email.com', NULL, 'Monday', 'Friday', '9:00', '18:00', 'Active', 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('Service','Product') NOT NULL DEFAULT 'Service',
  `name` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `sort_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `type`, `name`, `description`, `photo`, `sort_id`, `status`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'Service', 'TSET', NULL, NULL, NULL, 'Enabled', 1, 1, 'Live', '2025-08-25 08:22:01', '2025-08-25 08:22:01');

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `date_format` varchar(55) DEFAULT NULL,
  `default_payment` varchar(25) DEFAULT NULL,
  `currency` varchar(10) DEFAULT NULL,
  `currency_position` varchar(25) DEFAULT NULL,
  `precision` int(11) NOT NULL DEFAULT 2,
  `minimum_point_to_redeem` int(11) DEFAULT NULL,
  `loyalty_rate` decimal(10,2) DEFAULT NULL,
  `thousand_separator` enum('.',',','') NOT NULL DEFAULT '.',
  `decimal_separator` enum('.',',','') NOT NULL DEFAULT '.',
  `item_code_start_from` varchar(25) DEFAULT NULL,
  `white_label` varchar(255) DEFAULT NULL,
  `white_label_status` tinyint(1) NOT NULL DEFAULT 0,
  `collect_tax` enum('Yes','No') NOT NULL DEFAULT 'No',
  `tax_type` enum('Inclusive','Exclusive') NOT NULL DEFAULT 'Exclusive',
  `tax_title` varchar(55) DEFAULT NULL,
  `tax_registration_no` varchar(255) DEFAULT NULL,
  `tax_is_gst` enum('Yes','No') NOT NULL DEFAULT 'No',
  `state_code` varchar(255) DEFAULT NULL,
  `tax_setting` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tax_setting`)),
  `tax_string` varchar(255) DEFAULT NULL,
  `timezone` varchar(55) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `email`, `phone`, `website`, `address`, `date_format`, `default_payment`, `currency`, `currency_position`, `precision`, `minimum_point_to_redeem`, `loyalty_rate`, `thousand_separator`, `decimal_separator`, `item_code_start_from`, `white_label`, `white_label_status`, `collect_tax`, `tax_type`, `tax_title`, `tax_registration_no`, `tax_is_gst`, `state_code`, `tax_setting`, `tax_string`, `timezone`, `logo`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'Salon Buddy', 'admin@doorsoft.co', '+880 18 123 91633', NULL, NULL, 'Y/m/d', '1', '$', 'Before Amount', 2, 0, 0.00, '.', '.', '000001', '{\"site_name\":\"Salon Buddy\",\"site_footer\":\"Salon Buddy\",\"site_title\":\"Door Soft\",\"site_link\":\"https:\\/\\/doorsoft-demo.com\",\"site_logo\":\"initial-logo.png\",\"site_favicon\":\"initial-fav.ico\"}', 0, 'Yes', 'Exclusive', 'Local Tax', '100666666666666', 'Yes', NULL, '[{\"tax\":\"CGST\",\"tax_rate\":\"5\"},{\"tax\":\"IGST\",\"tax_rate\":\"5\"},{\"tax\":\"SGST\",\"tax_rate\":\"5\"}]', 'CGST:IGST:SGST:', 'Asia/Dhaka', NULL, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `phone` varchar(25) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `photo` varchar(55) DEFAULT NULL,
  `same_or_diff_state` enum('Same','Different') NOT NULL DEFAULT 'Same',
  `gst_number` varchar(55) DEFAULT NULL,
  `date_of_birth` varchar(25) DEFAULT NULL,
  `date_of_anniversary` varchar(25) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `name`, `phone`, `email`, `email_verified_at`, `password`, `address`, `photo`, `same_or_diff_state`, `gst_number`, `date_of_birth`, `date_of_anniversary`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'Walk-in Customer', NULL, NULL, NULL, NULL, NULL, NULL, 'Same', NULL, NULL, NULL, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `customer_receives`
--

CREATE TABLE `customer_receives` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(55) DEFAULT NULL,
  `date` varchar(25) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(55) DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(25) DEFAULT NULL,
  `date` varchar(25) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `category_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `faqs`
--

CREATE TABLE `faqs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `status` enum('Enabled','Disabled') NOT NULL DEFAULT 'Enabled',
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `day` varchar(10) DEFAULT NULL,
  `start_time` varchar(55) DEFAULT NULL,
  `end_time` varchar(255) DEFAULT NULL,
  `auto_response` enum('Yes','No') DEFAULT NULL,
  `mail_subject` varchar(255) DEFAULT NULL,
  `mail_body` longtext DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `code` varchar(55) DEFAULT NULL,
  `type` enum('Product','Service','Package') DEFAULT NULL,
  `duration` varchar(55) DEFAULT NULL,
  `duration_type` enum('Day','Hour','Minute') DEFAULT NULL,
  `purchase_price` decimal(10,3) DEFAULT NULL,
  `last_purchase_price` decimal(10,3) DEFAULT NULL,
  `last_three_purchase_avg` decimal(10,3) DEFAULT NULL,
  `sale_price` decimal(10,3) DEFAULT NULL,
  `profit_margin` decimal(10,3) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `tax_information` text DEFAULT NULL,
  `status` enum('Enable','Disable') NOT NULL DEFAULT 'Enable',
  `loyalty_point` varchar(25) DEFAULT NULL,
  `use_consumption` tinyint(1) NOT NULL DEFAULT 0,
  `alert_stock_qty` bigint(20) UNSIGNED DEFAULT NULL,
  `category_id` bigint(20) UNSIGNED DEFAULT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `unit_id` bigint(20) UNSIGNED DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `code`, `type`, `duration`, `duration_type`, `purchase_price`, `last_purchase_price`, `last_three_purchase_avg`, `sale_price`, `profit_margin`, `description`, `photo`, `tax_information`, `status`, `loyalty_point`, `use_consumption`, `alert_stock_qty`, `category_id`, `supplier_id`, `unit_id`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'TSET', '000001', 'Service', '0', 'Day', 0.000, NULL, NULL, 500.000, NULL, NULL, NULL, '[{\"tax\":\"CGST\",\"tax_rate\":\"5\"},{\"tax\":\"IGST\",\"tax_rate\":\"5\"},{\"tax\":\"SGST\",\"tax_rate\":\"5\"}]', 'Enable', NULL, 0, NULL, 1, 0, 0, 1, 1, 'Live', '2025-08-25 08:22:29', '2025-08-25 08:22:29'),
(2, 'Package', '000002', 'Package', '0', 'Day', 0.000, NULL, NULL, 500.000, NULL, NULL, NULL, '[{\"tax\":\"CGST\",\"tax_rate\":\"5\"},{\"tax\":\"IGST\",\"tax_rate\":\"5\"},{\"tax\":\"SGST\",\"tax_rate\":\"5\"}]', 'Enable', NULL, 0, NULL, 1, 0, 0, 1, 1, 'Live', '2025-08-25 12:26:02', '2025-08-25 12:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `item_details`
--

CREATE TABLE `item_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_relation_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `consumption` decimal(10,3) DEFAULT NULL,
  `unit_id` int(10) UNSIGNED DEFAULT NULL,
  `conversion_rate` decimal(10,3) DEFAULT NULL,
  `cost_per_unit` decimal(10,3) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,3) DEFAULT NULL,
  `discount` varchar(55) DEFAULT NULL,
  `total_price` decimal(10,3) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_details`
--

INSERT INTO `item_details` (`id`, `item_relation_id`, `item_id`, `consumption`, `unit_id`, `conversion_rate`, `cost_per_unit`, `quantity`, `price`, `discount`, `total_price`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 2, 1, NULL, NULL, NULL, NULL, 1, 500.000, '0', 500.000, 1, 1, 'Live', '2025-08-25 12:26:02', '2025-08-25 12:26:02');

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2025_02_27_062833_create_personal_access_tokens_table', 1),
(5, '2025_03_03_084040_create_permission_tables', 1),
(6, '2025_03_03_190756_create_companies_table', 1),
(7, '2025_03_03_204827_create_settings_table', 1),
(8, '2025_05_07_060006_create_items_table', 1),
(9, '2025_05_07_150235_create_branches_table', 1),
(10, '2025_05_07_165302_create_categories_table', 1),
(11, '2025_05_07_170015_create_units_table', 1),
(12, '2025_05_08_131623_create_customers_table', 1),
(13, '2025_05_08_131708_create_suppliers_table', 1),
(14, '2025_05_08_153731_create_expenses_table', 1),
(15, '2025_05_08_153739_create_expense_categories_table', 1),
(16, '2025_05_08_161642_create_payment_methods_table', 1),
(17, '2025_05_12_050324_create_timezones_table', 1),
(18, '2025_05_12_113746_create_attendances_table', 1),
(19, '2025_05_12_142758_create_promotions_table', 1),
(20, '2025_05_12_165612_create_purchases_table', 1),
(21, '2025_05_12_165624_create_purchase_details_table', 1),
(22, '2025_05_13_135402_create_customer_receives_table', 1),
(23, '2025_05_13_135518_create_supplier_payments_table', 1),
(24, '2025_05_13_151040_create_bookings_table', 1),
(25, '2025_05_13_164601_create_booking_details_table', 1),
(26, '2025_05_18_101021_create_sales_table', 1),
(27, '2025_05_18_101030_create_sale_details_table', 1),
(28, '2025_05_19_142047_create_vacations_table', 1),
(29, '2025_05_19_142139_create_holidays_table', 1),
(30, '2025_05_20_092842_create_item_details_table', 1),
(31, '2025_05_21_040712_create_salaries_table', 1),
(32, '2025_05_21_040738_create_salary_details_table', 1),
(33, '2025_05_21_044520_create_salary_payments_table', 1),
(34, '2025_06_23_084420_create_banners_table', 1),
(35, '2025_06_23_103800_create_website_settings_table', 1),
(36, '2025_06_24_092833_create_contacts_table', 1),
(37, '2025_06_24_093001_create_faqs_table', 1),
(38, '2025_06_25_184349_create_rattings_table', 1),
(39, '2025_06_29_184035_create_aboutus_pages_table', 1),
(40, '2025_07_22_154703_create_package_usages_summaries_table', 1),
(41, '2025_07_24_041942_create_notifications_table', 1),
(42, '2025_08_20_145616_create_working_processes_table', 1),
(43, '2025_08_21_081028_create_portfolios_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 3);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `notifications_details` text DEFAULT NULL,
  `read_status` enum('Yes','No') NOT NULL DEFAULT 'No',
  `booking_no` varchar(55) DEFAULT NULL,
  `notifications_date` varchar(55) DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `package_usages_summaries`
--

CREATE TABLE `package_usages_summaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `package_id` int(10) UNSIGNED DEFAULT NULL,
  `package_item_id` int(10) UNSIGNED DEFAULT NULL,
  `usages_qty` int(10) UNSIGNED DEFAULT NULL,
  `usages_date` varchar(55) DEFAULT NULL,
  `usages_time` varchar(55) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `account_type` varchar(25) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `payment_method_icon` varchar(255) DEFAULT NULL,
  `current_balance` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Enable','Disable') NOT NULL DEFAULT 'Enable',
  `use_in_website` enum('Yes','No') NOT NULL DEFAULT 'No',
  `is_deletable` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `sort_id` int(11) NOT NULL DEFAULT 0,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `name`, `account_type`, `description`, `payment_method_icon`, `current_balance`, `status`, `use_in_website`, `is_deletable`, `sort_id`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'Cash', 'Cash', NULL, 'payment-method/cash.png', 0.00, 'Enable', 'No', 'No', 1, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(2, 'Bank', 'Bank Account', NULL, 'payment-method/cash.png', 0.00, 'Enable', 'No', 'No', 2, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(3, 'Paypal', 'Paypal', NULL, 'payment-method/paypal.png', 0.00, 'Enable', 'No', 'No', 3, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(4, 'Stripe', 'Stripe', NULL, 'payment-method/stripe.png', 0.00, 'Enable', 'No', 'No', 4, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(5, 'Loyalty Point', 'Loyalty Point', NULL, 'payment-method/cash.png', 0.00, 'Enable', 'No', 'No', 5, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(6, 'Razorpay', 'Payment Gateway', NULL, 'payment-method/razorpay.png', 0.00, 'Enable', 'No', 'No', 6, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(7, 'Paytm', 'Payment Gateway', NULL, 'payment-method/paytm.png', 0.00, 'Enable', 'No', 'No', 7, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `group_name` varchar(255) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `group_name`, `name`, `guard_name`, `created_at`, `updated_at`) VALUES
(1, 'attendance-management', 'attendance-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(2, 'attendance-management', 'attendance-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(3, 'attendance-management', 'attendance-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(4, 'attendance-management', 'attendance-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(5, 'booking-management', 'booking-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(6, 'booking-management', 'booking-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(7, 'booking-management', 'booking-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(8, 'booking-management', 'booking-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(9, 'booking-management', 'booking-calendar', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(10, 'branch-management', 'branch-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(11, 'branch-management', 'branch-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(12, 'branch-management', 'branch-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(13, 'branch-management', 'branch-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(14, 'category-management', 'category-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(15, 'category-management', 'category-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(16, 'category-management', 'category-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(17, 'category-management', 'category-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(18, 'customer-management', 'customer-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(19, 'customer-management', 'customer-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(20, 'customer-management', 'customer-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(21, 'customer-management', 'customer-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(22, 'customer_receive-management', 'customer_receive-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(23, 'customer_receive-management', 'customer_receive-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(24, 'customer_receive-management', 'customer_receive-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(25, 'customer_receive-management', 'customer_receive-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(26, 'dashboard', 'dashboard', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(27, 'employee-management', 'employee-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(28, 'employee-management', 'employee-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(29, 'employee-management', 'employee-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(30, 'employee-management', 'employee-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(31, 'expense-management', 'expense-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(32, 'expense-management', 'expense-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(33, 'expense-management', 'expense-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(34, 'expense-management', 'expense-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(35, 'expense_category-management', 'expense_category-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(36, 'expense_category-management', 'expense_category-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(37, 'expense_category-management', 'expense_category-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(38, 'expense_category-management', 'expense_category-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(39, 'item-management', 'item-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(40, 'item-management', 'item-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(41, 'item-management', 'item-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(42, 'item-management', 'item-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(43, 'payment_method-management', 'payment_method-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(44, 'payment_method-management', 'payment_method-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(45, 'payment_method-management', 'payment_method-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(46, 'payment_method-management', 'payment_method-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(47, 'pos', 'pos', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(48, 'promotion-management', 'promotion-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(49, 'promotion-management', 'promotion-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(50, 'promotion-management', 'promotion-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(51, 'promotion-management', 'promotion-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(52, 'purchase-management', 'purchase-create', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(53, 'purchase-management', 'purchase-delete', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(54, 'purchase-management', 'purchase-edit', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(55, 'purchase-management', 'purchase-list', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(56, 'report-management', 'report-attendance', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(57, 'report-management', 'report-commission', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(58, 'report-management', 'report-expense', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(59, 'report-management', 'report-purchase', 'sanctum', '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(60, 'report-management', 'report-profit-loss', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(61, 'report-management', 'report-sale', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(62, 'report-management', 'report-salary', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(63, 'report-management', 'report-stock', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(64, 'report-management', 'report-alert', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(65, 'role-management', 'role-create', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(66, 'role-management', 'role-delete', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(67, 'role-management', 'role-edit', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(68, 'role-management', 'role-list', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(69, 'salary-management', 'salary-create', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(70, 'salary-management', 'salary-delete', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(71, 'salary-management', 'salary-edit', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(72, 'salary-management', 'salary-list', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(73, 'sales-management', 'sales-create', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(74, 'sales-management', 'sales-delete', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(75, 'sales-management', 'sales-edit', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(76, 'sales-management', 'sales-list', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(77, 'settings', 'settings-settings', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(78, 'settings', 'settings-tax', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(79, 'settings', 'settings-email', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(80, 'settings', 'settings-payment', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(81, 'settings', 'settings-white_label', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(82, 'settings', 'settings-vacation', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(83, 'settings', 'settings-holiday', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(84, 'stock-management', 'stock-stock', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(85, 'stock-management', 'stock-alert', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(86, 'supplier-management', 'supplier-create', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(87, 'supplier-management', 'supplier-delete', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(88, 'supplier-management', 'supplier-edit', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(89, 'supplier-management', 'supplier-list', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(90, 'supplier_payment-management', 'supplier_payment-create', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(91, 'supplier_payment-management', 'supplier_payment-delete', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(92, 'supplier_payment-management', 'supplier_payment-edit', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(93, 'supplier_payment-management', 'supplier_payment-list', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(94, 'unit-management', 'unit-create', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(95, 'unit-management', 'unit-delete', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(96, 'unit-management', 'unit-edit', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(97, 'unit-management', 'unit-list', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(98, 'website-management', 'website-settings', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(99, 'website-management', 'website-aboutus', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(100, 'website-management', 'website-banner', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(101, 'website-management', 'website-faq', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(102, 'website-management', 'website-workingprocess', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46'),
(103, 'website-management', 'website-portfolio', 'sanctum', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(2, 'App\\Models\\User', 1, 'Personal Access Token', 'c066123e9677adce11d0450a950aa56009e9c4dc47e4501a140d4560ce0c9d83', '[\"*\"]', '2025-08-25 12:41:48', NULL, '2025-08-25 08:11:16', '2025-08-25 12:41:48');

-- --------------------------------------------------------

--
-- Table structure for table `portfolios`
--

CREATE TABLE `portfolios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('Enabled','Disabled') DEFAULT 'Enabled',
  `position` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `promotions`
--

CREATE TABLE `promotions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `start_date` varchar(25) DEFAULT NULL,
  `end_date` varchar(25) DEFAULT NULL,
  `type` enum('Discount','Discount On Item','Free Item') NOT NULL DEFAULT 'Discount',
  `buy_item_id` int(10) UNSIGNED DEFAULT NULL,
  `buy_qty` int(11) DEFAULT NULL,
  `get_item_id` int(10) UNSIGNED DEFAULT NULL,
  `get_qty` int(11) DEFAULT NULL,
  `discount_item_id` int(10) UNSIGNED DEFAULT NULL,
  `discount` varchar(11) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(55) DEFAULT NULL,
  `supplier_invoice_no` varchar(55) DEFAULT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `date` varchar(25) DEFAULT NULL,
  `grand_total` varchar(11) DEFAULT NULL,
  `paid_amount` varchar(11) DEFAULT NULL,
  `due_amount` varchar(11) DEFAULT NULL,
  `attachment` varchar(255) DEFAULT NULL,
  `note` varchar(255) DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_details`
--

CREATE TABLE `purchase_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `purchase_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `quantity` varchar(11) DEFAULT NULL,
  `unit_price` varchar(11) DEFAULT NULL,
  `total_price` varchar(11) DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rattings`
--

CREATE TABLE `rattings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `rating` decimal(10,1) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `guard_name` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `company_id` bigint(20) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `guard_name`, `description`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'Super Admin', 'sanctum', NULL, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1);

-- --------------------------------------------------------

--
-- Table structure for table `salaries`
--

CREATE TABLE `salaries` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `generated_date` varchar(25) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `month` int(11) DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `total_amount` decimal(10,3) NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_details`
--

CREATE TABLE `salary_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `salary_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `salary_amount` decimal(10,3) NOT NULL,
  `overtime_rate` decimal(10,3) NOT NULL,
  `overtime_hour` decimal(10,3) NOT NULL,
  `additional_amount` decimal(10,3) NOT NULL,
  `deduction_amount` decimal(10,3) NOT NULL,
  `absent_day` decimal(10,3) NOT NULL,
  `absent_day_amount` decimal(10,3) NOT NULL,
  `advance_taken` decimal(10,3) NOT NULL,
  `net_salary` decimal(10,3) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_payments`
--

CREATE TABLE `salary_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `salary_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_method_id` decimal(10,3) NOT NULL,
  `amount` decimal(10,3) NOT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(25) DEFAULT NULL,
  `order_date` varchar(25) DEFAULT NULL,
  `order_update_date` varchar(25) DEFAULT NULL,
  `order_from` enum('Website','POS') DEFAULT NULL,
  `order_status` enum('Pending','Confirmed','Cancelled','Completed') DEFAULT NULL,
  `subtotal_without_tax_discount` decimal(10,3) DEFAULT NULL,
  `grandtotal_with_tax_discount` decimal(10,3) DEFAULT NULL,
  `discount` decimal(10,3) DEFAULT NULL,
  `total_tax` decimal(10,3) DEFAULT NULL,
  `total_payable` decimal(10,3) DEFAULT NULL,
  `total_paid` decimal(10,3) DEFAULT NULL,
  `total_due` decimal(10,3) DEFAULT NULL,
  `customer_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `reference_no`, `order_date`, `order_update_date`, `order_from`, `order_status`, `subtotal_without_tax_discount`, `grandtotal_with_tax_discount`, `discount`, `total_tax`, `total_payable`, `total_paid`, `total_due`, `customer_id`, `payment_method_id`, `branch_id`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'S-202508250001', '2025-08-25', NULL, 'POS', 'Completed', 500.000, 550.000, 0.000, 50.000, 550.000, 550.000, 0.000, 1, 1, 1, 1, 1, 'Live', '2025-08-25 12:31:23', '2025-08-25 12:31:23'),
(2, 'S-202508250002', '2025-08-25', NULL, 'POS', 'Completed', 500.000, 550.000, 0.000, 50.000, 550.000, 550.000, 0.000, 1, 1, 1, 1, 1, 'Live', '2025-08-25 12:33:54', '2025-08-25 12:33:54'),
(3, 'S-202508250003', '2025-08-25', NULL, 'POS', 'Completed', 1000.000, 1100.000, 0.000, 100.000, 1100.000, 1100.000, 0.000, 1, 1, 1, 1, 1, 'Live', '2025-08-25 12:35:32', '2025-08-25 12:35:32'),
(4, 'S-202508250004', '2025-08-25', NULL, 'POS', 'Completed', 1000.000, 1100.000, 0.000, 100.000, 1100.000, 1100.000, 0.000, 1, 1, 1, 1, 1, 'Live', '2025-08-25 12:37:02', '2025-08-25 12:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `sale_details`
--

CREATE TABLE `sale_details` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `sale_id` int(10) UNSIGNED DEFAULT NULL,
  `item_id` int(10) UNSIGNED DEFAULT NULL,
  `employee_id` int(10) UNSIGNED DEFAULT NULL,
  `unit_price` decimal(10,3) DEFAULT NULL,
  `quantity` decimal(10,3) DEFAULT NULL,
  `subtotal` decimal(10,3) DEFAULT NULL,
  `total_tax` decimal(10,3) DEFAULT NULL,
  `total_payable` decimal(10,3) DEFAULT NULL,
  `is_free` enum('Yes','No') NOT NULL DEFAULT 'No',
  `promotion_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sale_details`
--

INSERT INTO `sale_details` (`id`, `sale_id`, `item_id`, `employee_id`, `unit_price`, `quantity`, `subtotal`, `total_tax`, `total_payable`, `is_free`, `promotion_id`, `branch_id`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 1, 2, NULL, 500.000, 1.000, 500.000, 50.000, 550.000, 'No', NULL, 1, 1, 1, 'Live', '2025-08-25 12:31:23', '2025-08-25 12:31:23'),
(2, 2, 1, NULL, 500.000, 1.000, 500.000, 50.000, 550.000, 'No', NULL, 1, 1, 1, 'Live', '2025-08-25 12:33:54', '2025-08-25 12:33:54'),
(3, 3, 1, NULL, 500.000, 1.000, 500.000, 50.000, 550.000, 'No', NULL, 1, 1, 1, 'Live', '2025-08-25 12:35:32', '2025-08-25 12:35:32'),
(4, 3, 2, NULL, 500.000, 1.000, 500.000, 50.000, 550.000, 'No', NULL, 1, 1, 1, 'Live', '2025-08-25 12:35:32', '2025-08-25 12:35:32'),
(5, 4, 1, NULL, 500.000, 1.000, 500.000, 50.000, 550.000, 'No', NULL, 1, 1, 1, 'Live', '2025-08-25 12:37:02', '2025-08-25 12:37:02'),
(6, 4, 2, NULL, 500.000, 1.000, 500.000, 50.000, 550.000, 'No', NULL, 1, 1, 1, 'Live', '2025-08-25 12:37:02', '2025-08-25 12:37:02');

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('hLsOxVk15ss2yPM8LKrFON4w24d7LaYEFLCFmgYW', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/139.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiYTROeGhPUURlTVVvc3hJeUxSSVRnaGc4N1NMVk5FV3MwV2VSd3NSRCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzI6Imh0dHA6Ly9sb2NhbGhvc3Qvc2Fsb25fYnVkZHkvcG9zIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1756147302);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `key` varchar(255) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `contact_person` varchar(55) DEFAULT NULL,
  `phone` varchar(55) DEFAULT NULL,
  `email` varchar(55) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_payments`
--

CREATE TABLE `supplier_payments` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `reference_no` varchar(55) DEFAULT NULL,
  `date` varchar(25) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `note` varchar(55) DEFAULT NULL,
  `supplier_id` int(10) UNSIGNED DEFAULT NULL,
  `payment_method_id` int(10) UNSIGNED DEFAULT NULL,
  `branch_id` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `timezones`
--

CREATE TABLE `timezones` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `country_code` varchar(10) DEFAULT NULL,
  `zone_name` varchar(55) DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `timezones`
--

INSERT INTO `timezones` (`id`, `country_code`, `zone_name`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'AD', 'Europe/Andorra', 'Live', NULL, NULL),
(2, 'AE', 'Asia/Dubai', 'Live', NULL, NULL),
(3, 'AF', 'Asia/Kabul', 'Live', NULL, NULL),
(4, 'AG', 'America/Antigua', 'Live', NULL, NULL),
(5, 'AI', 'America/Anguilla', 'Live', NULL, NULL),
(6, 'AL', 'Europe/Tirane', 'Live', NULL, NULL),
(7, 'AM', 'Asia/Yerevan', 'Live', NULL, NULL),
(8, 'AO', 'Africa/Luanda', 'Live', NULL, NULL),
(9, 'AQ', 'Antarctica/McMurdo', 'Live', NULL, NULL),
(10, 'AQ', 'Antarctica/Casey', 'Live', NULL, NULL),
(11, 'AQ', 'Antarctica/Davis', 'Live', NULL, NULL),
(12, 'AQ', 'Antarctica/DumontDUrville', 'Live', NULL, NULL),
(13, 'AQ', 'Antarctica/Mawson', 'Live', NULL, NULL),
(14, 'AQ', 'Antarctica/Palmer', 'Live', NULL, NULL),
(15, 'AQ', 'Antarctica/Rothera', 'Live', NULL, NULL),
(16, 'AQ', 'Antarctica/Syowa', 'Live', NULL, NULL),
(17, 'AQ', 'Antarctica/Troll', 'Live', NULL, NULL),
(18, 'AQ', 'Antarctica/Vostok', 'Live', NULL, NULL),
(19, 'AR', 'America/Argentina/Buenos_Aires', 'Live', NULL, NULL),
(20, 'AR', 'America/Argentina/Cordoba', 'Live', NULL, NULL),
(21, 'AR', 'America/Argentina/Salta', 'Live', NULL, NULL),
(22, 'AR', 'America/Argentina/Jujuy', 'Live', NULL, NULL),
(23, 'AR', 'America/Argentina/Tucuman', 'Live', NULL, NULL),
(24, 'AR', 'America/Argentina/Catamarca', 'Live', NULL, NULL),
(25, 'AR', 'America/Argentina/La_Rioja', 'Live', NULL, NULL),
(26, 'AR', 'America/Argentina/San_Juan', 'Live', NULL, NULL),
(27, 'AR', 'America/Argentina/Mendoza', 'Live', NULL, NULL),
(28, 'AR', 'America/Argentina/San_Luis', 'Live', NULL, NULL),
(29, 'AR', 'America/Argentina/Rio_Gallegos', 'Live', NULL, NULL),
(30, 'AR', 'America/Argentina/Ushuaia', 'Live', NULL, NULL),
(31, 'AS', 'Pacific/Pago_Pago', 'Live', NULL, NULL),
(32, 'AT', 'Europe/Vienna', 'Live', NULL, NULL),
(33, 'AU', 'Australia/Lord_Howe', 'Live', NULL, NULL),
(34, 'AU', 'Antarctica/Macquarie', 'Live', NULL, NULL),
(35, 'AU', 'Australia/Hobart', 'Live', NULL, NULL),
(36, 'AU', 'Australia/Currie', 'Live', NULL, NULL),
(37, 'AU', 'Australia/Melbourne', 'Live', NULL, NULL),
(38, 'AU', 'Australia/Sydney', 'Live', NULL, NULL),
(39, 'AU', 'Australia/Broken_Hill', 'Live', NULL, NULL),
(40, 'AU', 'Australia/Brisbane', 'Live', NULL, NULL),
(41, 'AU', 'Australia/Lindeman', 'Live', NULL, NULL),
(42, 'AU', 'Australia/Adelaide', 'Live', NULL, NULL),
(43, 'AU', 'Australia/Darwin', 'Live', NULL, NULL),
(44, 'AU', 'Australia/Perth', 'Live', NULL, NULL),
(45, 'AU', 'Australia/Eucla', 'Live', NULL, NULL),
(46, 'AW', 'America/Aruba', 'Live', NULL, NULL),
(47, 'AX', 'Europe/Mariehamn', 'Live', NULL, NULL),
(48, 'AZ', 'Asia/Baku', 'Live', NULL, NULL),
(49, 'BA', 'Europe/Sarajevo', 'Live', NULL, NULL),
(50, 'BB', 'America/Barbados', 'Live', NULL, NULL),
(51, 'BD', 'Asia/Dhaka', 'Live', NULL, NULL),
(52, 'BE', 'Europe/Brussels', 'Live', NULL, NULL),
(53, 'BF', 'Africa/Ouagadougou', 'Live', NULL, NULL),
(54, 'BG', 'Europe/Sofia', 'Live', NULL, NULL),
(55, 'BH', 'Asia/Bahrain', 'Live', NULL, NULL),
(56, 'BI', 'Africa/Bujumbura', 'Live', NULL, NULL),
(57, 'BJ', 'Africa/Porto-Novo', 'Live', NULL, NULL),
(58, 'BL', 'America/St_Barthelemy', 'Live', NULL, NULL),
(59, 'BM', 'Atlantic/Bermuda', 'Live', NULL, NULL),
(60, 'BN', 'Asia/Brunei', 'Live', NULL, NULL),
(61, 'BO', 'America/La_Paz', 'Live', NULL, NULL),
(62, 'BQ', 'America/Kralendijk', 'Live', NULL, NULL),
(63, 'BR', 'America/Noronha', 'Live', NULL, NULL),
(64, 'BR', 'America/Belem', 'Live', NULL, NULL),
(65, 'BR', 'America/Fortaleza', 'Live', NULL, NULL),
(66, 'BR', 'America/Recife', 'Live', NULL, NULL),
(67, 'BR', 'America/Araguaina', 'Live', NULL, NULL),
(68, 'BR', 'America/Maceio', 'Live', NULL, NULL),
(69, 'BR', 'America/Bahia', 'Live', NULL, NULL),
(70, 'BR', 'America/Sao_Paulo', 'Live', NULL, NULL),
(71, 'BR', 'America/Campo_Grande', 'Live', NULL, NULL),
(72, 'BR', 'America/Cuiaba', 'Live', NULL, NULL),
(73, 'BR', 'America/Santarem', 'Live', NULL, NULL),
(74, 'BR', 'America/Porto_Velho', 'Live', NULL, NULL),
(75, 'BR', 'America/Boa_Vista', 'Live', NULL, NULL),
(76, 'BR', 'America/Manaus', 'Live', NULL, NULL),
(77, 'BR', 'America/Eirunepe', 'Live', NULL, NULL),
(78, 'BR', 'America/Rio_Branco', 'Live', NULL, NULL),
(79, 'BS', 'America/Nassau', 'Live', NULL, NULL),
(80, 'BT', 'Asia/Thimphu', 'Live', NULL, NULL),
(81, 'BW', 'Africa/Gaborone', 'Live', NULL, NULL),
(82, 'BY', 'Europe/Minsk', 'Live', NULL, NULL),
(83, 'BZ', 'America/Belize', 'Live', NULL, NULL),
(84, 'CA', 'America/St_Johns', 'Live', NULL, NULL),
(85, 'CA', 'America/Halifax', 'Live', NULL, NULL),
(86, 'CA', 'America/Glace_Bay', 'Live', NULL, NULL),
(87, 'CA', 'America/Moncton', 'Live', NULL, NULL),
(88, 'CA', 'America/Goose_Bay', 'Live', NULL, NULL),
(89, 'CA', 'America/Blanc-Sablon', 'Live', NULL, NULL),
(90, 'CA', 'America/Toronto', 'Live', NULL, NULL),
(91, 'CA', 'America/Nipigon', 'Live', NULL, NULL),
(92, 'CA', 'America/Thunder_Bay', 'Live', NULL, NULL),
(93, 'CA', 'America/Iqaluit', 'Live', NULL, NULL),
(94, 'CA', 'America/Pangnirtung', 'Live', NULL, NULL),
(95, 'CA', 'America/Atikokan', 'Live', NULL, NULL),
(96, 'CA', 'America/Winnipeg', 'Live', NULL, NULL),
(97, 'CA', 'America/Rainy_River', 'Live', NULL, NULL),
(98, 'CA', 'America/Resolute', 'Live', NULL, NULL),
(99, 'CA', 'America/Rankin_Inlet', 'Live', NULL, NULL),
(100, 'CA', 'America/Regina', 'Live', NULL, NULL),
(101, 'CA', 'America/Swift_Current', 'Live', NULL, NULL),
(102, 'CA', 'America/Edmonton', 'Live', NULL, NULL),
(103, 'CA', 'America/Cambridge_Bay', 'Live', NULL, NULL),
(104, 'CA', 'America/Yellowknife', 'Live', NULL, NULL),
(105, 'CA', 'America/Inuvik', 'Live', NULL, NULL),
(106, 'CA', 'America/Creston', 'Live', NULL, NULL),
(107, 'CA', 'America/Dawson_Creek', 'Live', NULL, NULL),
(108, 'CA', 'America/Fort_Nelson', 'Live', NULL, NULL),
(109, 'CA', 'America/Vancouver', 'Live', NULL, NULL),
(110, 'CA', 'America/Whitehorse', 'Live', NULL, NULL),
(111, 'CA', 'America/Dawson', 'Live', NULL, NULL),
(112, 'CC', 'Indian/Cocos', 'Live', NULL, NULL),
(113, 'CD', 'Africa/Kinshasa', 'Live', NULL, NULL),
(114, 'CD', 'Africa/Lubumbashi', 'Live', NULL, NULL),
(115, 'CF', 'Africa/Bangui', 'Live', NULL, NULL),
(116, 'CG', 'Africa/Brazzaville', 'Live', NULL, NULL),
(117, 'CH', 'Europe/Zurich', 'Live', NULL, NULL),
(118, 'CI', 'Africa/Abidjan', 'Live', NULL, NULL),
(119, 'CK', 'Pacific/Rarotonga', 'Live', NULL, NULL),
(120, 'CL', 'America/Santiago', 'Live', NULL, NULL),
(121, 'CL', 'America/Punta_Arenas', 'Live', NULL, NULL),
(122, 'CL', 'Pacific/Easter', 'Live', NULL, NULL),
(123, 'CM', 'Africa/Douala', 'Live', NULL, NULL),
(124, 'CN', 'Asia/Shanghai', 'Live', NULL, NULL),
(125, 'CN', 'Asia/Urumqi', 'Live', NULL, NULL),
(126, 'CO', 'America/Bogota', 'Live', NULL, NULL),
(127, 'CR', 'America/Costa_Rica', 'Live', NULL, NULL),
(128, 'CU', 'America/Havana', 'Live', NULL, NULL),
(129, 'CV', 'Atlantic/Cape_Verde', 'Live', NULL, NULL),
(130, 'CW', 'America/Curacao', 'Live', NULL, NULL),
(131, 'CX', 'Indian/Christmas', 'Live', NULL, NULL),
(132, 'CY', 'Asia/Nicosia', 'Live', NULL, NULL),
(133, 'CY', 'Asia/Famagusta', 'Live', NULL, NULL),
(134, 'CZ', 'Europe/Prague', 'Live', NULL, NULL),
(135, 'DE', 'Europe/Berlin', 'Live', NULL, NULL),
(136, 'DE', 'Europe/Busingen', 'Live', NULL, NULL),
(137, 'DJ', 'Africa/Djibouti', 'Live', NULL, NULL),
(138, 'DK', 'Europe/Copenhagen', 'Live', NULL, NULL),
(139, 'DM', 'America/Dominica', 'Live', NULL, NULL),
(140, 'DO', 'America/Santo_Domingo', 'Live', NULL, NULL),
(141, 'DZ', 'Africa/Algiers', 'Live', NULL, NULL),
(142, 'EC', 'America/Guayaquil', 'Live', NULL, NULL),
(143, 'EC', 'Pacific/Galapagos', 'Live', NULL, NULL),
(144, 'EE', 'Europe/Tallinn', 'Live', NULL, NULL),
(145, 'EG', 'Africa/Cairo', 'Live', NULL, NULL),
(146, 'EH', 'Africa/El_Aaiun', 'Live', NULL, NULL),
(147, 'ER', 'Africa/Asmara', 'Live', NULL, NULL),
(148, 'ES', 'Europe/Madrid', 'Live', NULL, NULL),
(149, 'ES', 'Africa/Ceuta', 'Live', NULL, NULL),
(150, 'ES', 'Atlantic/Canary', 'Live', NULL, NULL),
(151, 'ET', 'Africa/Addis_Ababa', 'Live', NULL, NULL),
(152, 'FI', 'Europe/Helsinki', 'Live', NULL, NULL),
(153, 'FJ', 'Pacific/Fiji', 'Live', NULL, NULL),
(154, 'FK', 'Atlantic/Stanley', 'Live', NULL, NULL),
(155, 'FM', 'Pacific/Chuuk', 'Live', NULL, NULL),
(156, 'FM', 'Pacific/Pohnpei', 'Live', NULL, NULL),
(157, 'FM', 'Pacific/Kosrae', 'Live', NULL, NULL),
(158, 'FO', 'Atlantic/Faroe', 'Live', NULL, NULL),
(159, 'FR', 'Europe/Paris', 'Live', NULL, NULL),
(160, 'GA', 'Africa/Libreville', 'Live', NULL, NULL),
(161, 'GB', 'Europe/London', 'Live', NULL, NULL),
(162, 'GD', 'America/Grenada', 'Live', NULL, NULL),
(163, 'GE', 'Asia/Tbilisi', 'Live', NULL, NULL),
(164, 'GF', 'America/Cayenne', 'Live', NULL, NULL),
(165, 'GG', 'Europe/Guernsey', 'Live', NULL, NULL),
(166, 'GH', 'Africa/Accra', 'Live', NULL, NULL),
(167, 'GI', 'Europe/Gibraltar', 'Live', NULL, NULL),
(168, 'GL', 'America/Godthab', 'Live', NULL, NULL),
(169, 'GL', 'America/Danmarkshavn', 'Live', NULL, NULL),
(170, 'GL', 'America/Scoresbysund', 'Live', NULL, NULL),
(171, 'GL', 'America/Thule', 'Live', NULL, NULL),
(172, 'GM', 'Africa/Banjul', 'Live', NULL, NULL),
(173, 'GN', 'Africa/Conakry', 'Live', NULL, NULL),
(174, 'GP', 'America/Guadeloupe', 'Live', NULL, NULL),
(175, 'GQ', 'Africa/Malabo', 'Live', NULL, NULL),
(176, 'GR', 'Europe/Athens', 'Live', NULL, NULL),
(177, 'GS', 'Atlantic/South_Georgia', 'Live', NULL, NULL),
(178, 'GT', 'America/Guatemala', 'Live', NULL, NULL),
(179, 'GU', 'Pacific/Guam', 'Live', NULL, NULL),
(180, 'GW', 'Africa/Bissau', 'Live', NULL, NULL),
(181, 'GY', 'America/Guyana', 'Live', NULL, NULL),
(182, 'HK', 'Asia/Hong_Kong', 'Live', NULL, NULL),
(183, 'HN', 'America/Tegucigalpa', 'Live', NULL, NULL),
(184, 'HR', 'Europe/Zagreb', 'Live', NULL, NULL),
(185, 'HT', 'America/Port-au-Prince', 'Live', NULL, NULL),
(186, 'HU', 'Europe/Budapest', 'Live', NULL, NULL),
(187, 'ID', 'Asia/Jakarta', 'Live', NULL, NULL),
(188, 'ID', 'Asia/Pontianak', 'Live', NULL, NULL),
(189, 'ID', 'Asia/Makassar', 'Live', NULL, NULL),
(190, 'ID', 'Asia/Jayapura', 'Live', NULL, NULL),
(191, 'IE', 'Europe/Dublin', 'Live', NULL, NULL),
(192, 'IL', 'Asia/Jerusalem', 'Live', NULL, NULL),
(193, 'IM', 'Europe/Isle_of_Man', 'Live', NULL, NULL),
(194, 'IN', 'Asia/Kolkata', 'Live', NULL, NULL),
(195, 'IO', 'Indian/Chagos', 'Live', NULL, NULL),
(196, 'IQ', 'Asia/Baghdad', 'Live', NULL, NULL),
(197, 'IR', 'Asia/Tehran', 'Live', NULL, NULL),
(198, 'IS', 'Atlantic/Reykjavik', 'Live', NULL, NULL),
(199, 'IT', 'Europe/Rome', 'Live', NULL, NULL),
(200, 'JE', 'Europe/Jersey', 'Live', NULL, NULL),
(201, 'JM', 'America/Jamaica', 'Live', NULL, NULL),
(202, 'JO', 'Asia/Amman', 'Live', NULL, NULL),
(203, 'JP', 'Asia/Tokyo', 'Live', NULL, NULL),
(204, 'KE', 'Africa/Nairobi', 'Live', NULL, NULL),
(205, 'KG', 'Asia/Bishkek', 'Live', NULL, NULL),
(206, 'KH', 'Asia/Phnom_Penh', 'Live', NULL, NULL),
(207, 'KI', 'Pacific/Tarawa', 'Live', NULL, NULL),
(208, 'KI', 'Pacific/Enderbury', 'Live', NULL, NULL),
(209, 'KI', 'Pacific/Kiritimati', 'Live', NULL, NULL),
(210, 'KM', 'Indian/Comoro', 'Live', NULL, NULL),
(211, 'KN', 'America/St_Kitts', 'Live', NULL, NULL),
(212, 'KP', 'Asia/Pyongyang', 'Live', NULL, NULL),
(213, 'KR', 'Asia/Seoul', 'Live', NULL, NULL),
(214, 'KW', 'Asia/Kuwait', 'Live', NULL, NULL),
(215, 'KY', 'America/Cayman', 'Live', NULL, NULL),
(216, 'KZ', 'Asia/Almaty', 'Live', NULL, NULL),
(217, 'KZ', 'Asia/Qyzylorda', 'Live', NULL, NULL),
(218, 'KZ', 'Asia/Aqtobe', 'Live', NULL, NULL),
(219, 'KZ', 'Asia/Aqtau', 'Live', NULL, NULL),
(220, 'KZ', 'Asia/Atyrau', 'Live', NULL, NULL),
(221, 'KZ', 'Asia/Oral', 'Live', NULL, NULL),
(222, 'LA', 'Asia/Vientiane', 'Live', NULL, NULL),
(223, 'LB', 'Asia/Beirut', 'Live', NULL, NULL),
(224, 'LC', 'America/St_Lucia', 'Live', NULL, NULL),
(225, 'LI', 'Europe/Vaduz', 'Live', NULL, NULL),
(226, 'LK', 'Asia/Colombo', 'Live', NULL, NULL),
(227, 'LR', 'Africa/Monrovia', 'Live', NULL, NULL),
(228, 'LS', 'Africa/Maseru', 'Live', NULL, NULL),
(229, 'LT', 'Europe/Vilnius', 'Live', NULL, NULL),
(230, 'LU', 'Europe/Luxembourg', 'Live', NULL, NULL),
(231, 'LV', 'Europe/Riga', 'Live', NULL, NULL),
(232, 'LY', 'Africa/Tripoli', 'Live', NULL, NULL),
(233, 'MA', 'Africa/Casablanca', 'Live', NULL, NULL),
(234, 'MC', 'Europe/Monaco', 'Live', NULL, NULL),
(235, 'MD', 'Europe/Chisinau', 'Live', NULL, NULL),
(236, 'ME', 'Europe/Podgorica', 'Live', NULL, NULL),
(237, 'MF', 'America/Marigot', 'Live', NULL, NULL),
(238, 'MG', 'Indian/Antananarivo', 'Live', NULL, NULL),
(239, 'MH', 'Pacific/Majuro', 'Live', NULL, NULL),
(240, 'MH', 'Pacific/Kwajalein', 'Live', NULL, NULL),
(241, 'MK', 'Europe/Skopje', 'Live', NULL, NULL),
(242, 'ML', 'Africa/Bamako', 'Live', NULL, NULL),
(243, 'MM', 'Asia/Yangon', 'Live', NULL, NULL),
(244, 'MN', 'Asia/Ulaanbaatar', 'Live', NULL, NULL),
(245, 'MN', 'Asia/Hovd', 'Live', NULL, NULL),
(246, 'MN', 'Asia/Choibalsan', 'Live', NULL, NULL),
(247, 'MO', 'Asia/Macau', 'Live', NULL, NULL),
(248, 'MP', 'Pacific/Saipan', 'Live', NULL, NULL),
(249, 'MQ', 'America/Martinique', 'Live', NULL, NULL),
(250, 'MR', 'Africa/Nouakchott', 'Live', NULL, NULL),
(251, 'MS', 'America/Montserrat', 'Live', NULL, NULL),
(252, 'MT', 'Europe/Malta', 'Live', NULL, NULL),
(253, 'MU', 'Indian/Mauritius', 'Live', NULL, NULL),
(254, 'MV', 'Indian/Maldives', 'Live', NULL, NULL),
(255, 'MW', 'Africa/Blantyre', 'Live', NULL, NULL),
(256, 'MX', 'America/Mexico_City', 'Live', NULL, NULL),
(257, 'MX', 'America/Cancun', 'Live', NULL, NULL),
(258, 'MX', 'America/Merida', 'Live', NULL, NULL),
(259, 'MX', 'America/Monterrey', 'Live', NULL, NULL),
(260, 'MX', 'America/Matamoros', 'Live', NULL, NULL),
(261, 'MX', 'America/Mazatlan', 'Live', NULL, NULL),
(262, 'MX', 'America/Chihuahua', 'Live', NULL, NULL),
(263, 'MX', 'America/Ojinaga', 'Live', NULL, NULL),
(264, 'MX', 'America/Hermosillo', 'Live', NULL, NULL),
(265, 'MX', 'America/Tijuana', 'Live', NULL, NULL),
(266, 'MX', 'America/Bahia_Banderas', 'Live', NULL, NULL),
(267, 'MY', 'Asia/Kuala_Lumpur', 'Live', NULL, NULL),
(268, 'MY', 'Asia/Kuching', 'Live', NULL, NULL),
(269, 'MZ', 'Africa/Maputo', 'Live', NULL, NULL),
(270, 'NA', 'Africa/Windhoek', 'Live', NULL, NULL),
(271, 'NC', 'Pacific/Noumea', 'Live', NULL, NULL),
(272, 'NE', 'Africa/Niamey', 'Live', NULL, NULL),
(273, 'NF', 'Pacific/Norfolk', 'Live', NULL, NULL),
(274, 'NG', 'Africa/Lagos', 'Live', NULL, NULL),
(275, 'NI', 'America/Managua', 'Live', NULL, NULL),
(276, 'NL', 'Europe/Amsterdam', 'Live', NULL, NULL),
(277, 'NO', 'Europe/Oslo', 'Live', NULL, NULL),
(278, 'NP', 'Asia/Kathmandu', 'Live', NULL, NULL),
(279, 'NR', 'Pacific/Nauru', 'Live', NULL, NULL),
(280, 'NU', 'Pacific/Niue', 'Live', NULL, NULL),
(281, 'NZ', 'Pacific/Auckland', 'Live', NULL, NULL),
(282, 'NZ', 'Pacific/Chatham', 'Live', NULL, NULL),
(283, 'OM', 'Asia/Muscat', 'Live', NULL, NULL),
(284, 'PA', 'America/Panama', 'Live', NULL, NULL),
(285, 'PE', 'America/Lima', 'Live', NULL, NULL),
(286, 'PF', 'Pacific/Tahiti', 'Live', NULL, NULL),
(287, 'PF', 'Pacific/Marquesas', 'Live', NULL, NULL),
(288, 'PF', 'Pacific/Gambier', 'Live', NULL, NULL),
(289, 'PG', 'Pacific/Port_Moresby', 'Live', NULL, NULL),
(290, 'PG', 'Pacific/Bougainville', 'Live', NULL, NULL),
(291, 'PH', 'Asia/Manila', 'Live', NULL, NULL),
(292, 'PK', 'Asia/Karachi', 'Live', NULL, NULL),
(293, 'PL', 'Europe/Warsaw', 'Live', NULL, NULL),
(294, 'PM', 'America/Miquelon', 'Live', NULL, NULL),
(295, 'PN', 'Pacific/Pitcairn', 'Live', NULL, NULL),
(296, 'PR', 'America/Puerto_Rico', 'Live', NULL, NULL),
(297, 'PS', 'Asia/Gaza', 'Live', NULL, NULL),
(298, 'PS', 'Asia/Hebron', 'Live', NULL, NULL),
(299, 'PT', 'Europe/Lisbon', 'Live', NULL, NULL),
(300, 'PT', 'Atlantic/Madeira', 'Live', NULL, NULL),
(301, 'PT', 'Atlantic/Azores', 'Live', NULL, NULL),
(302, 'PW', 'Pacific/Palau', 'Live', NULL, NULL),
(303, 'PY', 'America/Asuncion', 'Live', NULL, NULL),
(304, 'QA', 'Asia/Qatar', 'Live', NULL, NULL),
(305, 'RE', 'Indian/Reunion', 'Live', NULL, NULL),
(306, 'RO', 'Europe/Bucharest', 'Live', NULL, NULL),
(307, 'RS', 'Europe/Belgrade', 'Live', NULL, NULL),
(308, 'RU', 'Europe/Kaliningrad', 'Live', NULL, NULL),
(309, 'RU', 'Europe/Moscow', 'Live', NULL, NULL),
(310, 'RU', 'Europe/Simferopol', 'Live', NULL, NULL),
(311, 'RU', 'Europe/Volgograd', 'Live', NULL, NULL),
(312, 'RU', 'Europe/Kirov', 'Live', NULL, NULL),
(313, 'RU', 'Europe/Astrakhan', 'Live', NULL, NULL),
(314, 'RU', 'Europe/Saratov', 'Live', NULL, NULL),
(315, 'RU', 'Europe/Ulyanovsk', 'Live', NULL, NULL),
(316, 'RU', 'Europe/Samara', 'Live', NULL, NULL),
(317, 'RU', 'Asia/Yekaterinburg', 'Live', NULL, NULL),
(318, 'RU', 'Asia/Omsk', 'Live', NULL, NULL),
(319, 'RU', 'Asia/Novosibirsk', 'Live', NULL, NULL),
(320, 'RU', 'Asia/Barnaul', 'Live', NULL, NULL),
(321, 'RU', 'Asia/Tomsk', 'Live', NULL, NULL),
(322, 'RU', 'Asia/Novokuznetsk', 'Live', NULL, NULL),
(323, 'RU', 'Asia/Krasnoyarsk', 'Live', NULL, NULL),
(324, 'RU', 'Asia/Irkutsk', 'Live', NULL, NULL),
(325, 'RU', 'Asia/Chita', 'Live', NULL, NULL),
(326, 'RU', 'Asia/Yakutsk', 'Live', NULL, NULL),
(327, 'RU', 'Asia/Khandyga', 'Live', NULL, NULL),
(328, 'RU', 'Asia/Vladivostok', 'Live', NULL, NULL),
(329, 'RU', 'Asia/Ust-Nera', 'Live', NULL, NULL),
(330, 'RU', 'Asia/Magadan', 'Live', NULL, NULL),
(331, 'RU', 'Asia/Sakhalin', 'Live', NULL, NULL),
(332, 'RU', 'Asia/Srednekolymsk', 'Live', NULL, NULL),
(333, 'RU', 'Asia/Kamchatka', 'Live', NULL, NULL),
(334, 'RU', 'Asia/Anadyr', 'Live', NULL, NULL),
(335, 'RW', 'Africa/Kigali', 'Live', NULL, NULL),
(336, 'SA', 'Asia/Riyadh', 'Live', NULL, NULL),
(337, 'SB', 'Pacific/Guadalcanal', 'Live', NULL, NULL),
(338, 'SC', 'Indian/Mahe', 'Live', NULL, NULL),
(339, 'SD', 'Africa/Khartoum', 'Live', NULL, NULL),
(340, 'SE', 'Europe/Stockholm', 'Live', NULL, NULL),
(341, 'SG', 'Asia/Singapore', 'Live', NULL, NULL),
(342, 'SH', 'Atlantic/St_Helena', 'Live', NULL, NULL),
(343, 'SI', 'Europe/Ljubljana', 'Live', NULL, NULL),
(344, 'SJ', 'Arctic/Longyearbyen', 'Live', NULL, NULL),
(345, 'SK', 'Europe/Bratislava', 'Live', NULL, NULL),
(346, 'SL', 'Africa/Freetown', 'Live', NULL, NULL),
(347, 'SM', 'Europe/San_Marino', 'Live', NULL, NULL),
(348, 'SN', 'Africa/Dakar', 'Live', NULL, NULL),
(349, 'SO', 'Africa/Mogadishu', 'Live', NULL, NULL),
(350, 'SR', 'America/Paramaribo', 'Live', NULL, NULL),
(351, 'SS', 'Africa/Juba', 'Live', NULL, NULL),
(352, 'ST', 'Africa/Sao_Tome', 'Live', NULL, NULL),
(353, 'SV', 'America/El_Salvador', 'Live', NULL, NULL),
(354, 'SX', 'America/Lower_Princes', 'Live', NULL, NULL),
(355, 'SY', 'Asia/Damascus', 'Live', NULL, NULL),
(356, 'SZ', 'Africa/Mbabane', 'Live', NULL, NULL),
(357, 'TC', 'America/Grand_Turk', 'Live', NULL, NULL),
(358, 'TD', 'Africa/Ndjamena', 'Live', NULL, NULL),
(359, 'TF', 'Indian/Kerguelen', 'Live', NULL, NULL),
(360, 'TG', 'Africa/Lome', 'Live', NULL, NULL),
(361, 'TH', 'Asia/Bangkok', 'Live', NULL, NULL),
(362, 'TJ', 'Asia/Dushanbe', 'Live', NULL, NULL),
(363, 'TK', 'Pacific/Fakaofo', 'Live', NULL, NULL),
(364, 'TL', 'Asia/Dili', 'Live', NULL, NULL),
(365, 'TM', 'Asia/Ashgabat', 'Live', NULL, NULL),
(366, 'TN', 'Africa/Tunis', 'Live', NULL, NULL),
(367, 'TO', 'Pacific/Tongatapu', 'Live', NULL, NULL),
(368, 'TR', 'Europe/Istanbul', 'Live', NULL, NULL),
(369, 'TT', 'America/Port_of_Spain', 'Live', NULL, NULL),
(370, 'TV', 'Pacific/Funafuti', 'Live', NULL, NULL),
(371, 'TW', 'Asia/Taipei', 'Live', NULL, NULL),
(372, 'TZ', 'Africa/Dar_es_Salaam', 'Live', NULL, NULL),
(373, 'UA', 'Europe/Kiev', 'Live', NULL, NULL),
(374, 'UA', 'Europe/Uzhgorod', 'Live', NULL, NULL),
(375, 'UA', 'Europe/Zaporozhye', 'Live', NULL, NULL),
(376, 'UG', 'Africa/Kampala', 'Live', NULL, NULL),
(377, 'UM', 'Pacific/Midway', 'Live', NULL, NULL),
(378, 'UM', 'Pacific/Wake', 'Live', NULL, NULL),
(379, 'US', 'America/New_York', 'Live', NULL, NULL),
(380, 'US', 'America/Detroit', 'Live', NULL, NULL),
(381, 'US', 'America/Kentucky/Louisville', 'Live', NULL, NULL),
(382, 'US', 'America/Kentucky/Monticello', 'Live', NULL, NULL),
(383, 'US', 'America/Indiana/Indianapolis', 'Live', NULL, NULL),
(384, 'US', 'America/Indiana/Vincennes', 'Live', NULL, NULL),
(385, 'US', 'America/Indiana/Winamac', 'Live', NULL, NULL),
(386, 'US', 'America/Indiana/Marengo', 'Live', NULL, NULL),
(387, 'US', 'America/Indiana/Petersburg', 'Live', NULL, NULL),
(388, 'US', 'America/Indiana/Vevay', 'Live', NULL, NULL),
(389, 'US', 'America/Chicago', 'Live', NULL, NULL),
(390, 'US', 'America/Indiana/Tell_City', 'Live', NULL, NULL),
(391, 'US', 'America/Indiana/Knox', 'Live', NULL, NULL),
(392, 'US', 'America/Menominee', 'Live', NULL, NULL),
(393, 'US', 'America/North_Dakota/Center', 'Live', NULL, NULL),
(394, 'US', 'America/North_Dakota/New_Salem', 'Live', NULL, NULL),
(395, 'US', 'America/North_Dakota/Beulah', 'Live', NULL, NULL),
(396, 'US', 'America/Denver', 'Live', NULL, NULL),
(397, 'US', 'America/Boise', 'Live', NULL, NULL),
(398, 'US', 'America/Phoenix', 'Live', NULL, NULL),
(399, 'US', 'America/Los_Angeles', 'Live', NULL, NULL),
(400, 'US', 'America/Anchorage', 'Live', NULL, NULL),
(401, 'US', 'America/Juneau', 'Live', NULL, NULL),
(402, 'US', 'America/Sitka', 'Live', NULL, NULL),
(403, 'US', 'America/Metlakatla', 'Live', NULL, NULL),
(404, 'US', 'America/Yakutat', 'Live', NULL, NULL),
(405, 'US', 'America/Nome', 'Live', NULL, NULL),
(406, 'US', 'America/Adak', 'Live', NULL, NULL),
(407, 'US', 'Pacific/Honolulu', 'Live', NULL, NULL),
(408, 'UY', 'America/Montevideo', 'Live', NULL, NULL),
(409, 'UZ', 'Asia/Samarkand', 'Live', NULL, NULL),
(410, 'UZ', 'Asia/Tashkent', 'Live', NULL, NULL),
(411, 'VA', 'Europe/Vatican', 'Live', NULL, NULL),
(412, 'VC', 'America/St_Vincent', 'Live', NULL, NULL),
(413, 'VE', 'America/Caracas', 'Live', NULL, NULL),
(414, 'VG', 'America/Tortola', 'Live', NULL, NULL),
(415, 'VI', 'America/St_Thomas', 'Live', NULL, NULL),
(416, 'VN', 'Asia/Ho_Chi_Minh', 'Live', NULL, NULL),
(417, 'VU', 'Pacific/Efate', 'Live', NULL, NULL),
(418, 'WF', 'Pacific/Wallis', 'Live', NULL, NULL),
(419, 'WS', 'Pacific/Apia', 'Live', NULL, NULL),
(420, 'YE', 'Asia/Aden', 'Live', NULL, NULL),
(421, 'YT', 'Indian/Mayotte', 'Live', NULL, NULL),
(422, 'ZA', 'Africa/Johannesburg', 'Live', NULL, NULL),
(423, 'ZM', 'Africa/Lusaka', 'Live', NULL, NULL),
(424, 'ZW', 'Africa/Harare', 'Live', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(55) NOT NULL,
  `email` varchar(55) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `role` int(11) NOT NULL DEFAULT 2 COMMENT '1: Admin, 2: User',
  `salary` decimal(10,3) DEFAULT NULL,
  `commission` varchar(25) DEFAULT NULL,
  `branch_id` varchar(255) DEFAULT NULL,
  `service_id` varchar(255) DEFAULT NULL,
  `overtime_hour_rate` decimal(10,3) DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `question` mediumtext DEFAULT NULL,
  `answer` mediumtext DEFAULT NULL,
  `status` varchar(255) NOT NULL DEFAULT 'Active' COMMENT 'Active, Inactive',
  `is_first_login` tinyint(1) NOT NULL DEFAULT 0,
  `language` varchar(255) NOT NULL DEFAULT 'en',
  `designation` varchar(55) DEFAULT NULL,
  `social_media` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_media`)),
  `age` varchar(10) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `qualification` varchar(255) DEFAULT NULL,
  `experience` varchar(25) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `google_id` varchar(255) DEFAULT NULL,
  `facebook_id` varchar(255) DEFAULT NULL,
  `github_id` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `company_id` int(11) NOT NULL DEFAULT 1,
  `will_login` enum('Yes','No') NOT NULL DEFAULT 'Yes',
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `role`, `salary`, `commission`, `branch_id`, `service_id`, `overtime_hour_rate`, `email_verified_at`, `password`, `photo`, `question`, `answer`, `status`, `is_first_login`, `language`, `designation`, `social_media`, `age`, `gender`, `qualification`, `experience`, `description`, `google_id`, `facebook_id`, `github_id`, `avatar`, `company_id`, `will_login`, `del_status`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 'Mr Admin', 'admin@doorsoft.co', '01111111111', 1, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$GYk0Fy6In/QMHc2mbKWrm.yy.GcIkLIKWVobP.u9XwWR8kyB6vo9S', NULL, 'What is your mother\'s maiden name?', 'Mickey', 'Active', 1, 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'Yes', 'Live', NULL, '2025-08-25 07:10:44', '2025-08-25 07:33:00'),
(2, 'Mr User', 'user@doorsoft.co', '01111111112', 2, NULL, NULL, NULL, NULL, NULL, NULL, '$2y$12$YatZK7HkrpgCJNzmmcrelOEz3SuP1gxMDjuTKHW6d63.aP98toR6.', NULL, NULL, NULL, 'Active', 0, 'en', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 1, 'Yes', 'Live', NULL, '2025-08-25 07:10:45', '2025-08-25 07:10:45'),
(3, 'Azhar Ullah', 'azharraihan6969@gmail.com', '01629171720', 1, 0.000, '0', '1', '1', 0.000, NULL, '$2y$12$F5yziDvkV4RrW7lIeso8TOJYR.FS/zpqdGpiqoFyE/7.PCMXngw16', NULL, NULL, NULL, 'Active', 0, 'en', 'TSET', '[{\"name\":\"Facebook\",\"url\":\"\",\"is_active\":false},{\"name\":\"Instagram\",\"url\":\"\",\"is_active\":false},{\"name\":\"YouTube\",\"url\":\"\",\"is_active\":false},{\"name\":\"TikTok\",\"url\":\"\",\"is_active\":false}]', NULL, 'Male', NULL, '5', NULL, NULL, NULL, NULL, NULL, 1, 'Yes', 'Live', NULL, '2025-08-25 08:23:25', '2025-08-25 08:23:25');

-- --------------------------------------------------------

--
-- Table structure for table `vacations`
--

CREATE TABLE `vacations` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(55) DEFAULT NULL,
  `start_date` varchar(25) DEFAULT NULL,
  `end_date` varchar(25) DEFAULT NULL,
  `auto_response` enum('Yes','No') DEFAULT NULL,
  `is_free` enum('Yes','No') DEFAULT NULL,
  `mail_subject` varchar(55) DEFAULT NULL,
  `mail_body` longtext DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `website_settings`
--

CREATE TABLE `website_settings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `email` varchar(55) DEFAULT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `languages` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`languages`)),
  `social_media` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`social_media`)),
  `testimonial_title` varchar(30) DEFAULT NULL,
  `testimonial_heading` varchar(100) DEFAULT NULL,
  `testimonial_image` varchar(55) DEFAULT NULL,
  `common_banner_image` varchar(55) DEFAULT NULL,
  `login_image` varchar(55) DEFAULT NULL,
  `google_map_url` text DEFAULT NULL,
  `open_day_start` varchar(25) DEFAULT NULL,
  `open_day_end` varchar(25) DEFAULT NULL,
  `open_day_start_time` varchar(25) DEFAULT NULL,
  `open_day_end_time` varchar(25) DEFAULT NULL,
  `footer_copyright` varchar(255) DEFAULT NULL,
  `footer_mini_description` varchar(255) DEFAULT NULL,
  `header_logo` varchar(100) DEFAULT NULL,
  `footer_logo` varchar(100) DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `website_settings`
--

INSERT INTO `website_settings` (`id`, `email`, `phone`, `address`, `languages`, `social_media`, `testimonial_title`, `testimonial_heading`, `testimonial_image`, `common_banner_image`, `login_image`, `google_map_url`, `open_day_start`, `open_day_end`, `open_day_start_time`, `open_day_end_time`, `footer_copyright`, `footer_mini_description`, `header_logo`, `footer_logo`, `user_id`, `company_id`, `del_status`, `created_at`, `updated_at`) VALUES
(1, 'admin@doorsoft.co', '+880 18 123 91633', 'Uttara, Dhaka', '\"[\\\"en\\\",\\\"bn\\\"]\"', '\"[{\\\"name\\\":\\\"Facebook\\\",\\\"url\\\":\\\"\\\",\\\"is_active\\\":false},{\\\"name\\\":\\\"Instagram\\\",\\\"url\\\":\\\"\\\",\\\"is_active\\\":false},{\\\"name\\\":\\\"YouTube\\\",\\\"url\\\":\\\"\\\",\\\"is_active\\\":false},{\\\"name\\\":\\\"TikTok\\\",\\\"url\\\":\\\"\\\",\\\"is_active\\\":false}]\"', 'What Our Clients Say', 'Trusted by Thousands of Happy Customers', NULL, NULL, NULL, '', 'Monday', 'Friday', '09:00', '18:00', '© 2024 Salon Buddy. All rights reserved.', 'Your trusted partner for professional hair care services. We bring out the best in your hair with our expert stylists and premium products.', NULL, NULL, 1, 1, 'Live', '2025-08-25 07:10:46', '2025-08-25 07:10:46');

-- --------------------------------------------------------

--
-- Table structure for table `working_processes`
--

CREATE TABLE `working_processes` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `status` enum('Enabled','Disabled') DEFAULT 'Enabled',
  `position` int(10) UNSIGNED DEFAULT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `company_id` int(10) UNSIGNED DEFAULT NULL,
  `del_status` enum('Live','Deleted') NOT NULL DEFAULT 'Live',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aboutus_pages`
--
ALTER TABLE `aboutus_pages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `booking_details`
--
ALTER TABLE `booking_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_receives`
--
ALTER TABLE `customer_receives`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `faqs`
--
ALTER TABLE `faqs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `items_code_unique` (`code`);

--
-- Indexes for table `item_details`
--
ALTER TABLE `item_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `package_usages_summaries`
--
ALTER TABLE `package_usages_summaries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `portfolios`
--
ALTER TABLE `portfolios`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `promotions`
--
ALTER TABLE `promotions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `purchase_details`
--
ALTER TABLE `purchase_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `rattings`
--
ALTER TABLE `rattings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `salaries`
--
ALTER TABLE `salaries`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_details`
--
ALTER TABLE `salary_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `salary_payments`
--
ALTER TABLE `salary_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_details`
--
ALTER TABLE `sale_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `timezones`
--
ALTER TABLE `timezones`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_phone_unique` (`phone`);

--
-- Indexes for table `vacations`
--
ALTER TABLE `vacations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `website_settings`
--
ALTER TABLE `website_settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `working_processes`
--
ALTER TABLE `working_processes`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aboutus_pages`
--
ALTER TABLE `aboutus_pages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `booking_details`
--
ALTER TABLE `booking_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customer_receives`
--
ALTER TABLE `customer_receives`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `faqs`
--
ALTER TABLE `faqs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `item_details`
--
ALTER TABLE `item_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `package_usages_summaries`
--
ALTER TABLE `package_usages_summaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `portfolios`
--
ALTER TABLE `portfolios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `promotions`
--
ALTER TABLE `promotions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_details`
--
ALTER TABLE `purchase_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rattings`
--
ALTER TABLE `rattings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `salaries`
--
ALTER TABLE `salaries`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_details`
--
ALTER TABLE `salary_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_payments`
--
ALTER TABLE `salary_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sale_details`
--
ALTER TABLE `sale_details`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `supplier_payments`
--
ALTER TABLE `supplier_payments`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `timezones`
--
ALTER TABLE `timezones`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=425;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `vacations`
--
ALTER TABLE `vacations`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `website_settings`
--
ALTER TABLE `website_settings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `working_processes`
--
ALTER TABLE `working_processes`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
