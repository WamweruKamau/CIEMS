<?php
$pageTitle = 'Edit Case Dossier: ' . $case['case_number'];
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Update Case: <?= htmlspecialchars($case['case_number']) ?>
                </h4>
                <small class="text-muted">Modify incident classification, lifecycle status, or lead investigator</small>
            </div>
            <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $case['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Dossier
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/index.php?action=cases-edit&id=<?= $case['id'] ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Case Identifier</label>
                            <input type="text" class="form-control font-monospace bg-light" value="<?= htmlspecialchars($case['case_number']) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label small fw-semibold text-secondary">
                                Investigation Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="open" <?= $case['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                                <option value="in_progress" <?= $case['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                <option value="under_review" <?= $case['status'] === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                                <option value="closed" <?= $case['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                                <option value="archived" <?= $case['status'] === 'archived' ? 'selected' : '' ?>>Archived</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label small fw-semibold text-secondary">
                            Incident Title <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="title" name="title" required value="<?= htmlspecialchars($case['title']) ?>">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="incident_type" class="form-label small fw-semibold text-secondary">
                                Threat Classification <span class="text-danger">*</span>
                            </label>
                            <select name="incident_type" id="incident_type" class="form-select" required>
                                <option value="malware" <?= $case['incident_type'] === 'malware' ? 'selected' : '' ?>>Malware Infection</option>
                                <option value="ransomware" <?= $case['incident_type'] === 'ransomware' ? 'selected' : '' ?>>Ransomware Encryption</option>
                                <option value="data_breach" <?= $case['incident_type'] === 'data_breach' ? 'selected' : '' ?>>Data Exfiltration / Breach</option>
                                <option value="phishing" <?= $case['incident_type'] === 'phishing' ? 'selected' : '' ?>>Credential Phishing</option>
                                <option value="unauthorized_access" <?= $case['incident_type'] === 'unauthorized_access' ? 'selected' : '' ?>>Unauthorized Privileged Access</option>
                                <option value="denial_of_service" <?= $case['incident_type'] === 'denial_of_service' ? 'selected' : '' ?>>Denial of Service</option>
                                <option value="other" <?= $case['incident_type'] === 'other' ? 'selected' : '' ?>>Other Forensic Investigation</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="severity" class="form-label small fw-semibold text-secondary">
                                Threat Severity <span class="text-danger">*</span>
                            </label>
                            <select name="severity" id="severity" class="form-select" required>
                                <option value="low" <?= $case['severity'] === 'low' ? 'selected' : '' ?>>Low</option>
                                <option value="medium" <?= $case['severity'] === 'medium' ? 'selected' : '' ?>>Medium</option>
                                <option value="high" <?= $case['severity'] === 'high' ? 'selected' : '' ?>>High</option>
                                <option value="critical" <?= $case['severity'] === 'critical' ? 'selected' : '' ?>>Critical</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="lead_investigator_id" class="form-label small fw-semibold text-secondary">
                                Lead Investigator <span class="text-danger">*</span>
                            </label>
                            <select name="lead_investigator_id" id="lead_investigator_id" class="form-select" required>
                                <?php foreach ($investigators as $inv): ?>
                                    <option value="<?= $inv['id'] ?>" <?= $inv['id'] == $case['lead_investigator_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($inv['full_name']) ?> (<?= htmlspecialchars($inv['badge_number']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label small fw-semibold text-secondary">Incident Narrative & Findings</label>
                        <textarea class="form-control" id="description" name="description" rows="5"><?= htmlspecialchars($case['description']) ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $case['id'] ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="fa-solid fa-save me-1"></i> Save Updates
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
