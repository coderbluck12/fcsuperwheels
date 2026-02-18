CREATE DATABASE IF NOT EXISTS tertgxyp_fcsuperwheels;
USE tertgxyp_fcsuperwheels;

-- 1. Users table (with all columns expected by session_manager.php)
CREATE TABLE IF NOT EXISTS `user` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `firstname` VARCHAR(100),
    `lastname` VARCHAR(100),
    `email` VARCHAR(100),
    `level` VARCHAR(100),
    `status` INT DEFAULT 1,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Access Log table for auditing user activities
CREATE TABLE IF NOT EXISTS `access_log` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `username` VARCHAR(50),
    `action` VARCHAR(100) NOT NULL,
    `page` VARCHAR(255),
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `user`(`id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_username` (`username`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
);

-- Default admin user (password: admin123)
INSERT INTO `user` (`username`, `password`, `firstname`, `lastname`, `email`, `level`, `status`) 
VALUES ('admin', 'admin123', 'Admin', 'User', 'admin@example.com', 'admin', 1)
ON DUPLICATE KEY UPDATE username=username;

-- 2. Signatures table (required by signature_manager.php)
CREATE TABLE IF NOT EXISTS `signatures` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `signature_name` VARCHAR(255) NOT NULL,
    `signature_file` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Main Receipt table (required by dashboard.php and newreceipt.php)
CREATE TABLE IF NOT EXISTS `main_receipt` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `customer_name` VARCHAR(255),
    `customer_address` TEXT,
    `customer_phone` VARCHAR(50),
    `customer_email` VARCHAR(100),
    `vehicle_make` VARCHAR(100),
    `vehicle_model` VARCHAR(100),
    `vehicle_year` VARCHAR(50),
    `vehicle_chasis` VARCHAR(100),
    `vehicle_color` VARCHAR(50),
    `vehicle_price` DECIMAL(15, 2),
    `amount_paid` DECIMAL(15, 2),
    `payment_type` ENUM('full', 'installment') DEFAULT 'full',
    `payment_method` VARCHAR(100),
    `payment_reference` VARCHAR(100),
    `payment_date` DATE,
    `visibility` ENUM('yes', 'no') DEFAULT 'yes',
    `signature_id` INT,
    `time_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`signature_id`) REFERENCES `signatures`(`id`) ON DELETE SET NULL
);

-- 4. Main Invoice table (required by invoice_manager.php)
CREATE TABLE IF NOT EXISTS `main_invoice` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `prefix_invoice_number` VARCHAR(50),
    `customer_name` VARCHAR(255),
    `vehicle_make` VARCHAR(100),
    `vehicle_model` VARCHAR(100),
    `vehicle_year` VARCHAR(50),
    `invoice_date` DATE,
    `due_date` DATE,
    `total_amount` DECIMAL(15, 2),
    `visibility` ENUM('yes', 'no') DEFAULT 'yes',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Inventory: Vehicles table
CREATE TABLE IF NOT EXISTS `vehicles` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `make` VARCHAR(100) NOT NULL,
    `model` VARCHAR(100) NOT NULL,
    `year` INT NOT NULL,
    `vin` VARCHAR(50) UNIQUE,
    `color` VARCHAR(50),
    `purchase_price` DECIMAL(15, 2),
    `sale_price` DECIMAL(15, 2),
    `image` VARCHAR(255),
    `status` ENUM('Available', 'Sold', 'Reserved') DEFAULT 'Available',
    `purchase_date` DATE,
    `sale_date` DATE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 6. Inventory: Expenses table
CREATE TABLE IF NOT EXISTS `expenses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `vehicle_id` INT NOT NULL,
    `description` VARCHAR(255) NOT NULL,
    `amount` DECIMAL(15, 2) NOT NULL,
    `expense_date` DATE NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE CASCADE
);


CREATE TABLE IF NOT EXISTS `car_request` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(50),
    `others` TEXT,
    `status` VARCHAR(20) DEFAULT 'pending',
    `time_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);