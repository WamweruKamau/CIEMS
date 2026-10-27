<?php
/**
 * Database Migration / Schema Updater
 * Cyber Incident Evidence Management System (CIEMS)
 * Adds the Security Classification / Confidentiality Level column
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/Database.php';

echo "=== CIEMS Database Migration ===\n";

try {
    $pdo = Database::getConnection();
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM `evidence` LIKE 'classification_level'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $pdo->exec("
            ALTER TABLE `evidence` 
            ADD COLUMN `classification_level` ENUM('top_secret', 'secret', 'confidential', 'restricted', 'unclassified') 
            NOT NULL DEFAULT 'confidential' 
            COMMENT 'Security classification clearance level' 
            AFTER `description`
        ");
        echo "[✓] Added column 'classification_level' to table 'evidence'.\n";
    } else {
        echo "[✓] Column 'classification_level' already exists in 'evidence'.\n";
    }

    // Ensure sample evidence has 'secret' classification
    $pdo->exec("UPDATE `evidence` SET `classification_level` = 'secret' WHERE `evidence_number` = 'EVID-2026-001-A'");
    echo "[✓] Updated existing evidence artifacts to 'secret' clearance.\n";

    echo "=== Migration Complete! ===\n";
} catch (Exception $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
}
