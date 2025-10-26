-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 25, 2025 at 04:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `office_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignments`
--

CREATE TABLE `assignments` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `assigned_date` date NOT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `table_name` varchar(100) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Electronics', 'Electronic devices and equipment', '2025-10-24 20:43:16'),
(2, 'Audio Equipment', 'Headphones, speakers, and audio devices', '2025-10-24 20:43:16'),
(3, 'Computer Peripherals', 'Mice, keyboards, and computer accessories', '2025-10-24 20:43:16'),
(4, 'IT Equipment', 'Computers, servers, and networking gear', '2025-10-24 20:43:16'),
(5, 'Stationery', 'Office supplies and writing materials', '2025-10-25 00:19:58'),
(6, 'Furniture', 'Office furniture and fixtures', '2025-10-25 00:19:58'),
(7, 'Tools & Maintenance', 'Maintenance tools and equipment', '2025-10-25 00:19:58'),
(8, 'Medical Supplies', 'Medical and first aid equipment', '2025-10-25 00:19:58'),
(9, 'Decorative Materials', 'Office decor and aesthetic items', '2025-10-25 00:19:58'),
(10, 'Safety Equipment', 'Safety and protective gear', '2025-10-25 00:19:58');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'IT Support', 'IT assistance and technical support', '2025-10-24 20:43:16'),
(2, 'Inventory Management', 'Equipment tracking and inventory control', '2025-10-24 20:43:16'),
(3, 'Procurement', 'Vendor management and purchasing', '2025-10-24 20:43:16'),
(4, 'IT', 'Information Technology - Computers, networks, software', '2025-10-25 11:28:57'),
(5, 'HR', 'Human Resources - Employee relations, hiring, benefits', '2025-10-25 11:28:57'),
(6, 'Operations', 'Operations - Office management, facilities, supplies', '2025-10-25 11:28:57'),
(7, 'Finance', 'Finance - Accounting, budgeting, payments', '2025-10-25 11:28:57'),
(8, 'Marketing', 'Marketing - Advertising, promotions, campaigns', '2025-10-25 11:28:57'),
(9, 'Sales', 'Sales - Customer acquisition, business development', '2025-10-25 11:28:57');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `equipment`
--

CREATE TABLE `equipment` (
  `id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `model` varchar(100) DEFAULT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `status` enum('available','assigned','maintenance','retired','with_vendor') DEFAULT 'available',
  `purchase_date` date DEFAULT NULL,
  `purchase_price` decimal(10,2) DEFAULT NULL,
  `warranty_expiry` date DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `equipment`
--

INSERT INTO `equipment` (`id`, `category_id`, `name`, `serial_number`, `model`, `brand`, `status`, `purchase_date`, `purchase_price`, `warranty_expiry`, `vendor_id`, `department_id`, `notes`, `created_at`, `updated_at`) VALUES
(2, 1, 'Laptop', '12423424', '', '', 'available', '2025-10-25', 0.00, NULL, NULL, 4, 'test', '2025-10-25 00:12:46', '2025-10-25 11:29:53');

-- --------------------------------------------------------

--
-- Table structure for table `equipment_documents`
--

CREATE TABLE `equipment_documents` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `file_type` varchar(100) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintenance_logs`
--

CREATE TABLE `maintenance_logs` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `maintenance_type` enum('routine','repair','upgrade','inspection') DEFAULT NULL,
  `description` text DEFAULT NULL,
  `performed_by` int(11) DEFAULT NULL,
  `maintenance_date` date DEFAULT NULL,
  `next_maintenance_date` date DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` int(11) NOT NULL,
  `permission_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `permission_name`, `description`, `created_at`) VALUES
(1, 'view_dashboard', 'Access to main dashboard', '2025-10-24 20:46:13'),
(2, 'manage_equipment', 'Add, edit, delete equipment', '2025-10-24 20:46:13'),
(3, 'manage_assignments', 'Assign equipment to employees', '2025-10-24 20:46:13'),
(4, 'manage_vendor_returns', 'Process vendor returns and RMAs', '2025-10-24 20:46:13'),
(5, 'manage_tickets', 'Create, update, resolve tickets', '2025-10-24 20:46:13'),
(6, 'manage_users', 'User management and role assignment', '2025-10-24 20:46:13'),
(7, 'manage_categories', 'Manage equipment categories', '2025-10-24 20:46:13'),
(8, 'view_reports', 'Access to reports and analytics', '2025-10-24 20:46:13'),
(9, 'vendor_access', 'Limited vendor portal access', '2025-10-24 20:46:13'),
(10, 'self_service', 'Create tickets and view assigned equipment', '2025-10-24 20:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` int(11) NOT NULL,
  `role_id` int(11) DEFAULT NULL,
  `permission_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`) VALUES
