<?php
$pageTitle = 'Evidence Vault Registry';
require dirname(__DIR__) . '/layouts/header.php';
$role = Auth::role();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-box-archive text-success me-2"></i> Evidence Vault Registry
        </h4>
        <small class="text-muted">Catalog of all seized digital forensic artifacts & cryptographic digests</small>
    </div>
</div>

<!-- Search & Filtering Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2 align-items-center">
            <input type="hidden" name="action" value="evidence">

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="search" placeholder="Search by evidence #, filename, SHA-256..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-4">
                <select name="case_id" class="form-select">
                    <option value="">All Incident Cases</option>
                    <?php foreach ($cases as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= ($_GET['case_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['case_number']) ?> - <?= htmlspecialchars($c['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=evidence" class="btn btn-outline-secondary" title="Reset Filters">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Evidence Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?php if (empty($evidenceList)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-box-open display-4 mb-3 text-secondary"></i>
                <h5>No forensic artifacts matched your criteria</h5>
                <p class="small text-muted mb-0">Select a case to upload and ingest evidence files.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th class="ps-3">Evidence ID</th>
                            <th>Artifact Filename</th>
                            <th>Associated Case</th>
                            <th>Size</th>
                            <th>SHA-256 Digest</th>
                            <th>Integrity Status</th>
                            <th>Acquired At</th>
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
                                    <small class="text-muted font-monospace"><?= strtoupper(htmlspecialchars($ev['file_extension'])) ?> • <?= htmlspecialchars($ev['source_device']) ?></small>
                                </td>
                                <td>
                                    <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $ev['case_id'] ?>" class="text-decoration-none small fw-medium">
                                        <?= htmlspecialchars($ev['case_number']) ?>
                                    </a>
                                </td>
                                <td class="font-monospace small">
                                    <?= number_format($ev['file_size_bytes'] / 1024, 2) ?> KB
                                </td>
                                <td>
                                    <span class="font-monospace small text-primary bg-light px-2 py-1 rounded d-inline-block text-truncate" style="max-width: 170px;" title="<?= htmlspecialchars($ev['sha256_hash']) ?>">
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
                                <td class="small text-muted font-monospace">
                                    <?= date('Y-m-d H:i', strtotime($ev['acquired_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="<?= BASE_URL ?>/index.php?action=evidence-show&id=<?= $ev['id'] ?>" class="btn btn-outline-secondary" title="View Dossier">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <?php if ($role === 'custodian' || $role === 'admin'): ?>
                                            <a href="<?= BASE_URL ?>/index.php?action=evidence-verify&id=<?= $ev['id'] ?>" class="btn btn-outline-info" title="Verify Integrity">
                                                <i class="fa-solid fa-fingerprint"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= BASE_URL ?>/index.php?action=evidence-download&id=<?= $ev['id'] ?>" class="btn btn-outline-success" title="Download">
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

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
