-- ====================================================================
-- CYBER INCIDENT EVIDENCE MANAGEMENT SYSTEM (CIEMS)
-- Database Initialization & Schema Script
-- Developed for IST4900 Systems Design and Planning
-- Compatible with MySQL 5.7+ / MariaDB 10.4+ (InnoDB Engine)
-- ====================================================================

CREATE DATABASE IF NOT EXISTS `ciems_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `ciems_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `chain_of_custody`;
DROP TABLE IF EXISTS `evidence`;
DROP TABLE IF EXISTS `cases`;
DROP TABLE IF EXISTS `system_settings`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------------------
-- 1. Table: users
-- Stores investigator, custodian, and administrative credentials.
-- Passwords must be hashed using bcrypt (PASSWORD_BCRYPT).
-- --------------------------------------------------------------------
CREATE TABLE `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `badge_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Forensic analyst / officer badge or staff ID',
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(191) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role` ENUM('admin', 'investigator', 'custodian') NOT NULL DEFAULT 'investigator',
    `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
    `last_login` DATETIME NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_users_role` (`role`),
    INDEX `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 2. Table: cases
-- Central record for cyber incident investigations.
-- --------------------------------------------------------------------
CREATE TABLE `cases` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `case_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Standardized case ID e.g. CIEMS-2026-001',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NULL,
    `incident_type` ENUM('malware', 'ransomware', 'data_breach', 'phishing', 'unauthorized_access', 'denial_of_service', 'other') NOT NULL DEFAULT 'other',
    `severity` ENUM('low', 'medium', 'high', 'critical') NOT NULL DEFAULT 'medium',
    `status` ENUM('open', 'in_progress', 'under_review', 'closed', 'archived') NOT NULL DEFAULT 'open',
    `lead_investigator_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`lead_investigator_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_cases_status` (`status`),
    INDEX `idx_cases_severity` (`severity`),
    INDEX `idx_cases_lead` (`lead_investigator_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. Table: evidence
-- Catalog of digital forensic artifacts and baseline cryptographic hashes.
-- --------------------------------------------------------------------
CREATE TABLE `evidence` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `case_id` INT NOT NULL,
    `evidence_number` VARCHAR(50) NOT NULL UNIQUE COMMENT 'Unique identifier e.g. EVID-2026-001-A',
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL UNIQUE COMMENT 'Opaque secure vault filename (UUID)',
    `file_path` VARCHAR(500) NOT NULL COMMENT 'Vault relative path',
    `file_size_bytes` BIGINT NOT NULL DEFAULT 0,
    `mime_type` VARCHAR(150) NOT NULL DEFAULT 'application/octet-stream',
    `file_extension` VARCHAR(20) NOT NULL,
    `source_device` VARCHAR(255) NOT NULL COMMENT 'Origin workstation, server hostname, or IP',
    `acquired_at` DATETIME NOT NULL COMMENT 'Timestamp when artifact was seized/acquired',
    `sha256_hash` CHAR(64) NOT NULL COMMENT 'Baseline SHA-256 cryptographic digest',
    `md5_hash` CHAR(32) NULL COMMENT 'Secondary cross-check MD5 digest',
    `description` TEXT NULL,
    `uploaded_by` INT NOT NULL,
    `status` ENUM('secured', 'in_analysis', 'tampered', 'archived') NOT NULL DEFAULT 'secured',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_evidence_case` (`case_id`),
    INDEX `idx_evidence_hash` (`sha256_hash`),
    INDEX `idx_evidence_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 4. Table: chain_of_custody
-- Immutable forensic custody log. Append-only table.
-- Tracks every custody transfer, verification, download, and review.
-- --------------------------------------------------------------------
CREATE TABLE `chain_of_custody` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `evidence_id` INT NOT NULL,
    `case_id` INT NOT NULL,
    `performed_by` INT NOT NULL,
    `action_type` ENUM('ingested', 'viewed', 'downloaded', 'verified', 'transferred', 'status_changed', 'exported') NOT NULL,
    `reason_or_purpose` VARCHAR(500) NOT NULL,
    `verification_hash` CHAR(64) NULL COMMENT 'Computed hash at verification event',
    `integrity_status` ENUM('intact', 'compromised', 'not_applicable') NOT NULL DEFAULT 'not_applicable',
    `client_ip` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL,
    `recorded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`evidence_id`) REFERENCES `evidence` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`case_id`) REFERENCES `cases` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    FOREIGN KEY (`performed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX `idx_coc_evidence` (`evidence_id`),
    INDEX `idx_coc_case` (`case_id`),
    INDEX `idx_coc_action` (`action_type`),
    INDEX `idx_coc_timestamp` (`recorded_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. Table: audit_logs
-- Master system audit trail for compliance, forensic accountability,
-- and administrative oversight.
-- --------------------------------------------------------------------
CREATE TABLE `audit_logs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `action` VARCHAR(100) NOT NULL,
    `target_module` VARCHAR(50) NOT NULL COMMENT 'auth, cases, evidence, custody, users, system',
    `record_id` INT NULL,
    `details` TEXT NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX `idx_audit_user` (`user_id`),
    INDEX `idx_audit_module` (`target_module`),
    INDEX `idx_audit_action` (`action`),
    INDEX `idx_audit_time` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 6. Table: system_settings
-- System-level operational parameters and forensic policies.
-- --------------------------------------------------------------------
CREATE TABLE `system_settings` (
    `setting_key` VARCHAR(50) PRIMARY KEY,
    `setting_value` TEXT NOT NULL,
    `description` VARCHAR(255) NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `system_settings` (`setting_key`, `setting_value`, `description`) VALUES
('organization_name', 'Cyber Defense & Forensics Laboratory (USIU-Africa)', 'Institutional legal entity name for reports'),
('retention_period_days', '3650', 'Mandatory evidence retention duration in days (10 years)'),
('hash_algorithm', 'SHA-256', 'Primary cryptographic digest algorithm'),
('require_download_reason', '1', 'Enforce mandatory justification prompt before evidence downloads'),
('max_upload_size_mb', '2048', 'System upload capacity limit in Megabytes');
