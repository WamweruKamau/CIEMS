<?php
/**
 * IncidentCase Model
 */

require_once dirname(__DIR__) . '/core/Database.php';

class IncidentCase {
    public static function getAll(array $filters = []): array {
        $pdo = Database::getConnection();
        $sql = "
            SELECT c.*, u.full_name as lead_name, u.badge_number as lead_badge,
                   (SELECT COUNT(*) FROM evidence e WHERE e.case_id = c.id) as evidence_count
            FROM cases c
            JOIN users u ON c.lead_investigator_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND c.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['severity'])) {
            $sql .= " AND c.severity = ?";
            $params[] = $filters['severity'];
        }
        if (!empty($filters['lead_id'])) {
            $sql .= " AND c.lead_investigator_id = ?";
            $params[] = (int)$filters['lead_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (c.case_number LIKE ? OR c.title LIKE ? OR c.description LIKE ?)";
            $term = "%" . $filters['search'] . "%";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $sql .= " ORDER BY c.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT c.*, u.full_name as lead_name, u.badge_number as lead_badge, u.email as lead_email
            FROM cases c
            JOIN users u ON c.lead_investigator_id = u.id
            WHERE c.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function generateCaseNumber(): string {
        $pdo = Database::getConnection();
        $year = date('Y');
        $prefix = "CIEMS-$year-";
        $stmt = $pdo->prepare("SELECT case_number FROM cases WHERE case_number LIKE ? ORDER BY id DESC LIMIT 1");
        $stmt->execute(["$prefix%"]);
        $last = $stmt->fetch();

        if ($last) {
            $parts = explode('-', $last['case_number']);
            $num = (int)end($parts) + 1;
        } else {
            $num = 1;
        }
        return sprintf("CIEMS-%s-%03d", $year, $num);
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $caseNumber = !empty($data['case_number']) ? trim($data['case_number']) : self::generateCaseNumber();

        $stmt = $pdo->prepare("
            INSERT INTO cases (case_number, title, description, incident_type, severity, status, lead_investigator_id)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $caseNumber,
            trim($data['title']),
            trim($data['description'] ?? ''),
            $data['incident_type'],
            $data['severity'],
            $data['status'] ?? 'open',
            (int)$data['lead_investigator_id']
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("
            UPDATE cases
            SET title = ?, description = ?, incident_type = ?, severity = ?, status = ?, lead_investigator_id = ?
            WHERE id = ?
        ");
        return $stmt->execute([
            trim($data['title']),
            trim($data['description'] ?? ''),
            $data['incident_type'],
            $data['severity'],
            $data['status'],
            (int)$data['lead_investigator_id'],
            $id
        ]);
    }

    public static function getStatistics(): array {
        $pdo = Database::getConnection();
        $stats = [
            'total'       => 0,
            'open'        => 0,
            'in_progress' => 0,
            'closed'      => 0,
            'critical'    => 0,
        ];

        $stmt = $pdo->query("SELECT status, COUNT(*) as count FROM cases GROUP BY status");
        while ($row = $stmt->fetch()) {
            $stats['total'] += (int)$row['count'];
            if (isset($stats[$row['status']])) {
                $stats[$row['status']] = (int)$row['count'];
            }
        }

        $critStmt = $pdo->query("SELECT COUNT(*) as c FROM cases WHERE severity = 'critical' AND status != 'closed'");
        $stats['critical'] = (int)$critStmt->fetch()['c'];

        return $stats;
    }
}
