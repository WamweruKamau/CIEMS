<?php
/**
 * EvidenceController
 * Orchestrates evidence ingestion, cryptographic SHA-256 hashing,
 * tamper-detection verification, and access-controlled streaming downloads.
 */

require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/core/Session.php';
require_once dirname(__DIR__) . '/models/Evidence.php';
require_once dirname(__DIR__) . '/models/IncidentCase.php';
require_once dirname(__DIR__) . '/models/ChainOfCustody.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';

class EvidenceController {
    public function __construct() {
        Auth::requireLogin();
    }

    public function index(): void {
        $filters = [
            'case_id'        => $_GET['case_id'] ?? '',
            'status'         => $_GET['status'] ?? '',
            'classification' => $_GET['classification'] ?? '',
            'search'         => $_GET['search'] ?? ''
        ];

        $evidenceList = Evidence::getAll($filters);
        $stats = Evidence::getStatistics();
        $cases = IncidentCase::getAll();

        require dirname(__DIR__) . '/views/evidence/index.php';
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $evidence = Evidence::findById($id);

        if (!$evidence) {
            Session::setFlash('danger', 'Evidence artifact not found.');
            header('Location: ' . BASE_URL . '/index.php?action=evidence');
            exit;
        }

        $custodyLedger = ChainOfCustody::getByEvidenceId($id);

        // Record viewing event in CoC
        AuditLog::record(Auth::id(), 'EVIDENCE_VIEWED', 'evidence', $id, "Viewed evidence metadata for {$evidence['evidence_number']}");

        require dirname(__DIR__) . '/views/evidence/show.php';
    }

    public function upload(): void {
        Auth::requireRole(['admin', 'investigator']);

        $caseId = (int)($_GET['case_id'] ?? ($_POST['case_id'] ?? 0));
        $case = IncidentCase::findById($caseId);

        if (!$case) {
            Session::setFlash('danger', 'Valid incident case must be specified for evidence upload.');
            header('Location: ' . BASE_URL . '/index.php?action=cases');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
                Session::setFlash('danger', 'Security token invalid.');
                header('Location: ' . BASE_URL . '/index.php?action=evidence-upload&case_id=' . $caseId);
                exit;
            }

            if (!isset($_FILES['evidence_file']) || $_FILES['evidence_file']['error'] !== UPLOAD_ERR_OK) {
                $errCode = $_FILES['evidence_file']['error'] ?? 'missing';
                Session::setFlash('danger', 'File upload error (Code: ' . htmlspecialchars($errCode) . '). Please verify file size.');
                header('Location: ' . BASE_URL . '/index.php?action=evidence-upload&case_id=' . $caseId);
                exit;
            }

            $file = $_FILES['evidence_file'];
            $origName = basename($file['name']);
            $extension = strtolower(pathinfo($origName, PATHINFO_EXTENSION));

            if (!in_array($extension, ALLOWED_EXTENSIONS, true)) {
                Session::setFlash('danger', "File extension '.$extension' is not permitted in forensic repository.");
                header('Location: ' . BASE_URL . '/index.php?action=evidence-upload&case_id=' . $caseId);
                exit;
            }

            if ($file['size'] > MAX_FILE_SIZE_BYTES) {
                Session::setFlash('danger', 'File exceeds maximum permissible ingestion size (500MB).');
                header('Location: ' . BASE_URL . '/index.php?action=evidence-upload&case_id=' . $caseId);
                exit;
            }

            $sourceDevice = trim($_POST['source_device'] ?? 'Unknown Source');
            $acquiredAt   = !empty($_POST['acquired_at']) ? $_POST['acquired_at'] : date('Y-m-d H:i:s');
            $description  = trim($_POST['description'] ?? '');
            
            $classificationLevel = $_POST['classification_level'] ?? 'confidential';
            $validLevels = ['top_secret', 'secret', 'confidential', 'restricted', 'unclassified'];
            if (!in_array($classificationLevel, $validLevels, true)) {
                $classificationLevel = 'confidential';
            }

            // Ensure vault directory exists
            if (!is_dir(VAULT_PATH)) {
                mkdir(VAULT_PATH, 0755, true);
            }

            // Cryptographic Hashing directly from temp upload file
            $tmpPath = $file['tmp_name'];
            $sha256Hash = hash_file('sha256', $tmpPath);
            $md5Hash    = hash_file('md5', $tmpPath);

            // Generate opaque stored filename
            $storedFilename = sprintf('vault_%s_%s.%s', date('Ymd_His'), bin2hex(random_bytes(8)), $extension);
            $destinationPath = VAULT_PATH . DIRECTORY_SEPARATOR . $storedFilename;
            $relativeFilePath = 'storage/vault/' . $storedFilename;

            if (!move_uploaded_file($tmpPath, $destinationPath)) {
                Session::setFlash('danger', 'Failed to securely transfer artifact to forensic vault storage.');
                header('Location: ' . BASE_URL . '/index.php?action=evidence-upload&case_id=' . $caseId);
                exit;
            }

            $evidenceNumber = Evidence::generateEvidenceNumber($case['case_number']);

            $evidenceId = Evidence::create([
                'case_id'           => $caseId,
                'evidence_number'   => $evidenceNumber,
                'original_filename' => $origName,
                'stored_filename'   => $storedFilename,
                'file_path'         => $relativeFilePath,
                'file_size_bytes'   => $file['size'],
                'mime_type'         => mime_content_type($destinationPath) ?: 'application/octet-stream',
                'file_extension'    => $extension,
                'source_device'     => $sourceDevice,
                'acquired_at'       => $acquiredAt,
                'sha256_hash'       => $sha256Hash,
                'md5_hash'          => $md5Hash,
                'description'          => $description,
                'uploaded_by'          => Auth::id(),
                'classification_level' => $classificationLevel,
                'status'               => 'secured'
            ]);

            Session::setFlash('success', "Forensic artifact {$evidenceNumber} ingested successfully. SHA-256: {$sha256Hash}");
            header('Location: ' . BASE_URL . '/index.php?action=evidence-show&id=' . $evidenceId);
            exit;
        }

