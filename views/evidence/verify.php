<?php
$pageTitle = 'Integrity Verification: ' . $evidence['evidence_number'];
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-fingerprint text-cyan me-2"></i> Cryptographic Integrity Verification
                </h4>
                <small class="text-muted">On-demand SHA-256 hash recalculation and tamper detection</small>
            </div>
            <a href="<?= BASE_URL ?>/index.php?action=evidence-show&id=<?= $evidence['id'] ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fa-solid fa-arrow-left me-1"></i> Back to Artifact
            </a>
        </div>

        <div class="card border-0 shadow-sm rounded-3 mb-4">
            <div class="card-header bg-navy text-white py-3">
                <h6 class="mb-0 fw-bold">Artifact Under Inspection</h6>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Evidence Identifier</label>
                        <div class="font-monospace fw-bold fs-6 text-dark"><?= htmlspecialchars($evidence['evidence_number']) ?></div>
                    </div>
                    <div class="col-md-6">
                        <label class="small text-muted text-uppercase fw-bold">Original Filename</label>
                        <div class="fw-semibold text-dark"><?= htmlspecialchars($evidence['original_filename']) ?></div>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="small text-muted text-uppercase fw-bold">Stored Baseline SHA-256 Digest</label>
                    <div class="font-monospace text-primary bg-light p-2 rounded small fw-bold">
                        <?= htmlspecialchars($evidence['sha256_hash']) ?>
                    </div>
                    <div class="form-text small">
                        This cryptographic signature was locked into the database at the time of initial ingestion.
                    </div>
                </div>

                <div class="alert alert-light border small text-muted mb-4">
                    <i class="fa-solid fa-circle-info text-primary me-2"></i>
                    <strong>Verification Procedure:</strong> Clicking "Execute Verification" triggers a server-side read of the raw artifact file stored inside the restricted vault (`storage/vault/`). The server re-computes its SHA-256 hash in real-time and evaluates bit-for-bit parity against the baseline digest.
                </div>

                <form action="<?= BASE_URL ?>/index.php?action=evidence-verify" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">
                    <input type="hidden" name="evidence_id" value="<?= $evidence['id'] ?>">

                    <div class="mb-4">
                        <label for="verification_reason" class="form-label small fw-semibold text-secondary">
                            Audit Purpose / Verification Trigger <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="verification_reason" name="verification_reason" required
                            placeholder="e.g. Routine Pre-Trial Integrity Verification or Monthly Custodial Audit"
                            value="Routine Forensic Custodial Integrity Audit">
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/index.php?action=evidence-show&id=<?= $evidence['id'] ?>" class="btn btn-outline-secondary px-4">Cancel</a>
                        <button type="submit" class="btn btn-cyan px-4 fw-bold">
                            <i class="fa-solid fa-play me-1"></i> Execute Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
