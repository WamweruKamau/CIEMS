<?php
/**
 * Single Entry Front Controller & Dispatcher
 * Cyber Incident Evidence Management System (CIEMS)
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Session.php';
require_once __DIR__ . '/core/Auth.php';

Session::start();

$action = $_GET['action'] ?? (Auth::check() ? 'dashboard' : 'login');

// Route Dispatcher
switch ($action) {
    // Auth Routes
    case 'login':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->showLogin();
        break;

    case 'login-submit':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->login();
        break;

    case 'logout':
        require_once __DIR__ . '/controllers/AuthController.php';
        (new AuthController())->logout();
        break;

    // Dashboard
    case 'dashboard':
        require_once __DIR__ . '/controllers/DashboardController.php';
        (new DashboardController())->index();
        break;

    // Case Management
    case 'cases':
        require_once __DIR__ . '/controllers/CaseController.php';
        (new CaseController())->index();
        break;

    case 'cases-create':
        require_once __DIR__ . '/controllers/CaseController.php';
        (new CaseController())->create();
        break;

    case 'cases-show':
        require_once __DIR__ . '/controllers/CaseController.php';
        (new CaseController())->show();
        break;

    case 'cases-edit':
        require_once __DIR__ . '/controllers/CaseController.php';
        (new CaseController())->edit();
        break;

    // Evidence Ingestion & Forensic Management
    case 'evidence':
        require_once __DIR__ . '/controllers/EvidenceController.php';
        (new EvidenceController())->index();
        break;

    case 'evidence-upload':
        require_once __DIR__ . '/controllers/EvidenceController.php';
        (new EvidenceController())->upload();
        break;

    case 'evidence-show':
        require_once __DIR__ . '/controllers/EvidenceController.php';
        (new EvidenceController())->show();
        break;

    case 'evidence-verify':
        require_once __DIR__ . '/controllers/EvidenceController.php';
        (new EvidenceController())->verify();
        break;

    case 'evidence-download':
        require_once __DIR__ . '/controllers/EvidenceController.php';
        (new EvidenceController())->download();
        break;

    // Chain of Custody & Reporting
    case 'custody-timeline':
        require_once __DIR__ . '/controllers/CustodyController.php';
        (new CustodyController())->timeline();
        break;

    case 'custody-certificate':
        require_once __DIR__ . '/controllers/CustodyController.php';
        (new CustodyController())->certificate();
        break;

    // Admin Routes
    case 'admin-users':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->users();
        break;

    case 'admin-audit':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->auditLogs();
        break;

    case 'admin-settings':
        require_once __DIR__ . '/controllers/AdminController.php';
        (new AdminController())->settings();
        break;

    default:
        http_response_code(404);
        require dirname(__DIR__) . '/ciems/views/layouts/404.php';
        break;
}
