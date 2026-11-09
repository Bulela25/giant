<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageKey = 'contact';
$pageTitle = 'Contact Us';
$pageDesc = 'Contact Giant Auto Upholstery in Johannesburg for advice, quotes and bookings.';
$user = current_user();
$prefillSubject = ($_GET['topic'] ?? '') === 'password' ? 'Password reset request' : '';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Talk to the workshop</h1>
        <p>Have a question or want advice before you book? We usually reply within 24 hours.</p>
    </div>
    <div class="stitch static" aria-hidden="true"></div>
</section>

<section class="section">
    <div class="container contact-layout">

        <div class="form-card">
            <h2>Send us a message</h2>
            <form id="contactForm" class="js-form" data-endpoint="api/contact.php" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="field">
                    <label for="c_name">Full name <span class="req">*</span></label>
                    <input id="c_name" name="full_name" type="text" autocomplete="name" required
                           value="<?= e(trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? ''))) ?>">
                    <p class="field-error" data-error-for="full_name"></p>
                </div>

                <div class="field">
                    <label for="c_email">Email address <span class="req">*</span></label>
                    <input id="c_email" name="email" type="email" autocomplete="email" required
                           placeholder="you@example.com" value="<?= e($user['email'] ?? '') ?>">
                    <p class="field-error" data-error-for="email"></p>
                </div>

                <div class="field">
                    <label for="c_subject">Subject <span class="req">*</span></label>
                    <input id="c_subject" name="subject" type="text" required placeholder="What can we help with?" value="<?= e($prefillSubject) ?>">
                    <p class="field-error" data-error-for="subject"></p>
                </div>

                <div class="field">
                    <label for="c_message">Message <span class="req">*</span></label>
                    <textarea id="c_message" name="message" rows="6" required placeholder="Tell us about the item and the damage."></textarea>
                    <p class="field-error" data-error-for="message"></p>
                </div>

                <div class="form-alert" role="alert" hidden></div>

                <button type="submit" class="btn btn-primary btn-lg">Send message</button>
            </form>
        </div>

        <div class="contact-info">
            <div class="contact-card">
                <span class="info-icon"><?= icon('pin', 22) ?></span>
                <div><h3>Workshop</h3><p>123 Main Road, Johannesburg<br>Gauteng, South Africa, 2000</p></div>
            </div>
            <div class="contact-card">
                <span class="info-icon"><?= icon('phone', 22) ?></span>
                <div><h3>Phone</h3><p><a href="tel:+27111234567">+27 11 123 4567</a><br><a href="https://wa.me/27712345678">+27 71 234 5678 (WhatsApp)</a></p></div>
            </div>
            <div class="contact-card">
                <span class="info-icon"><?= icon('mail', 22) ?></span>
                <div><h3>Email</h3><p><a href="mailto:info@giantupholstery.co.za">info@giantupholstery.co.za</a><br><a href="mailto:bookings@giantupholstery.co.za">bookings@giantupholstery.co.za</a></p></div>
            </div>
            <div class="contact-card">
                <span class="info-icon"><?= icon('clock', 22) ?></span>
                <div><h3>Opening hours</h3><p>Mon to Fri: 08:00 to 17:00<br>Saturday: 09:00 to 14:00<br>Sunday: closed</p></div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
