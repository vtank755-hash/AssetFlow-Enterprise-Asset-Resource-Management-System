-- Create database if it does not exist
CREATE DATABASE IF NOT EXISTS `assetflow_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `assetflow_db`;

-- Set Foreign Key checks
SET FOREIGN_KEY_CHECKS = 0;

-- Drop existing tables to ensure clean setup
DROP TABLE IF EXISTS `report_exports`;
DROP TABLE IF EXISTS `sessions`;
DROP TABLE IF EXISTS `password_resets`;
DROP TABLE IF EXISTS `asset_history`;
DROP TABLE IF EXISTS `activity_logs`;
DROP TABLE IF EXISTS `notifications`;
DROP TABLE IF EXISTS `audit_details`;
DROP TABLE IF EXISTS `audit_cycles`;
DROP TABLE IF EXISTS `maintenance_requests`;
DROP TABLE IF EXISTS `resource_bookings`;
DROP TABLE IF EXISTS `transfer_requests`;
DROP TABLE IF EXISTS `asset_allocations`;
DROP TABLE IF EXISTS `asset_documents`;
DROP TABLE IF EXISTS `assets`;
DROP TABLE IF EXISTS `asset_categories`;
DROP TABLE IF EXISTS `employees`;
DROP TABLE IF EXISTS `departments`;
DROP TABLE IF EXISTS `roles`;

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================================
-- 1. ROLES TABLE
-- =========================================================================
CREATE TABLE `roles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(50) NOT NULL UNIQUE,
  `permissions` TEXT COMMENT 'JSON formatted permission keys list',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================================
-- 2. DEPARTMENTS TABLE
-- =========================================================================
CREATE TABLE `departments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `code` VARCHAR(10) NOT NULL UNIQUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================================
-- 3. EMPLOYEES TABLE
-- =========================================================================
CREATE TABLE `employees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `role_id` INT NOT NULL,
  `department_id` INT DEFAULT NULL,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('Active', 'Inactive') NOT NULL DEFAULT 'Active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_employee_email` ON `employees` (`email`);
CREATE INDEX `idx_employee_role` ON `employees` (`role_id`);

-- =========================================================================
-- 4. ASSET CATEGORIES TABLE
-- =========================================================================
CREATE TABLE `asset_categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =========================================================================
-- 5. ASSETS TABLE
-- =========================================================================
CREATE TABLE `assets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_tag` VARCHAR(50) NOT NULL UNIQUE,
  `category_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `model` VARCHAR(100) DEFAULT NULL,
  `serial_number` VARCHAR(100) NOT NULL UNIQUE,
  `purchase_date` DATE NOT NULL,
  `purchase_cost` DECIMAL(12,2) NOT NULL,
  `depreciation_rate` DECIMAL(5,2) NOT NULL COMMENT 'Annual straight-line rate (e.g. 20.00)',
  `status` ENUM('Available', 'Allocated', 'Maintenance', 'Disposed') NOT NULL DEFAULT 'Available',
  `location` VARCHAR(150) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `asset_categories`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX `idx_asset_tag` ON `assets` (`asset_tag`);
CREATE INDEX `idx_asset_category` ON `assets` (`category_id`);
CREATE INDEX `idx_asset_status` ON `assets` (`status`);

-- =========================================================================
-- 6. ASSET DOCUMENTS TABLE (Receipts, warranties, manuals)
-- =========================================================================
CREATE TABLE `asset_documents` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `document_name` VARCHAR(150) NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_doc_asset` ON `asset_documents` (`asset_id`);

-- =========================================================================
-- 7. ASSET ALLOCATIONS TABLE
-- =========================================================================
CREATE TABLE `asset_allocations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `employee_id` INT NOT NULL,
  `allocated_by` INT NOT NULL,
  `allocated_date` DATE NOT NULL,
  `due_date` DATE NOT NULL,
  `returned_date` DATE DEFAULT NULL,
  `status` ENUM('Active', 'Returned', 'Overdue') NOT NULL DEFAULT 'Active',
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`allocated_by`) REFERENCES `employees`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX `idx_alloc_asset` ON `asset_allocations` (`asset_id`);
CREATE INDEX `idx_alloc_employee` ON `asset_allocations` (`employee_id`);
CREATE INDEX `idx_alloc_status` ON `asset_allocations` (`status`);

-- =========================================================================
-- 8. TRANSFER REQUESTS TABLE (Custodian transfers)
-- =========================================================================
CREATE TABLE `transfer_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `from_employee_id` INT NOT NULL,
  `to_employee_id` INT NOT NULL,
  `requested_by` INT NOT NULL,
  `approved_by` INT DEFAULT NULL,
  `request_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `action_date` TIMESTAMP NULL DEFAULT NULL,
  `status` ENUM('Pending', 'Approved', 'Rejected') NOT NULL DEFAULT 'Pending',
  `reason` TEXT,
  `notes` TEXT,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`from_employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`to_employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requested_by`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`approved_by`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_transfer_asset` ON `transfer_requests` (`asset_id`);
CREATE INDEX `idx_transfer_status` ON `transfer_requests` (`status`);

-- =========================================================================
-- 9. RESOURCE BOOKINGS TABLE (Short term hourly reservations)
-- =========================================================================
CREATE TABLE `resource_bookings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `employee_id` INT NOT NULL,
  `start_time` DATETIME NOT NULL,
  `end_time` DATETIME NOT NULL,
  `purpose` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('Pending', 'Approved', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_booking_asset` ON `resource_bookings` (`asset_id`);
