<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}
$pageTitle = 'Create an account';
$errors = array();
$firstName = '';
$lastName = '';
$email = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $firstName = isset($_POST['first_name']) && is_string($_POST['first_name']) ? trim($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) && is_string($_POST['last_name']) ? trim($_POST['last_name']) : '';
    $email = isset($_POST['email']) && is_string($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) && is_string($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    if ($firstName === '' || strlen($firstName) > 80) {
        $errors[] = 'Enter your first name (up to 80 characters).';
    }
    if ($lastName === '' || strlen($lastName) > 80) {
        $errors[] = 'Enter your last name (up to 80 characters).';
    }
    if (!is_valid_email($email) || strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8 || strlen($password) > 72) {
        $errors[] = 'Your password must be between 8 and 72 characters.';
    }
    if ($password !== $confirmPassword) {
        $errors[] = 'The passwords do not match.';
    }
    if (!$errors) {
        $check = mysqli_prepare($conn, 'SELECT id FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($check, 's', $email);
        mysqli_stmt_execute($check);
        $existingUser = mysqli_fetch_assoc(mysqli_stmt_get_result($check));
        mysqli_stmt_close($check);
        if ($existingUser) {
            $errors[] = 'An account with that email already exists.';
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $insert = mysqli_prepare($conn, "INSERT INTO users (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, 'user')");
            mysqli_stmt_bind_param($insert, 'ssss', $firstName, $lastName, $email, $hashedPassword);
            if (mysqli_stmt_execute($insert)) {
                mysqli_stmt_close($insert);
                set_flash('success', 'Your account has been created. You can now log in.');
                redirect('login.php');
            }
            if (mysqli_errno($conn) === 1062) {
                $errors[] = 'An account with that email already exists.';
            } else {
                $errors[] = 'Your account could not be created. Please try again.';
            }
            mysqli_stmt_close($insert);
        }
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section"><div class="auth-card"><p class="eyebrow">Join our travel community</p><h1>Create an account</h1><p>Share destinations and keep your travel inspiration together.</p>
    <?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="<?= e(url('register.php')) ?>" data-validate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="field-row"><div class="field"><label for="first_name">First name</label><input id="first_name" name="first_name" required maxlength="80" autocomplete="given-name" value="<?= e($firstName) ?>"></div><div class="field"><label for="last_name">Last name</label><input id="last_name" name="last_name" required maxlength="80" autocomplete="family-name" value="<?= e($lastName) ?>"></div></div>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e($email) ?>"></div>
        <div class="field"><label for="password">Password <span class="field-hint">(8–72 characters)</span></label><input id="password" name="password" type="password" required minlength="8" maxlength="72" autocomplete="new-password"></div>
        <div class="field"><label for="confirm_password">Confirm password</label><input id="confirm_password" name="confirm_password" type="password" required minlength="8" maxlength="72" autocomplete="new-password" data-match="password"></div>
        <button class="button button-primary button-full" type="submit">Create account</button>
    </form>
    <p class="auth-footnote">Already a member? <a href="<?= e(url('login.php')) ?>">Log in</a></p>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
