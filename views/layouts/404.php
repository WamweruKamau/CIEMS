<?php
$pageTitle = '404 Not Found';
require __DIR__ . '/header.php';
?>
<div class="row justify-content-center py-5">
    <div class="col-md-6 text-center">
        <i class="fa-solid fa-triangle-exclamation text-warning display-1 mb-3"></i>
        <h2 class="fw-bold">404 - Resource Not Found</h2>
        <p class="text-muted">The requested investigation module or forensic record does not exist or has been relocated.</p>
        <a href="<?= BASE_URL ?>/index.php?action=dashboard" class="btn btn-primary">
            <i class="fa-solid fa-house me-1"></i> Return to Dashboard
        </a>
    </div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
