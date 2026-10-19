<?php
/**
 * Top Navbar Layout
 */
$currentUser = Auth::user();
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-navy border-bottom border-dark px-3 py-2 shadow-sm">
    <div class="container-fluid">
        <span class="navbar-brand text-light fw-semibold fs-6 d-flex align-items-center mb-0">
            <i class="fa-solid fa-shield-halved text-cyan me-2 fs-5"></i>
            <span class="d-none d-sm-inline">Cyber Incident Evidence Management System</span>
            <span class="d-inline d-sm-none">CIEMS</span>
        </span>

        <div class="d-flex align-items-center ms-auto">
            <div class="me-3 text-end d-none d-md-block">
                <div class="text-white fw-medium small mb-0"><?= htmlspecialchars($currentUser['full_name']) ?></div>
                <div class="badge bg-secondary font-monospace" style="font-size: 0.72rem;">
                    <?= strtoupper(htmlspecialchars($currentUser['role'])) ?> • <?= htmlspecialchars($currentUser['badge_number']) ?>
                </div>
            </div>
            <a href="<?= BASE_URL ?>/index.php?action=logout" class="btn btn-outline-danger btn-sm px-3" title="Sign Out">
                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Sign Out
            </a>
        </div>
    </div>
</nav>
