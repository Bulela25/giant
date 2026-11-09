<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageKey = 'services';
$pageTitle = 'Services and Prices';
$pageDesc = 'Vehicle seat reupholstery, leather sofa repair, headliners, dashboards and custom upholstery. See starting prices and book online.';
$services = get_services();
$icons = ['vehicle' => 'car', 'home' => 'sofa', 'custom' => 'wand'];
$labels = ['vehicle' => 'Vehicle', 'home' => 'Home furniture', 'custom' => 'Specialist'];
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
    <div class="container">
        <h1>Professional upholstery services</h1>
        <p>From vehicle interiors to custom furniture, quality repairs and restoration at clear starting prices.</p>
    </div>
    <div class="stitch static" aria-hidden="true"></div>
</section>

<section class="section">
    <div class="container">
        <div class="service-grid">
            <?php foreach ($services as $s): ?>
                <article class="service-card">
                    <a class="service-media" href="booking.php?service=<?= e($s['slug']) ?>" tabindex="-1" aria-hidden="true">
                        <img src="<?= e(service_image($s['slug'])) ?>" alt="" loading="lazy" width="640" height="400">
                        <span class="tag"><?= e($labels[$s['category']] ?? '') ?></span>
                    </a>
                    <div class="service-body">
                        <h2><?= e($s['name']) ?></h2>
                        <p><?= e($s['description']) ?></p>
                        <div class="service-foot">
                            <span class="price <?= $s['price_from'] === null ? 'is-quote' : '' ?>"><?= e(money_from($s['price_from'])) ?></span>
                            <a href="booking.php?service=<?= e($s['slug']) ?>" class="btn btn-primary">Book this service</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2>Not sure what you need?</h2>
            <p>Send us a description or a photo on WhatsApp and we will recommend the right repair.</p>
        </div>
        <a href="contact.php" class="btn btn-thread btn-lg">Contact our team</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