(1, 1, 3, '2025-10-24 20:46:13'),
(2, 1, 7, '2025-10-24 20:46:13'),
(3, 1, 2, '2025-10-24 20:46:13'),
(4, 1, 5, '2025-10-24 20:46:13'),
(5, 1, 6, '2025-10-24 20:46:13'),
(6, 1, 4, '2025-10-24 20:46:13'),
(7, 1, 10, '2025-10-24 20:46:13'),
(8, 1, 9, '2025-10-24 20:46:13'),
(9, 1, 1, '2025-10-24 20:46:13'),
(10, 1, 8, '2025-10-24 20:46:13'),
(16, 2, 3, '2025-10-24 20:46:13'),
(17, 2, 7, '2025-10-24 20:46:13'),
(18, 2, 2, '2025-10-24 20:46:13'),
(19, 2, 5, '2025-10-24 20:46:13'),
(20, 2, 4, '2025-10-24 20:46:13'),
(21, 2, 10, '2025-10-24 20:46:13'),
(22, 2, 9, '2025-10-24 20:46:13'),
(23, 2, 1, '2025-10-24 20:46:13'),
(24, 2, 8, '2025-10-24 20:46:13'),
(31, 3, 1, '2025-10-24 20:46:13'),
(32, 3, 2, '2025-10-24 20:46:13'),
(33, 3, 3, '2025-10-24 20:46:13'),
(34, 3, 4, '2025-10-24 20:46:13'),
(35, 3, 5, '2025-10-24 20:46:13'),
(36, 3, 7, '2025-10-24 20:46:13'),
(37, 3, 8, '2025-10-24 20:46:13'),
(38, 3, 10, '2025-10-24 20:46:13'),
(39, 4, 1, '2025-10-24 20:46:13'),
(40, 4, 4, '2025-10-24 20:46:13'),
(41, 4, 9, '2025-10-24 20:46:13'),
(42, 5, 1, '2025-10-24 20:46:13'),
(43, 5, 5, '2025-10-24 20:46:13'),
(44, 5, 10, '2025-10-24 20:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `ticket_number` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `ticket_type` enum('repair','replacement','it_assistance','equipment_request','other') NOT NULL,
  `priority` enum('low','medium','high','critical') DEFAULT 'medium',
  `status` enum('open','in_progress','on_hold','resolved','closed') DEFAULT 'open',
  `created_by` int(11) DEFAULT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `related_vendor_return_id` int(11) DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `resolved_date` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `ticket_number`, `title`, `description`, `ticket_type`, `priority`, `status`, `created_by`, `assigned_to`, `equipment_id`, `department_id`, `related_vendor_return_id`, `due_date`, `resolved_date`, `created_at`, `updated_at`) VALUES
