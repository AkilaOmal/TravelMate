<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Discover your next destination';
$featuredStatement = mysqli_query($conn, "SELECT id, name, country, location, category, short_description, image FROM places WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3");
$featuredPlaces = mysqli_fetch_all($featuredStatement, MYSQLI_ASSOC);
$recentStatement = mysqli_query($conn, "SELECT id, name, country, location, category, short_description, image FROM places WHERE status = 'approved' ORDER BY created_at DESC LIMIT 6");
$recentPlaces = mysqli_fetch_all($recentStatement, MYSQLI_ASSOC);
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="container hero-content">
         
        <h1>Discover Beautiful Places Around the World</h1>
        <p>Find inspiration, practical tips, and destinations worth adding to your story.</p>
        <form class="hero-search" method="get" action="<?= e(url('places.php')) ?>">
            <label class="sr-only" for="hero-search">Search destinations</label>
            <input id="hero-search" name="q" type="search" placeholder="Where would you like to go?">
            <button class="button button-primary" type="submit">Search places</button>
        </form>
        <a class="hero-explore" href="<?= e(url('places.php')) ?>">Explore destinations <span aria-hidden="true">&darr;</span></a>
    </div>
</section>
<section class="section">
    <div class="container">
        <div class="section-heading"><div><p class="eyebrow">Handpicked inspiration</p><h2>Featured destinations</h2></div><a class="text-link" href="<?= e(url('places.php')) ?>">See all destinations &rarr;</a></div>
        <?php if ($featuredPlaces): ?>
            <div class="place-grid"><?php foreach ($featuredPlaces as $place) { require __DIR__ . '/includes/place-card.php'; } ?></div>
        <?php else: ?>
            <div class="empty-state"><h3>New adventures are on the way</h3><p>There are no approved destinations yet. Check back soon or help us add the first one.</p></div>
        <?php endif; ?>
    </div>
</section>
<section class="section section-soft">
    <div class="container">
        <div class="section-heading"><div><p class="eyebrow">Fresh from the community</p><h2>Recently added</h2></div></div>
        <?php if ($recentPlaces): ?><div class="place-grid"><?php foreach ($recentPlaces as $place) { require __DIR__ . '/includes/place-card.php'; } ?></div><?php else: ?><p class="empty-state">Approved places will appear here as our travel community grows.</p><?php endif; ?>
    </div>
</section>
<section class="section intro-section">
    <div class="container intro-grid"><div><p class="eyebrow">Travel with curiosity</p><h2>A little inspiration goes a long way.</h2></div><div><p>TravelMate helps you discover places, learn from other travellers, and plan more thoughtful journeys. Browse ideas, share a destination you love, and make your next trip feel a little closer.</p><a class="button button-outline" href="<?= e(url('about.php')) ?>">More about TravelMate</a></div></div>
</section>
<section class="cta-band"><div class="container cta-content"><div><p class="eyebrow light-eyebrow">Your story belongs here</p><h2>Know a place people should discover?</h2><p>Join TravelMate and share a destination with fellow travellers.</p></div><a class="button button-light" href="<?= e(url(isLoggedIn() ? 'add-place.php' : 'register.php')) ?>"><?= isLoggedIn() ? 'Add a destination' : 'Create your account' ?></a></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
