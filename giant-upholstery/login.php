<?php
require_once __DIR__ . '/includes/bootstrap.php';
if (current_user()) { header('Location: dashboard.php'); exit; }
$pageKey = 'login';
$pageTitle = 'Log In';
$bodyClass = 'auth-body';
require __DIR__ . '/includes/header.php';
?>

<section class="auth-wrap">
    <div class="container auth-grid">
        <div class="auth-side">
            <img src="assets/img/mark.png" alt="" width="110" height="108">
            <h2>Track every repair in one place.</h2>
            <p>Log in to see your bookings, their status and your saved details.</p>
            <div class="stitch static" aria-hidden="true"></div>
        </div>

        <div class="auth-card">
            <h1>Welcome back</h1>
            <p class="muted">Log in to manage your bookings.</p>

            <form id="loginForm" class="js-form" data-endpoint="api/login.php" novalidate>
                <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                <input type="text" name="website" class="hp" tabindex="-1" autocomplete="off" aria-hidden="true">

                <div class="field">
                    <label for="l_email">Email address</label>
                    <input id="l_email" name="email" type="email" autocomplete="email" required placeholder="you@example.com">
                    <p class="field-error" data-error-for="email"></p>
                </div>

                <div class="field">
                    <label for="l_pass">Password</label>
                    <input id="l_pass" name="password" type="password" autocomplete="current-password" required>
                    <p class="field-error" data-error-for="password"></p>
                </div>

                <div class="auth-options">
                    <label class="inline-check"><input type="checkbox" name="remember" value="1"> Keep me logged in</label>
                    <a href="contact.php?topic=password">Forgot password?</a>
                </div>

                <div class="form-alert" role="alert" hidden></div>

                <button type="submit" class="btn btn-primary btn-lg btn-block">Log in</button>
            </form>

            <p class="auth-switch">New here? <a href="register.php">Create an account</a></p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
