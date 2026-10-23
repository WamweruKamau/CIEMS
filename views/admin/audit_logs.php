<?php
$pageTitle = 'Master Compliance Audit Trail';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-0 text-dark">
            <i class="fa-solid fa-shield-virus text-danger me-2"></i> Master Compliance Audit Trail
        </h4>
        <small class="text-muted">Immutable system audit records for forensic accountability and legal compliance</small>
    </div>
</div>

<!-- Search & Module Filter -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form method="GET" action="<?= BASE_URL ?>/index.php" class="row g-2 align-items-center">
            <input type="hidden" name="action" value="admin-audit">

            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" name="search" placeholder="Search action, IP, or details..."
                        value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                </div>
            </div>

            <div class="col-md-4">
                <select name="module" class="form-select">
                    <option value="">All Forensic Modules</option>
                    <option value="auth" <?= ($_GET['module'] ?? '') === 'auth' ? 'selected' : '' ?>>Authentication & Sessions</option>
                    <option value="cases" <?= ($_GET['module'] ?? '') === 'cases' ? 'selected' : '' ?>>Case Management</option>
                    <option value="evidence" <?= ($_GET['module'] ?? '') === 'evidence' ? 'selected' : '' ?>>Evidence & Hashing</option>
                    <option value="custody" <?= ($_GET['module'] ?? '') === 'custody' ? 'selected' : '' ?>>Chain of Custody</option>
                    <option value="users" <?= ($_GET['module'] ?? '') === 'users' ? 'selected' : '' ?>>User Management</option>
                    <option value="system" <?= ($_GET['module'] ?? '') === 'system' ? 'selected' : '' ?>>System Configuration</option>
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark w-100">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                <a href="<?= BASE_URL ?>/index.php?action=admin-audit" class="btn btn-outline-secondary" title="Reset Filters">
                    <i class="fa-solid fa-arrows-rotate"></i>
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Audit Log Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <?php if (empty($logs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="fa-solid fa-file-shield display-4 mb-3 text-secondary"></i>
                <h5>No audit logs found matching criteria</h5>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-uppercase">
                        <tr>
                            <th class="ps-3">Timestamp</th>
                            <th>User / Officer</th>
                            <th>Module</th>
                            <th>Action Trigger</th>
                            <th>Audit Narrative / Metadata</th>
                            <th>Client IP</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($logs as $l): ?>
                            <tr>
                                <td class="ps-3 font-monospace text-muted" style="white-space: nowrap;">
                                    <?= date('Y-m-d H:i:s', strtotime($l['created_at'])) ?>
                                </td>
                                <td>
                                    <?php if (!empty($l['user_full_name'])): ?>
                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($l['user_full_name']) ?></div>
                                        <small class="text-muted font-monospace"><?= htmlspecialchars($l['badge_number']) ?> • <?= strtoupper($l['user_role']) ?></small>
                                    <?php else: ?>
                                        <span class="text-muted font-monospace">System / Unauthenticated</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary font-monospace text-uppercase" style="font-size: 0.7rem;">
                                        <?= htmlspecialchars($l['target_module']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="font-monospace fw-bold <?= strpos($l['action'], 'FAILED') !== false || strpos($l['action'], 'DENIED') !== false ? 'text-danger' : 'text-primary' ?>">
                                        <?= htmlspecialchars($l['action']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="text-dark" style="max-width: 380px; word-break: break-word;">
                                        <?= htmlspecialchars($l['details']) ?>
                                    </div>
                                </td>
                                <td class="font-monospace text-muted" style="white-space: nowrap;">
                                    <?= htmlspecialchars($l['ip_address']) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="card-footer bg-white d-flex justify-content-between align-items-center py-3">
                    <small class="text-muted">
                        Page <?= $page ?> of <?= $totalPages ?> (Total: <?= $totalLogs ?> records)
                    </small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php for ($p = 1; $p <= $totalPages; $p++): ?>
                                <li class="page-item <?= $p === $page ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= BASE_URL ?>/index.php?action=admin-audit&page=<?= $p ?>&module=<?= urlencode($module) ?>&search=<?= urlencode($search) ?>">
                                        <?= $p ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
