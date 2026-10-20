<?php
$pageTitle = 'Incident Cases';
require dirname(__DIR__) . '/layouts/header.php';
$role = Auth::role();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-folder-open text-primary me-2"></i> Incident Investigation Cases
        </h4>
        <small class="text-muted">Central repository of registered cybersecurity incidents</small>
    </div>
    <?php if ($role === 'investigator' || $role === 'admin'): ?>
        <div>
            <a href="<?= BASE_URL ?>/index.php?action=cases-create" class="btn btn-primary px-3 shadow-sm">
                <i class="fa-solid fa-plus me-1"></i> Register New Case
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Search & Filtering Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2 align-items-center">
            <input type="hidden" name="action" value="cases">

            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="search" placeholder="Search case #, title, keyword..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <option value="open" <?= ($_GET['status'] ?? '') === 'open' ? 'selected' : '' ?>>Open</option>
                    <option value="in_progress" <?= ($_GET['status'] ?? '') === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="under_review" <?= ($_GET['status'] ?? '') === 'under_review' ? 'selected' : '' ?>>Under Review</option>
                    <option value="closed" <?= ($_GET['status'] ?? '') === 'closed' ? 'selected' : '' ?>>Closed</option>
                    <option value="archived" <?= ($_GET['status'] ?? '') === 'archived' ? 'selected' : '' ?>>Archived</option>
                </select>
            </div>

            <div class="col-md-3">
                <select name="severity" class="form-select">
                    <option value="">All Severities</option>
                    <option value="low" <?= ($_GET['severity'] ?? '') === 'low' ? 'selected' : '' ?>>Low</option>
                    <option value="medium" <?= ($_GET['severity'] ?? '') === 'medium' ? 'selected' : '' ?>>Medium</option>
                    <option value="high" <?= ($_GET['severity'] ?? '') === 'high' ? 'selected' : '' ?>>High</option>
                    <option value="critical" <?= ($_GET['severity'] ?? '') === 'critical' ? 'selected' : '' ?>>Critical</option>
                </select>
            </div>

            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=cases" class="btn btn-outline-secondary" title="Reset Filters">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Cases List Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?php if (empty($cases)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-regular fa-folder-open display-4 mb-3 text-secondary"></i>
                <h5>No incident cases found</h5>
                <p class="small text-muted mb-0">Try adjusting your filters or register a new case above.</p>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small text-uppercase">
                        <tr>
                            <th class="ps-3">Case ID</th>
                            <th>Case Title & Classification</th>
                            <th>Severity</th>
                            <th>Status</th>
                            <th>Lead Investigator</th>
                            <th>Artifacts</th>
                            <th>Date Created</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cases as $c): ?>
                            <tr>
                                <td class="ps-3">
                                    <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $c['id'] ?>" class="font-monospace fw-bold text-decoration-none">
                                        <?= htmlspecialchars($c['case_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">
                                        <?= htmlspecialchars($c['title']) ?>
                                    </div>
                                    <small class="text-muted text-capitalize">
                                        <i class="fa-solid fa-shield-virus me-1 text-secondary"></i>
                                        <?= str_replace('_', ' ', htmlspecialchars($c['incident_type'])) ?>
                                    </small>
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
                                    <div class="small fw-medium text-dark"><?= htmlspecialchars($c['lead_name']) ?></div>
                                    <small class="text-muted font-monospace"><?= htmlspecialchars($c['lead_badge']) ?></small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-dark font-monospace px-2 py-1">
                                        <i class="fa-solid fa-paperclip me-1 text-muted"></i> <?= (int)$c['evidence_count'] ?> files
                                    </span>
                                </td>
                                <td class="small text-muted">
                                    <?= date('d M Y, H:i', strtotime($c['created_at'])) ?>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="<?= BASE_URL ?>/index.php?action=cases-show&id=<?= $c['id'] ?>" class="btn btn-outline-primary btn-sm px-2 py-1" title="View Case Dossier">
                                        <i class="fa-solid fa-folder-open me-1"></i> Dossier
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

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
