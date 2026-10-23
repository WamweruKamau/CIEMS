<?php
/**
 * Automated System Integration & Forensic Verification Test
 */

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/core/Database.php';
require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/IncidentCase.php';
require_once dirname(__DIR__) . '/models/Evidence.php';
require_once dirname(__DIR__) . '/models/ChainOfCustody.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';

echo "========================================================\n";
echo "   CIEMS AUTOMATED SYSTEM & FORENSIC INTEGRATION TEST   \n";
echo "========================================================\n\n";

$testsPassed = 0;
$totalTests = 0;

function assertTest($description, $condition) {
    global $testsPassed, $totalTests;
    $totalTests++;
    if ($condition) {
        echo "[PASS] $description\n";
        $testsPassed++;
    } else {
        echo "[FAIL] $description\n";
    }
}

// 1. Test Database
try {
    $pdo = Database::getConnection();
    assertTest("Database Connection (PDO / MySQL)", $pdo instanceof PDO);
} catch (Exception $e) {
    assertTest("Database Connection (PDO / MySQL)", false);
}

// 2. Test User Accounts & Password Hashing
$investigator = User::findByEmail('investigator@ciems.usiu.ac.ke');
assertTest("Investigator Account Exists", $investigator !== null && $investigator['role'] === 'investigator');

$custodian = User::findByEmail('custodian@ciems.usiu.ac.ke');
assertTest("Evidence Custodian Account Exists", $custodian !== null && $custodian['role'] === 'custodian');

$admin = User::findByEmail('admin@ciems.usiu.ac.ke');
assertTest("Admin Account Exists", $admin !== null && $admin['role'] === 'admin');

assertTest("Investigator Password Verification", password_verify('Investigator@2026!', $investigator['password_hash']));

// 3. Test Case Retrieval & Creation
$cases = IncidentCase::getAll();
assertTest("Incident Cases Retrieved", count($cases) > 0);

$testCaseId = IncidentCase::create([
    'title'                => 'Automated Test Breach - SQL Injection',
    'incident_type'        => 'unauthorized_access',
    'severity'             => 'critical',
    'status'               => 'open',
    'description'          => 'Automated test case simulating SQL injection exfiltration.',
    'lead_investigator_id' => $investigator['id']
]);
assertTest("Case Registration Functionality", $testCaseId > 0);

$createdCase = IncidentCase::findById($testCaseId);
assertTest("Case Number Auto-Generation Format", strpos($createdCase['case_number'], 'CIEMS-') === 0);

// 4. Test Evidence Ingestion & SHA-256 Hashing
$testPayload = "FORENSIC LOG CAPTURE - IP: 10.0.0.15 - MALICIOUS QUERY DETECTED AT " . date('Y-m-d H:i:s');
$computedSha256 = hash('sha256', $testPayload);

$testFilename = 'test_evidence_' . bin2hex(random_bytes(4)) . '.log';
$vaultPath = VAULT_PATH . DIRECTORY_SEPARATOR . $testFilename;
file_put_contents($vaultPath, $testPayload);

$evidenceId = Evidence::create([
    'case_id'           => $testCaseId,
    'evidence_number'   => Evidence::generateEvidenceNumber($createdCase['case_number']),
    'original_filename' => 'auth_server_access.log',
    'stored_filename'   => $testFilename,
    'file_path'         => 'storage/vault/' . $testFilename,
    'file_size_bytes'   => strlen($testPayload),
    'mime_type'         => 'text/plain',
    'file_extension'    => 'log',
    'source_device'     => 'SRV-AUTH-01 (10.0.0.15)',
    'acquired_at'       => date('Y-m-d H:i:s'),
    'sha256_hash'       => $computedSha256,
    'description'       => 'Automated test evidence payload.',
    'uploaded_by'       => $investigator['id'],
    'status'            => 'secured'
]);
assertTest("Evidence Artifact Ingestion", $evidenceId > 0);

// 5. Test Live Integrity Verification (Intact Match)
$verifyResult = Evidence::verifyIntegrity($evidenceId, $custodian['id'], 'Automated Test Verification');
assertTest("Cryptographic Integrity Verification (Hashes Match)", $verifyResult['matched'] === true && $verifyResult['integrity_status'] === 'intact');

// 6. Test Tamper Detection (Modify 1 byte in physical file)
file_put_contents($vaultPath, $testPayload . " [TAMPERED_BYTE]");
$tamperResult = Evidence::verifyIntegrity($evidenceId, $custodian['id'], 'Automated Tamper Simulation Check');
assertTest("Tamper Detection (Hash Mismatch Caught)", $tamperResult['matched'] === false && $tamperResult['integrity_status'] === 'compromised');

$tamperedEvidence = Evidence::findById($evidenceId);
assertTest("Evidence Status Updated to 'tampered'", $tamperedEvidence['status'] === 'tampered');

// Clean up test file and database test records
if (file_exists($vaultPath)) {
    unlink($vaultPath);
}
$pdo->exec("DELETE FROM chain_of_custody WHERE evidence_id = $evidenceId");
$pdo->exec("DELETE FROM evidence WHERE id = $evidenceId");
$pdo->exec("DELETE FROM cases WHERE id = $testCaseId");

// 7. Test Chain of Custody Ledger & Audit Trail
$cocRecords = ChainOfCustody::getByEvidenceId($evidenceId);
assertTest("Chain of Custody Multi-Event Logging (Ingested + 2 Verifications)", count($cocRecords) >= 3);

$auditCount = AuditLog::countAll();
assertTest("Master Compliance Audit Trail Active", $auditCount > 0);

echo "\n========================================================\n";
echo "SUMMARY: $testsPassed / $totalTests Tests Passed Successfully!\n";
echo "========================================================\n";

if ($testsPassed === $totalTests) {
    exit(0);
} else {
    exit(1);
}
