<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageKey = 'booking';
$pageTitle = 'Book a Repair';
$pageDesc = 'Book your upholstery repair online. Choose a service, tell us about the damage and we will confirm within 24 hours.';
$services = get_services();
$user = current_user();
$preselect = $_GET['service'] ?? '';
$icons = ['vehicle' => 'car', 'home' => 'sofa', 'custom' => 'wand'];
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Book your repair</h1>
        <p>Tell us what you need and we will contact you within 24 hours to confirm.</p>
    </div>
    <div class="stitch static" aria-hidden="true"></div>
</section>

<section class="section">
    <div class="container narrow-wide">

        <ol class="stepper" id="stepper" aria-label="Booking progress">
            <li class="is-active" data-step-label="1"><span>1</span> Service</li>
            <li data-step-label="2"><span>2</span> Your details</li>
            <li data-step-label="3"><span>3</span> Confirm</li>
        </ol>

        <div class="form-card">
            <form id="bookingForm" class="js-form wizard" data-endpoint="api/booking.php" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                <!-- STEP 1 -->
                <div class="wizard-panel is-active" data-step="1">
                    <h2>Which service do you need?</h2>
                    <p class="muted">Starting prices shown. We confirm the final quote after the free assessment.</p>

                    <div class="choice-grid" role="radiogroup" aria-label="Service">
                        <?php foreach ($services as $s): ?>
                            <label class="choice">
                                <input type="radio" name="service_id" value="<?= (int)$s['id'] ?>"
                                       data-name="<?= e($s['name']) ?>"
                                       <?= $preselect === $s['slug'] ? 'checked' : '' ?>>
                                <span class="choice-body">
                                    <span class="info-icon"><?= icon($icons[$s['category']] ?? 'layers', 22) ?></span>
                                    <span class="choice-text">
                                        <strong><?= e($s['name']) ?></strong>
                                        <small><?= e(money_from($s['price_from'])) ?></small>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <p class="field-error" data-error-for="service_id"></p>
                </div>

                <!-- STEP 2 -->
                <div class="wizard-panel" data-step="2">
                    <h2>Your details and the job</h2>
                    <p class="muted">Fields marked <span class="req">*</span> are required.</p>

                    <div class="form-row">
                        <div class="field">
                            <label for="b_first">First name <span class="req">*</span></label>
                            <input id="b_first" name="first_name" type="text" autocomplete="given-name" required
                                   value="<?= e($user['first_name'] ?? '') ?>">
                            <p class="field-error" data-error-for="first_name"></p>
                        </div>
                        <div class="field">
                            <label for="b_last">Last name <span class="req">*</span></label>
                            <input id="b_last" name="last_name" type="text" autocomplete="family-name" required
                                   value="<?= e($user['last_name'] ?? '') ?>">
                            <p class="field-error" data-error-for="last_name"></p>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="b_email">Email address <span class="req">*</span></label>
                            <input id="b_email" name="email" type="email" autocomplete="email" required
                                   placeholder="you@example.com" value="<?= e($user['email'] ?? '') ?>">
                            <p class="field-error" data-error-for="email"></p>
                        </div>
                        <div class="field">
                            <label for="b_phone">Mobile number <span class="req">*</span></label>
                            <input id="b_phone" name="phone" type="tel" autocomplete="tel" required
                                   placeholder="071 234 5678" data-validate="sa-phone"
                                   value="<?= e($user['phone'] ?? '') ?>">
                            <p class="field-error" data-error-for="phone"></p>
                        </div>
                    </div>

                    <div class="field">
                        <label for="b_address">Service address <span class="req">*</span></label>
                        <textarea id="b_address" name="address" rows="2" required
                                  placeholder="Street, suburb and city where the work will happen"></textarea>
                        <p class="field-error" data-error-for="address"></p>
                    </div>

                    <div class="form-row">
                        <div class="field">
                            <label for="b_date">Preferred date <span class="req">*</span></label>
                            <input id="b_date" name="preferred_date" type="date" required data-min-today>
                            <p class="field-error" data-error-for="preferred_date"></p>
                        </div>
                        <div class="field">
                            <label for="b_time">Preferred time <span class="req">*</span></label>
                            <select id="b_time" name="preferred_time" required>
                                <option value="">Choose a time slot</option>
                                <option>Morning (08:00-12:00)</option>
                                <option>Afternoon (12:00-16:00)</option>
                                <option>Evening (16:00-18:00)</option>
                            </select>
                            <p class="field-error" data-error-for="preferred_time"></p>
                        </div>
                    </div>

                    <div class="field">
                        <label for="b_damage">What is damaged?</label>
                        <textarea id="b_damage" name="damage_description" rows="4"
                                  placeholder="For example: split on the driver's seat bolster, about 10 cm long."></textarea>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div class="wizard-panel" data-step="3">
                    <h2>Check and send</h2>
                    <p class="muted">Make sure everything is right. You can go back to change anything.</p>

                    <dl class="review" id="reviewList"></dl>

                    <div class="check-field">
                        <input type="checkbox" id="b_terms" name="terms" value="1" required>
                        <label for="b_terms">
                            I agree that Giant Auto Upholstery may store these details and contact me about this booking. <span class="req">*</span>
                        </label>
                    </div>
                    <p class="field-error" data-error-for="terms"></p>
                </div>

                <div class="form-alert" id="bookingAlert" role="alert" hidden></div>

                <div class="wizard-nav">
                    <button type="button" class="btn btn-ghost" data-wizard-back hidden>Back</button>
                    <button type="button" class="btn btn-primary" data-wizard-next>Continue</button>
                    <button type="submit" class="btn btn-thread" data-wizard-submit hidden>Send booking request</button>
                </div>
            </form>

            <div class="success-panel" id="bookingSuccess" hidden>
                <span class="success-icon"><?= icon('check', 30) ?></span>
                <h2>Booking request received</h2>
                <p>Your reference number is</p>
                <p class="reference" id="bookingRef"></p>
                <p class="muted">We will contact you within 24 hours to confirm the date and arrange your free assessment.</p>
                <div class="success-actions">
                    <?php if ($user): ?>
                        <a href="dashboard.php" class="btn btn-primary">View my bookings</a>
                    <?php else: ?>
                        <a href="register.php" class="btn btn-primary">Create an account to track it</a>
                    <?php endif; ?>
                    <a href="index.php" class="btn btn-ghost">Back to home</a>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
