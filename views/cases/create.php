<?php
$pageTitle = 'Register Incident Case';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-folder-plus text-primary me-2"></i> Register New Incident Case
                </h4>
                <small class="text-muted">Initiate forensic incident investigation dossier</small>
            </div>
            <a href="<?= BASE_URL ?>/index.php?action=cases" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Cases
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/index.php?action=cases-create" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">
                                System Case Number (Auto-Generated)
                            </label>
                            <input type="text" class="form-control font-monospace bg-light fw-bold" name="case_number"
                                value="<?= htmlspecialchars($generatedCaseNumber) ?>" readonly>
                            <div class="form-text">Follows standardized forensic case nomenclature.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="lead_investigator_id" class="form-label small fw-semibold text-secondary">
                                Lead Forensic Investigator <span class="text-danger">*</span>
                            </label>
                            <select name="lead_investigator_id" id="lead_investigator_id" class="form-select" required>
                                <?php foreach ($investigators as $inv): ?>
                                    <option value="<?= $inv['id'] ?>" <?= $inv['id'] == Auth::id() ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($inv['full_name']) ?> (<?= htmlspecialchars($inv['badge_number']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label small fw-semibold text-secondary">
                            Incident Title / Headline <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" required
                            placeholder="e.g. Ransomware Outbreak on Production Database Server PRD-SQL-02">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="incident_type" class="form-label small fw-semibold text-secondary">
                                Threat Classification / Incident Type <span class="text-danger">*</span>
                            </label>
                            <select name="incident_type" id="incident_type" class="form-select" required>
                                <option value="malware">Malware Infection</option>
                                <option value="ransomware">Ransomware Encryption</option>
                                <option value="data_breach">Data Exfiltration / Breach</option>
                                <option value="phishing">Credential Phishing</option>
                                <option value="unauthorized_access">Unauthorized Privileged Access</option>
                                <option value="denial_of_service">Denial of Service (DoS/DDoS)</option>
                                <option value="other">Other Forensic Investigation</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="severity" class="form-label small fw-semibold text-secondary">
                                Threat Severity Assessment <span class="text-danger">*</span>
                            </label>
                            <select name="severity" id="severity" class="form-select" required>
                                <option value="low">Low (Minimal Operational Impact)</option>
                                <option value="medium" selected>Medium (Localized Compromise)</option>
                                <option value="high">High (Mission-Critical Systems Affected)</option>
                                <option value="critical">Critical (Catastrophic Breach / Enterprise-wide)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label small fw-semibold text-secondary">
                            Incident Executive Summary & Initial Indicators of Compromise (IoCs)
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="5"
                            placeholder="Detail initial discovery timeline, affected IP addresses/hostnames, compromised user accounts, and scope of triage..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/index.php?action=cases" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="fa-solid fa-check me-1"></i> Create Case Dossier
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