(1, 'TICKET-20251025-3B4YJ5', 'Laptop working slow', 'test', 'repair', 'high', 'open', 3, NULL, 2, NULL, NULL, '2025-11-10', NULL, '2025-10-25 00:36:15', '2025-10-25 00:36:15'),
(2, 'TICKET-20251025-3Z39JA', 'Laptop working slow', 'test', 'repair', 'high', 'open', 3, NULL, 2, NULL, NULL, '2025-11-10', NULL, '2025-10-25 00:36:20', '2025-10-25 00:36:20'),
(3, 'TICKET-20251025-JDFTTF', 'Laptop slow', 'test', 'repair', 'high', 'open', 3, 3, 2, NULL, NULL, '2025-11-11', NULL, '2025-10-25 00:36:45', '2025-10-25 00:36:45'),
(4, 'TICKET-20251025-RSK5R5', 'Laptop slow', 'test', 'repair', 'high', 'open', 3, 3, 2, NULL, NULL, '2025-11-11', NULL, '2025-10-25 00:37:38', '2025-10-25 00:37:38'),
(6, 'TICKET-20251025-OMG93E', 'Laptop slow', 'test', 'repair', 'high', 'open', 3, 3, 2, NULL, NULL, '2025-11-11', NULL, '2025-10-25 00:38:03', '2025-10-25 00:38:03'),
(7, 'TICKET-20251025-V5E7WZ', 'test', 'test', 'replacement', 'low', 'open', 3, 3, 2, NULL, NULL, '2025-11-11', NULL, '2025-10-25 00:38:20', '2025-10-25 00:59:40'),
(9, 'TICKET-20251025-QFQ6GI', 'test1', 'testing ', 'replacement', 'high', 'open', 3, 3, 2, NULL, NULL, '2025-11-11', NULL, '2025-10-25 00:39:28', '2025-10-25 00:50:17'),
(10, 'TICKET-20251025-WR8Q81', 'Issue with laptop', 'Test', 'repair', 'high', 'open', 9, 8, NULL, NULL, NULL, NULL, NULL, '2025-10-25 13:18:44', '2025-10-25 13:18:44'),
(11, 'TICKET-20251025-Q3ERYL', 'Issue with laptop', 'Test', 'repair', 'high', 'open', 9, 8, NULL, NULL, NULL, NULL, NULL, '2025-10-25 13:19:46', '2025-10-25 13:19:46'),
(12, 'TICKET-20251025-6XY056', 'tester', 'testing this ', 'repair', 'low', 'open', 6, 8, NULL, 2, NULL, '2025-11-10', NULL, '2025-10-25 14:16:29', '2025-10-25 14:16:29');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_responses`
--

CREATE TABLE `ticket_responses` (
  `id` int(11) NOT NULL,
  `ticket_id` int(11) DEFAULT NULL,
  `responded_by` int(11) DEFAULT NULL,
  `response_text` text NOT NULL,
  `internal_note` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_responses`
--

INSERT INTO `ticket_responses` (`id`, `ticket_id`, `responded_by`, `response_text`, `internal_note`, `created_at`) VALUES
(8, 9, 3, 'working ', 0, '2025-10-25 00:50:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `employee_id`, `role_id`, `department_id`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(3, 'superadmin', 'admin@company.com', '$2y$10$XFm77FstJHzOK.ayeqTIkePQkuPYpOV1RuojlNqWO95lhuOzAcoRm', NULL, 1, NULL, 1, '2025-10-25 11:18:41', '2025-10-24 21:37:23', '2025-10-25 11:18:41'),
(6, 'jay@talentrupt.com', 'jay@talentrupt.com', '$2y$10$JBELPjuZR8kG9hqkNV7WdOnSzko/TNz2AtXdEBRtKA5AWLXnf.03e', NULL, 1, NULL, 1, '2025-10-25 14:15:44', '2025-10-25 11:19:22', '2025-10-25 14:15:44'),
(7, 'hiren@talentrupt.com', 'hiren@talentrupt.com', '$2y$10$9hiVMYc9s.t42Rmfmlsfcemt.ljPv6eHBkY4H2uXr78UZ36AKASxG', NULL, 4, NULL, 1, '2025-10-25 14:09:13', '2025-10-25 11:44:14', '2025-10-25 14:09:13'),
(8, 'rushabh@hiretalent.com', 'rushabh@hiretalent.com', '$2y$10$3JPZJaNIe3OlaSQw.YPYt.zlKNI8HkWxkMqlvDW59q9Dx8d6dJ6FW', NULL, 3, NULL, 1, '2025-10-25 14:16:47', '2025-10-25 13:17:06', '2025-10-25 14:16:47'),
(9, 'vinayak@talentrupt.com', 'vinayak@talentrupt.com', '$2y$10$XSLQa8ysu5xbzTc1Jb5R7ORFTxpE/EZWWnOdkcmfmmFjdbz9PqpEi', NULL, 5, NULL, 1, '2025-10-25 14:11:35', '2025-10-25 13:17:40', '2025-10-25 14:11:35');

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `role_name`, `description`, `created_at`) VALUES
(1, 'super_admin', 'Full system access with all permissions', '2025-10-24 20:46:13'),
(2, 'admin', 'Administrative access for inventory management', '2025-10-24 20:46:13'),
(3, 'manager', 'Can manage teams and approve requests', '2025-10-24 20:46:13'),
(4, 'vendor', 'External vendor access for return updates', '2025-10-24 20:46:13'),
(5, 'user', 'Regular employee for ticket creation and basic viewing', '2025-10-24 20:46:13');

