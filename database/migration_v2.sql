-- FixIt Hub Pakistan - Schema Migration v2.0
-- Run AFTER the original schema.sql
-- Adds: enhanced booking statuses, notification types, API tokens, payment enhancements

-- ═══════ ENHANCED BOOKING STATUS ═══════
-- Add new statuses: accepted, technician_on_way, refunded
ALTER TABLE `bookings`
    MODIFY COLUMN `status` ENUM('pending','accepted','technician_on_way','in_progress','completed','cancelled','refunded','disputed') NOT NULL DEFAULT 'pending';

ALTER TABLE `booking_status_history`
    MODIFY COLUMN `status` ENUM('pending','accepted','technician_on_way','in_progress','completed','cancelled','refunded','disputed') NOT NULL;

-- ═══════ ENHANCED NOTIFICATION TYPES ═══════
ALTER TABLE `notifications`
    MODIFY COLUMN `type` ENUM('booking','message','payment','system','review','admin') NOT NULL DEFAULT 'system';

-- ═══════ API TOKENS TABLE ═══════
CREATE TABLE IF NOT EXISTS `api_tokens` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `token_hash` VARCHAR(255) NOT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT 'API Token',
    `abilities` JSON DEFAULT NULL,
    `last_used_at` DATETIME DEFAULT NULL,
    `expires_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    INDEX `idx_token_hash` (`token_hash`),
    INDEX `idx_token_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════ ENHANCED PAYMENTS TABLE ═══════
-- Add gateway_response column for storing full payment gateway responses
ALTER TABLE `payments`
    ADD COLUMN IF NOT EXISTS `gateway_response` JSON DEFAULT NULL AFTER `transaction_id`,
    ADD COLUMN IF NOT EXISTS `refund_reason` TEXT DEFAULT NULL AFTER `gateway_response`,
    ADD COLUMN IF NOT EXISTS `refunded_at` DATETIME DEFAULT NULL AFTER `refund_reason`;

-- ═══════ LOGIN ATTEMPTS TABLE ═══════
CREATE TABLE IF NOT EXISTS `login_attempts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(150) NOT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_login_email` (`email`),
    INDEX `idx_login_ip` (`ip_address`),
    INDEX `idx_login_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════ SEO METADATA TABLE ═══════
CREATE TABLE IF NOT EXISTS `seo_pages` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `page_slug` VARCHAR(150) NOT NULL UNIQUE,
    `meta_title` VARCHAR(200) DEFAULT NULL,
    `meta_description` TEXT DEFAULT NULL,
    `meta_keywords` VARCHAR(500) DEFAULT NULL,
    `og_image` VARCHAR(255) DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════ CACHE TABLE (DB-based fallback) ═══════
CREATE TABLE IF NOT EXISTS `cache_store` (
    `cache_key` VARCHAR(200) NOT NULL PRIMARY KEY,
    `cache_value` LONGTEXT NOT NULL,
    `expires_at` INT UNSIGNED NOT NULL,
    INDEX `idx_cache_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══════ UPDATE existing data status values ═══════
-- Convert old 'confirmed' status to new 'accepted'
UPDATE `bookings` SET `status` = 'accepted' WHERE `status` = 'confirmed';
UPDATE `booking_status_history` SET `status` = 'accepted' WHERE `status` = 'confirmed';
