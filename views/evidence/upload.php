<?php
$pageTitle = 'Ingest Forensic Evidence: ' . $case['case_number'];
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-cloud-arrow-up text-primary me-2"></i> Ingest Forensic Evidence Artifact
                </h4>
                <small class="text-muted">Target Case: <strong><?= htmlspecialchars($case['case_number']) ?></strong> — <?= htmlspecialchars($case['title']) ?></small>
            </div>
            <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $case['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Case
            </a>
        </div>

        <!-- Forensic Notice Callout -->
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-start gap-3 mb-4 rounded-3">
            <i class="fa-solid fa-shield-halved fs-3 text-info mt-1"></i>
            <div>
                <div class="fw-bold">Automated Cryptographic Hashing (Forensic Integrity)</div>
                <div class="small">
                    Upon submission, CIEMS will automatically generate baseline <strong>SHA-256</strong> and <strong>MD5</strong> checksums directly from the incoming temporary file stream before moving the payload into the isolated server vault. The original filename and metadata will be permanently recorded into the immutable Chain of Custody ledger.
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/index.php?action=evidence-upload" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="case_id" value="<?= $case['id'] ?>">

                    <div class="mb-4">
                        <label for="evidence_file" class="form-label small fw-semibold text-secondary">
                            Forensic Artifact Payload <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control form-control-lg" id="evidence_file" name="evidence_file" required>
                        <div class="form-text small">
                            Accepted extensions: PCAP, PCAPNG, RAW, DD, IMG, BIN, VMDK, MEM, DMP, LOG, EVTX, TXT, CSV, JSON, ZIP, etc. (Max 500MB)
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label for="source_device" class="form-label small fw-semibold text-secondary">
                                Origin Device / Seizure Location <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="source_device" name="source_device" required
                                placeholder="e.g. Workstation DESKTOP-HQ04">
                        </div>

                        <div class="col-md-4">
                            <label for="acquired_at" class="form-label small fw-semibold text-secondary">
                                Acquisition Timestamp <span class="text-danger">*</span>
                            </label>
                            <input type="datetime-local" class="form-control" id="acquired_at" name="acquired_at" required
                                value="<?= date('Y-m-d\TH:i') ?>">
                        </div>

                        <div class="col-md-4">
                            <label for="classification_level" class="form-label small fw-semibold text-secondary">
                                Confidentiality / Clearance Level <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="classification_level" name="classification_level" required>
                                <option value="top_secret">🔴 Top Secret</option>
                                <option value="secret">🟠 Secret</option>
                                <option value="confidential" selected>🟡 Confidential</option>
                                <option value="restricted">🔵 Restricted</option>
                                <option value="unclassified">⚪ Unclassified</option>
                            </select>
                            <div class="form-text small">Mandated handling clearance level.</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="form-label small fw-semibold text-secondary">
                            Artifact Acquisition Notes & Forensics Context
                        </label>
                        <textarea class="form-control" id="description" name="description" rows="4"
                            placeholder="State tools utilized during acquisition (e.g. FTK Imager, WinPmem, tcpdump), write-blocker serial number, and initial observations..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $case['id'] ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="fa-solid fa-shield-cat me-1"></i> Compute SHA-256 & Secure Ingest
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
