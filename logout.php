<?php
require_once __DIR__ . '/includes/auth.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the logout button to end your session.');
}
verify_csrf();
$_SESSION = array();
if (ini_get('session.use_cookies')) {
    $parameters = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $parameters['path'], '', $parameters['secure'], $parameters['httponly']);
}
session_destroy();
setcookie('travelmate_email', '', array(
    'expires' => time() - 3600,
    'path' => app_base_url() === '' ? '/' : app_base_url() . '/',
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'httponly' => true,
    'samesite' => 'Lax',
));
header('Location: ' . url('index.php'));
exit;
