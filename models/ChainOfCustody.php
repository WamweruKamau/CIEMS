<?php
/**
 * ChainOfCustody Model
 * Manages the immutable audit ledger tracking every physical/logical
 * access, verification, and transfer of digital forensic artifacts.
 */

require_once dirname(__DIR__) . '/core/Database.php';

class ChainOfCustody {
    public static function record(array $data): int {
        $pdo = Database::getConnection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Internal';

        $stmt = $pdo->prepare("
            INSERT INTO chain_of_custody (
                evidence_id, case_id, performed_by, action_type, reason_or_purpose,
                verification_hash, integrity_status, client_ip, user_agent
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            (int)$data['evidence_id'],
            (int)$data['case_id'],
            (int)$data['performed_by'],
            $data['action_type'],
            trim($data['reason_or_purpose']),
            $data['verification_hash'] ?? null,
            $data['integrity_status'] ?? 'not_applicable',
            $ip,
            substr($ua, 0, 250)
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function getByEvidenceId(int $evidenceId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT coc.*, u.full_name as handler_name, u.badge_number as handler_badge, u.role as handler_role,
                   e.evidence_number, e.original_filename
            FROM chain_of_custody coc
            JOIN users u ON coc.performed_by = u.id
            JOIN evidence e ON coc.evidence_id = e.id
            WHERE coc.evidence_id = ?
            ORDER BY coc.recorded_at ASC
        ");
        $stmt->execute([$evidenceId]);
        return $stmt->fetchAll();
    }

    public static function getByCaseId(int $caseId): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT coc.*, u.full_name as handler_name, u.badge_number as handler_badge, u.role as handler_role,
                   e.evidence_number, e.original_filename
            FROM chain_of_custody coc
            JOIN users u ON coc.performed_by = u.id
            JOIN evidence e ON coc.evidence_id = e.id
            WHERE coc.case_id = ?
            ORDER BY coc.recorded_at DESC
        ");
        $stmt->execute([$caseId]);
        return $stmt->fetchAll();
    }

    public static function getRecent(int $limit = 10): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT coc.*, u.full_name as handler_name, u.badge_number as handler_badge,
                   e.evidence_number, e.original_filename, c.case_number
            FROM chain_of_custody coc
            JOIN users u ON coc.performed_by = u.id
            JOIN evidence e ON coc.evidence_id = e.id
            JOIN cases c ON coc.case_id = c.id
            ORDER BY coc.recorded_at DESC
            LIMIT " . (int)$limit
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function countAll(): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) as c FROM chain_of_custody");
        return (int)$stmt->fetch()['c'];
    }
}
