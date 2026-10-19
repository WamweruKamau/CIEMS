<?php
/**
 * DashboardController
 * Delivers tailored role-based telemetry and quick-action command centers.
 */

require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/models/IncidentCase.php';
require_once dirname(__DIR__) . '/models/Evidence.php';
require_once dirname(__DIR__) . '/models/ChainOfCustody.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';

class DashboardController {
    public function __construct() {
        Auth::requireLogin();
    }

    public function index(): void {
        $role = Auth::role();
        $caseStats = IncidentCase::getStatistics();
        $evidenceStats = Evidence::getStatistics();
        $recentCustody = ChainOfCustody::getRecent(6);
        $recentCases = IncidentCase::getAll(['status' => '']);
        $recentCases = array_slice($recentCases, 0, 5);

        $totalUsers = User::countAll();
        $totalCustodyEvents = ChainOfCustody::countAll();

        require dirname(__DIR__) . '/views/dashboard/index.php';
    }
}
