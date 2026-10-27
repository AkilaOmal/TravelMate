<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireAdmin();
$pageTitle = 'Contact messages';
$messages = mysqli_fetch_all(mysqli_query($conn, 'SELECT id, name, email, subject, message, created_at FROM contact_messages ORDER BY created_at DESC'), MYSQLI_ASSOC);
require __DIR__ . '/../includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">Administrator tools</p><h1>Contact messages</h1><p>Messages submitted by visitors through the contact form.</p></div></section>
<section class="section"><div class="container">
    <?php if ($messages): ?><div class="message-list"><?php foreach ($messages as $message): ?><article class="message-card"><div class="message-heading"><div><p class="eyebrow"><?= e(date('F j, Y \a\t g:i a', strtotime($message['created_at']))) ?></p><h2><?= e($message['subject']) ?></h2></div><a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a></div><p class="message-sender">From <?= e($message['name']) ?></p><p><?= nl2br(e($message['message'])) ?></p></article><?php endforeach; ?></div><?php else: ?><div class="empty-state"><h2>No messages yet</h2><p>Messages from the contact page will appear here.</p></div><?php endif; ?>
</div></section>
<?php require __DIR__ . '/../includes/footer.php'; ?>
