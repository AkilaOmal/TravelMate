<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'About TravelMate';
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">A companion for curious travellers</p><h1>About TravelMate</h1><p>Good journeys begin with a little inspiration and a willingness to explore.</p></div></section>
<section class="section"><div class="container prose"><h2>Discover more, together</h2><p>TravelMate is a simple place to discover destinations and learn from other travellers. From familiar favourites to hidden corners, our goal is to make it easier to find places that spark your curiosity.</p><p>We believe travel can create memorable experiences and a deeper appreciation for the people, cultures, and environments we encounter. Our community can share destinations and useful tips to help others travel thoughtfully.</p><h2>Our purpose</h2><p>TravelMate brings destination inspiration and practical advice together in one friendly space. We hope it helps you plan a trip that is rewarding for you and respectful of the places you visit.</p><a class="button button-primary" href="<?= e(url('places.php')) ?>">Start exploring</a></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
