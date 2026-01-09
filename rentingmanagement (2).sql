-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 09, 2026 at 06:43 AM
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
-- Database: `rentingmanagement`
--

-- --------------------------------------------------------

--
-- Table structure for table `app_updates`
--
-- Error reading structure for table rentingmanagement.app_updates: #1932 - Table &#039;rentingmanagement.app_updates&#039; doesn&#039;t exist in engine
-- Error reading data for table rentingmanagement.app_updates: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `rentingmanagement`.`app_updates`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `operator_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_price` varchar(50) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `booking_status` enum('Pending','Approved','Declined','Completed','Cancelled') DEFAULT 'Pending',
  `status` varchar(50) DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') DEFAULT 'pending',
  `booking_reference` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `equipment_id`, `user_id`, `operator_id`, `start_date`, `end_date`, `total_price`, `location`, `booking_status`, `status`, `payment_status`, `booking_reference`, `created_at`) VALUES
(1, 3, 4, NULL, '2026-01-09', '2026-01-09', '1900.0', '', 'Pending', 'pending', 'pending', 'AGR-2026-0337', '2026-01-08 09:06:59'),
(2, 2, 4, NULL, '2026-01-09', '2026-01-09', '1700.0', '', 'Pending', 'pending', 'pending', 'AGR-2026-8235', '2026-01-08 09:07:23'),
(3, 11, 4, NULL, '2026-01-10', '2026-01-10', '1500.0', '', 'Pending', 'pending', 'pending', 'AGR-2026-0025', '2026-01-08 09:11:09'),
(4, 11, 4, NULL, '2026-01-09', '2026-01-09', '1500.0', '', 'Pending', 'pending', 'pending', 'AGR-2026-8812', '2026-01-09 04:14:20');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `icon`, `color`, `created_at`) VALUES
(1, 'Tractor', 'ic_tractor', '#4CAF50', '2026-01-08 09:04:56'),
(2, 'Harvester', 'ic_harvester', '#2196F3', '2026-01-08 09:04:56'),
(3, 'Tiller', 'ic_tiller', '#FF9800', '2026-01-08 09:04:56'),
(4, 'Sprayer', 'ic_sprayer', '#9C27B0', '2026-01-08 09:04:56'),
(5, 'Plough', 'ic_plough', '#F44336', '2026-01-08 09:04:56'),
(6, 'Seeder', 'ic_seeder', '#00BCD4', '2026-01-08 09:04:56'),
(7, 'Cultivator', 'ic_cultivator', '#795548', '2026-01-08 09:04:56'),
(8, 'Rotavator', 'ic_rotavator', '#607D8B', '2026-01-08 09:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `equipments`
--

CREATE TABLE `equipments` (
  `id` int(11) NOT NULL,
  `equipment_name` varchar(255) NOT NULL,
  `specification` text DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `price_per_hour` decimal(10,2) DEFAULT NULL,
  `availability` varchar(50) DEFAULT 'Available',
  `year` varchar(10) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `power` varchar(50) DEFAULT NULL,
  `insurance` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `owner_id` int(11) DEFAULT 0,
  `rating` decimal(3,2) DEFAULT 0.00,
  `total_bookings` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipments`
--

INSERT INTO `equipments` (`id`, `equipment_name`, `specification`, `type`, `price_per_hour`, `availability`, `year`, `model`, `fuel_type`, `power`, `insurance`, `description`, `image`, `owner_id`, `rating`, `total_bookings`, `created_at`, `updated_at`) VALUES
(1, 'John Deere 5310', '55 HP, 2WD', 'Tractor', 500.00, 'Available', '2022', '5310', 'Diesel', '55 HP', NULL, 'Powerful and reliable tractor perfect for medium to large farms. Features excellent fuel efficiency and comfortable operation.', NULL, 1, 4.30, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(2, 'Mahindra 575 DI', '45 HP, 2WD', 'Tractor', 400.00, 'Available', '2021', '575 DI', 'Diesel', '45 HP', NULL, 'India\'s most popular tractor with excellent performance and low maintenance cost.', NULL, 1, 4.60, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(3, 'Swaraj 744 FE', '48 HP, 4WD', 'Tractor', 450.00, 'Available', '2023', '744 FE', 'Diesel', '48 HP', NULL, 'Modern 4WD tractor with advanced features and superior ground clearance.', NULL, 1, 4.90, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(4, 'Kubota DC-68G', 'Combine Harvester', 'Harvester', 1200.00, 'Available', '2022', 'DC-68G', 'Diesel', '68 HP', NULL, 'High-capacity combine harvester for efficient wheat and paddy harvesting.', NULL, 1, 4.80, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(5, 'Shaktiman Rotavator', '5 feet width', 'Rotavator', 350.00, 'Available', '2023', 'SRT-150', 'N/A', '45-55 HP required', NULL, 'Heavy-duty rotavator for superior soil preparation and mixing.', NULL, 1, 4.70, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(6, 'KS Sprayer', '16L Tank, Battery', 'Sprayer', 100.00, 'Available', '2024', 'KS-16B', 'Battery', '12V DC', NULL, 'Rechargeable battery-operated sprayer for pesticide and fertilizer application.', NULL, 1, 4.10, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(7, 'MB Plough 3-Bottom', 'Reversible', 'Plough', 250.00, 'Available', '2022', 'MB-3R', 'N/A', '40+ HP required', NULL, 'Professional grade reversible plough for deep tillage operations.', NULL, 1, 5.00, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(8, 'Seed Drill Machine', '9 Row', 'Seeder', 300.00, 'Available', '2023', 'SD-9R', 'N/A', '35+ HP required', NULL, 'Precision seed drill for accurate seed placement and spacing.', NULL, 1, 4.80, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(9, 'Power Tiller', '12 HP', 'Tiller', 200.00, 'Available', '2023', 'PT-12', 'Diesel', '12 HP', NULL, 'Compact power tiller ideal for small farms and gardens.', NULL, 1, 4.50, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(10, 'Spring Cultivator', '9 Tyne', 'Cultivator', 180.00, 'Available', '2022', 'SC-9T', 'N/A', '35+ HP required', NULL, 'Spring-loaded cultivator for effective secondary tillage.', NULL, 1, 4.10, 0, '2026-01-08 09:04:56', '2026-01-08 09:04:56'),
(11, 'Swaraj', '65Hp', 'Tractor', 350.00, 'Available', '2019', '65hp-SW', 'diesel', '550', 'yes', 'good condition for suitable for ploughing and harvesting', '1767863455_9474.jpg', 4, 0.00, 0, '2026-01-08 09:10:55', '2026-01-08 09:10:55');

-- --------------------------------------------------------

--
-- Table structure for table `equipments_details`
--

CREATE TABLE `equipments_details` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price_per_hour` int(11) DEFAULT NULL,
  `rating` float DEFAULT NULL,
  `distance_km` float DEFAULT NULL,
  `availability` varchar(20) DEFAULT NULL,
  `power_hp` int(11) DEFAULT NULL,
  `fuel_type` varchar(50) DEFAULT NULL,
  `year` int(11) DEFAULT NULL,
  `insurance` varchar(20) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipments_details`
--

INSERT INTO `equipments_details` (`id`, `name`, `price_per_hour`, `rating`, `distance_km`, `availability`, `power_hp`, `fuel_type`, `year`, `insurance`, `description`, `owner_id`, `image_url`, `created_at`) VALUES
(5, 'Tractor', 650, 4.5, 2, 'available', 450, 'Diesel', 2022, 'yes', 'good condition and good ploughing', 7584, '1764841456_9213.png', '2025-12-31 03:09:11');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_categories`
--

CREATE TABLE `equipment_categories` (
  `id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `icon` varchar(100) DEFAULT NULL,
  `available_count` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment_insurance`
--

CREATE TABLE `equipment_insurance` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `insurance_company` varchar(150) NOT NULL,
  `policy_number` varchar(150) NOT NULL,
  `insurance_type` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `insurance_amount` text NOT NULL,
  `damage_deductible` text DEFAULT '',
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment_insurance`
--

INSERT INTO `equipment_insurance` (`id`, `equipment_id`, `insurance_company`, `policy_number`, `insurance_type`, `start_date`, `end_date`, `insurance_amount`, `damage_deductible`, `remarks`, `created_at`) VALUES
(1, 1, 'TATA AIG', 'TATA-AGRI-33221', 'Damage', '2025-03-15', '2026-03-15', '30000', '1500', 'Only damage coverage given', '2025-12-08 09:51:14'),
(2, 3, 'ICICI Lombard', 'IC-AG-78215', 'Third Party', '2025-02-20', '2026-02-20', '18000', '1200', 'Third party liability only', '2025-12-08 09:51:39'),
(3, 4, 'Reliance General', 'REL-AGRI-99410', 'Damage', '2025-05-10', '2026-05-10', '28000', '900', 'Covers accidental damage only', '2025-12-08 09:51:49'),
(5, 5, 'HDFC Ergo', 'HDF-AGRI-66081', 'Full Coverage', '2025-01-25', '2026-01-25', '35000', '600', 'Covers theft, fire and accident', '2025-12-09 06:45:34'),
(6, 5, 'HDFC Ergo', 'HDF-AGRI-66081', 'Full Coverage', '2025-01-25', '2026-01-25', '35000', '600', 'Covers theft, fire and accident', '2025-12-09 06:45:50');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'general',
  `is_read` tinyint(4) DEFAULT 0,
  `reference_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `operators`
--

CREATE TABLE `operators` (
  `id` int(11) NOT NULL,
  `operator_name` varchar(255) NOT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `specification` text DEFAULT NULL,
  `availability` varchar(50) DEFAULT 'Available',
  `rating` decimal(3,2) DEFAULT 4.50,
  `price_per_hour` decimal(10,2) DEFAULT 300.00,
  `phone` varchar(20) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `operators`
--

INSERT INTO `operators` (`id`, `operator_name`, `experience`, `specification`, `availability`, `rating`, `price_per_hour`, `phone`, `image`, `created_at`) VALUES
(1, 'Ram Kumar', '8 years', 'Tractor, Harvester, Rotavator', 'Available', 4.90, 300.00, '9876543001', NULL, '2026-01-08 09:04:56'),
(2, 'Suresh Patel', '5 years', 'Tractor, Tiller, Cultivator', 'Available', 4.70, 280.00, '9876543002', NULL, '2026-01-08 09:04:56'),
(3, 'Venkat Reddy', '10 years', 'All Equipment Types', 'Available', 4.80, 350.00, '9876543003', NULL, '2026-01-08 09:04:56'),
(4, 'Raju Singh', '3 years', 'Tractor, Sprayer', 'Available', 4.50, 250.00, '9876543004', NULL, '2026-01-08 09:04:56'),
(5, 'Mohan Yadav', '6 years', 'Harvester, Seeder', 'Available', 4.60, 320.00, '9876543005', NULL, '2026-01-08 09:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_otp`
--

CREATE TABLE `password_reset_otp` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `otp` varchar(10) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(4) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `password_reset_otp`
--

INSERT INTO `password_reset_otp` (`id`, `email`, `otp`, `expires_at`, `used`, `created_at`) VALUES
(3, 'mamudurun@gmail.com', '056197', '2026-01-06 10:14:08', 0, '2026-01-06 09:04:08');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `razorpay_payment_id` varchar(255) DEFAULT NULL,
  `razorpay_order_id` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `status` enum('pending','success','failed','refunded') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `razorpay_payment_id`, `razorpay_order_id`, `amount`, `status`, `created_at`) VALUES
(1, 1, 4, 'pay_S1KTfy0Hw1wQC0', '', 4012.00, 'success', '2026-01-08 09:08:19'),
(2, 1, 4, 'pay_S1KXd9qsqwtKon', '', 4012.00, 'success', '2026-01-08 09:12:02');

-- --------------------------------------------------------

--
-- Table structure for table `ratings`
--

CREATE TABLE `ratings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `equipment_id` int(11) NOT NULL,
  `operator_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ratings`
--

INSERT INTO `ratings` (`id`, `user_id`, `equipment_id`, `operator_id`, `rating`, `comment`, `created_at`) VALUES
(1, 1, 2, 2, 5, '', '2025-12-06 03:37:52');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `type` enum('credit','debit') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `reference_id` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `user_type` enum('user','owner','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `mobile`, `password`, `profile_image`, `location`, `user_type`, `created_at`) VALUES
(1, 'Test User', 'test@test.com', '9876543210', '$2y$10$aetJHeQedHHjjYVgvXY35u4/mhDcR0dafDWEPAxlDgAc1IYB8cRHq', NULL, NULL, 'owner', '2026-01-08 09:04:56');

-- --------------------------------------------------------

--
-- Table structure for table `wallet`
--

CREATE TABLE `wallet` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipments`
--
ALTER TABLE `equipments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_type` (`type`),
  ADD KEY `idx_owner_id` (`owner_id`);

--
-- Indexes for table `equipments_details`
--
ALTER TABLE `equipments_details`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment_insurance`
--
ALTER TABLE `equipment_insurance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `operators`
--
ALTER TABLE `operators`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_booking_id` (`booking_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `ratings`
--
ALTER TABLE `ratings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallet`
--
ALTER TABLE `wallet`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `equipments`
--
ALTER TABLE `equipments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `equipments_details`
--
ALTER TABLE `equipments_details`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `equipment_categories`
--
ALTER TABLE `equipment_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment_insurance`
--
ALTER TABLE `equipment_insurance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `operators`
--
ALTER TABLE `operators`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `password_reset_otp`
--
ALTER TABLE `password_reset_otp`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `ratings`
--
ALTER TABLE `ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wallet`
--
ALTER TABLE `wallet`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
