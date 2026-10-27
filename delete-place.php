<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the destination delete button to remove a place.');
}
verify_csrf();
$id = isset($_POST['id']) ? filter_var($_POST['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
if (!$id) {
    set_flash('error', 'Choose a valid destination to delete.');
    redirect(isAdmin() ? 'admin/places.php' : 'dashboard.php');
}
$statement = mysqli_prepare($conn, 'SELECT id, user_id, image FROM places WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($statement, 'i', $id);
mysqli_stmt_execute($statement);
$place = mysqli_fetch_assoc(mysqli_stmt_get_result($statement));
mysqli_stmt_close($statement);
if (!$place) {
    set_flash('error', 'That destination could not be found.');
    redirect(isAdmin() ? 'admin/places.php' : 'dashboard.php');
}
if (!can_manage_place($place)) {
    http_response_code(403);
    exit('You do not have permission to delete this destination.');
}
$delete = mysqli_prepare($conn, 'DELETE FROM places WHERE id = ?');
mysqli_stmt_bind_param($delete, 'i', $id);
mysqli_stmt_execute($delete);
mysqli_stmt_close($delete);
delete_place_image_file($place['image']);
set_flash('success', 'Destination deleted.');
redirect(isAdmin() ? 'admin/places.php' : 'dashboard.php');
