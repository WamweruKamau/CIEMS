/**
 * CIEMS Client Application Scripts
 */

document.addEventListener('DOMContentLoaded', function () {
    // Enable Bootstrap Tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Auto-dismiss alerts after 6 seconds
    const alerts = document.querySelectorAll('.alert-dismissible');
    alerts.forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 6000);
    });

    // File input size validator
    const evidenceFileInput = document.getElementById('evidence_file');
    if (evidenceFileInput) {
        evidenceFileInput.addEventListener('change', function () {
            if (this.files && this.files[0]) {
                const fileSize = this.files[0].size;
                const maxSize = 500 * 1024 * 1024; // 500MB
                if (fileSize > maxSize) {
                    alert('Selected file exceeds the maximum 500MB upload threshold.');
                    this.value = '';
                }
            }
        });
    }
});
