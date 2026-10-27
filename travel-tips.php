<?php
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Travel tips';
$tips = array(
    array('Pack thoughtfully', 'Choose versatile clothing, bring essential medication, and leave room for the things you may find along the way.'),
    array('Plan your budget', 'Estimate transport, meals, accommodation, and activities. Keep a small reserve for unexpected costs.'),
    array('Look after your safety', 'Learn local emergency numbers, share your itinerary with someone you trust, and stay aware of your surroundings.'),
    array('Respect local cultures', 'Learn a few local phrases, follow community customs, and ask before photographing people or private places.'),
    array('Check the weather', 'Review the forecast shortly before travelling and pack for changing conditions at your destination.'),
    array('Protect important documents', 'Keep copies of your identification and bookings in a separate secure place from the originals.'),
    array('Travel responsibly', 'Reduce waste, respect wildlife, support local businesses, and follow marked trails and local guidance.'),
);
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">A little preparation goes a long way</p><h1>Travel tips for better journeys</h1><p>Simple reminders to help you travel with confidence and care.</p></div></section>
<section class="section"><div class="container tip-grid"><?php foreach ($tips as $index => $tip): ?><article class="tip-card"><span class="tip-number"><?= sprintf('%02d', $index + 1) ?></span><h2><?= e($tip[0]) ?></h2><p><?= e($tip[1]) ?></p></article><?php endforeach; ?></div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
