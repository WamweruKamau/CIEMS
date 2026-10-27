<?php
/**
 * Evidence Model
 * Preserves digital forensic artifacts, calculates cryptographic hashes,
 * and executes on-demand integrity verification.
 */

require_once dirname(__DIR__) . '/core/Database.php';
require_once __DIR__ . '/ChainOfCustody.php';
require_once __DIR__ . '/AuditLog.php';

class Evidence {
    /**
     * Self-healing schema validation: ensures classification_level column exists in MySQL.
     */
    public static function ensureClassificationColumn(): void {
        static $checked = false;
        if ($checked) return;
        $checked = true;
        try {
            $pdo = Database::getConnection();
            $stmt = $pdo->query("SHOW COLUMNS FROM `evidence` LIKE 'classification_level'");
            if (!$stmt->fetch()) {
                $pdo->exec("
                    ALTER TABLE `evidence` 
                    ADD COLUMN `classification_level` ENUM('top_secret', 'secret', 'confidential', 'restricted', 'unclassified') 
                    NOT NULL DEFAULT 'confidential' 
                    COMMENT 'Security classification clearance level' 
                    AFTER `description`
                ");
                $pdo->exec("UPDATE `evidence` SET `classification_level` = 'secret' WHERE `evidence_number` = 'EVID-2026-001-A'");
            }
        } catch (\Throwable $e) {
            // Silently allow if permissions or already altered
        }
    }

    public static function getAll(array $filters = []): array {
        self::ensureClassificationColumn();
        $pdo = Database::getConnection();
        $sql = "
            SELECT e.*, c.case_number, c.title as case_title, u.full_name as uploader_name, u.badge_number as uploader_badge
            FROM evidence e
            JOIN cases c ON e.case_id = c.id
            JOIN users u ON e.uploaded_by = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['case_id'])) {
            $sql .= " AND e.case_id = ?";
            $params[] = (int)$filters['case_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['classification'])) {
            $sql .= " AND e.classification_level = ?";
            $params[] = $filters['classification'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (e.evidence_number LIKE ? OR e.original_filename LIKE ? OR e.description LIKE ? OR e.sha256_hash LIKE ?)";
            $term = "%" . $filters['search'] . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY e.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT e.*, c.case_number, c.title as case_title, c.status as case_status,
                   u.full_name as uploader_name, u.badge_number as uploader_badge
            FROM evidence e
            JOIN cases c ON e.case_id = c.id
            JOIN users u ON e.uploaded_by = u.id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getByCaseId(int $caseId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT e.*, u.full_name as uploader_name, u.badge_number as uploader_badge
            FROM evidence e
            JOIN users u ON e.uploaded_by = u.id
            WHERE e.case_id = ?
            ORDER BY e.created_at ASC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }

    public static function generateEvidenceNumber(string $caseNumber): string {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM evidence e JOIN cases c ON e.case_id = c.id WHERE c.case_number = ?");
        $stmt->execute([$caseNumber]);
        $cnt = (int)$stmt->fetch()['cnt'];
        $letter = chr(65 + ($cnt % 26)); // A, B, C...
        $suffix = ($cnt >= 26) ? '-' . floor($cnt / 26) : '';
        return sprintf("EVID-%s-%s%s", str_replace('CIEMS-', '', $caseNumber), $letter, $suffix);
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            INSERT INTO evidence (
                case_id, evidence_number, original_filename, stored_filename, file_path,
                file_size_bytes, mime_type, file_extension, source_device, acquired_at,
                sha256_hash, md5_hash, description, uploaded_by, classification_level, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['case_id'],
            $data['evidence_number'],
            $data['original_filename'],
            $data['stored_filename'],
            $data['file_path'],
            (int)$data['file_size_bytes'],
            $data['mime_type'],
            $data['file_extension'],
            $data['source_device'],
            $data['acquired_at'],
            $data['sha256_hash'],
            $data['md5_hash'] ?? null,
            $data['description'] ?? '',
            (int)$data['uploaded_by'],
            $data['classification_level'] ?? 'confidential',
            $data['status'] ?? 'secured'
        ]);

        $evidenceId = (int)$pdo->lastInsertId();

        // Automatically record initial ingestion into Chain of Custody
        ChainOfCustody::record([
            'evidence_id'       => $evidenceId,
            'case_id'           => (int)$data['case_id'],
            'performed_by'      => (int)$data['uploaded_by'],
            'action_type'       => 'ingested',
            'reason_or_purpose' => 'Initial evidence acquisition and secure ingestion into vault.',
            'verification_hash' => $data['sha256_hash'],
            'integrity_status'  => 'intact'
        ]);

        // Record in Master Audit Log
        AuditLog::record(
            (int)$data['uploaded_by'],
            'EVIDENCE_INGESTED',
            'evidence',
            $evidenceId,
            "Artifact {$data['evidence_number']} ({$data['original_filename']}) ingested with SHA-256: {$data['sha256_hash']}"
        );

        return $evidenceId;
    }

    public static function updateStatus(int $id, string $status): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE evidence SET status = ? WHERE id = ?");
        return $stmt->execute([$status, $id]);
    }

    /**
     * Re-calculates cryptographic hash of physical artifact on disk
     * and performs forensic comparison against the baseline stored hash.
     */
    public static function verifyIntegrity(int $id, int $performedBy, string $reason): array {
        $evidence = self::findById($id);
        if (!$evidence) {
            return ['success' => false, 'message' => 'Evidence record not found.'];
        }

        $fullPath = BASE_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $evidence['file_path']);

        if (!file_exists($fullPath)) {
            $result = [
                'success'          => false,
                'matched'          => false,
                'integrity_status' => 'compromised',
                'error'            => 'Physical file is missing from vault!',
                'stored_hash'      => $evidence['sha256_hash'],
                'computed_hash'    => null
            ];

            self::updateStatus($id, 'tampered');
            ChainOfCustody::record([
                'evidence_id'       => $id,
                'case_id'           => (int)$evidence['case_id'],
                'performed_by'      => $performedBy,
                'action_type'       => 'verified',
                'reason_or_purpose' => $reason . ' [FAILED: Missing file]',
                'verification_hash' => null,
                'integrity_status'  => 'compromised'
            ]);
            AuditLog::record($performedBy, 'INTEGRITY_CHECK_FAILED_MISSING', 'evidence', $id, 'Evidence file missing from vault!');
            return $result;
        }

        // Recalculate SHA-256 hash of physical file
        $computedSha256 = hash_file('sha256', $fullPath);
        $matched = hash_equals(strtolower($evidence['sha256_hash']), strtolower($computedSha256));
        $integrityStatus = $matched ? 'intact' : 'compromised';

        if (!$matched) {
            self::updateStatus($id, 'tampered');
            AuditLog::record(
                $performedBy,
                'INTEGRITY_CHECK_FAILED_MISMATCH',
                'evidence',
                $id,
                "CRITICAL: Cryptographic hash mismatch! Baseline: {$evidence['sha256_hash']}, Computed: {$computedSha256}"
            );
        } else {
            AuditLog::record(
                $performedBy,
                'INTEGRITY_CHECK_VERIFIED',
                'evidence',
                $id,
                "Integrity verified intact: SHA-256 matches baseline ({$computedSha256})"
            );
        }

        // Record in Chain of Custody
        ChainOfCustody::record([
            'evidence_id'       => $id,
            'case_id'           => (int)$evidence['case_id'],
            'performed_by'      => $performedBy,
            'action_type'       => 'verified',
            'reason_or_purpose' => $reason,
            'verification_hash' => $computedSha256,
            'integrity_status'  => $integrityStatus
        ]);

        return [
            'success'          => true,
            'matched'          => $matched,
            'integrity_status' => $integrityStatus,
            'stored_hash'      => $evidence['sha256_hash'],
            'computed_hash'    => $computedSha256
        ];
    }

    public static function getStatistics(): array {
        $pdo = Database::getConnection();
        $stats = [
            'total'       => 0,
            'secured'     => 0,
            'tampered'    => 0,
            'in_analysis' => 0,
            'total_bytes' => 0
        ];

        $stmt = $pdo->query("SELECT status, COUNT(*) as c, SUM(file_size_bytes) as bytes FROM evidence GROUP BY status");
        while ($row = $stmt->fetch()) {
            $stats['total'] += (int)$row['c'];
            $stats['total_bytes'] += (int)($row['bytes'] ?? 0);
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = (int)$row['c'];
            }
        }
        return $stats;
    }
}
