<?php
/**
 * Official Forensic Chain of Custody Certificate
 * Printable Court / Regulatory Audit Document
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chain of Custody Certificate - <?= htmlspecialchars($evidence['evidence_number']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }
        .cert-container {
            max-width: 900px;
            margin: 30px auto;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            padding: 40px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .header-seal {
            border-bottom: 3px double #0f172a;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .hash-box {
            background: #f1f5f9;
            border-left: 4px solid #0284c7;
            padding: 12px 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            word-break: break-all;
        }
        .signature-line {
            border-top: 1px solid #0f172a;
            margin-top: 50px;
            padding-top: 8px;
            font-size: 0.85rem;
        }
        @media print {
            body {
                background: #ffffff;
                color: #000000;
            }
            .cert-container {
                border: none;
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="no-print text-center py-3 bg-dark text-white d-flex justify-content-center align-items-center gap-3">
    <span>Forensic Chain of Custody Certificate Preview</span>
    <button onclick="window.print()" class="btn btn-primary btn-sm px-4">
        <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
    </button>
    <a href="<?= BASE_URL ?>/index.php?action=evidence-show&id=<?= $evidence['id'] ?>" class="btn btn-outline-light btn-sm">
        Close Preview
    </a>
</div>

<div class="cert-container">
    <!-- Institutional Header -->
    <div class="header-seal text-center">
        <h5 class="text-uppercase tracking-wider fw-bold mb-1">United States International University – Africa</h5>
        <h6 class="text-secondary text-uppercase small mb-2">School of Science and Technology • IST4900 Systems Design</h6>
        <h4 class="fw-bold text-dark mb-0 mt-3">DIGITAL FORENSIC CHAIN OF CUSTODY CERTIFICATE</h4>
        <div class="small text-muted mt-1 font-monospace">
            Document ID: COC-CERT-<?= date('Ymd') ?>-<?= htmlspecialchars($evidence['evidence_number']) ?>
        </div>
    </div>

    <!-- Case & Evidence Overview -->
    <div class="row g-3 mb-4">
        <div class="col-6">
            <h6 class="fw-bold text-uppercase small text-secondary mb-2">1. Incident Case Details</h6>
            <table class="table table-sm table-bordered small mb-0">
                <tr>
                    <th class="bg-light w-40">Case Number:</th>
                    <td class="font-monospace fw-bold"><?= htmlspecialchars($case['case_number']) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Case Title:</th>
                    <td><?= htmlspecialchars($case['title']) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Classification:</th>
                    <td class="text-capitalize"><?= str_replace('_', ' ', htmlspecialchars($case['incident_type'])) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Current Status:</th>
                    <td class="text-uppercase fw-semibold"><?= str_replace('_', ' ', htmlspecialchars($case['status'])) ?></td>
                </tr>
            </table>
        </div>

        <div class="col-6">
            <h6 class="fw-bold text-uppercase small text-secondary mb-2">2. Artifact Identification</h6>
            <table class="table table-sm table-bordered small mb-0">
                <tr>
                    <th class="bg-light w-40">Evidence ID:</th>
                    <td class="font-monospace fw-bold text-primary"><?= htmlspecialchars($evidence['evidence_number']) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Original Filename:</th>
                    <td class="font-monospace"><?= htmlspecialchars($evidence['original_filename']) ?></td>
                </tr>
                <tr>
                    <th class="bg-light">File Size:</th>
                    <td><?= number_format($evidence['file_size_bytes'] / 1024, 2) ?> KB</td>
                </tr>
                <tr>
                    <th class="bg-light">Clearance Level:</th>
                    <td><?= getClassificationBadge($evidence['classification_level'] ?? 'confidential') ?></td>
                </tr>
                <tr>
                    <th class="bg-light">Acquisition Time:</th>
                    <td><?= date('Y-m-d H:i:s', strtotime($evidence['acquired_at'])) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Cryptographic Integrity Stamp -->
    <div class="mb-4">
        <h6 class="fw-bold text-uppercase small text-secondary mb-2">3. Cryptographic Hashes (Forensic Baseline)</h6>
        <div class="hash-box mb-2">
            <strong class="d-block text-dark">Baseline SHA-256 Digest:</strong>
            <?= htmlspecialchars($evidence['sha256_hash']) ?>
        </div>
        <?php if (!empty($evidence['md5_hash'])): ?>
            <div class="hash-box" style="border-left-color: #64748b;">
                <strong class="d-block text-dark">Baseline MD5 Digest:</strong>
                <?= htmlspecialchars($evidence['md5_hash']) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Chain of Custody Ledger Table -->
    <div class="mb-4">
        <h6 class="fw-bold text-uppercase small text-secondary mb-2">4. Chronological Custody Audit Ledger</h6>
        <table class="table table-sm table-bordered align-middle small mb-0">
            <thead class="table-light text-uppercase" style="font-size: 0.75rem;">
                <tr>
                    <th>#</th>
                    <th>Date & Time (UTC+3)</th>
                    <th>Action</th>
                    <th>Forensic Officer / Handler</th>
                    <th>Purpose / Reason</th>
                    <th>Integrity Verification</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($custodyLedger as $idx => $entry): ?>
                    <tr>
                        <td class="text-center font-monospace"><?= $idx + 1 ?></td>
                        <td class="font-monospace"><?= date('Y-m-d H:i:s', strtotime($entry['recorded_at'])) ?></td>
                        <td class="font-monospace text-uppercase fw-semibold"><?= htmlspecialchars($entry['action_type']) ?></td>
                        <td><?= htmlspecialchars($entry['handler_name']) ?> (<?= htmlspecialchars($entry['handler_badge']) ?>)</td>
                        <td><?= htmlspecialchars($entry['reason_or_purpose']) ?></td>
                        <td>
                            <?php if ($entry['integrity_status'] === 'intact'): ?>
                                <span class="text-success fw-bold">✓ Intact</span>
                            <?php elseif ($entry['integrity_status'] === 'compromised'): ?>
                                <span class="text-danger fw-bold">⚠ Tampered</span>
                            <?php else: ?>
                                <span class="text-muted">Recorded</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Forensic Certification Statement -->
    <div class="p-3 bg-light rounded small text-muted mb-4 border">
        <strong>Forensic Integrity Certification:</strong> I hereby certify that the evidence artifact detailed herein has been handled in strict compliance with standard operating procedures and digital forensic best practices (ISO/IEC 27037). The cryptographic fingerprints documented above reflect an unbroken chain of custody maintained within the CIEMS repository.
    </div>

    <!-- Signatures -->
    <div class="row pt-4 text-center">
        <div class="col-4">
            <div class="signature-line">
                <strong>Kamau Margaret Wamweru</strong><br>
                Lead Forensic Investigator 
            </div>
        </div>
        <div class="col-4">
            <div class="signature-line">
                <strong>Michael Ochieng</strong><br>
                Certified Evidence Custodian
            </div>
        </div>
        <div class="col-4">
            <div class="signature-line">
                <strong>Anna May</strong><br>
                Lab Director / System Administrator
            </div>
        </div>
    </div>
</div>

</body>
</html>
