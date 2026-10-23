<?php
/**
 * AuditLog Model
 * Handles immutable system logging and administrative oversight.
 */

require_once dirname(__DIR__) . '/core/Database.php';

class AuditLog {
    public static function record(?int $userId, string $action, string $module, ?int $recordId = null, ?string $details = null): bool {
        try {
            $pdo = Database::getConnection();
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'CLI/Internal';

            $stmt = $pdo->prepare("
                INSERT INTO audit_logs (user_id, action, target_module, record_id, details, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            return $stmt->execute([
                $userId,
                $action,
                $module,
                $recordId,
                $details,
                $ip,
                substr($ua, 0, 250)
            ]);
        } catch (Exception $e) {
            // Fail silently or log to error log to avoid breaking core flow if audit fails
            error_log("Audit logging error: " . $e->getMessage());
            return false;
        }
    }

    public static function getAll(int $limit = 100, int $offset = 0, ?string $module = null, ?string $search = null): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT a.*, u.full_name as user_full_name, u.role as user_role, u.badge_number
            FROM audit_logs a
            LEFT JOIN users u ON a.user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($module)) {
            $sql .= " AND a.target_module = ?";
            $params[] = $module;
        }

        if (!empty($search)) {
            $sql .= " AND (a.action LIKE ? OR a.details LIKE ? OR a.ip_address LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY a.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countAll(?string $module = null, ?string $search = null): int {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) as total FROM audit_logs a WHERE 1=1";
        $params = [];

        if (!empty($module)) {
            $sql .= " AND a.target_module = ?";
            $params[] = $module;
        }
        if (!empty($search)) {
            $sql .= " AND (a.action LIKE ? OR a.details LIKE ? OR a.ip_address LIKE ?)";
            $term = "%$search%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch();
        return (int)($res['total'] ?? 0);
    }
}
