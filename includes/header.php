<?php
$pageTitle = isset($pageTitle) ? $pageTitle : 'TravelMate';
$flashMessage = get_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | TravelMate</title>
    <link rel="stylesheet" href="<?= e(url('assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/auth.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/dashboard.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/css/admin.css')) ?>">
    <script defer src="<?= e(url('assets/js/main.js')) ?>"></script>
</head>
<body>
<header class="site-header">
    <div class="container nav-wrap">
        <a class="brand" href="<?= e(url('index.php')) ?>" aria-label="TravelMate home"><span class="brand-mark">T</span> TravelMate</a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">Menu</button>
        <nav class="primary-nav" id="primary-nav">
            <a href="<?= e(url('places.php')) ?>">Explore</a>
            <a href="<?= e(url('about.php')) ?>">About</a>
            <a href="<?= e(url('travel-tips.php')) ?>">Travel tips</a>
            <a href="<?= e(url('contact.php')) ?>">Contact</a>
            <?php if (isAdmin()): ?>
                <a href="<?= e(url('admin/dashboard.php')) ?>">Admin</a>
            <?php elseif (isLoggedIn()): ?>
                <a href="<?= e(url('dashboard.php')) ?>">My dashboard</a>
            <?php else: ?>
                <a href="<?= e(url('login.php')) ?>">Log in</a>
                <a class="nav-cta" href="<?= e(url('register.php')) ?>">Join TravelMate</a>
            <?php endif; ?>
            <?php if (isLoggedIn()): ?>
                <form class="nav-logout" method="post" action="<?= e(url('logout.php')) ?>">
                    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                    <button type="submit">Log out</button>
                </form>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main>
    <?php if ($flashMessage): ?>
        <div class="container flash-wrap"><div class="notice notice-<?= e($flashMessage['type']) ?>" role="status"><?= e($flashMessage['message']) ?></div></div>
    <?php endif; ?>