        require dirname(__DIR__) . '/views/evidence/upload.php';
    }

    public function verify(): void {
        Auth::requireRole(['admin', 'custodian']);

        $id = (int)($_POST['evidence_id'] ?? ($_GET['id'] ?? 0));
        $reason = trim($_POST['verification_reason'] ?? 'Routine Custodial Integrity Audit');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
                Session::setFlash('danger', 'Security token invalid.');
                header('Location: ' . BASE_URL . '/index.php?action=evidence-show&id=' . $id);
                exit;
            }

            $result = Evidence::verifyIntegrity($id, Auth::id(), $reason);

            if ($result['matched']) {
                Session::setFlash('success', "Integrity Verified Intact! SHA-256 Checksum: {$result['computed_hash']}");
            } else {
                Session::setFlash('danger', "CRITICAL WARNING: Hash Mismatch Detected! Possible evidence tampering. Status set to Compromised.");
            }

            header('Location: ' . BASE_URL . '/index.php?action=evidence-show&id=' . $id);
            exit;
        }

        $evidence = Evidence::findById($id);
        require dirname(__DIR__) . '/views/evidence/verify.php';
    }

    public function download(): void {
        $id = (int)($_GET['id'] ?? 0);
        $evidence = Evidence::findById($id);

        if (!$evidence) {
            die("Evidence artifact not found.");
        }

        $reason = trim($_GET['reason'] ?? 'Forensic Analysis / Chain of Custody Transfer');

        $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $evidence['file_path']);

        if (!file_exists($fullPath)) {
            AuditLog::record(Auth::id(), 'EVIDENCE_DOWNLOAD_FAILED_MISSING', 'evidence', $id, 'Attempted download of missing file.');
            die("Artifact file not present in forensic vault.");
        }

        // Record download in Chain of Custody
        ChainOfCustody::record([
            'evidence_id'       => $id,
            'case_id'           => (int)$evidence['case_id'],
            'performed_by'      => Auth::id(),
            'action_type'       => 'downloaded',
            'reason_or_purpose' => $reason,
            'verification_hash' => $evidence['sha256_hash'],
            'integrity_status'  => ($evidence['status'] === 'tampered' ? 'compromised' : 'intact')
        ]);

        AuditLog::record(Auth::id(), 'EVIDENCE_DOWNLOADED', 'evidence', $id, "Downloaded file {$evidence['original_filename']} ({$evidence['evidence_number']})");

        // Clear output buffer
        if (ob_get_level()) {
            ob_end_clean();
        }

        // Send hardened streaming headers
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . rawurlencode($evidence['original_filename']) . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        header('X-Content-Type-Options: nosniff');

        readfile($fullPath);
        exit;
    }
}
