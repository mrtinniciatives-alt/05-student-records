<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
$pageTitle = $pageTitle ?? 'Student Records Manager';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="/swd/05-student-records/assets/app.css">
</head>
<body>
<header class="topbar">
    <div class="container">
        <a class="brand" href="/swd/05-student-records/index.php">Student Records Manager</a>
    </div>
</header>
<main class="container">
<?php $flash = getFlash(); if ($flash): ?>
    <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
