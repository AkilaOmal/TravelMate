<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
$pageTitle = 'Contact us';
$errors = array();
$values = array('name' => '', 'email' => '', 'subject' => '', 'message' => '');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $unused) {
        $values[$key] = isset($_POST[$key]) && is_string($_POST[$key]) ? trim($_POST[$key]) : '';
    }
    if ($values['name'] === '' || strlen($values['name']) > 120) {
        $errors[] = 'Enter your name (up to 120 characters).';
    }
    if (!is_valid_email($values['email']) || strlen($values['email']) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if ($values['subject'] === '' || strlen($values['subject']) > 180) {
        $errors[] = 'Enter a subject (up to 180 characters).';
    }
    if ($values['message'] === '' || strlen($values['message']) > 10000) {
        $errors[] = 'Enter a message (up to 10,000 characters).';
    }
    if (!$errors) {
        $statement = mysqli_prepare($conn, 'INSERT INTO contact_messages (name, email, subject, message) VALUES (?, ?, ?, ?)');
        mysqli_stmt_bind_param($statement, 'ssss', $values['name'], $values['email'], $values['subject'], $values['message']);
        if (mysqli_stmt_execute($statement)) {
            mysqli_stmt_close($statement);
            set_flash('success', 'Thanks for getting in touch. Your message has been received.');
            redirect('contact.php');
        }
        mysqli_stmt_close($statement);
        $errors[] = 'Your message could not be saved. Please try again.';
    }
}
require __DIR__ . '/includes/header.php';
?>
<section class="page-hero"><div class="container"><p class="eyebrow">We would love to hear from you</p><h1>Contact us</h1><p>Send the TravelMate team a note using the form below.</p></div></section>
<section class="section"><div class="container form-layout"><div class="form-intro"><h2>Let’s talk travel</h2><p>Questions, feedback, or a destination suggestion are all welcome. Your message will be saved for our team to review.</p><p><strong>Email:</strong> hello@travelmate.local</p></div>
    <form class="form-card" method="post" action="<?= e(url('contact.php')) ?>" data-validate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <?php if ($errors): ?><div class="notice notice-error"><ul><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
        <div class="field"><label for="name">Name</label><input id="name" name="name" required maxlength="120" value="<?= e($values['name']) ?>"></div>
        <div class="field"><label for="email">Email</label><input id="email" name="email" type="email" required maxlength="190" value="<?= e($values['email']) ?>"></div>
        <div class="field"><label for="subject">Subject</label><input id="subject" name="subject" required maxlength="180" value="<?= e($values['subject']) ?>"></div>
        <div class="field"><label for="message">Message</label><textarea id="message" name="message" rows="6" required maxlength="10000"><?= e($values['message']) ?></textarea></div>
        <button class="button button-primary" type="submit">Send message</button>
    </form>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
