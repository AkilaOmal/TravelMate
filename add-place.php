<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();
$pageTitle = 'Add a destination';
$categories = array('Beach', 'Mountain', 'Historical', 'Nature', 'City', 'Other');
$fields = array('name', 'country', 'location', 'category', 'short_description', 'full_description', 'travel_tips');
$values = array_fill_keys($fields, '');
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
        $status = isAdmin() ? 'approved' : 'pending';
        $userId = current_user_id();
        $statement = mysqli_prepare($conn, 'INSERT INTO places (user_id, name, country, location, category, short_description, full_description, travel_tips, image, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'isssssssss', $userId, $values['name'], $values['country'], $values['location'], $values['category'], $values['short_description'], $values['full_description'], $values['travel_tips'], $uploadedImage, $status);
        if (mysqli_stmt_execute($statement)) {
            mysqli_stmt_close($statement);
            set_flash('success', isAdmin() ? 'Destination added and published.' : 'Destination submitted. It will appear publicly after administrator approval.');
            redirect(isAdmin() ? 'admin/places.php' : 'dashboard.php');
        }
        mysqli_stmt_close($statement);
        if ($uploadedImage !== null) {
            delete_place_image_file($uploadedImage);
        }
        $errors[] = 'The destination could not be saved. Please try again.';
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Share a place you love</p><h1>Add a destination</h1><p>Tell the community what makes this place worth discovering.</p></div></section>
<section class="section"><div class="container narrow-container">
    <?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if (!isAdmin()): ?><p class="notice notice-info">New destinations are reviewed by an administrator before they appear publicly.</p><?php endif; ?>
    <form class="form-card" method="post" action="<?= e(url('add-place.php')) ?>" enctype="multipart/form-data" data-validate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="field"><label for="name">Place name</label><input id="name" name="name" required maxlength="150" value="<?= e($values['name']) ?>"></div>
        <div class="field-row"><div class="field"><label for="country">Country</label><input id="country" name="country" required maxlength="100" value="<?= e($values['country']) ?>"></div><div class="field"><label for="location">Location / region</label><input id="location" name="location" maxlength="150" value="<?= e($values['location']) ?>"></div></div>
        <div class="field"><label for="category">Category</label><select id="category" name="category" required><option value="">Choose a category</option><?php foreach ($categories as $category): ?><option value="<?= e($category) ?>" <?= $values['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="short_description">Short description</label><textarea id="short_description" name="short_description" rows="3" maxlength="500" required><?= e($values['short_description']) ?></textarea></div>
        <div class="field"><label for="full_description">Full description</label><textarea id="full_description" name="full_description" rows="7" maxlength="10000" required><?= e($values['full_description']) ?></textarea></div>
        <div class="field"><label for="travel_tips">Travel tips (optional)</label><textarea id="travel_tips" name="travel_tips" rows="4" maxlength="5000"><?= e($values['travel_tips']) ?></textarea></div>
        <div class="field"><label for="image">Place image (optional)</label><input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"><span class="field-hint">Choose a JPEG, PNG, GIF, or WebP image up to 5 MB.</span></div>
        <button class="button button-primary" type="submit">Submit destination</button>
    </form>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
