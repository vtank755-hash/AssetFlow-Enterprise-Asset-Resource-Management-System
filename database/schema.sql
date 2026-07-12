-- Create Database
CREATE DATABASE IF NOT EXISTS `assetflow_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `assetflow_db`;

-- Drop Tables if they exist (for clean setup)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `inventory`;
DROP TABLE IF EXISTS `maintenance_schedules`;
DROP TABLE IF EXISTS `allocations`;
DROP TABLE IF EXISTS `assets`;
DROP TABLE IF EXISTS `categories`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- Users Table
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('Admin', 'Manager', 'Staff') NOT NULL DEFAULT 'Staff',
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Categories Table
CREATE TABLE `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Assets Table
CREATE TABLE `assets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_tag` VARCHAR(50) NOT NULL UNIQUE,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `model` VARCHAR(100) DEFAULT NULL,
  `serial_number` VARCHAR(100) NOT NULL UNIQUE,
  `purchase_date` DATE NOT NULL,
  `purchase_cost` DECIMAL(12,2) NOT NULL,
  `depreciation_rate` DECIMAL(5,2) NOT NULL COMMENT 'Annual percentage rate (e.g. 10.00 for 10%)',
  `status` ENUM('Available', 'Allocated', 'Maintenance', 'Disposed') NOT NULL DEFAULT 'Available',
  `location` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Allocations Table
CREATE TABLE `allocations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `allocated_by` INT NOT NULL,
  `allocated_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `returned_date` DATE DEFAULT NULL,
  `status` ENUM('Active', 'Returned', 'Overdue') NOT NULL DEFAULT 'Active',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`allocated_by`) REFERENCES `users`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Maintenance Schedules Table
CREATE TABLE `maintenance_schedules` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `scheduled_date` DATE NOT NULL,
  `completion_date` DATE DEFAULT NULL,
  `cost` DECIMAL(12,2) DEFAULT 0.00,
  `status` ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `performed_by` VARCHAR(150) DEFAULT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Consumable Inventory Table
CREATE TABLE `inventory` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `sku` VARCHAR(100) NOT NULL UNIQUE,
  `quantity` INT NOT NULL DEFAULT 0,
  `min_threshold` INT NOT NULL DEFAULT 5,
  `unit_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `location` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Audit Logs Table
CREATE TABLE `audit_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(100) NOT NULL,
  `record_id` INT DEFAULT NULL,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Seed Data: Users
-- Admin password: Admin123!
-- Manager password: Manager123!
-- Staff password: Staff123!
INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `status`) VALUES
(1, 'System Administrator', 'admin@assetflow.com', '$2y$12$8leMdEMtSg02H/YnmodMoOLXED0FtLiZCmSnVHFeoggRMts27qJgG', 'Admin', 'Active'),
(2, 'Sarah Manager', 'manager@assetflow.com', '$2y$12$Y76W4QPiousk2uQzLIZauesDXqh8ZlWKKxYXiep3QcfT82lfHnYoO', 'Manager', 'Active'),
(3, 'John Staff', 'staff@assetflow.com', '$2y$12$6w0QFi5l9lYrDShhcQZHZuT/TrtHW/Ehl3oolSkobZZBtBDmaVZQe', 'Staff', 'Active');

-- Seed Data: Categories
INSERT INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'IT Equipment', 'Computers, laptops, servers, routers, and peripherals'),
(2, 'Office Furniture', 'Desks, chairs, filing cabinets, and conference tables'),
(3, 'Vehicles', 'Company cars, vans, and utility trucks');

-- Seed Data: Assets
INSERT INTO `assets` (`id`, `asset_tag`, `category_id`, `name`, `model`, `serial_number`, `purchase_date`, `purchase_cost`, `depreciation_rate`, `status`, `location`) VALUES
(1, 'AST-2026-0001', 1, 'MacBook Pro 16"', 'M3 Max 36GB/1TB', 'C02F234XMD6M', '2026-01-15', 3499.00, 20.00, 'Allocated', 'HQ - 3rd Floor'),
(2, 'AST-2026-0002', 1, 'Dell UltraSharp 32" 4K Monitor', 'U3223QE', 'CN078X1Y12903', '2026-02-10', 899.00, 15.00, 'Available', 'HQ - 3rd Floor'),
(3, 'AST-2026-0003', 2, 'Herman Miller Aeron Chair', 'Size B - Fully Loaded', 'HM-AERON-998822', '2026-01-20', 1495.00, 10.00, 'Allocated', 'HQ - 4th Floor'),
(4, 'AST-2026-0004', 3, 'Ford Transit Cargo Van', 'Transit 250 Medium Roof', '1FTYR239871239', '2026-03-01', 45000.00, 15.00, 'Maintenance', 'Warehouse A');

-- Seed Data: Allocations
INSERT INTO `allocations` (`id`, `asset_id`, `user_id`, `allocated_by`, `allocated_date`, `due_date`, `returned_date`, `status`, `notes`) VALUES
(1, 1, 3, 2, '2026-01-15', '2026-12-31', NULL, 'Active', 'Issued to John Staff for developers role'),
(2, 3, 3, 1, '2026-01-20', '2027-01-20', NULL, 'Active', 'Office chair for desk 304');

-- Seed Data: Maintenance Schedules
INSERT INTO `maintenance_schedules` (`id`, `asset_id`, `title`, `description`, `scheduled_date`, `completion_date`, `cost`, `status`, `performed_by`, `notes`) VALUES
(1, 4, 'Routine Oil & Filter Change', 'Perform standard 5,000-mile engine maintenance service.', '2026-07-10', NULL, 0.00, 'In Progress', 'Quick Lube Express', 'Pending invoice clearance'),
(2, 1, 'Screen Replacement', 'Repair damaged retina display screen under AppleCare.', '2026-05-12', '2026-05-15', 299.00, 'Completed', 'Apple Store HQ', 'Paid via corporate credit card');

-- Seed Data: Consumable Inventory
INSERT INTO `inventory` (`id`, `name`, `sku`, `quantity`, `min_threshold`, `unit_price`, `location`) VALUES
(1, 'Cat6 Ethernet Cable (10ft)', 'ETH-CAT6-10', 45, 10, 4.50, 'IT Storage Closet'),
(2, 'Logitech MX Master 3S Mouse', 'LOGI-MX3S', 8, 3, 99.00, 'IT Storage Closet'),
(3, 'AA Alkaline Batteries (24-pack)', 'BAT-AA-24', 2, 5, 14.99, 'Office Supply Room');
