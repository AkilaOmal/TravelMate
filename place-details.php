<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
if (!$id) {
    http_response_code(404);
    $pageTitle = 'Destination not found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state"><h1>Destination not found</h1><p>The link may be incorrect.</p><a class="button button-primary" href="' . e(url('places.php')) . '">Explore destinations</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$statement = mysqli_prepare($conn, 'SELECT p.*, CONCAT(u.first_name, " ", u.last_name) AS creator_name FROM places p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = ? LIMIT 1');
mysqli_stmt_bind_param($statement, 'i', $id);
mysqli_stmt_execute($statement);
$place = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
mysqli_stmt_close($statement);
if (!$place || ($place['status'] !== 'approved' && !can_manage_place($place))) {
    http_response_code(404);
    $pageTitle = 'Destination not found';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section"><div class="container empty-state"><h1>Destination not found</h1><p>This destination is unavailable.</p><a class="button button-primary" href="' . e(url('places.php')) . '">Explore destinations</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}
$pageTitle = $place['name'];
require __DIR__ . '/includes/header.php';
?>
<article class="container detail-page">
    <p class="eyebrow"><a href="<?= e(url('places.php')) ?>">Destinations</a> / <?= e($place['country']) ?></p>
    <div class="detail-grid">
        <div class="detail-image"><?php if ($place['image']): ?><img src="<?= e($place['image']) ?>" alt="<?= e($place['name']) ?>" data-fallback-image><?php else: ?><div class="image-placeholder">TravelMate destination</div><?php endif; ?></div>
        <div class="detail-copy"><span class="category-pill static-pill"><?= e($place['category']) ?></span><h1><?= e($place['name']) ?></h1><p class="detail-location"><?= e($place['location']) ?><?= $place['location'] !== '' ? ', ' : '' ?><?= e($place['country']) ?></p><p class="lead"><?= nl2br(e($place['full_description'])) ?></p><h2>Travel tips</h2><p><?= $place['travel_tips'] !== '' ? nl2br(e($place['travel_tips'])) : 'Take time to explore respectfully and check local guidance before your visit.' ?></p><p class="muted">Added <?= e(date('F j, Y', strtotime($place['created_at']))) ?></p>
            <?php if ($place['status'] === 'pending'): ?><div class="notice notice-warning">This destination is pending approval and is only visible to you and administrators.</div><?php endif; ?>
            <?php if (can_manage_place($place)): ?><div class="button-row"><a class="button button-outline" href="<?= e(url('edit-place.php?id=' . (int) $place['id'])) ?>">Edit destination</a><form method="post" action="<?= e(url('delete-place.php')) ?>" data-confirm="Delete this destination permanently?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $place['id'] ?>"><button class="button button-danger" type="submit">Delete</button></form></div><?php endif; ?>
        </div>
    </div>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>
