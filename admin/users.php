<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$pageTitle = 'Manage users';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';
    $userId = isset($_POST['user_id']) ? filter_var($_POST['user_id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
    if (!$userId || (int) $userId === current_user_id()) {
        set_flash('error', 'You cannot change or delete your own administrator account.');
        redirect('admin/users.php');
    }
    $lookup = mysqli_prepare($conn, 'SELECT id, role FROM users WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($lookup, 'i', $userId);
    mysqli_stmt_execute($lookup);
    $target = mysqli_fetch_assoc(mysqli_stmt_get_result($lookup));
    mysqli_stmt_close($lookup);
    if (!$target) {
        set_flash('error', 'That user could not be found.');
        redirect('admin/users.php');
    }
    if ($action === 'update_role') {
        $role = isset($_POST['role']) && is_string($_POST['role']) ? $_POST['role'] : '';
        if (!in_array($role, array('user', 'admin'), true)) {
            set_flash('error', 'Choose a valid user role.');
        } else {
            $update = mysqli_prepare($conn, 'UPDATE users SET role = ? WHERE id = ?');
            mysqli_stmt_bind_param($update, 'si', $role, $userId);
            if (mysqli_stmt_execute($update)) {
                set_flash('success', 'User role updated.');
            } else {
                set_flash('error', 'The user role could not be updated.');
            }
            mysqli_stmt_close($update);
        }
    } elseif ($action === 'delete_user') {
        $delete = mysqli_prepare($conn, 'DELETE FROM users WHERE id = ?');
        mysqli_stmt_bind_param($delete, 'i', $userId);
        if (mysqli_stmt_execute($delete)) {
            set_flash('success', 'User account deleted. Their destinations were retained without an owner.');
        } else {
            set_flash('error', 'The user account could not be deleted.');
        }
        mysqli_stmt_close($delete);
    } else {
        set_flash('error', 'Unknown user management action.');
    }
    redirect('admin/users.php');
}
$query = isset($_GET['q']) && is_string($_GET['q']) ? trim(substr($_GET['q'], 0, 100)) : '';
if ($query !== '') {
    $search = '%' . mysqli_real_escape_string($conn, $query) . '%';
    $statement = mysqli_query($conn, "SELECT id, first_name, last_name, email, role, created_at FROM users WHERE first_name LIKE '$search' OR last_name LIKE '$search' OR email LIKE '$search' ORDER BY created_at DESC");
} else {
    $statement = mysqli_query($conn, 'SELECT id, first_name, last_name, email, role, created_at FROM users ORDER BY created_at DESC');
}
$users = mysqli_fetch_all($statement, MYSQLI_ASSOC);
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Administrator tools</p><h1>Manage users</h1><p>Review accounts and assign roles carefully.</p></div></section>
<section class="section"><div class="container">
    <form class="filter-form compact-filter" method="get" action="<?= e(url('admin/users.php')) ?>"><div class="field"><label for="q">Search name or email</label><input id="q" name="q" maxlength="100" value="<?= e($query) ?>" placeholder="Search users"></div><button class="button button-primary" type="submit">Search</button><?php if ($query !== ''): ?><a class="button button-outline" href="<?= e(url('admin/users.php')) ?>">Clear</a><?php endif; ?></form>
    <?php if ($users): ?><div class="table-wrap"><table><thead><tr><th>Name</th><th>Email</th><th>Registered</th><th>Role</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($users as $user): ?><tr><td><?= e($user['first_name'] . ' ' . $user['last_name']) ?><?= (int) $user['id'] === current_user_id() ? ' (you)' : '' ?></td><td><?= e($user['email']) ?></td><td><?= e(date('M j, Y', strtotime($user['created_at']))) ?></td><td><span class="status-badge status-<?= e($user['role']) ?>"><?= e(ucfirst($user['role'])) ?></span></td><td><?php if ((int) $user['id'] !== current_user_id()): ?><div class="table-actions"><form method="post" action="<?= e(url('admin/users.php')) ?>"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update_role"><input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>"><select name="role" aria-label="Role for <?= e($user['email']) ?>"><option value="user" <?= $user['role'] === 'user' ? 'selected' : '' ?>>User</option><option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option></select><button class="button button-small button-outline" type="submit">Save role</button></form><form method="post" action="<?= e(url('admin/users.php')) ?>" data-confirm="Delete this user? Their destinations will remain but be unassigned."><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_user"><input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>"><button class="button button-small button-danger" type="submit">Delete</button></form></div><?php else: ?><span class="muted">Protected account</span><?php endif; ?></td></tr><?php endforeach; ?>
    </tbody></table></div><?php else: ?><div class="empty-state"><h2>No users found</h2><p>Try changing your search.</p></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
