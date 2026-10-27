<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
$adminResult = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'admin'");
$adminRow = mysqli_fetch_assoc($adminResult);
$adminCount = (int) $adminRow['total'];
$pageTitle = 'Create first administrator';
$errors = array();
$name = '';
$email = '';
if ($adminCount === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $name = isset($_POST['name']) && is_string($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if ($name === '' || strlen($name) > 160) { $errors[] = 'Enter a name up to 160 characters.'; }
    if (!is_valid_email($email) || strlen($email) > 190) { $errors[] = 'Enter a valid email address.'; }
    if (strlen($password) < 12 || strlen($password) > 72) { $errors[] = 'Use a password between 12 and 72 characters.'; }
    if (!$errors) {
        $parts = preg_split('/\s+/', $name, 2);
        $firstName = $parts[0];
        $lastName = isset($parts[1]) ? $parts[1] : '';
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $statement = mysqli_prepare($conn, "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'admin')");
        mysqli_stmt_bind_param($statement, 'ssss', $firstName, $lastName, $email, $hashedPassword);
        if (mysqli_stmt_execute($statement)) {
            mysqli_stmt_close($statement);
            set_flash('success', 'The first administrator account is ready. Remove or disable setup/create-admin.php now.');
            redirect('login.php');
        }
        $errors[] = mysqli_errno($conn) === 1062
            ? 'An account with that email already exists. Use another address or sign in.'
            : 'The administrator account could not be created. Please try again.';
        mysqli_stmt_close($statement);
    }
}
require __DIR__ . '/../includes/header.php';
?>
<section class="auth-section"><div class="auth-card"><p class="eyebrow">One-time local setup</p><h1>Create the first admin</h1>
    <?php if ($adminCount > 0): ?><div class="notice notice-info">An administrator already exists. This setup page is disabled. Delete this file after creating your first administrator.</div>
    <?php else: ?>
        <p>Use this one-time form only on your local XAMPP installation. It will stop working once an administrator exists.</p>
        <?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <form method="post" action="<?= e(url('setup/create-admin.php')) ?>" data-validate><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><div class="field"><label for="name">Full name</label><input id="name" name="name" required maxlength="160" value="<?= e($name) ?>"></div><div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="190" value="<?= e($email) ?>"></div><div class="field"><label for="password">Password (12–72 characters)</label><input id="password" name="password" type="password" required minlength="12" maxlength="72" autocomplete="new-password"></div><button class="button button-primary button-full" type="submit">Create administrator</button></form>
    <?php endif; ?>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
