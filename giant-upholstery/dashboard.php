<?php
require_once __DIR__ . '/includes/bootstrap.php';
$user = require_login();

$stmt = db()->prepare(
    'SELECT b.id, b.reference, b.status, b.preferred_date, b.preferred_time, b.address,
            b.damage_description, b.created_at, s.name AS service_name
     FROM bookings b
     JOIN services s ON s.id = b.service_id
     WHERE b.user_id = ? OR b.email = ?
     ORDER BY b.created_at DESC'
);
$stmt->execute([$user['id'], $user['email']]);
$bookings = $stmt->fetchAll();

$counts = ['pending' => 0, 'confirmed' => 0, 'completed' => 0];
foreach ($bookings as $b) {
    if (isset($counts[$b['status']])) $counts[$b['status']]++;
}
$statusLabel = ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
$initial = strtoupper(substr($user['first_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="robots" content="noindex">
    <title>My account | Giant Auto Upholstery</title>
    <link rel="icon" href="assets/img/mark.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dash" data-page="dashboard">

<div class="dash-layout">
    <aside class="dash-side" id="dashSide">
        <a href="index.php" class="brand">
            <img src="assets/img/mark.png" alt="" width="36" height="36">
            <span class="brand-text">Giant <b>Auto</b> Upholstery</span>
        </a>

        <nav class="dash-nav" aria-label="Account">
            <a href="#overview" class="active"><?= icon('home', 20) ?> Overview</a>
            <a href="#bookings"><?= icon('list', 20) ?> My bookings</a>
            <a href="booking.php"><?= icon('plus', 20) ?> New booking</a>
            <a href="#profile"><?= icon('user', 20) ?> My details</a>
            <a href="contact.php"><?= icon('mail', 20) ?> Contact us</a>
        </nav>

        <div class="dash-side-foot">
            <a href="index.php">Back to website</a>
            <a href="logout.php?t=<?= e(csrf_token()) ?>" class="logout"><?= icon('logout', 18) ?> Log out</a>
        </div>
    </aside>

    <div class="dash-main">
        <header class="dash-top">
            <button class="nav-toggle dash-toggle" id="dashToggle" aria-label="Open menu"><?= icon('menu', 24) ?></button>
            <div>
                <p class="muted">Customer account</p>
                <h1>Welcome, <?= e($user['first_name']) ?></h1>
            </div>
            <div class="dash-user">
                <span class="avatar"><?= e($initial) ?></span>
                <span>
                    <strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                    <small><?= e($user['email']) ?></small>
                </span>
            </div>
        </header>

        <div class="dash-content">

            <section id="overview" class="stat-row">
                <div class="stat-card"><span>Total bookings</span><strong><?= count($bookings) ?></strong></div>
                <div class="stat-card is-pending"><span>Pending</span><strong><?= $counts['pending'] ?></strong></div>
                <div class="stat-card is-confirmed"><span>Confirmed</span><strong><?= $counts['confirmed'] ?></strong></div>
                <div class="stat-card is-completed"><span>Completed</span><strong><?= $counts['completed'] ?></strong></div>
            </section>

            <section class="panel" id="bookings">
                <div class="panel-head">
                    <h2>My bookings</h2>
                    <a href="booking.php" class="btn btn-primary"><?= icon('plus', 18) ?> New booking</a>
                </div>

                <?php if (!$bookings): ?>
                    <div class="empty">
                        <p><strong>No bookings yet.</strong></p>
                        <p class="muted">Book a repair and it will show up here with its status.</p>
                        <a href="booking.php" class="btn btn-thread">Book a repair</a>
                    </div>
                <?php else: ?>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Reference</th><th>Service</th><th>Preferred date</th><th>Status</th><th><span class="sr-only">Details</span></th></tr>
                            </thead>
                            <tbody>
                            <?php foreach ($bookings as $b): ?>
                                <tr>
                                    <td data-label="Reference"><?= e($b['reference']) ?></td>
                                    <td data-label="Service"><?= e($b['service_name']) ?></td>
                                    <td data-label="Preferred date"><?= e(date('j M Y', strtotime($b['preferred_date']))) ?></td>
                                    <td data-label="Status"><span class="status s-<?= e($b['status']) ?>"><?= e($statusLabel[$b['status']] ?? $b['status']) ?></span></td>
                                    <td>
                                        <button type="button" class="btn btn-ghost btn-sm js-view-booking"
                                            data-ref="<?= e($b['reference']) ?>"
                                            data-service="<?= e($b['service_name']) ?>"
                                            data-date="<?= e(date('j M Y', strtotime($b['preferred_date']))) ?>"
                                            data-time="<?= e($b['preferred_time']) ?>"
                                            data-status="<?= e($statusLabel[$b['status']] ?? $b['status']) ?>"
                                            data-address="<?= e($b['address']) ?>"
                                            data-notes="<?= e($b['damage_description'] ?? 'No notes added') ?>">View</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="panel" id="profile">
                <div class="panel-head"><h2>My details</h2></div>

                <form id="profileForm" class="js-form" data-endpoint="api/profile.php" novalidate>
                    <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                    <div class="form-row">
                        <div class="field">
                            <label for="p_first">First name</label>
                            <input id="p_first" name="first_name" value="<?= e($user['first_name']) ?>" required>
                            <p class="field-error" data-error-for="first_name"></p>
                        </div>
                        <div class="field">
                            <label for="p_last">Last name</label>
                            <input id="p_last" name="last_name" value="<?= e($user['last_name']) ?>" required>
                            <p class="field-error" data-error-for="last_name"></p>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="field">
                            <label for="p_email">Email address</label>
                            <input id="p_email" value="<?= e($user['email']) ?>" disabled>
                        </div>
                        <div class="field">
                            <label for="p_phone">Mobile number</label>
                            <input id="p_phone" name="phone" type="tel" value="<?= e($user['phone']) ?>" required data-validate="sa-phone">
                            <p class="field-error" data-error-for="phone"></p>
                        </div>
                    </div>
                    <div class="check-field">
                        <input type="checkbox" id="p_promo" name="marketing_opt_in" value="1" <?= $user['marketing_opt_in'] ? 'checked' : '' ?>>
                        <label for="p_promo">Send me occasional news and special offers.</label>
                    </div>
                    <div class="form-alert" role="alert" hidden></div>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </form>
            </section>

        </div>
    </div>
</div>

<div class="modal" id="bookingModal" hidden>
    <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="mRef">
        <button class="modal-close" id="modalClose" aria-label="Close"><?= icon('close', 22) ?></button>
        <p class="muted">Booking details</p>
        <h2 id="mRef"></h2>
        <dl class="review">
            <div><dt>Service</dt><dd id="mService"></dd></div>
            <div><dt>Preferred date</dt><dd id="mDate"></dd></div>
            <div><dt>Time slot</dt><dd id="mTime"></dd></div>
            <div><dt>Status</dt><dd id="mStatus"></dd></div>
            <div><dt>Address</dt><dd id="mAddress"></dd></div>
            <div><dt>Notes</dt><dd id="mNotes"></dd></div>
        </dl>
    </div>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>
