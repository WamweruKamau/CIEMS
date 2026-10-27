<?php
/**
 * Master Header Layout
 */
$currentUser = Auth::user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'CIEMS') ?> | Cyber Incident Evidence Management</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter & JetBrains Mono -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Custom Forensic Stylesheet -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/custom.css?v=<?= file_exists(dirname(__DIR__, 2) . '/assets/css/custom.css') ? filemtime(dirname(__DIR__, 2) . '/assets/css/custom.css') : time() ?>">
</head>
<body class="bg-light">
<div class="wrapper d-flex">
    <?php if (Auth::check()): ?>
        <?php require __DIR__ . '/sidebar.php'; ?>
    <?php endif; ?>

    <div class="main-content flex-grow-1 d-flex flex-column min-vh-100">
        <?php if (Auth::check()): ?>
            <?php require __DIR__ . '/navbar.php'; ?>
        <?php endif; ?>

        <div class="container-fluid p-4 flex-grow-1">
            <!-- Flash Message Alerts -->
            <?php foreach (Session::getFlashes() as $flash): ?>
                <div class="alert alert-<?= htmlspecialchars($flash['type']) ?> alert-dismissible fade show d-flex align-items-center shadow-sm" role="alert">
                    <i class="fa-solid <?= $flash['type'] === 'success' ? 'fa-circle-check' : ($flash['type'] === 'danger' ? 'fa-triangle-exclamation' : 'fa-circle-info') ?> me-2 fs-5"></i>
                    <div><?= htmlspecialchars($flash['message']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endforeach; ?>
