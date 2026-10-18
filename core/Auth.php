<?php
/**
 * Role-Based Access Control (RBAC) & Authentication Engine
 */

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Session.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';

class Auth {
    public static function check(): bool {
        return Session::get('user_id') !== null;
    }

    public static function user(): ?array {
        if (!self::check()) {
            return null;
        }
        return [
            'id'           => Session::get('user_id'),
            'badge_number' => Session::get('user_badge'),
            'full_name'    => Session::get('user_name'),
            'email'        => Session::get('user_email'),
            'role'         => Session::get('user_role'),
        ];
    }

    public static function id(): ?int {
        return Session::get('user_id');
    }

    public static function role(): ?string {
        return Session::get('user_role');
    }

    public static function hasRole($roles): bool {
        if (!self::check()) {
            return false;
        }
        $currentRole = self::role();
        if (is_array($roles)) {
            return in_array($currentRole, $roles, true);
        }
        return $currentRole === $roles;
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            Session::setFlash('warning', 'Please sign in to access this forensic workspace.');
            header('Location: ' . BASE_URL . '/index.php?action=login');
            exit;
        }
    }

    public static function requireRole($roles): void {
        self::requireLogin();
        if (!self::hasRole($roles)) {
            AuditLog::record(
                self::id(),
                'ACCESS_DENIED_UNAUTHORIZED_ROLE',
                'auth',
                null,
                'Unauthorized access attempt to restricted endpoint. Required: ' . (is_array($roles) ? implode(',', $roles) : $roles)
            );
            Session::setFlash('danger', 'Access Denied: You do not have the required operational permissions.');
            header('Location: ' . BASE_URL . '/index.php?action=dashboard');
            exit;
        }
    }

    public static function attempt(string $email, string $password): bool {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([trim($email)]);
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'active' && password_verify($password, $user['password_hash'])) {
            // Regenerate session to counter fixation
            Session::regenerate();

            Session::set('user_id', (int)$user['id']);
            Session::set('user_badge', $user['badge_number']);
            Session::set('user_name', $user['full_name']);
            Session::set('user_email', $user['email']);
            Session::set('user_role', $user['role']);

            // Update last_login
            $update = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $update->execute([$user['id']]);

            // Record in audit log
            AuditLog::record($user['id'], 'AUTH_LOGIN_SUCCESS', 'auth', $user['id'], 'User signed in successfully.');

            return true;
        }

        // Record failed attempt
        AuditLog::record(null, 'AUTH_LOGIN_FAILED', 'auth', null, 'Failed login attempt for email: ' . htmlspecialchars($email));
        return false;
    }

    public static function logout(): void {
        if (self::check()) {
            AuditLog::record(self::id(), 'AUTH_LOGOUT', 'auth', self::id(), 'User signed out.');
        }
        Session::destroy();
    }
}
