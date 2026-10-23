<?php
/**
 * AdminController
 * Oversees user administration, security parameters, and master compliance logs.
 */

require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/core/Session.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';
require_once dirname(__DIR__) . '/core/Database.php';

class AdminController {
    public function __construct() {
        Auth::requireRole('admin');
    }

    public function users(): void {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
                Session::setFlash('danger', 'Security validation failed.');
                header('Location: ' . BASE_URL . '/index.php?action=admin-users');
                exit;
            }

            $userId = (int)($_POST['user_id'] ?? 0);

            if ($userId > 0) {
                // Update user
                $data = [
                    'full_name'    => $_POST['full_name'] ?? '',
                    'badge_number' => $_POST['badge_number'] ?? '',
                    'role'         => $_POST['role'] ?? 'investigator',
                    'status'       => $_POST['status'] ?? 'active'
                ];
                if (!empty($_POST['password'])) {
                    $data['password'] = $_POST['password'];
                }

                User::update($userId, $data);
                AuditLog::record(Auth::id(), 'USER_UPDATED', 'users', $userId, "Updated user account ID #$userId");
                Session::setFlash('success', 'User profile successfully updated.');
            } else {
                // Create new user
                $email = trim($_POST['email'] ?? '');
                $password = trim($_POST['password'] ?? '');

                if (empty($email) || empty($password)) {
                    Session::setFlash('danger', 'Email and password are mandatory.');
                    header('Location: ' . BASE_URL . '/index.php?action=admin-users');
                    exit;
                }

                $newId = User::create([
                    'badge_number' => $_POST['badge_number'] ?? ('STAFF-' . rand(100, 999)),
                    'full_name'    => $_POST['full_name'] ?? 'New Analyst',
                    'email'        => $email,
                    'password'     => $password,
                    'role'         => $_POST['role'] ?? 'investigator',
                    'status'       => $_POST['status'] ?? 'active'
                ]);

                AuditLog::record(Auth::id(), 'USER_CREATED', 'users', $newId, "Created user account for $email");
                Session::setFlash('success', 'New system account created successfully.');
            }

            header('Location: ' . BASE_URL . '/index.php?action=admin-users');
            exit;
        }

        $users = User::getAll();
        require dirname(__DIR__) . '/views/admin/users.php';
    }

    public function auditLogs(): void {
        $module = $_GET['module'] ?? '';
        $search = $_GET['search'] ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $limit  = 30;
        $offset = ($page - 1) * $limit;

        $totalLogs = AuditLog::countAll($module ?: null, $search ?: null);
        $totalPages = ceil($totalLogs / $limit);
        $logs = AuditLog::getAll($limit, $offset, $module ?: null, $search ?: null);

        require dirname(__DIR__) . '/views/admin/audit_logs.php';
    }

    public function settings(): void {
        $pdo = Database::getConnection();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
                Session::setFlash('danger', 'Security validation failed.');
                header('Location: ' . BASE_URL . '/index.php?action=admin-settings');
                exit;
            }

            foreach ($_POST['settings'] ?? [] as $key => $val) {
                $stmt = $pdo->prepare("UPDATE system_settings SET setting_value = ? WHERE setting_key = ?");
                $stmt->execute([trim($val), $key]);
            }

            AuditLog::record(Auth::id(), 'SETTINGS_UPDATED', 'system', null, 'Administrative system parameters updated.');
            Session::setFlash('success', 'System parameters updated.');
            header('Location: ' . BASE_URL . '/index.php?action=admin-settings');
            exit;
        }

        $stmt = $pdo->query("SELECT * FROM system_settings");
        $settings = $stmt->fetchAll();

        require dirname(__DIR__) . '/views/admin/settings.php';
    }
}
