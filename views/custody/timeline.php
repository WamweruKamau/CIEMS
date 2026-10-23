<?php
$pageTitle = 'Chain of Custody Timeline';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-link text-info me-2"></i> Chain of Custody Timeline
        </h4>
        <small class="text-muted">
            <?php if (!empty($evidence)): ?>
                Chronological ledger for Artifact: <strong><?= htmlspecialchars($evidence['evidence_number']) ?></strong> (<?= htmlspecialchars($evidence['original_filename']) ?>)
            <?php elseif (!empty($case)): ?>
                Chronological ledger for Case: <strong><?= htmlspecialchars($case['case_number']) ?></strong> (<?= htmlspecialchars($case['title']) ?>)
            <?php else: ?>
                Global system-wide custody transactions
            <?php endif; ?>
        </small>
    </div>

    <div>
        <?php if (!empty($evidence)): ?>
            <a href="<?= BASE_URL ?>/index.php?action=custody-certificate&evidence_id=<?= $evidence['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm">
                <i class="fa-solid fa-print me-1"></i> Print Legal Certificate
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-4">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-timeline display-4 mb-3 text-secondary"></i>
                <h5>No custody transactions recorded</h5>
            </div>
        <?php else: ?>
            <div class="timeline-container position-relative ps-4 ps-md-5">
                <div class="timeline-line position-absolute top-0 bottom-0 start-0 border-start border-3 border-primary ms-3 ms-md-4"></div>

                <?php foreach ($logs as $index => $log): ?>
                    <div class="timeline-item position-relative mb-4 pb-2">
                        <!-- Timeline Bullet Node -->
                        <div class="position-absolute top-0 start-0 translate-middle rounded-circle bg-navy text-white d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 34px; height: 34px; margin-left: -1px;">
                            <?php if ($log['action_type'] === 'ingested'): ?>
                                <i class="fa-solid fa-cloud-arrow-up text-cyan small"></i>
                            <?php elseif ($log['action_type'] === 'verified'): ?>
                                <i class="fa-solid fa-fingerprint text-success small"></i>
                            <?php elseif ($log['action_type'] === 'downloaded'): ?>
                                <i class="fa-solid fa-download text-warning small"></i>
                            <?php elseif ($log['action_type'] === 'exported'): ?>
                                <i class="fa-solid fa-file-pdf text-info small"></i>
                            <?php else: ?>
                                <i class="fa-solid fa-eye text-light small"></i>
                            <?php endif; ?>
                        </div>

                        <!-- Card Body for Event -->
                        <div class="card border-0 bg-light rounded-3 shadow-xs ms-3">
                            <div class="card-body p-3">
                                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-dark font-monospace text-uppercase">
                                            <?= htmlspecialchars($log['action_type']) ?>
                                        </span>
                                        <strong class="text-dark small">
                                            Artifact: <?= htmlspecialchars($log['evidence_number']) ?>
                                        </strong>
                                    </div>
                                    <span class="text-muted font-monospace small">
                                        <i class="fa-regular fa-clock me-1"></i> <?= date('Y-m-d H:i:s', strtotime($log['recorded_at'])) ?>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <span class="text-secondary small fw-semibold">Handler / Custodian:</span>
                                    <span class="text-dark small fw-bold"><?= htmlspecialchars($log['handler_name']) ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($log['handler_badge']) ?>
                                    </span>
                                </div>

                                <div class="mb-2">
                                    <span class="text-secondary small fw-semibold">Justification / Action Notes:</span>
                                    <span class="text-dark small"><?= htmlspecialchars($log['reason_or_purpose']) ?></span>
                                </div>

                                <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top small">
                                    <div>
                                        <?php if ($log['integrity_status'] === 'intact'): ?>
                                            <span class="badge bg-success-subtle text-success border border-success">
                                                <i class="fa-solid fa-circle-check me-1"></i> Cryptographically Intact
                                            </span>
                                        <?php elseif ($log['integrity_status'] === 'compromised'): ?>
                                            <span class="badge bg-danger text-white">
                                                <i class="fa-solid fa-triangle-exclamation me-1"></i> Integrity Compromised
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="text-muted font-monospace" style="font-size: 0.75rem;">
                                        Client IP: <?= htmlspecialchars($log['client_ip']) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
