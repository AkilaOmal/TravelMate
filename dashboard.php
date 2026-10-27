<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();
if (isAdmin()) {
    redirect('admin/dashboard.php');
}
$pageTitle = 'My dashboard';
$userId = current_user_id();
$statement = mysqli_prepare($conn, 'SELECT id, name, country, location, category, short_description, image, status, created_at FROM places WHERE user_id = ? ORDER BY created_at DESC');
mysqli_stmt_bind_param($statement, 'i', $userId);
mysqli_stmt_execute($statement);
$myPlaces = mysqli_fetch_all(mysqli_stmt_get_result($statement), MYSQLI_ASSOC);
mysqli_stmt_close($statement);
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Your TravelMate space</p><h1>Welcome, <?= e($_SESSION['name']) ?></h1><p>Manage your shared destinations and keep exploring.</p></div></section>
<section class="section"><div class="container">
    <div class="dashboard-actions"><a class="button button-primary" href="<?= e(url('add-place.php')) ?>">Add a destination</a><a class="button button-outline" href="<?= e(url('places.php')) ?>">Explore destinations</a></div>
    <div class="section-heading"><div><p class="eyebrow">Your contributions</p><h2>Destinations you added</h2></div></div>
    <?php if ($myPlaces): ?><div class="place-grid"><?php foreach ($myPlaces as $place): ?><div class="dashboard-place"><?php require __DIR__ . '/includes/place-card.php'; ?><div class="place-management"><span class="status-badge status-<?= e($place['status']) ?>"><?= e(ucfirst($place['status'])) ?></span><div class="button-row"><a class="button button-small button-outline" href="<?= e(url('edit-place.php?id=' . (int) $place['id'])) ?>">Edit</a><form method="post" action="<?= e(url('delete-place.php')) ?>" data-confirm="Delete this destination permanently?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $place['id'] ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></div></div></div><?php endforeach; ?></div><?php else: ?><div class="empty-state"><h3>Your travel story starts here</h3><p>You have not added a destination yet.</p><a class="button button-primary" href="<?= e(url('add-place.php')) ?>">Add your first destination</a></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
