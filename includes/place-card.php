<article class="place-card">
    <a class="place-card-image" href="<?= e(url('place-details.php?id=' . (int) $place['id'])) ?>">
        <?php if (!empty($place['image'])): ?>
            <img src="<?= e($place['image']) ?>" alt="<?= e($place['name']) ?>" data-fallback-image>
        <?php else: ?>
            <div class="image-placeholder">TravelMate destination</div>
        <?php endif; ?>
        <span class="category-pill"><?= e($place['category']) ?></span>
    </a>
    <div class="place-card-body">
        <p class="eyebrow"><?= e($place['country']) ?><?= !empty($place['location']) ? ' · ' . e($place['location']) : '' ?></p>
        <h3><a href="<?= e(url('place-details.php?id=' . (int) $place['id'])) ?>"><?= e($place['name']) ?></a></h3>
        <p><?= e($place['short_description']) ?></p>
        <a class="text-link" href="<?= e(url('place-details.php?id=' . (int) $place['id'])) ?>">View details <span aria-hidden="true">&rarr;</span></a>
    </div>
</article>
