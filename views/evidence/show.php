<?php
$pageTitle = 'Artifact Dossier: ' . $evidence['evidence_number'];
require dirname(__DIR__) . '/layouts/header.php';
$role = Auth::role();
?>

<!-- Evidence Header Banner -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge bg-dark font-monospace fs-6 px-3 py-2">
                        <?= htmlspecialchars($evidence['evidence_number']) ?>
                    </span>
                    <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $evidence['case_id'] ?>" class="badge bg-primary text-decoration-none px-3 py-2">
                        <i class="fa-solid fa-folder me-1"></i> <?= htmlspecialchars($evidence['case_number']) ?>
                    </a>
                    <?= getClassificationBadge($evidence['classification_level'] ?? 'confidential', true) ?>
                    <?php if ($evidence['status'] === 'tampered'): ?>
                        <span class="badge bg-danger px-3 py-2 text-uppercase">
                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Compromised / Tampered
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success px-3 py-2 text-uppercase">
                            <i class="fa-solid fa-check-double me-1"></i> Cryptographically Intact
                        </span>
                    <?php endif; ?>
                </div>

                <h3 class="fw-bold text-dark mb-1 font-monospace">
                    <?= htmlspecialchars($evidence['original_filename']) ?>
                </h3>
                <div class="text-muted small d-flex flex-wrap gap-3 mt-2">
                    <span><i class="fa-solid fa-desktop me-1 text-primary"></i> Source: <strong><?= htmlspecialchars($evidence['source_device']) ?></strong></span>
                    <span><i class="fa-solid fa-user-shield me-1 text-primary"></i> Ingested By: <strong><?= htmlspecialchars($evidence['uploader_name']) ?></strong></span>
                    <span><i class="fa-regular fa-calendar-check me-1 text-primary"></i> Seized: <strong><?= date('d M Y, H:i', strtotime($evidence['acquired_at'])) ?></strong></span>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="d-flex flex-wrap gap-2">
                <?php if ($role === 'custodian' || $role === 'admin'): ?>
                    <a href="<?= BASE_URL ?>/index.php?action=evidence-verify&id=<?= $evidence['id'] ?>" class="btn btn-cyan btn-sm px-3 fw-semibold shadow-sm">
                        <i class="fa-solid fa-fingerprint me-1"></i> Verify Checksum
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-outline-success btn-sm px-3" data-bs-toggle="modal" data-bs-target="#downloadModal">
                    <i class="fa-solid fa-download me-1"></i> Secure Download
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=custody-certificate&evidence_id=<?= $evidence['id'] ?>" target="_blank" class="btn btn-outline-dark btn-sm px-3">
                    <i class="fa-solid fa-file-contract me-1"></i> Export CoC Certificate
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Cryptographic Fingerprints Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-navy text-white py-3">
        <h6 class="mb-0 fw-bold">
            <i class="fa-solid fa-fingerprint text-cyan me-2"></i> Immutable Cryptographic Signatures & Checksums
        </h6>
    </div>
    <div class="card-body p-4 bg-white">
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">SHA-256 Digest (Baseline Forensic Standard)</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace bg-light fw-bold text-primary" id="sha256Text" value="<?= htmlspecialchars($evidence['sha256_hash']) ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($evidence['sha256_hash']) ?>'); alert('SHA-256 copied to clipboard!');">
                            <i class="fa-solid fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>

                <?php if (!empty($evidence['md5_hash'])): ?>
                <div class="mb-3">
                    <label class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">MD5 Digest (Secondary Verification)</label>
                    <div class="input-group">
                        <input type="text" class="form-control font-monospace bg-light text-secondary" value="<?= htmlspecialchars($evidence['md5_hash']) ?>" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('<?= htmlspecialchars($evidence['md5_hash']) ?>'); alert('MD5 copied to clipboard!');">
                            <i class="fa-solid fa-copy me-1"></i> Copy
                        </button>
                    </div>
                </div>
                <?php endif; ?>

                <div>
                    <label class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Isolated Storage Identifier</label>
                    <div class="font-monospace text-muted small bg-light p-2 rounded">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i> <?= htmlspecialchars($evidence['file_path']) ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 border-start-lg">
                <div class="p-3 bg-light rounded-3 h-100">
                    <h6 class="fw-bold text-dark small text-uppercase mb-3">Artifact Properties</h6>
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span class="text-muted">File Size:</span>
                        <strong class="font-monospace"><?= number_format($evidence['file_size_bytes'] / 1024, 2) ?> KB</strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span class="text-muted">MIME Format:</span>
                        <strong class="font-monospace"><?= htmlspecialchars($evidence['mime_type']) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span class="text-muted">File Extension:</span>
                        <strong class="font-monospace">.<?= strtoupper(htmlspecialchars($evidence['file_extension'])) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom small">
                        <span class="text-muted">Clearance Level:</span>
                        <span><?= getClassificationBadge($evidence['classification_level'] ?? 'confidential') ?></span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom small">
                        <span class="text-muted">Storage Engine:</span>
                        <strong class="text-success">Secured Vault</strong>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($evidence['description'])): ?>
            <div class="mt-4 pt-3 border-top">
                <h6 class="fw-bold text-secondary text-uppercase small mb-2">Acquisition Notes:</h6>
                <div class="bg-light p-3 rounded small text-dark">
                    <?= nl2br(htmlspecialchars($evidence['description'])) ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chain of Custody History Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fa-solid fa-timeline text-primary me-2"></i> Chain of Custody Activity Ledger
            </h5>
            <small class="text-muted">Unbroken chronological record of all handling events</small>
        </div>
        <a href="<?= BASE_URL ?>/index.php?action=custody-certificate&evidence_id=<?= $evidence['id'] ?>" target="_blank" class="btn btn-sm btn-outline-dark">
            <i class="fa-solid fa-print me-1"></i> Print Certificate
        </a>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="table-light text-uppercase">
                    <tr>
                        <th class="ps-3">Event Date & Time</th>
                        <th>Action Performed</th>
                        <th>Evidence Handler / Officer</th>
                        <th>Stated Justification / Purpose</th>
                        <th>Verification Status</th>
                        <th class="pe-3">IP Address</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($custodyLedger as $coc): ?>
                        <tr>
                            <td class="ps-3 font-monospace text-muted">
                                <?= date('Y-m-d H:i:s', strtotime($coc['recorded_at'])) ?>
                            </td>
                            <td>
                                <span class="badge bg-dark font-monospace text-uppercase">
                                    <?= htmlspecialchars($coc['action_type']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= htmlspecialchars($coc['handler_name']) ?></div>
                                <small class="text-muted font-monospace"><?= htmlspecialchars($coc['handler_badge']) ?> • <?= strtoupper($coc['handler_role']) ?></small>
                            </td>
                            <td>
                                <span class="text-dark"><?= htmlspecialchars($coc['reason_or_purpose']) ?></span>
                            </td>
                            <td>
                                <?php if ($coc['integrity_status'] === 'intact'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Intact
                                    </span>
                                <?php elseif ($coc['integrity_status'] === 'compromised'): ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">
                                        <i class="fa-solid fa-triangle-exclamation me-1"></i> Compromised
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted">N/A</span>
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
    </div>
</div>

<!-- Modal: Secure Download Justification Prompt -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-navy text-white">
                <h5 class="modal-title" id="downloadModalLabel">
                    <i class="fa-solid fa-download me-2"></i> Evidence Checkout & Download
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= BASE_URL ?>/index.php" method="GET">
                <input type="hidden" name="action" value="evidence-download">
                <input type="hidden" name="id" value="<?= $evidence['id'] ?>">

                <div class="modal-body">
                    <div class="alert alert-warning small mb-3">
                        <i class="fa-solid fa-triangle-exclamation me-1"></i>
                        <strong>Legal Custody Notice:</strong> All digital artifact transfers and downloads are recorded into the immutable Chain of Custody ledger with your user credentials, timestamp, and IP address.
                    </div>

                    <div class="mb-3">
                        <label for="reason" class="form-label small fw-semibold text-secondary">
                            Operational Justification / Purpose for Retrieval <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="reason" name="reason" required
                            placeholder="e.g. Memory triage in Volatility / Packet analysis in Wireshark"
                            value="Forensic Analysis and Indicator Extraction">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fa-solid fa-file-arrow-down me-1"></i> Log & Download File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
