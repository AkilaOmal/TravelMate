<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Explore destinations';
$query = isset($_GET['q']) && is_string($_GET['q']) ? trim(substr($_GET['q'], 0, 100)) : '';
$category = isset($_GET['category']) && is_string($_GET['category']) ? trim($_GET['category']) : '';
$categories = array('Beach', 'Mountain', 'Historical', 'Nature', 'City', 'Other');
$conditions = array("status = 'approved'");
if ($query !== '') {
    $searchValue = '%' . mysqli_real_escape_string($conn, $query) . '%';
    $conditions[] = "(name LIKE '$searchValue' OR country LIKE '$searchValue' OR location LIKE '$searchValue' OR short_description LIKE '$searchValue' OR full_description LIKE '$searchValue')";
}
if ($category !== '' && in_array($category, $categories, true)) {
    $conditions[] = "category = '" . mysqli_real_escape_string($conn, $category) . "'";
} elseif ($category !== '') {
    $category = '';
}
$statement = mysqli_query($conn, 'SELECT id, name, country, location, category, short_description, image FROM places WHERE ' . implode(' AND ', $conditions) . ' ORDER BY created_at DESC');
$places = mysqli_fetch_all($statement, MYSQLI_ASSOC);
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Find your next favourite place</p><h1>Explore destinations</h1><p>Browse the places our community is excited to share.</p></div></section>
<section class="section"><div class="container">
    <form class="filter-form" method="get" action="<?= e(url('places.php')) ?>">
        <div class="field"><label for="q">Search places</label><input id="q" name="q" type="search" maxlength="100" value="<?= e($query) ?>" placeholder="Name, country or keyword"></div>
        <div class="field"><label for="category">Category</label><select id="category" name="category"><option value="">All categories</option><?php foreach ($categories as $option): ?><option value="<?= e($option) ?>" <?= $category === $option ? 'selected' : '' ?>><?= e($option) ?></option><?php endforeach; ?></select></div>
        <button class="button button-primary" type="submit">Find places</button>
        <?php if ($query !== '' || $category !== ''): ?><a class="button button-outline" href="<?= e(url('places.php')) ?>">Clear</a><?php endif; ?>
    </form>
    <?php if ($places): ?><div class="place-grid"><?php foreach ($places as $place) { require __DIR__ . '/includes/place-card.php'; } ?></div><?php else: ?><div class="empty-state"><h2>No destinations found</h2><p>Try a different search or category. New places are added regularly.</p></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
