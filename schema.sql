-- Create database if it does not exist
CREATE DATABASE IF NOT EXISTS `mouhamat_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `mouhamat_db`;

-- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` VARCHAR(36) PRIMARY KEY,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) UNIQUE DEFAULT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `oath_date` VARCHAR(50) NOT NULL,
  `is_syndicate_member` TINYINT(1) DEFAULT 0,
  `role` ENUM('admin', 'delegate', 'lawyer', 'guest') NOT NULL,
  `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
  `id_card_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create announcements table
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` VARCHAR(36) PRIMARY KEY,
  `text` TEXT NOT NULL,
  `author_name` VARCHAR(200) NOT NULL,
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` BIGINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create requests table
CREATE TABLE IF NOT EXISTS `requests` (
  `id` VARCHAR(36) PRIMARY KEY,
  `lawyer_name` VARCHAR(200) NOT NULL,
  `oath_date` VARCHAR(50) NOT NULL,
  `is_syndicate_member` TINYINT(1) DEFAULT 0,
  `case_number` VARCHAR(50) NOT NULL,
  `parties` VARCHAR(255) NOT NULL,
  `purpose` ENUM('delay', 'advance') NOT NULL,
  `session_date` DATE NOT NULL,
  `is_colleague` TINYINT(1) DEFAULT 0,
  `jurisdiction_type` ENUM('council', 'court') NOT NULL,
  `jurisdiction_name` VARCHAR(150) NOT NULL,
  `jurisdiction_sub_entity` VARCHAR(150) NOT NULL,
  `creator_id` VARCHAR(36) NOT NULL,
  `creator_role` VARCHAR(50) NOT NULL,
  `is_archived` TINYINT(1) DEFAULT 0,
  `created_at` BIGINT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create system_settings table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_key` VARCHAR(50) PRIMARY KEY,
  `setting_value` TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create councils table
CREATE TABLE IF NOT EXISTS `councils` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create courts table
CREATE TABLE IF NOT EXISTS `courts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) UNIQUE NOT NULL,
  `council_name` VARCHAR(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create sections table
CREATE TABLE IF NOT EXISTS `sections` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create chambers table
CREATE TABLE IF NOT EXISTS `chambers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) UNIQUE NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default system settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`) VALUES
('is_list_open', '1')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Insert default councils
INSERT INTO `councils` (`name`) VALUES
('مجلس قضاء البليدة'),
('مجلس قضاء الشلف'),
('مجلس قضاء تيبازة'),
('مجلس قضاء عين الدفلى')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert default courts
INSERT INTO `courts` (`name`, `council_name`) VALUES
('محكمة البليدة', 'مجلس قضاء البليدة'),
('محكمة بوفاريك', 'مجلس قضاء البليدة'),
('محكمة الأربعاء', 'مجلس قضاء البليدة'),
('محكمة العفرون', 'مجلس قضاء البليدة'),
('محكمة الشلف', 'مجلس قضاء الشلف'),
('محكمة تنس', 'مجلس قضاء الشلف'),
('محكمة الشطية', 'مجلس قضاء الشلف'),
('محكمة بوقادير', 'مجلس قضاء الشلف'),
('محكمة تيبازة', 'مجلس قضاء تيبازة'),
('محكمة القليعة', 'مجلس قضاء تيبازة'),
('محكمة حجوط', 'مجلس قضاء تيبازة'),
('محكمة شرشال', 'مجلس قضاء تيبازة'),
('محكمة عين الدفلى', 'مجلس قضاء عين الدفلى'),
('محكمة خميس مليانة', 'مجلس قضاء عين الدفلى'),
('محكمة مليانة', 'مجلس قضاء عين الدفلى'),
('محكمة العطاف', 'مجلس قضاء عين الدفلى')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert default sections
INSERT INTO `sections` (`name`) VALUES
('قسم الجنح'),
('قسم المخالفات')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert default chambers
INSERT INTO `chambers` (`name`) VALUES
('الغرفة الجزائية الأولى'),
('الغرفة الجزائية الثانية'),
('الغرفة الجزائية الثالثة')
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- Insert seeded users
-- passwords: admin123, delegate123, 123
INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `oath_date`, `is_syndicate_member`, `role`, `status`) VALUES
('1', 'أحمد', 'علي', 'ali@gmail.com', '0555555557', '$2y$10$.dmxUeyJSVmGc1tYcwHnfuFPYRpD2BBAdW1pkL5NbZpF1/dxUqaWO', '2010', 0, 'lawyer', 'approved'),
('2', 'سارة', 'عمر', 'omar@gmail.com', '0555555558', '$2y$10$1j7k0GFIV3VlBA3SlaFj6uitpvXxAPsSAQA5KfLo9FEzuppSoDMDi', '2015', 1, 'delegate', 'approved'),
('3', 'محمد', 'سعيد', 'said@gmail.com', '0555555559', '$2y$10$1sneZh8M1R0ZYjHU4UOP4uPVLWN53/EtPiX0fFtyn4cV89mY2SKz2', '2005', 1, 'admin', 'approved')
ON DUPLICATE KEY UPDATE `first_name` = VALUES(`first_name`), `last_name` = VALUES(`last_name`);
