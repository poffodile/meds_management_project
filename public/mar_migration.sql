-- ============================================================
-- MAR Sheets Migration SQL
-- Run this in phpMyAdmin on your live server (careoneos.ltd)
-- ============================================================

-- Step 1: Create mar_sheets table
CREATE TABLE IF NOT EXISTS `mar_sheets` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `home_id` INT UNSIGNED NOT NULL,
    `client_id` INT UNSIGNED NOT NULL,
    `medication_name` VARCHAR(255) NOT NULL,
    `dosage` VARCHAR(100) NULL,
    `dose` VARCHAR(100) NULL,
    `route` VARCHAR(100) NULL,
    `frequency` VARCHAR(255) NULL,
    `time_slots` JSON NULL,
    `as_required` TINYINT(1) NOT NULL DEFAULT 0,
    `prn_details` TEXT NULL,
    `reason_for_medication` TEXT NULL,
    `prescribed_by` VARCHAR(255) NULL,
    `prescriber` VARCHAR(255) NULL,
    `pharmacy` VARCHAR(255) NULL,
    `start_date` DATE NULL,
    `end_date` DATE NULL,
    `stock_level` INT UNSIGNED NULL,
    `reorder_level` INT UNSIGNED NULL,
    `quantity_received` INT UNSIGNED NULL,
    `quantity_carried_forward` INT UNSIGNED NULL,
    `quantity_returned` INT UNSIGNED NULL,
    `storage_requirements` TEXT NULL,
    `allergies_warnings` TEXT NULL,
    `mar_status` VARCHAR(20) NOT NULL DEFAULT 'active',
    `discontinued` TINYINT(1) NOT NULL DEFAULT 0,
    `discontinued_date` DATE NULL,
    `discontinued_reason` TEXT NULL,
    `last_audited` DATE NULL,
    `created_by` INT UNSIGNED NOT NULL,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    INDEX `mar_sheets_home_id_index` (`home_id`),
    INDEX `mar_sheets_client_id_index` (`client_id`),
    INDEX `mar_sheets_mar_status_index` (`mar_status`),
    INDEX `mar_sheets_is_deleted_index` (`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Create mar_administrations table
CREATE TABLE IF NOT EXISTS `mar_administrations` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `mar_sheet_id` BIGINT UNSIGNED NOT NULL,
    `home_id` INT UNSIGNED NOT NULL,
    `date` DATE NOT NULL,
    `time_slot` VARCHAR(10) NOT NULL,
    `given` TINYINT(1) NOT NULL DEFAULT 0,
    `dose_given` VARCHAR(100) NULL,
    `administered_by` INT UNSIGNED NOT NULL,
    `witnessed_by` VARCHAR(255) NULL,
    `code` VARCHAR(5) NOT NULL,
    `notes` TEXT NULL,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    INDEX `mar_administrations_mar_sheet_id_index` (`mar_sheet_id`),
    INDEX `mar_administrations_home_id_index` (`home_id`),
    INDEX `mar_administrations_date_index` (`date`),
    INDEX `mar_administrations_administered_by_index` (`administered_by`),
    INDEX `mar_administrations_compound_index` (`mar_sheet_id`, `date`, `time_slot`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Mark these migrations as run (so artisan doesn't try to run them again)
INSERT IGNORE INTO `migrations` (`migration`, `batch`)
VALUES
    ('2026_04_23_100000_create_mar_sheets_tables', 99),
    ('2026_04_25_100000_add_stock_tracking_to_mar_sheets', 99);

-- Done! The MAR grid should now work.
