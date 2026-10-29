<?php

require_once __DIR__ . '/../src/helpers.php';

$pageTitle = $pageTitle ?? 'Student Records';
$flashMessage = getFlash();
$flashType = $flashMessage['type'] ?? 'info';
$flashClass = in_array($flashType, ['success', 'error', 'info'], true) ? $flashType : 'info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Student Records</title>
    <link rel="stylesheet" href="assets/app.css">
</head>
<body>
    <header class="topbar">
        <div class="container topbar-inner">
            <a class="brand" href="index.php">Student Records</a>
            <span class="topbar-label">Administration</span>
        </div>
    </header>
    <main class="container main-content">
        <?php if ($flashMessage !== null): ?>
            <div class="notice notice-<?= e($flashClass) ?>" role="status">
                <?= e($flashMessage['msg'] ?? '') ?>
            </div>
        <?php endif; ?>
