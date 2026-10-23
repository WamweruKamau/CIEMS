<?php
/**
 * CustodyController
 * Manages Chain of Custody tracking, chronological timeline auditing,
 * and official court-admissible forensic certificate exports.
 */

require_once dirname(__DIR__) . '/core/Auth.php';
require_once dirname(__DIR__) . '/models/ChainOfCustody.php';
require_once dirname(__DIR__) . '/models/Evidence.php';
require_once dirname(__DIR__) . '/models/IncidentCase.php';

class CustodyController {
    public function __construct() {
        Auth::requireLogin();
    }

    public function timeline(): void {
        $caseId = (int)($_GET['case_id'] ?? 0);
        $evidenceId = (int)($_GET['evidence_id'] ?? 0);

        if ($evidenceId > 0) {
            $evidence = Evidence::findById($evidenceId);
            $logs = ChainOfCustody::getByEvidenceId($evidenceId);
            $case = IncidentCase::findById($evidence['case_id'] ?? 0);
        } elseif ($caseId > 0) {
            $evidence = null;
            $case = IncidentCase::findById($caseId);
            $logs = ChainOfCustody::getByCaseId($caseId);
        } else {
            $evidence = null;
            $case = null;
            $logs = ChainOfCustody::getRecent(50);
        }

        require dirname(__DIR__) . '/views/custody/timeline.php';
    }

    public function certificate(): void {
        $evidenceId = (int)($_GET['evidence_id'] ?? 0);
        $evidence = Evidence::findById($evidenceId);

        if (!$evidence) {
            die("Evidence artifact not found.");
        }

        $case = IncidentCase::findById($evidence['case_id']);
        $custodyLedger = ChainOfCustody::getByEvidenceId($evidenceId);

        // Record export event
        ChainOfCustody::record([
            'evidence_id'       => $evidenceId,
            'case_id'           => (int)$evidence['case_id'],
            'performed_by'      => Auth::id(),
            'action_type'       => 'exported',
            'reason_or_purpose' => 'Exported official Chain of Custody Forensic Certificate for judicial/audit review.',
            'verification_hash' => $evidence['sha256_hash'],
            'integrity_status'  => ($evidence['status'] === 'tampered' ? 'compromised' : 'intact')
        ]);

        AuditLog::record(Auth::id(), 'COC_REPORT_EXPORTED', 'custody', $evidenceId, "Exported Chain of Custody Certificate for {$evidence['evidence_number']}");

        require dirname(__DIR__) . '/views/custody/certificate.php';
    }
}
