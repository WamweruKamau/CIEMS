<?php
/**
 * CaseController
 * Manages incident investigation cases, assignments, and case reporting.
 */

require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/core/Session.php';
require_once dirname(__DIR__) . '/models/IncidentCase.php';
require_once dirname(__DIR__) . '/models/Evidence.php';
require_once dirname(__DIR__) . '/models/ChainOfCustody.php';
require_once dirname(__DIR__) . '/models/User.php';
require_once dirname(__DIR__) . '/models/AuditLog.php';

class CaseController {
    public function __construct() {
        Auth::requireLogin();
    }

    public function index(): void {
        $filters = [
            'status'   => $_GET['status'] ?? '',
            'severity' => $_GET['severity'] ?? '',
            'search'   => $_GET['search'] ?? ''
        ];

        // If investigator, provide option to filter their own
        if (Auth::role() === 'investigator' && isset($_GET['mine']) && $_GET['mine'] === '1') {
            $filters['lead_id'] = Auth::id();
        }

        $cases = IncidentCase::getAll($filters);
        $stats = IncidentCase::getStatistics();

        require dirname(__DIR__) . '/views/cases/index.php';
    }

    public function create(): void {
        Auth::requireRole(['admin', 'investigator']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
                Session::setFlash('danger', 'Security token invalid.');
                header('Location: ' . BASE_URL . '/index.php?action=cases-create');
                exit;
            }

            $title         = trim($_POST['title'] ?? '');
            $incidentType  = $_POST['incident_type'] ?? 'other';
            $severity      = $_POST['severity'] ?? 'medium';
            $description   = trim($_POST['description'] ?? '');
            $leadId        = (int)($_POST['lead_investigator_id'] ?? Auth::id());

            if (empty($title)) {
                Session::setFlash('danger', 'Please provide a valid case title.');
                header('Location: ' . BASE_URL . '/index.php?action=cases-create');
                exit;
            }

            $caseId = IncidentCase::create([
                'title'                => $title,
                'incident_type'        => $incidentType,
                'severity'             => $severity,
                'status'               => 'open',
                'description'          => $description,
                'lead_investigator_id' => $leadId
            ]);

            AuditLog::record(Auth::id(), 'CASE_CREATED', 'cases', $caseId, "Case #$caseId ($title) registered by " . Auth::user()['full_name']);
            Session::setFlash('success', 'Incident Case registered successfully.');
            header('Location: ' . BASE_URL . '/index.php?action=cases-show&id=' . $caseId);
            exit;
        }

        $investigators = User::getInvestigators();
        $generatedCaseNumber = IncidentCase::generateCaseNumber();
        require dirname(__DIR__) . '/views/cases/create.php';
    }

    public function show(): void {
        $id = (int)($_GET['id'] ?? 0);
        $case = IncidentCase::findById($id);

        if (!$case) {
            Session::setFlash('danger', 'Case not found.');
            header('Location: ' . BASE_URL . '/index.php?action=cases');
            exit;
        }

        $evidenceList = Evidence::getByCaseId($id);
        $custodyHistory = ChainOfCustody::getByCaseId($id);

        require dirname(__DIR__) . '/views/cases/show.php';
    }

    public function edit(): void {
        Auth::requireRole(['admin', 'investigator']);
        $id = (int)($_GET['id'] ?? 0);
        $case = IncidentCase::findById($id);

        if (!$case) {
            Session::setFlash('danger', 'Case not found.');
            header('Location: ' . BASE_URL . '/index.php?action=cases');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!Session::verifyCsrf($_POST['csrf_token'] ?? '')) {
                Session::setFlash('danger', 'Security token invalid.');
                header('Location: ' . BASE_URL . '/index.php?action=cases-edit&id=' . $id);
                exit;
            }

            $data = [
                'title'                => trim($_POST['title'] ?? ''),
                'incident_type'        => $_POST['incident_type'] ?? 'other',
                'severity'             => $_POST['severity'] ?? 'medium',
                'status'               => $_POST['status'] ?? 'open',
                'description'          => trim($_POST['description'] ?? ''),
                'lead_investigator_id' => (int)($_POST['lead_investigator_id'] ?? $case['lead_investigator_id'])
            ];

            IncidentCase::update($id, $data);
            AuditLog::record(Auth::id(), 'CASE_UPDATED', 'cases', $id, "Updated details for case {$case['case_number']}");

            Session::setFlash('success', 'Case details successfully updated.');
            header('Location: ' . BASE_URL . '/index.php?action=cases-show&id=' . $id);
            exit;
        }

        $investigators = User::getInvestigators();
        require dirname(__DIR__) . '/views/cases/edit.php';
    }
}
