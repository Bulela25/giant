-- =====================================================================
-- Giant Auto Upholstery - database schema
-- Import this file in phpMyAdmin (Import tab) or run it in the SQL tab.
-- It creates the database, all tables, and the service price list.
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `giant_upholstery`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `giant_upholstery`;

-- ---------------------------------------------------------------------
-- users: every registered customer
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `first_name`         VARCHAR(80)  NOT NULL,
  `last_name`          VARCHAR(80)  NOT NULL,
  `email`              VARCHAR(190) NOT NULL,
  `phone`              VARCHAR(20)  NOT NULL,
  `password_hash`      VARCHAR(255) NOT NULL,
  `marketing_opt_in`   TINYINT(1)   NOT NULL DEFAULT 0,
  `terms_accepted_at`  DATETIME     NOT NULL,
  `registration_ip`    VARCHAR(45)  DEFAULT NULL,
  `user_agent`         VARCHAR(255) DEFAULT NULL,
  `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login_at`      DATETIME     DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_users_email` (`email`),
  KEY `idx_users_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- services: the price list shown on the website
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `slug`        VARCHAR(80)  NOT NULL,
  `name`        VARCHAR(120) NOT NULL,
  `category`    ENUM('vehicle','home','custom') NOT NULL,
  `description` VARCHAR(400) NOT NULL,
  `price_from`  DECIMAL(10,2) DEFAULT NULL COMMENT 'NULL = quote on request',
  `sort_order`  SMALLINT NOT NULL DEFAULT 0,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_services_slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `services` (`slug`,`name`,`category`,`description`,`price_from`,`sort_order`) VALUES
('vehicle-seat-reupholstery','Vehicle Seat Reupholstery','vehicle',
 'Complete seat restoration including leather repair, foam replacement and custom stitching.',2500.00,1),
('dashboard-interior-trim','Dashboard & Interior Trim','vehicle',
 'Professional restoration of dashboards, door panels and vehicle interior trim.',3500.00,2),
('headliner-replacement','Headliner Replacement','vehicle',
 'Replace sagging, damaged or worn vehicle headliners with professional-quality materials.',1500.00,3),
('leather-sofa-repair','Leather Sofa Repair','home',
 'Repair torn, faded or cracked leather with professional colour matching and restoration.',1800.00,4),
('fabric-sofa-reupholstery','Fabric Sofa Reupholstery','home',
 'Full fabric replacement using a selection of durable and premium-quality upholstery fabrics.',2200.00,5),
('custom-upholstery','Custom Upholstery','custom',
 'Bespoke upholstery for classic cars, boats, custom furniture and specialist projects.',NULL,6)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- ---------------------------------------------------------------------
-- bookings: every repair request (guests and registered users)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `bookings` (
  `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference`          VARCHAR(20)  DEFAULT NULL,
  `user_id`            INT UNSIGNED DEFAULT NULL,
  `service_id`         INT UNSIGNED NOT NULL,
  `first_name`         VARCHAR(80)  NOT NULL,
  `last_name`          VARCHAR(80)  NOT NULL,
  `email`              VARCHAR(190) NOT NULL,
  `phone`              VARCHAR(20)  NOT NULL,
  `address`            VARCHAR(500) NOT NULL,
  `preferred_date`     DATE         NOT NULL,
  `preferred_time`     VARCHAR(40)  NOT NULL,
  `damage_description` TEXT         DEFAULT NULL,
  `status`             ENUM('pending','confirmed','completed','cancelled') NOT NULL DEFAULT 'pending',
  `consent_at`         DATETIME     NOT NULL,
  `ip_address`         VARCHAR(45)  DEFAULT NULL,
  `created_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`         DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_bookings_reference` (`reference`),
  KEY `idx_bookings_user` (`user_id`),
  KEY `idx_bookings_email` (`email`),
  KEY `idx_bookings_status` (`status`),
  CONSTRAINT `fk_bookings_user`    FOREIGN KEY (`user_id`)    REFERENCES `users` (`id`)    ON DELETE SET NULL,
  CONSTRAINT `fk_bookings_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- contact_messages: messages from the Contact page
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `full_name`   VARCHAR(160) NOT NULL,
  `email`       VARCHAR(190) NOT NULL,
  `subject`     VARCHAR(190) NOT NULL,
  `message`     TEXT         NOT NULL,
  `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_contact_email` (`email`),
  KEY `idx_contact_read` (`is_read`),
  CONSTRAINT `fk_contact_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- login_log: audit trail, also used to throttle repeated failed logins
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `login_log` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `email`       VARCHAR(190) NOT NULL,
  `success`     TINYINT(1)   NOT NULL,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `user_agent`  VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_login_email_time` (`email`,`created_at`),
  KEY `idx_login_ip_time` (`ip_address`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
