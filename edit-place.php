<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$id = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT, array('options' => array('min_range' => 1))) : false;
if (!$id) {
    set_flash('error', 'Choose a valid destination to edit.');
    redirect(isAdmin() ? 'admin/places.php' : 'dashboard.php');
}
$statement = mysqli_prepare($conn, 'SELECT * FROM places WHERE id = ? LIMIT 1');
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
    exit('You do not have permission to edit this destination.');
}
$pageTitle = 'Edit ' . $place['name'];
$categories = array('Beach', 'Mountain', 'Historical', 'Nature', 'City', 'Other');
$fields = array('name', 'country', 'location', 'category', 'short_description', 'full_description', 'travel_tips');
$values = array();
foreach ($fields as $field) { $values[$field] = $place[$field] === null ? '' : $place[$field]; }
$errors = array();
$uploadedImage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($fields as $field) {
        $values[$field] = isset($_POST[$field]) && is_string($_POST[$field]) ? trim($_POST[$field]) : '';
    }
    if ($values['name'] === '' || strlen($values['name']) > 150) { $errors[] = 'Enter a place name (up to 150 characters).'; }
    if ($values['country'] === '' || strlen($values['country']) > 100) { $errors[] = 'Enter a country (up to 100 characters).'; }
    if (strlen($values['location']) > 150) { $errors[] = 'Location must be 150 characters or fewer.'; }
    if (!in_array($values['category'], $categories, true)) { $errors[] = 'Choose a valid category.'; }
    if ($values['short_description'] === '' || strlen($values['short_description']) > 500) { $errors[] = 'Enter a short description (up to 500 characters).'; }
    if ($values['full_description'] === '' || strlen($values['full_description']) > 10000) { $errors[] = 'Enter a full description (up to 10,000 characters).'; }
    if (strlen($values['travel_tips']) > 5000) { $errors[] = 'Travel tips must be 5,000 characters or fewer.'; }
    if (!$errors) {
        $imageUpload = save_place_image(isset($_FILES['image']) ? $_FILES['image'] : array());
        if ($imageUpload['error'] !== null) {
            $errors[] = $imageUpload['error'];
        } else {
            $uploadedImage = $imageUpload['path'];
        }
    }
    if (!$errors) {
        $imagePath = $uploadedImage !== null ? $uploadedImage : $place['image'];
        $update = mysqli_prepare($conn, 'UPDATE places SET name=?, country=?, location=?, category=?, short_description=?, full_description=?, travel_tips=?, image=?, updated_at=CURRENT_TIMESTAMP WHERE id=?');
        mysqli_stmt_bind_param($update, 'ssssssssi', $values['name'], $values['country'], $values['location'], $values['category'], $values['short_description'], $values['full_description'], $values['travel_tips'], $imagePath, $id);
        if (mysqli_stmt_execute($update)) {
            mysqli_stmt_close($update);
            if ($uploadedImage !== null) {
                delete_place_image_file($place['image']);
            }
            set_flash('success', 'Destination updated.');
            redirect(isAdmin() ? 'admin/places.php' : 'dashboard.php');
        }
        mysqli_stmt_close($update);
        if ($uploadedImage !== null) {
            delete_place_image_file($uploadedImage);
        }
        $errors[] = 'The destination could not be updated. Please try again.';
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Keep your information up to date</p><h1>Edit destination</h1></div></section>
<section class="section"><div class="container narrow-container">
    <?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form class="form-card" method="post" action="<?= e(url('edit-place.php?id=' . (int) $id)) ?>" enctype="multipart/form-data" data-validate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="field"><label for="name">Place name</label><input id="name" name="name" required maxlength="150" value="<?= e($values['name']) ?>"></div>
        <div class="field-row"><div class="field"><label for="country">Country</label><input id="country" name="country" required maxlength="100" value="<?= e($values['country']) ?>"></div><div class="field"><label for="location">Location / region</label><input id="location" name="location" maxlength="150" value="<?= e($values['location']) ?>"></div></div>
        <div class="field"><label for="category">Category</label><select id="category" name="category" required><option value="">Choose a category</option><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= $values['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="short_description">Short description</label><textarea id="short_description" name="short_description" rows="3" maxlength="500" required><?= e($values['short_description']) ?></textarea></div>
        <div class="field"><label for="full_description">Full description</label><textarea id="full_description" name="full_description" rows="7" maxlength="10000" required><?= e($values['full_description']) ?></textarea></div>
        <div class="field"><label for="travel_tips">Travel tips</label><textarea id="travel_tips" name="travel_tips" rows="4" maxlength="5000"><?= e($values['travel_tips']) ?></textarea></div>
        <?php if (!empty($place['image'])): ?><div class="current-image"><p class="field-hint">Current image</p><img src="<?= e($place['image']) ?>" alt="<?= e($place['name']) ?>" data-fallback-image></div><?php endif; ?>
        <div class="field"><label for="image">Replace image (optional)</label><input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"><span class="field-hint">Choose a JPEG, PNG, GIF, or WebP image up to 5 MB. Leave empty to keep the current image.</span></div>
        <div class="button-row"><button class="button button-primary" type="submit">Save changes</button><a class="button button-outline" href="<?= e(url(isAdmin() ? 'admin/places.php' : 'dashboard.php')) ?>">Cancel</a></div>
    </form>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