CREATE INDEX `idx_booking_times` ON `resource_bookings` (`start_time`, `end_time`);

-- =========================================================================
-- 10. MAINTENANCE REQUESTS TABLE
-- =========================================================================
CREATE TABLE `maintenance_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `requested_by` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT,
  `scheduled_date` DATE NOT NULL,
  `completion_date` DATE DEFAULT NULL,
  `cost` DECIMAL(12,2) DEFAULT 0.00,
  `status` ENUM('Pending', 'In Progress', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Pending',
  `performed_by` VARCHAR(150) DEFAULT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`requested_by`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_maint_asset` ON `maintenance_requests` (`asset_id`);
CREATE INDEX `idx_maint_status` ON `maintenance_requests` (`status`);

-- =========================================================================
-- 11. AUDIT CYCLES TABLE (Periodic stocktakes)
-- =========================================================================
CREATE TABLE `audit_cycles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(100) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('Scheduled', 'In Progress', 'Completed') NOT NULL DEFAULT 'Scheduled',
  `created_by` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `employees`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- =========================================================================
-- 12. AUDIT DETAILS TABLE (Individual asset check status)
-- =========================================================================
CREATE TABLE `audit_details` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `audit_cycle_id` INT NOT NULL,
  `asset_id` INT NOT NULL,
  `audited_by` INT NOT NULL,
  `audit_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `status` ENUM('Verified', 'Missing', 'Damaged') NOT NULL DEFAULT 'Verified',
  `notes` TEXT,
  FOREIGN KEY (`audit_cycle_id`) REFERENCES `audit_cycles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`audited_by`) REFERENCES `employees`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE INDEX `idx_audit_detail_cycle` ON `audit_details` (`audit_cycle_id`);
CREATE INDEX `idx_audit_detail_asset` ON `audit_details` (`asset_id`);

-- =========================================================================
-- 13. NOTIFICATIONS TABLE
-- =========================================================================
CREATE TABLE `notifications` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE INDEX `idx_notification_unread` ON `notifications` (`employee_id`, `is_read`);

-- =========================================================================
-- 14. ACTIVITY LOGS TABLE (Administrative Auditing)
-- =========================================================================
CREATE TABLE `activity_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT DEFAULT NULL,
  `action` VARCHAR(100) NOT NULL,
  `table_name` VARCHAR(100) NOT NULL,
  `record_id` INT DEFAULT NULL,
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================================
-- 15. ASSET HISTORY TABLE (Tracking changes)
-- =========================================================================
CREATE TABLE `asset_history` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `asset_id` INT NOT NULL,
  `employee_id` INT DEFAULT NULL COMMENT 'Actor employee',
  `action` ENUM('Purchase', 'Allocation', 'Return', 'Maintenance', 'Transfer', 'Disposal') NOT NULL,
  `notes` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`asset_id`) REFERENCES `assets`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE INDEX `idx_history_asset` ON `asset_history` (`asset_id`);

-- =========================================================================
-- 16. PASSWORD RESETS TABLE
-- =========================================================================
CREATE TABLE `password_resets` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(150) NOT NULL,
  `token` VARCHAR(255) NOT NULL UNIQUE,
  `expires_at` TIMESTAMP NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE INDEX `idx_pwd_reset_email` ON `password_resets` (`email`);

-- =========================================================================
-- 17. SESSIONS TABLE (Persisting Login Sessions)
-- =========================================================================
CREATE TABLE `sessions` (
  `id` VARCHAR(255) PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `last_activity` INT UNSIGNED NOT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================================
-- 18. REPORT EXPORTS TABLE (Trace reports downloads)
-- =========================================================================
CREATE TABLE `report_exports` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `employee_id` INT NOT NULL,
  `report_type` ENUM('Valuation', 'Maintenance', 'Utilization') NOT NULL,
  `file_path` VARCHAR(255) NOT NULL,
  `exported_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;


-- =========================================================================
-- SEED DATA SETUP
-- =========================================================================

-- Seed Roles
INSERT INTO `roles` (`id`, `name`, `permissions`) VALUES
(1, 'Admin', '{"users":"all","assets":"all","allocations":"all","maintenance":"all","inventory":"all","reports":"all"}'),
(2, 'Manager', '{"assets":"all","allocations":"all","maintenance":"all","inventory":"all","reports":"view"}'),
(3, 'Staff', '{"assets":"read","allocations":"self","maintenance":"read","inventory":"use"}');

-- Seed Departments
INSERT INTO `departments` (`id`, `name`, `code`) VALUES
(1, 'Executive Office', 'EXEC'),
(2, 'Information Technology', 'IT'),
(3, 'Operations & Warehouse', 'OPS');

-- Seed Employees (Admin Password: Admin123!)
INSERT INTO `employees` (`id`, `role_id`, `department_id`, `name`, `email`, `password_hash`, `status`) VALUES
(1, 1, 2, 'System Administrator', 'admin@assetflow.com', '$2y$12$8leMdEMtSg02H/YnmodMoOLXED0FtLiZCmSnVHFeoggRMts27qJgG', 'Active'),
(2, 2, 3, 'Sarah Manager', 'manager@assetflow.com', '$2y$12$Y76W4QPiousk2uQzLIZauesDXqh8ZlWKKxYXiep3QcfT82lfHnYoO', 'Active'),
(3, 3, 2, 'John Staff', 'staff@assetflow.com', '$2y$12$6w0QFi5l9lYrDShhcQZHZuT/TrtHW/Ehl3oolSkobZZBtBDmaVZQe', 'Active');
