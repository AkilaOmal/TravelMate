<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$pageTitle = 'Manage destinations';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
    $id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
    if ($action !== 'approve' || !$id) {
        set_flash('error', 'Invalid destination action.');
        redirect('admin/places.php');
    }
    $update = mysqli_prepare($conn, "UPDATE places SET status = 'approved', updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'pending'");
    mysqli_stmt_bind_param($update, 'i', $id);
    $approved = mysqli_stmt_execute($update) && mysqli_stmt_affected_rows($update) > 0;
    mysqli_stmt_close($update);
    set_flash($approved ? 'success' : 'error', $approved ? 'Destination approved and published.' : 'The pending destination could not be found.');
    redirect('admin/places.php');
}
$query = isset($_GET['q']) && is_string($_GET['q']) ? trim(substr($_GET['q'], 0, 100)) : '';
$status = isset($_GET['status']) && is_string($_GET['status']) ? $_GET['status'] : '';
$conditions = array('1=1');
if ($query !== '') {
    $search = '%' . mysqli_real_escape_string($conn, $query) . '%';
    $conditions[] = "(p.name LIKE '$search' OR p.country LIKE '$search' OR p.location LIKE '$search')";
}
if (in_array($status, array('pending', 'approved'), true)) {
    $conditions[] = "p.status = '" . mysqli_real_escape_string($conn, $status) . "'";
} else {
    $status = '';
}
$statement = mysqli_query($conn, 'SELECT p.id, p.name, p.country, p.category, p.status, p.created_at, u.first_name, u.last_name FROM places p LEFT JOIN users u ON p.user_id = u.id WHERE ' . implode(' AND ', $conditions) . ' ORDER BY p.created_at DESC');
$places = mysqli_fetch_all($statement, MYSQLI_ASSOC);
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Administrator tools</p><h1>Manage destinations</h1><p>Review submissions and manage published places.</p></div></section>
<section class="section"><div class="container">
    <form class="filter-form" method="get" action="<?= e(url('admin/places.php')) ?>"><div class="field"><label for="q">Search destinations</label><input id="q" name="q" maxlength="100" value="<?= e($query) ?>" placeholder="Name, country or region"></div><div class="field"><label for="status">Status</label><select id="status" name="status"><option value="">All statuses</option><option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option><option value="approved" <?= $status === 'approved' ? 'selected' : '' ?>>Approved</option></select></div><button class="button button-primary" type="submit">Filter</button><?php if ($query !== '' || $status !== ''): ?><a class="button button-outline" href="<?= e(url('admin/places.php')) ?>">Clear</a><?php endif; ?></form>
    <?php if ($places): ?><div class="table-wrap"><table><thead><tr><th>Destination</th><th>Category</th><th>Submitted by</th><th>Status</th><th>Added</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($places as $place): ?><tr><td><a href="<?= e(url('place-details.php?id=' . (int) $place['id'])) ?>"><?= e($place['name']) ?></a><span class="table-subtext"><?= e($place['country']) ?></span></td><td><?= e($place['category']) ?></td><td><?= e(trim($place['first_name'] . ' ' . $place['last_name']) ?: 'Former user / sample') ?></td><td><span class="status-badge status-<?= e($place['status']) ?>"><?= e(ucfirst($place['status'])) ?></span></td><td><?= e(date('M j, Y', strtotime($place['created_at']))) ?></td><td><div class="table-actions"><?php if ($place['status'] === 'pending'): ?><form method="post" action="<?= e(url('admin/places.php')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="approve"><input type="hidden" name="id" value="<?= (int) $place['id'] ?>"><button class="button button-small button-primary" type="submit">Approve</button></form><?php endif; ?><a class="button button-small button-outline" href="<?= e(url('edit-place.php?id=' . (int) $place['id'])) ?>">Edit</a><form method="post" action="<?= e(url('delete-place.php')) ?>" data-confirm="Delete this destination permanently?"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int) $place['id'] ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></div></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><h2>No destinations found</h2><p>Try changing your search or status filter.</p></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
