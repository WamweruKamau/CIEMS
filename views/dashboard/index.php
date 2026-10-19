<?php
$pageTitle = 'Forensic Operations Dashboard';
require dirname(__DIR__) . '/layouts/header.php';
$role = Auth::role();
?>

<!-- Top Welcome & Status Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-navy text-white p-3 p-md-4 rounded-3">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-cyan text-dark fw-bold text-uppercase px-2 py-1"><?= htmlspecialchars($role) ?> VIEW</span>
                        <span class="text-white-50 small">Badge: <?= htmlspecialchars($currentUser['badge_number']) ?></span>
                    </div>
                    <h3 class="fw-bold mb-1">Welcome back, <?= htmlspecialchars($currentUser['full_name']) ?></h3>
                    <p class="text-white-50 mb-0 small">
                        Incident Command & Digital Forensic Evidence Repository • <?= date('l, d F Y') ?>
                    </p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <?php if ($role === 'investigator' || $role === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=cases-create" class="btn btn-cyan btn-sm px-3 fw-semibold shadow-sm">
                            <i class="fa-solid fa-folder-plus me-1"></i> New Case
                        </a>
                    <?php endif; ?>
                    <?php if ($role === 'custodian' || $role === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/index.php?action=evidence" class="btn btn-outline-light btn-sm px-3 fw-semibold">
                            <i class="fa-solid fa-fingerprint me-1"></i> Verify Checksums
                        </a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/index.php?action=custody-timeline" class="btn btn-outline-light btn-sm px-3">
                        <i class="fa-solid fa-timeline me-1"></i> Custody Ledger
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Telemetry Counter Cards -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Cases</div>
                        <h2 class="fw-bold my-1 text-dark"><?= $caseStats['total'] ?></h2>
                        <div class="small text-muted">
                            <span class="text-primary fw-semibold"><?= $caseStats['open'] ?> Open</span> • 
                            <span class="text-warning fw-semibold"><?= $caseStats['in_progress'] ?> Active</span>
                        </div>
                    </div>
                    <div class="stat-icon bg-primary-soft text-primary">
                        <i class="fa-solid fa-folder-open"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Vault Artifacts</div>
                        <h2 class="fw-bold my-1 text-dark"><?= $evidenceStats['total'] ?></h2>
                        <div class="small text-muted">
                            <span class="text-success fw-semibold"><?= $evidenceStats['secured'] ?> Secured</span>
                            <?php if ($evidenceStats['tampered'] > 0): ?>
                                • <span class="text-danger fw-bold"><i class="fa-solid fa-triangle-exclamation"></i> <?= $evidenceStats['tampered'] ?> Compromised</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="stat-icon bg-success-soft text-success">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Custody Events</div>
                        <h2 class="fw-bold my-1 text-dark"><?= $totalCustodyEvents ?></h2>
                        <div class="small text-success fw-semibold">
                            <i class="fa-solid fa-shield-check me-1"></i> Append-Only Logs
                        </div>
                    </div>
                    <div class="stat-icon bg-info-soft text-info">
                        <i class="fa-solid fa-link"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 stat-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Critical Threats</div>
                        <h2 class="fw-bold my-1 text-danger"><?= $caseStats['critical'] ?></h2>
                        <div class="small text-muted">High Priority Incidents</div>
                    </div>
                    <div class="stat-icon bg-danger-soft text-danger">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Operations Section -->
<div class="row g-4 mb-4">
    <!-- Active Cases Column -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fa-solid fa-folder-closed text-primary me-2"></i> Active Incident Cases
                </h6>
                <a href="<?= BASE_URL ?>/index.php?action=cases" class="btn btn-sm btn-link text-decoration-none">View All Cases</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($recentCases)): ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fa-regular fa-folder-open display-6 mb-2 text-secondary"></i>
                        <p class="mb-0">No active cases found. Register a case to begin evidence collection.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small text-uppercase">
                                <tr>
                                    <th>Case ID</th>
                                    <th>Incident Title</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                    <th>Artifacts</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentCases as $c): ?>
                                    <tr>
                                        <td>
                                            <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $c['id'] ?>" class="font-monospace fw-semibold text-decoration-none">
                                                <?= htmlspecialchars($c['case_number']) ?>
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-truncate" style="max-width: 220px;">
                                                <?= htmlspecialchars($c['title']) ?>
                                            </div>
                                            <small class="text-muted text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($c['incident_type'])) ?></small>
                                        </td>
                                        <td>
                                            <span class="badge badge-severity-<?= htmlspecialchars($c['severity']) ?>">
                                                <?= strtoupper(htmlspecialchars($c['severity'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-status-<?= htmlspecialchars($c['status']) ?>">
                                                <?= strtoupper(str_replace('_', ' ', htmlspecialchars($c['status']))) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary font-monospace">
                                                <?= (int)$c['evidence_count'] ?> files
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-sm py-1 px-2" title="View Case">
                                                <i class="fa-solid fa-arrow-right"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Custody Ledger Column -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-3 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-dark">
                    <i class="fa-solid fa-link text-info me-2"></i> Recent Chain of Custody Events
                </h6>
                <a href="<?= BASE_URL ?>/index.php?action=custody-timeline" class="btn btn-sm btn-link text-decoration-none">Full Ledger</a>
            </div>
            <div class="card-body p-3">
                <?php if (empty($recentCustody)): ?>
                    <div class="text-center text-muted py-4">No recent custody transactions recorded.</div>
                <?php else: ?>
                    <div class="timeline-small">
                        <?php foreach ($recentCustody as $coc): ?>
                            <div class="border-start border-2 border-primary ps-3 pb-3 position-relative">
                                <span class="position-absolute top-0 start-0 translate-middle p-1 bg-primary rounded-circle" style="left: -1px !important;"></span>
                                <div class="d-flex justify-content-between align-items-baseline">
                                    <span class="badge bg-dark font-monospace text-uppercase" style="font-size: 0.68rem;">
                                        <?= htmlspecialchars($coc['action_type']) ?>
                                    </span>
                                    <small class="text-muted" style="font-size: 0.72rem;">
                                        <?= date('M d, H:i', strtotime($coc['recorded_at'])) ?>
                                    </small>
                                </div>
                                <div class="small fw-semibold text-dark mt-1">
                                    <?= htmlspecialchars($coc['evidence_number']) ?>
                                    <span class="text-muted fw-normal">(<?= htmlspecialchars($coc['original_filename']) ?>)</span>
                                </div>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    Handler: <?= htmlspecialchars($coc['handler_name']) ?> (<?= htmlspecialchars($coc['handler_badge']) ?>)
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
