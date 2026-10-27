<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$pageTitle = 'Admin dashboard';
$stats = array();
$stats['users'] = (int) mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM users'))[0];
$stats['places'] = (int) mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM places'))[0];
$stats['pending'] = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM places WHERE status = 'pending'"))[0];
$stats['approved'] = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM places WHERE status = 'approved'"))[0];
$stats['messages'] = (int) mysqli_fetch_row(mysqli_query($conn, 'SELECT COUNT(*) FROM contact_messages'))[0];
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">TravelMate administration</p><h1>Admin dashboard</h1><p>Review community content and manage the website.</p></div></section>
<section class="section"><div class="container">
    <div class="stats-grid"><?php foreach (array('users' => 'Total users', 'places' => 'Total destinations', 'pending' => 'Pending review', 'approved' => 'Approved destinations', 'messages' => 'Contact messages') as $key => $label): ?><article class="stat-card"><span><?= e($label) ?></span><strong><?= $stats[$key] ?></strong></article><?php endforeach; ?></div>
    <div class="admin-link-grid">
        <a class="admin-link-card" href="<?= e(url('admin/users.php')) ?>"><span class="admin-icon">01</span><h2>Manage users</h2><p>Search accounts and update user roles.</p></a>
        <a class="admin-link-card" href="<?= e(url('admin/places.php')) ?>"><span class="admin-icon">02</span><h2>Manage destinations</h2><p>Review pending submissions and maintain place listings.</p></a>
        <a class="admin-link-card" href="<?= e(url('admin/places.php?status=pending')) ?>"><span class="admin-icon">03</span><h2>Review pending</h2><p><?= $stats['pending'] ?> destination(s) are waiting for review.</p></a>
        <a class="admin-link-card" href="<?= e(url('admin/messages.php')) ?>"><span class="admin-icon">04</span><h2>Contact messages</h2><p>Read messages sent through the public contact form.</p></a>
    </div>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
