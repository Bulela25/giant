<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageKey = 'about';
$pageTitle = 'About Us';
$pageDesc = 'Meet the Johannesburg upholstery team behind Giant Auto Upholstery: certified technicians, premium materials and a six-month guarantee.';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Craftsmanship built around quality</h1>
        <p>Professional upholstery repair and restoration for vehicles and home furniture.</p>
    </div>
    <div class="stitch static" aria-hidden="true"></div>
</section>

<section class="section">
    <div class="container split">
        <div class="logo-card">
            <img src="assets/img/mark.png" alt="Giant Auto Upholstery logo" width="200" height="196">
            <strong>Giant Upholstery</strong>
        </div>

        <div class="prose">
            <h2>Experience that makes a difference</h2>
            <p>
                Giant Auto Upholstery was started with a simple goal: dependable upholstery repairs
                that never compromise on craftsmanship or materials.
            </p>
            <p>
                Over the years we have completed thousands of projects, from vehicle seat restoration
                and interior trim to leather sofa repairs and complete furniture reupholstery.
            </p>
            <p>
                We combine traditional upholstery skills with modern repair techniques, so the result
                is practical, good-looking and built to last.
            </p>
        </div>
    </div>
</section>

<section class="section section-tint">
    <div class="container narrow">
        <div class="section-head">
            <h2>Our mission</h2>
        </div>
        <blockquote class="mission">
            <p>
                To restore the comfort, appearance and value of our customers' vehicles and furniture
                through skilled workmanship, premium materials, honest pricing and service that keeps
                you informed from the first call to the final fitting.
            </p>
        </blockquote>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>The people doing the work</h2>
            <p>Our technicians bring practical experience and attention to detail to every project.</p>
        </div>

        <div class="trio">
            <article class="info-card">
                <span class="info-icon"><?= icon('shield', 24) ?></span>
                <h3>Certified technicians</h3>
                <p>Experienced specialists trained to work with leather, vinyl, fabric, foam and interior trim.</p>
            </article>
            <article class="info-card">
                <span class="info-icon"><?= icon('layers', 24) ?></span>
                <h3>Detail-focused craftsmen</h3>
                <p>Every stitch, panel and finish is inspected so the result looks clean and professional.</p>
            </article>
            <article class="info-card">
                <span class="info-icon"><?= icon('user', 24) ?></span>
                <h3>Customer-first service</h3>
                <p>We listen to what you need and recommend practical options that fit your budget.</p>
            </article>
        </div>
    </div>
</section>

<section class="promise">
    <div class="container">
        <h2>Our commitment to you</h2>
        <div class="promise-grid">
            <p><?= icon('check', 22) ?> Premium-grade materials from certified suppliers</p>
            <p><?= icon('check', 22) ?> Six-month workmanship guarantee</p>
            <p><?= icon('check', 22) ?> Free assessment and transparent quoting</p>
            <p><?= icon('check', 22) ?> Environmentally responsible disposal</p>
        </div>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
