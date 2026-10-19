<?php
/**
 * User Model
 */

require_once dirname(__DIR__) . '/core/Database.php';

class User {
    public static function findById(int $id): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT id, badge_number, full_name, email, role, status, last_login, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([trim($email)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getAll(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, badge_number, full_name, email, role, status, last_login, created_at FROM users ORDER BY created_at DESC");
        return $stmt->fetchAll();
    }

    public static function getInvestigators(): array {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT id, badge_number, full_name, email FROM users WHERE role = 'investigator' AND status = 'active' ORDER BY full_name ASC");
        return $stmt->fetchAll();
    }

    public static function create(array $data): int {
        $pdo = Database::getConnection();
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("
            INSERT INTO users (badge_number, full_name, email, password_hash, role, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            trim($data['badge_number']),
            trim($data['full_name']),
            trim($data['email']),
            $hash,
            $data['role'],
            $data['status'] ?? 'active'
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function update(int $id, array $data): bool {
        $pdo = Database::getConnection();
        $fields = [];
        $params = [];

        if (isset($data['full_name'])) {
            $fields[] = "full_name = ?";
            $params[] = trim($data['full_name']);
        }
        if (isset($data['badge_number'])) {
            $fields[] = "badge_number = ?";
            $params[] = trim($data['badge_number']);
        }
        if (isset($data['role'])) {
            $fields[] = "role = ?";
            $params[] = $data['role'];
        }
        if (isset($data['status'])) {
            $fields[] = "status = ?";
            $params[] = $data['status'];
        }
        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public static function countAll(): int {
        $pdo = Database::getConnection();
        $stmt = $pdo->query("SELECT COUNT(*) as c FROM users");
        return (int)$stmt->fetch()['c'];
    }
}