-- --------------------------------------------------------

--
-- Table structure for table `vendors`
--

CREATE TABLE `vendors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `vendor_returns`
--

CREATE TABLE `vendor_returns` (
  `id` int(11) NOT NULL,
  `equipment_id` int(11) DEFAULT NULL,
  `vendor_id` int(11) DEFAULT NULL,
  `rma_number` varchar(100) DEFAULT NULL,
  `return_reason` text DEFAULT NULL,
  `return_date` date DEFAULT NULL,
  `expected_return_date` date DEFAULT NULL,
  `actual_return_date` date DEFAULT NULL,
  `status` enum('requested','approved','shipped','received','replaced','repaired','cancelled') DEFAULT 'requested',
  `replacement_equipment_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `processed_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assignments`
--
ALTER TABLE `assignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_assignments_dates` (`assigned_date`,`actual_return_date`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `equipment`
--
ALTER TABLE `equipment`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `idx_equipment_status` (`status`),
  ADD KEY `idx_equipment_serial` (`serial_number`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `equipment_documents`
--
ALTER TABLE `equipment_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `performed_by` (`performed_by`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permission_name` (`permission_name`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role_permission` (`role_id`,`permission_id`),
  ADD KEY `permission_id` (`permission_id`),
  ADD KEY `idx_role_permissions` (`role_id`,`permission_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ticket_number` (`ticket_number`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `related_vendor_return_id` (`related_vendor_return_id`),
  ADD KEY `idx_tickets_status` (`status`),
  ADD KEY `idx_tickets_priority` (`priority`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `assigned_to` (`assigned_to`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ticket_id` (`ticket_id`),
  ADD KEY `responded_by` (`responded_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `idx_users_email` (`email`),
  ADD KEY `idx_users_role` (`role_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `vendors`
--
ALTER TABLE `vendors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vendor_returns`
--
ALTER TABLE `vendor_returns`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `rma_number` (`rma_number`),
  ADD KEY `equipment_id` (`equipment_id`),
  ADD KEY `vendor_id` (`vendor_id`),
  ADD KEY `replacement_equipment_id` (`replacement_equipment_id`),
  ADD KEY `idx_vendor_returns_status` (`status`),
  ADD KEY `processed_by` (`processed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assignments`
--
ALTER TABLE `assignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `equipment`
--
ALTER TABLE `equipment`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `equipment_documents`
--
ALTER TABLE `equipment_documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vendors`
--
ALTER TABLE `vendors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vendor_returns`
--
ALTER TABLE `vendor_returns`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `assignments`
--
ALTER TABLE `assignments`
  ADD CONSTRAINT `assignments_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `assignments_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `equipment`
--
ALTER TABLE `equipment`
  ADD CONSTRAINT `equipment_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`),
  ADD CONSTRAINT `equipment_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  ADD CONSTRAINT `equipment_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `equipment_documents`
--
ALTER TABLE `equipment_documents`
  ADD CONSTRAINT `equipment_documents_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `equipment_documents_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `maintenance_logs`
--
ALTER TABLE `maintenance_logs`
  ADD CONSTRAINT `maintenance_logs_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `maintenance_logs_ibfk_2` FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`),
  ADD CONSTRAINT `role_permissions_ibfk_2` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`);

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`related_vendor_return_id`) REFERENCES `vendor_returns` (`id`),
  ADD CONSTRAINT `tickets_ibfk_5` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_ibfk_6` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tickets_ibfk_7` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `ticket_responses`
--
ALTER TABLE `ticket_responses`
  ADD CONSTRAINT `ticket_responses_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`),
  ADD CONSTRAINT `ticket_responses_ibfk_2` FOREIGN KEY (`responded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `users_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `user_roles` (`id`),
  ADD CONSTRAINT `users_ibfk_3` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `vendor_returns`
--
ALTER TABLE `vendor_returns`
  ADD CONSTRAINT `vendor_returns_ibfk_1` FOREIGN KEY (`equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `vendor_returns_ibfk_2` FOREIGN KEY (`vendor_id`) REFERENCES `vendors` (`id`),
  ADD CONSTRAINT `vendor_returns_ibfk_3` FOREIGN KEY (`replacement_equipment_id`) REFERENCES `equipment` (`id`),
  ADD CONSTRAINT `vendor_returns_ibfk_4` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
