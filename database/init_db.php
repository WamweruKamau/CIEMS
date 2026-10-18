<?php
/**
 * Database Initializer & Seeder for CIEMS
 * Run via CLI: php database/init_db.php
 * Or accessible via web during setup.
 */

$host = '127.0.0.1';
$user = 'root';
$pass = '';
$dbname = 'ciems_db';

echo "=== CIEMS Database Initialization & Seeding ===\n";

try {
    // 1. Connect to MySQL server
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "[✓] Connected to MySQL Server.\n";

    // 2. Read and execute schema
    $schemaFile = __DIR__ . '/schema.sql';
    if (!file_exists($schemaFile)) {
        throw new Exception("schema.sql not found at $schemaFile");
    }

    $schemaSql = file_get_contents($schemaFile);
    $pdo->exec($schemaSql);
    echo "[✓] Database 'ciems_db' and tables created successfully.\n";

    // Connect to the specific database
    $pdo->exec("USE `$dbname`");

    // 3. Prepare Seed Users
    $adminPass = password_hash('Admin@2026!', PASSWORD_BCRYPT);
    $invPass   = password_hash('Investigator@2026!', PASSWORD_BCRYPT);
    $custPass  = password_hash('Custodian@2026!', PASSWORD_BCRYPT);

    $stmt = $pdo->prepare("INSERT INTO `users` (`badge_number`, `full_name`, `email`, `password_hash`, `role`, `status`) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Admin
    $stmt->execute(['ADM-001', 'Prof. Justus Nyamweya (System Admin)', 'admin@ciems.usiu.ac.ke', $adminPass, 'admin', 'active']);
    $adminId = $pdo->lastInsertId();

    // Investigator (Student)
    $stmt->execute(['INV-669925', 'Kamau Margaret Wamweru (Lead Investigator)', 'investigator@ciems.usiu.ac.ke', $invPass, 'investigator', 'active']);
    $invId = $pdo->lastInsertId();

    // Evidence Custodian
    $stmt->execute(['CST-201', 'Michael Ochieng (Evidence Custodian)', 'custodian@ciems.usiu.ac.ke', $custPass, 'custodian', 'active']);
    $custId = $pdo->lastInsertId();

    echo "[✓] 3 Core User Accounts Seeded:\n";
    echo "    - Admin:        admin@ciems.usiu.ac.ke / Admin@2026!\n";
    echo "    - Investigator: investigator@ciems.usiu.ac.ke / Investigator@2026!\n";
    echo "    - Custodian:    custodian@ciems.usiu.ac.ke / Custodian@2026!\n";

    // 4. Seed Demonstration Case
    $caseStmt = $pdo->prepare("INSERT INTO `cases` (`case_number`, `title`, `description`, `incident_type`, `severity`, `status`, `lead_investigator_id`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $caseStmt->execute([
        'CIEMS-2026-001',
        'Ransomware Attack on Financial Server FIN-SRV-01',
        'High-severity cyber incident involving suspicious PowerShell execution and unauthorized encryption of shared accounting volumes on internal host FIN-SRV-01. Network isolation initiated.',
        'ransomware',
        'high',
        'in_progress',
        $invId
    ]);
    $caseId = $pdo->lastInsertId();
    echo "[✓] Seeded Initial Investigation Case: CIEMS-2026-001\n";

    // 5. Seed a Sample Evidence File inside Storage Vault
    $vaultDir = dirname(__DIR__) . '/storage/vault';
    if (!is_dir($vaultDir)) {
        mkdir($vaultDir, 0755, true);
    }

    // Write a sample evidence payload file
    $sampleArtifactContent = "--- FORENSIC PCAP SAMPLE CAPTURE ---\n"
        . "Timestamp: 2026-10-05 14:22:18 UTC\n"
        . "Source IP: 192.168.10.45:49152\n"
        . "Destination IP: 198.51.100.23:443\n"
        . "Protocol: TLS 1.3 C2 Beaconing Detected\n"
        . "Payload Signature: Win64.LockBit.Payload.Drop\n"
        . "Integrity Stamp: VERIFIED-FORENSIC-IMAGE-USIU-2026\n";

    $sampleStoredFilename = 'sample_artifact_' . bin2hex(random_bytes(8)) . '.bin';
    $sampleFilePath = $vaultDir . '/' . $sampleStoredFilename;
    file_put_contents($sampleFilePath, $sampleArtifactContent);

    $sha256 = hash('sha256', $sampleArtifactContent);
    $md5 = hash('md5', $sampleArtifactContent);
    $fileSize = strlen($sampleArtifactContent);

    $evidStmt = $pdo->prepare("INSERT INTO `evidence` (`case_id`, `evidence_number`, `original_filename`, `stored_filename`, `file_path`, `file_size_bytes`, `mime_type`, `file_extension`, `source_device`, `acquired_at`, `sha256_hash`, `md5_hash`, `description`, `uploaded_by`, `status`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $evidStmt->execute([
        $caseId,
        'EVID-2026-001-A',
        'fin_srv01_suspicious_beacon.pcap',
        $sampleStoredFilename,
        'storage/vault/' . $sampleStoredFilename,
        $fileSize,
        'application/vnd.tcpdump.pcap',
        'pcap',
        'FIN-SRV-01 (Gateway T-Tap)',
        '2026-10-05 14:30:00',
        $sha256,
        $md5,
        'Network traffic capture exhibiting suspicious TLS beaconing to external IP address during incident detection.',
        $invId,
        'secured'
    ]);
    $evidId = $pdo->lastInsertId();
    echo "[✓] Seeded Sample Forensic Evidence: EVID-2026-001-A (SHA-256: $sha256)\n";

    // 6. Seed Initial Chain of Custody Log
    $cocStmt = $pdo->prepare("INSERT INTO `chain_of_custody` (`evidence_id`, `case_id`, `performed_by`, `action_type`, `reason_or_purpose`, `verification_hash`, `integrity_status`, `client_ip`, `user_agent`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $cocStmt->execute([
        $evidId,
        $caseId,
        $invId,
        'ingested',
        'Initial evidence acquisition and secure ingestion into CIEMS vault.',
        $sha256,
        'intact',
        '127.0.0.1',
        'Mozilla/5.0 (Windows NT 10.0; Win64; x64) CIEMS Forensics'
    ]);
    echo "[✓] Seeded Initial Chain of Custody Record.\n";

    // 7. Seed Initial System Audit Log
    $auditStmt = $pdo->prepare("INSERT INTO `audit_logs` (`user_id`, `action`, `target_module`, `record_id`, `details`, `ip_address`, `user_agent`) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $auditStmt->execute([
        $adminId,
        'SYSTEM_INITIALIZED',
        'system',
        1,
        'CIEMS system schema deployed and initial parameters configured.',
        '127.0.0.1',
        'CIEMS Database Initializer'
    ]);
    $auditStmt->execute([
        $invId,
        'CASE_CREATED',
        'cases',
        $caseId,
        'Incident case CIEMS-2026-001 registered.',
        '127.0.0.1',
        'CIEMS Database Initializer'
    ]);
    $auditStmt->execute([
        $invId,
        'EVIDENCE_INGESTED',
        'evidence',
        $evidId,
        'Evidence artifact EVID-2026-001-A uploaded with baseline SHA-256 recorded.',
        '127.0.0.1',
        'CIEMS Database Initializer'
    ]);
    echo "[✓] Seeded Initial Audit Trail.\n";

    echo "\n=== Database Initialization Complete! ===\n";
} catch (Exception $e) {
    echo "[ERROR] Database setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
