<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    session_set_cookie_params(array(
        'lifetime' => 0,
        'path' => '/',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ));
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function app_base_url()
{
    $directory = str_replace('\\', '/', dirname(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '/'));
    $directory = rtrim($directory, '/');
    if ($directory === '' || $directory === '.') {
        return '';
    }

    $lastSegment = basename($directory);
    if ($lastSegment === 'admin' || $lastSegment === 'setup') {
        $directory = rtrim(dirname($directory), '/');
    }

    return $directory === '.' ? '' : $directory;
}

function url($path = '')
{
    $base = app_base_url();
    return $base . ($path === '' ? '/' : '/' . ltrim($path, '/'));
}

function redirect($path)
{
    header('Location: ' . url($path));
    exit;
}

function isLoggedIn()
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

function isAdmin()
{
    return isLoggedIn() && $_SESSION['role'] === 'admin';
}

function requireLogin()
{
    if (!isLoggedIn()) {
        set_flash('error', 'Please log in to continue.');
        redirect('login.php');
    }
}

function requireAdmin()
{
    if (!isAdmin()) {
        http_response_code(403);
        $pageTitle = 'Access denied';
        require __DIR__ . '/header.php';
        echo '<section class="section"><div class="container"><div class="notice notice-error">You do not have permission to access this page.</div><a class="button" href="' . e(url('index.php')) . '">Return home</a></div></section>';
        require __DIR__ . '/footer.php';
        exit;
    }
}

function csrf_token()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf()
{
    $submitted = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!is_string($submitted) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submitted)) {
        http_response_code(400);
        exit('The form expired or could not be verified. Please go back and try again.');
    }
}

function set_flash($type, $message)
{
    $_SESSION['flash'] = array('type' => $type, 'message' => $message);
}

function get_flash()
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function is_valid_email($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_valid_http_url($value)
{
    if ($value === '') {
        return true;
    }
    $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
    return filter_var($value, FILTER_VALIDATE_URL) !== false && ($scheme === 'http' || $scheme === 'https');
}

function current_user_id()
{
    return isLoggedIn() ? (int) $_SESSION['user_id'] : 0;
}

function can_manage_place($place)
{
    return isAdmin() || (isLoggedIn() && (int) $place['user_id'] === current_user_id());
}

if (isset($_SESSION['user_id'])) {
    $userId = (int) $_SESSION['user_id'];
    $sessionUser = mysqli_prepare($conn, 'SELECT first_name, last_name, role FROM users WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($sessionUser, 'i', $userId);
    mysqli_stmt_execute($sessionUser);
    $currentUser = mysqli_fetch_assoc(mysqli_stmt_get_result($sessionUser));
    mysqli_stmt_close($sessionUser);
    if (!$currentUser) {
        unset($_SESSION['user_id'], $_SESSION['name'], $_SESSION['role']);
        session_regenerate_id(true);
        set_flash('error', 'Your account is no longer available. Please log in again.');
    } else {
        if (isset($_SESSION['role']) && $_SESSION['role'] !== $currentUser['role']) {
            session_regenerate_id(true);
        }
        $_SESSION['name'] = $currentUser['first_name'] . ' ' . $currentUser['last_name'];
        $_SESSION['role'] = $currentUser['role'];
    }
}
