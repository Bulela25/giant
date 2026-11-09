<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$pageKey = 'register';
$pageTitle = 'Create Account';
$bodyClass = 'auth-body';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-wrap">
    <div class="container auth-grid">
        <div class="auth-side">
            <img src="assets/img/mark.png" alt="" width="110" height="108">
            <h2>Book faster and keep your history.</h2>
            <p>Your details are saved, so the next booking is two clicks, and you can follow each repair from request to completion.</p>
            <div class="stitch static" aria-hidden="true"></div>
        </div>

        <div class="auth-card">
            <h1>Create your account</h1>
            <p class="muted">It takes under a minute.</p>

            <form id="registerForm" class="js-form" data-endpoint="api/register.php" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="form-row">
                    <div class="field">
                        <label for="r_first">First name <span class="req">*</span></label>
                        <input id="r_first" name="first_name" type="text" autocomplete="given-name" required>
                        <p class="field-error" data-error-for="first_name"></p>
                    </div>
                    <div class="field">
                        <label for="r_last">Last name <span class="req">*</span></label>
                        <input id="r_last" name="last_name" type="text" autocomplete="family-name" required>
                        <p class="field-error" data-error-for="last_name"></p>
                    </div>
                </div>

                <div class="field">
                    <label for="r_email">Email address <span class="req">*</span></label>
                    <input id="r_email" name="email" type="email" autocomplete="email" required placeholder="you@example.com">
                    <p class="field-error" data-error-for="email"></p>
                </div>

                <div class="field">
                    <label for="r_phone">Mobile number <span class="req">*</span></label>
                    <input id="r_phone" name="phone" type="tel" autocomplete="tel" required placeholder="071 234 5678" data-validate="sa-phone">
                    <p class="field-error" data-error-for="phone"></p>
                </div>

                <div class="form-row">
                    <div class="field">
                        <label for="r_pass">Password <span class="req">*</span></label>
                        <input id="r_pass" name="password" type="password" autocomplete="new-password" required minlength="8" placeholder="At least 8 characters">
                        <p class="field-error" data-error-for="password"></p>
                    </div>
                    <div class="field">
                        <label for="r_confirm">Confirm password <span class="req">*</span></label>
                        <input id="r_confirm" name="confirm_password" type="password" autocomplete="new-password" required data-match="password">
                        <p class="field-error" data-error-for="confirm_password"></p>
                    </div>
                </div>

                <div class="check-field">
                    <input type="checkbox" id="r_promo" name="marketing_opt_in" value="1">
                    <label for="r_promo">Send me occasional news and special offers.</label>
                </div>

                <div class="check-field">
                    <input type="checkbox" id="r_terms" name="terms" value="1" required>
                    <label for="r_terms">I agree that my details are stored to manage my account and bookings. <span class="req">*</span></label>
                </div>
                <p class="field-error" data-error-for="terms"></p>

                <div class="form-alert" role="alert" hidden></div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Create account</button>
            </form>

            <p class="auth-switch">Already registered? <a href="login.php">Log in</a></p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
