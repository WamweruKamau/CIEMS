<?php
/**
 * Role-Based Sidebar Navigation
 */
$currentAction = $_GET['action'] ?? 'dashboard';
$userRole = Auth::role();
?>
<aside class="sidebar bg-dark-navy text-white d-flex flex-column p-3" style="width: 270px; min-height: 100vh;">
    <!-- Brand / Department Title -->
    <div class="sidebar-header pb-3 mb-3 border-bottom border-secondary">
        <a href="<?= BASE_URL ?>/index.php?action=dashboard" class="text-decoration-none text-white d-flex align-items-center">
            <div class="bg-cyan-soft text-cyan p-2 rounded me-2">
                <i class="fa-solid fa-fingerprint fs-4"></i>
            </div>
            <div>
                <h6 class="mb-0 fw-bold tracking-wide">CIEMS FORENSICS</h6>
                <small class="text-muted" style="font-size: 0.75rem;">USIU-AFRICA LAB</small>
            </div>
        </a>
    </div>

    <!-- Navigation Menu -->
    <ul class="nav nav-pills flex-column mb-auto gap-1">
        <!-- Main Dashboard -->
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=dashboard" class="nav-link <?= $currentAction === 'dashboard' ? 'active' : '' ?>">
                <i class="fa-solid fa-gauge-high me-2"></i> Dashboard
            </a>
        </li>

        <li class="nav-header text-uppercase text-muted mt-3 mb-1 px-3" style="font-size: 0.72rem; letter-spacing: 0.08em;">
            Incident Operations
        </li>

        <!-- Cases -->
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=cases" class="nav-link <?= strpos($currentAction, 'cases') === 0 && $currentAction !== 'cases-create' ? 'active' : '' ?>">
                <i class="fa-solid fa-folder-open me-2"></i> Incident Cases
            </a>
        </li>

        <?php if ($userRole === 'admin' || $userRole === 'investigator'): ?>
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=cases-create" class="nav-link <?= $currentAction === 'cases-create' ? 'active' : '' ?>">
                <i class="fa-solid fa-folder-plus me-2 text-cyan"></i> Register New Case
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-header text-uppercase text-muted mt-3 mb-1 px-3" style="font-size: 0.72rem; letter-spacing: 0.08em;">
            Forensic Custody
        </li>

        <!-- Evidence Vault -->
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=evidence" class="nav-link <?= strpos($currentAction, 'evidence') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-box-archive me-2"></i> Evidence Vault
            </a>
        </li>

        <!-- Chain of Custody -->
        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=custody-timeline" class="nav-link <?= strpos($currentAction, 'custody') === 0 ? 'active' : '' ?>">
                <i class="fa-solid fa-link me-2"></i> Chain of Custody
            </a>
        </li>

        <?php if ($userRole === 'admin'): ?>
        <li class="nav-header text-uppercase text-muted mt-3 mb-1 px-3" style="font-size: 0.72rem; letter-spacing: 0.08em;">
            Administration
        </li>

        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=admin-users" class="nav-link <?= $currentAction === 'admin-users' ? 'active' : '' ?>">
                <i class="fa-solid fa-users-gear me-2"></i> User Access & Roles
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=admin-audit" class="nav-link <?= $currentAction === 'admin-audit' ? 'active' : '' ?>">
                <i class="fa-solid fa-shield-virus me-2"></i> Master Audit Trail
            </a>
        </li>

        <li class="nav-item">
            <a href="<?= BASE_URL ?>/index.php?action=admin-settings" class="nav-link <?= $currentAction === 'admin-settings' ? 'active' : '' ?>">
                <i class="fa-solid fa-sliders me-2"></i> Forensic Settings
            </a>
        </li>
        <?php endif; ?>
    </ul>

    <!-- Bottom Status Card -->
    <div class="sidebar-footer pt-3 mt-auto border-top border-secondary">
        <div class="bg-navy p-2 rounded-2 text-center">
            <div class="d-flex align-items-center justify-content-center text-success small mb-1">
                <span class="status-indicator bg-success me-2"></span> Vault Online & Guarded
            </div>
            <div class="text-muted" style="font-size: 0.7rem;">IST4900 Project • 2026</div>
        </div>
    </div>
</aside>
