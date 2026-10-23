<?php
$pageTitle = 'Forensic System Settings';
require dirname(__DIR__) . '/layouts/header.php';
?>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-sliders text-primary me-2"></i> System Configuration & Forensic Policies
                </h4>
                <small class="text-muted">Global operational standards, retention rules, and institutional identity</small>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <form action="<?= BASE_URL ?>/index.php?action=admin-settings" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

                    <?php foreach ($settings as $s): ?>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary text-uppercase" style="letter-spacing: 0.05em;">
                                <?= htmlspecialchars(str_replace('_', ' ', $s['setting_key'])) ?>
                            </label>
                            <input type="text" class="form-control" name="settings[<?= htmlspecialchars($s['setting_key']) ?>]"
                                value="<?= htmlspecialchars($s['setting_value']) ?>" required>
                            <?php if (!empty($s['description'])): ?>
                                <div class="form-text small text-muted"><?= htmlspecialchars($s['description']) ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="fa-solid fa-save me-1"></i> Update System Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require dirname(__DIR__) . '/layouts/footer.php'; ?>
