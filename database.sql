-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2026 at 12:48 AM
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
-- Database: `cainta_scholarship`
--

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` enum('1st','2nd') DEFAULT NULL,
  `gwa` decimal(4,2) DEFAULT NULL,
  `monthly_income` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','for_review','approved','rejected','incomplete') DEFAULT 'pending',
  `remarks` text DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applications`
--

INSERT INTO `applications` (`application_id`, `scholar_id`, `school_year`, `semester`, `gwa`, `monthly_income`, `status`, `remarks`, `submitted_at`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`) VALUES
(4, 2, '2026-2027', '2nd', NULL, NULL, 'approved', '', '2026-04-03 08:13:46', 'IAN VILLAR', 'DRIVER', 'IRY CALOOYONG MAANO', 'TGP'),
(5, 3, '2025-2026', '1st', NULL, NULL, 'pending', NULL, '2026-04-26 15:27:47', 'ASDASDSA', 'ASDASDADA', 'afsfasfaAFDASDA', 'ASFAFFS');

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `disbursement_id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `school_year` varchar(20) DEFAULT NULL,
  `semester` enum('1st','2nd') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','released') DEFAULT 'pending',
  `released_by` int(11) DEFAULT NULL,
  `released_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `document_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `document_type` varchar(100) DEFAULT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `verified` tinyint(4) DEFAULT 0,
  `verified_by` int(11) DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`document_id`, `application_id`, `document_type`, `file_path`, `verified`, `verified_by`, `verified_at`) VALUES
(5, 4, 'barangay', 'Brgy. San Juan', 0, NULL, NULL),
(6, 4, 'birthdate', '2006-09-12', 0, NULL, NULL),
(7, 4, 'school', 'ICCT COLLEGE CAINTA', 0, NULL, NULL),
(8, 4, 'course', 'BSIT', 0, NULL, NULL),
(9, 4, 'year_level', '1', 0, NULL, NULL),
(10, 4, 'grade_slip', 'grade_slip_2_1775204026.jpg', 0, NULL, NULL),
(11, 4, 'enrollment_receipt', 'enrollment_receipt_2_1775204026.jpg', 0, NULL, NULL),
(12, 4, 'enrollment_form', 'enrollment_form_2_1775204026.jpg', 0, NULL, NULL),
(13, 5, 'barangay', 'Brgy. Santo Niño', 0, NULL, NULL),
(14, 5, 'birthdate', '2002-08-28', 0, NULL, NULL),
(15, 5, 'school', 'ASDASDDAS', 0, NULL, NULL),
(16, 5, 'course', 'AFSASFAF', 0, NULL, NULL),
(17, 5, 'year_level', '1', 0, NULL, NULL),
(18, 5, 'grade_slip', 'grade_slip_3_1777217267.png', 0, NULL, NULL),
(19, 5, 'enrollment_receipt', 'enrollment_receipt_3_1777217267.png', 0, NULL, NULL),
(20, 5, 'enrollment_form', 'enrollment_form_3_1777217267.png', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `item_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `unit` varchar(30) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `reorder_level` int(11) DEFAULT 10,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_transactions`
--

CREATE TABLE `inventory_transactions` (
  `transaction_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `scholar_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `type` enum('IN','OUT') DEFAULT NULL,
  `processed_by` int(11) DEFAULT NULL,
  `processed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scholars`
--

CREATE TABLE `scholars` (
  `scholar_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `address` text DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `school` varchar(100) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `year_level` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(4) DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholars`
--

INSERT INTO `scholars` (`scholar_id`, `first_name`, `last_name`, `middle_name`, `birthdate`, `gender`, `address`, `barangay`, `contact_no`, `email`, `school`, `course`, `year_level`, `status`, `created_at`, `is_archived`, `archived_at`, `archive_reason`) VALUES
(1, 'John Ryan', 'Villar', '', '2006-09-12', 'Male', 'PELICAN ST ANAKPAWIS CAINTA RIZAL', 'Brgy. San Andres', '09123132123', 'johnryanvillar7@gmail.com', 'ICCT COLLEGE CAINTA', 'BSIT', 1, 'active', '2026-04-30 00:40:17', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `contact_no` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_archived` tinyint(4) DEFAULT 0,
  `archived_at` timestamp NULL DEFAULT NULL,
  `archive_reason` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `first_name`, `last_name`, `middle_name`, `email`, `password`, `contact_no`, `address`, `barangay`, `birthdate`, `gender`, `is_active`, `created_at`, `is_archived`, `archived_at`, `archive_reason`) VALUES
(1, 'James', 'Villar', 'Brian', 'villarjamesbrian1@gmail.com', '$2y$10$xkD9aZiyqQKTURkemomCi.kSacSq8py0TyOQybEFYxZVNq44YnNjq', '09305622222', 'PELICAN ST ANAKPAWIS CAINTA RIZAL', 'Brgy. San Juan', '2002-09-28', 'Male', 1, '2026-04-30 00:37:13', 0, NULL, NULL),
(2, 'John Ryan', 'Villar', '', 'johnryanvillar7@gmail.com', '$2y$10$sHQuxt.pCb7MeTi6Gnh/9em5FiePCWUuYNJwLVjUjlet8uOMQx1F.', '09123132123', 'PELICAN ST ANAKPAWIS CAINTA RIZAL', 'Brgy. San Andres', '2006-09-12', 'Male', 1, '2026-04-30 00:39:19', 0, NULL, NULL),
(3, 'Angelus', 'Pacheco', '', 'angeluspacheco2827@gmail.com', '$2y$10$cFpKYROvue8ELspWWN9bgOniHTRjX1T5AIBvf5jdUl7A7NIsyKJCG', '09612376162', 'Tassel St Greenland Subd', 'Brgy. San Isidro', '2002-08-28', 'Female', 1, '2026-04-30 00:39:55', 0, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','officer','cashier') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `full_name`, `username`, `password`, `role`, `email`, `is_active`, `created_at`) VALUES
(1, 'Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'admin@cainta.gov.ph', 1, '2026-03-31 08:23:49'),
(2, 'Scholarship Officer', 'officer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'officer', 'officer@cainta.gov.ph', 1, '2026-03-31 08:23:49'),
(3, 'Cashier', 'cashier', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'cashier', 'cashier@cainta.gov.ph', 1, '2026-03-31 08:23:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `scholar_id` (`scholar_id`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`disbursement_id`),
  ADD KEY `scholar_id` (`scholar_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`document_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`item_id`);

--
-- Indexes for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `scholars`
--
ALTER TABLE `scholars`
  ADD PRIMARY KEY (`scholar_id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `disbursement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `document_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `scholars`
--
ALTER TABLE `scholars`
  MODIFY `scholar_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `applications` (`application_id`);

--
-- Constraints for table `inventory_transactions`
--
ALTER TABLE `inventory_transactions`
  ADD CONSTRAINT `inventory_transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`item_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
