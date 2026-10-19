<?php
/**
 * Login Portal View
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Cyber Incident Evidence Management System (CIEMS)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css">
</head>
<body class="bg-navy d-flex align-items-center justify-content-center min-vh-100 p-3">

<div class="card login-card shadow-lg border-0" style="max-width: 440px; width: 100%;">
    <div class="card-header bg-dark-navy text-white text-center p-4 border-bottom border-secondary">
        <div class="display-6 mb-2 text-cyan">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <h4 class="fw-bold mb-1 tracking-wide">CIEMS PORTAL</h4>
        <div class="text-cyan small fw-semibold">Cyber Incident Evidence Management System</div>
        <div class="text-muted small mt-1">USIU-Africa Forensic Operations</div>
    </div>

    <div class="card-body p-4 bg-white">
        <!-- Flash messages -->
        <?php foreach (Session::getFlashes() as $flash): ?>
            <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show small" role="alert">
                <?= htmlspecialchars($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endforeach; ?>

        <form action="<?= BASE_URL ?>/index.php?action=login-submit" method="POST" autocomplete="off">
            <input type="hidden" name="csrf_token" value="<?= Session::csrfToken() ?>">

            <div class="mb-3">
                <label for="email" class="form-label small fw-semibold text-secondary">
                    <i class="fa-solid fa-envelope me-1"></i> Authorized Email Address
                </label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="analyst@ciems.usiu.ac.ke" autofocus>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label small fw-semibold text-secondary">
                    <i class="fa-solid fa-lock me-1"></i> Security Keyphrase / Password
                </label>
                <div class="input-group">
                    <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••••••">
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                        <i class="fa-regular fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="d-grid mt-4">
                <button type="submit" class="btn btn-navy py-2 fw-semibold">
                    <i class="fa-solid fa-key me-2"></i> Authenticate & Enter
                </button>
            </div>
        </form>

        <!-- Quick Demo Credentials for Grading & Evaluation -->
        <div class="mt-4 pt-3 border-top">
            <div class="small fw-semibold text-muted mb-2 text-center text-uppercase" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                Quick Demo Access (Select Role to Fill):
            </div>
            <div class="d-flex flex-column gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary text-start fill-cred"
                    data-email="investigator@ciems.usiu.ac.ke" data-pass="Investigator@2026!">
                    <i class="fa-solid fa-magnifying-glass me-1"></i> <strong>Lead Investigator:</strong> Kamau Margaret 
                </button>
                <button type="button" class="btn btn-sm btn-outline-success text-start fill-cred"
                    data-email="custodian@ciems.usiu.ac.ke" data-pass="Custodian@2026!">
                    <i class="fa-solid fa-box-archive me-1"></i> <strong>Evidence Custodian:</strong> Michael Ochieng
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger text-start fill-cred"
                    data-email="admin@ciems.usiu.ac.ke" data-pass="Admin@2026!">
                    <i class="fa-solid fa-user-shield me-1"></i> <strong>System Admin:</strong> Anna May
                </button>
            </div>
        </div>
    </div>

    <div class="card-footer bg-light text-center py-3 text-muted small border-top">
        <i class="fa-solid fa-lock text-success me-1"></i> End-to-End Cryptographic Audit Active
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Password toggle
    document.getElementById('togglePassword')?.addEventListener('click', function() {
        const pass = document.getElementById('password');
        const icon = document.getElementById('eyeIcon');
        if (pass.type === 'password') {
            pass.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            pass.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    });

    // Fill credential buttons
    document.querySelectorAll('.fill-cred').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('email').value = this.getAttribute('data-email');
            document.getElementById('password').value = this.getAttribute('data-pass');
        });
    });
</script>
</body>
</html>
