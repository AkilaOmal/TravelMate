<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
if (isLoggedIn()) {
    redirect(isAdmin() ? 'admin/dashboard.php' : 'dashboard.php');
}
$pageTitle = 'Log in';
$error = '';
$email = isset($_COOKIE['travelmate_email']) && is_string($_COOKIE['travelmate_email']) ? $_COOKIE['travelmate_email'] : '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = isset($_POST['email']) && is_string($_POST['email']) ? strtolower(trim($_POST['email'])) : '';
    $password = isset($_POST['password']) && is_string($_POST['password']) ? $_POST['password'] : '';
    if (!is_valid_email($email) || $password === '') {
        $error = 'Enter a valid email address and password.';
    } else {
        $statement = mysqli_prepare($conn, 'SELECT id, first_name, last_name, email, password, role FROM users WHERE email = ? LIMIT 1');
        mysqli_stmt_bind_param($statement, 's', $email);
        mysqli_stmt_execute($statement);
        $user = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
        mysqli_stmt_close($statement);
        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['role'] = $user['role'];
            $cookieOptions = array(
                'expires' => !empty($_POST['remember_email']) ? time() + (30 * 24 * 60 * 60) : time() - 3600,
                'path' => app_base_url() === '' ? '/' : app_base_url() . '/',
                'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
                'httponly' => true,
                'samesite' => 'Lax',
            );
            setcookie('travelmate_email', !empty($_POST['remember_email']) ? $email : '', $cookieOptions);
            redirect($user['role'] === 'admin' ? 'admin/dashboard.php' : 'dashboard.php');
        }
        $error = 'The email or password was not correct.';
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="auth-section"><div class="auth-card"><p class="eyebrow">Welcome back</p><h1>Log in to TravelMate</h1><p>Continue discovering places and sharing ideas.</p>
    <?php if ($error !== ''): ?><div class="notice notice-error" role="alert"><?= e($error) ?></div><?php endif; ?>
    <form method="post" action="<?= e(url('login.php')) ?>" data-validate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?= e($email) ?>"></div>
        <div class="field"><label for="password">Password</label><input id="password" name="password" type="password" required autocomplete="current-password"></div>
        <label class="checkbox-field"><input type="checkbox" name="remember_email" value="1"> Remember my email on this device</label>
        <p class="field-hint">Only your email is saved in a cookie; your password is never stored there.</p>
        <button class="button button-primary button-full" type="submit">Log in</button>
    </form>
    <p class="auth-footnote">New to TravelMate? <a href="<?= e(url('register.php')) ?>">Create an account</a></p>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
