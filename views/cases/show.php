<?php
$pageTitle = 'Case Dossier: ' . $case['case_number'];
require dirname(__DIR__) . '/layouts/header.php';
$role = Auth::role();
?>

<!-- Case Header Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-dark font-monospace fs-6 px-3 py-2">
                        <?= htmlspecialchars($case['case_number']) ?>
                    </span>
                    <span class="badge badge-severity-<?= htmlspecialchars($case['severity']) ?> px-3 py-2 text-uppercase">
                        <?= htmlspecialchars($case['severity']) ?> Severity
                    </span>
                    <span class="badge badge-status-<?= htmlspecialchars($case['status']) ?> px-3 py-2 text-uppercase">
                        <?= str_replace('_', ' ', htmlspecialchars($case['status'])) ?>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-1"><?= htmlspecialchars($case['title']) ?></h3>
                <div class="text-muted small d-flex flex-wrap gap-3 mt-2">
                    <span>
                        <i class="fa-solid fa-shield-virus me-1 text-primary"></i>
                        Type: <strong class="text-dark text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($case['incident_type'])) ?></strong>
                    </span>
                    <span>
                        <i class="fa-solid fa-user-ninja me-1 text-primary"></i>
                        Lead: <strong class="text-dark"><?= htmlspecialchars($case['lead_name']) ?></strong> (<?= htmlspecialchars($case['lead_badge']) ?>)
                    </span>
                    <span>
                        <i class="fa-regular fa-clock me-1 text-primary"></i>
                        Reported: <strong class="text-dark"><?= date('d M Y, H:i', strtotime($case['created_at'])) ?></strong>
                    </span>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="d-flex flex-wrap gap-2">
                <?php if ($role === 'investigator' || $role === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/index.php?action=evidence-upload&case_id=<?= $case['id'] ?>" class="btn btn-primary btn-sm px-3 shadow-sm">
                        <i class="fa-solid fa-upload me-1"></i> Ingest Evidence
                    </a>
                    <a href="<?= BASE_URL ?>/index.php?action=cases-edit&id=<?= $case['id'] ?>" class="btn btn-outline-secondary btn-sm px-3">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Edit Dossier
                    </a>
                <?php endif; ?>
                <a href="<?= BASE_URL ?>/index.php?action=custody-timeline&case_id=<?= $case['id'] ?>" class="btn btn-outline-dark btn-sm px-3">
                    <i class="fa-solid fa-timeline me-1"></i> Custody Audit
                </a>
            </div>
        </div>

        <?php if (!empty($case['description'])): ?>
            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-secondary text-uppercase small mb-2" style="letter-spacing: 0.05em;">Incident Narrative & Scope:</h6>
                <p class="text-dark mb-0 bg-light p-3 rounded-2 text-pre-line" style="font-size: 0.92rem; line-height: 1.6;">
                    <?= nl2br(htmlspecialchars($case['description'])) ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Evidence Artifacts Section -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa-solid fa-box-archive text-success me-2"></i> Ingested Forensic Evidence Artifacts
            </h5>
            <small class="text-muted"><?= count($evidenceList) ?> cryptographic artifacts preserved in vault</small>
        </div>
        <?php if ($role === 'investigator' || $role === 'admin'): ?>
            <a href="<?= BASE_URL ?>/index.php?action=evidence-upload&case_id=<?= $case['id'] ?>" class="btn btn-sm btn-outline-primary">
                <i class="fa-solid fa-plus me-1"></i> Add Artifact
            </a>
        <?php endif; ?>
    </div>

    <div class="card-body p-0">
        <?php if (empty($evidenceList)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-box-open display-5 mb-3 text-secondary"></i>
                <h6>No evidence artifacts ingested for this case yet.</h6>
                <p class="small text-muted mb-3">Upload disk images, PCAPs, memory dumps, or log files to begin chain of custody.</p>
                <?php if ($role === 'investigator' || $role === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/index.php?action=evidence-upload&case_id=<?= $case['id'] ?>" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-upload me-1"></i> Ingest First Artifact
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th class="ps-3">Evidence ID</th>
                            <th>Original Filename</th>
                            <th>Origin Device</th>
                            <th>Size</th>
                            <th>SHA-256 Checksum Fingerprint</th>
                            <th>Integrity Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($evidenceList as $ev): ?>
                            <tr>
                                <td class="ps-3">
                                    <a href="<?= BASE_URL ?>/index.php?action=evidence-show&id=<?= $ev['id'] ?>" class="font-monospace fw-bold text-decoration-none">
                                        <?= htmlspecialchars($ev['evidence_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= htmlspecialchars($ev['original_filename']) ?></div>
                                    <small class="text-muted font-monospace"><?= strtoupper(htmlspecialchars($ev['file_extension'])) ?> • <?= htmlspecialchars($ev['mime_type']) ?></small>
                                </td>
                                <td>
                                    <span class="small text-dark"><?= htmlspecialchars($ev['source_device']) ?></span>
                                </td>
                                <td class="font-monospace small">
                                    <?= number_format($ev['file_size_bytes'] / 1024, 2) ?> KB
                                </td>
                                <td>
                                    <span class="font-monospace small text-primary bg-light px-2 py-1 rounded d-inline-block text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($ev['sha256_hash']) ?>">
                                        <?= htmlspecialchars($ev['sha256_hash']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($ev['status'] === 'tampered'): ?>
                                        <span class="badge bg-danger text-uppercase px-2 py-1">
                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Compromised
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success-subtle text-success border border-success text-uppercase px-2 py-1">
                                            <i class="fa-solid fa-check me-1"></i> Secured
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?action=evidence-show&id=<?= $ev['id'] ?>" class="btn btn-outline-secondary" title="View Details">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($role === 'custodian' || $role === 'admin'): ?>
                                            <a href="<?= BASE_URL ?>/index.php?action=evidence-verify&id=<?= $ev['id'] ?>" class="btn btn-outline-info" title="Verify Checksum">
                                                <i class="fa-solid fa-fingerprint"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/index.php?action=evidence-download&id=<?= $ev['id'] ?>" class="btn btn-outline-success" title="Secure Download">
                                            <i class="fa-solid fa-download"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chain of Custody Activity Log -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-bold text-dark">
            <i class="fa-solid fa-link text-info me-2"></i> Case Chain of Custody Ledger
        </h5>
        <a href="<?= BASE_URL ?>/index.php?action=custody-timeline&case_id=<?= $case['id'] ?>" class="btn btn-sm btn-link text-decoration-none">
            Detailed Timeline &rarr;
        </a>
    </div>
    <div class="card-body p-0">
        <?php if (empty($custodyHistory)): ?>
            <div class="text-center py-4 text-muted">No custody transactions recorded yet.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th class="ps-3">Timestamp</th>
                            <th>Artifact</th>
                            <th>Action</th>
                            <th>Handler / Custodian</th>
                            <th>Purpose / Justification</th>
                            <th>Integrity Check</th>
                            <th class="pe-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($custodyHistory, 0, 10) as $coc): ?>
                            <tr>
                                <td class="ps-3 font-monospace text-muted">
                                    <?= date('Y-m-d H:i:s', strtotime($coc['recorded_at'])) ?>
                                </td>
                                <td class="font-monospace fw-semibold">
                                    <?= htmlspecialchars($coc['evidence_number']) ?>
                                </td>
                                <td>
                                    <span class="badge bg-dark font-monospace text-uppercase">
                                        <?= htmlspecialchars($coc['action_type']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?= htmlspecialchars($coc['handler_name']) ?>
                                    <span class="text-muted font-monospace">(<?= htmlspecialchars($coc['handler_badge']) ?>)</span>
                                </td>
                                <td>
                                    <span class="text-dark"><?= htmlspecialchars($coc['reason_or_purpose']) ?></span>
                                </td>
                                <td>
                                    <?php if ($coc['integrity_status'] === 'intact'): ?>
                                        <span class="text-success fw-semibold"><i class="fa-solid fa-circle-check"></i> Intact</span>
                                    <?php elseif ($coc['integrity_status'] === 'compromised'): ?>
                                        <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> Compromised</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="pe-3 font-monospace text-muted">
                                    <?= htmlspecialchars($coc['client_ip']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
