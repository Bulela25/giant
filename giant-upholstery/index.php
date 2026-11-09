<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageKey = 'home';
$pageTitle = 'Premium Upholstery & Sofa Repair in Johannesburg';
$services = get_services();
$groups = [
    'vehicle' => ['Vehicles', 'car'],
    'home'    => ['Home furniture', 'sofa'],
    'custom'  => ['Specialist work', 'wand'],
];
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <p class="hero-note"><?= icon('pin', 16) ?> Workshop in Johannesburg, Gauteng</p>
            <h1>Seats, sofas and trim, rebuilt by hand.</h1>
            <p class="lede">
                Giant Auto Upholstery restores worn, torn and sagging interiors for cars,
                trucks and the furniture at home, using premium materials and
                a stitch we are happy to guarantee.
            </p>
            <div class="hero-actions">
                <a href="booking.php" class="btn btn-thread btn-lg">Get a free quote</a>
                <a href="services.php" class="btn btn-ghost-light btn-lg">See services and prices</a>
            </div>
            <ul class="hero-proof">
                <li><strong>15+</strong> years in the trade</li>
                <li><strong>5,000+</strong> repairs completed</li>
                <li><strong>6 months</strong> workmanship guarantee</li>
            </ul>
        </div>

        <aside class="quote-desk" aria-labelledby="quoteTitle">
            <h2 id="quoteTitle">Check a starting price</h2>
            <label for="quoteService">What needs repairing?</label>
            <select id="quoteService">
                <?php foreach ($services as $s): ?>
                    <option value="<?= e($s['slug']) ?>"
                            data-price="<?= e(money_from($s['price_from'])) ?>"
                            data-text="<?= e($s['description']) ?>">
                        <?= e($s['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <div class="quote-result" aria-live="polite">
                <span class="quote-price" id="quotePrice"></span>
                <p id="quoteText"></p>
            </div>

            <a href="booking.php" class="btn btn-primary btn-block" id="quoteLink">Book this service</a>
            <p class="quote-foot"><?= icon('check', 16) ?> The assessment is free. You approve the final quote before any work starts.</p>
        </aside>
    </div>
    <div class="stitch" aria-hidden="true"></div>
</section>


<section class="section">
    <div class="container">
        <div class="section-head">
            <h2>Price list</h2>
            <p>Starting prices for the work we do most. Every job is quoted properly once we have seen it.</p>
        </div>

        <div class="price-columns">
            <?php foreach ($groups as $cat => [$label, $ico]): ?>
                <?php $rows = array_filter($services, function ($s) use ($cat) { return $s['category'] === $cat; }); if (!$rows) continue; ?>
                <div class="price-group">
                    <h3><?= icon($ico, 22) ?> <?= e($label) ?></h3>
                    <?php foreach ($rows as $s): ?>
                        <a class="price-row" href="booking.php?service=<?= e($s['slug']) ?>">
                            <span class="price-main">
                                <strong><?= e($s['name']) ?></strong>
                                <small><?= e($s['description']) ?></small>
                            </span>
                            <span class="price-amount"><?= e(money_from($s['price_from'])) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>


<section class="section section-tint">
    <div class="container">
        <div class="section-head">
            <h2>How a repair works</h2>
            <p>From the first message to a finished interior, you always know what happens next.</p>
        </div>

        <ol class="steps">
            <li>
                <span class="step-no">1</span>
                <h3>Book online</h3>
                <p>Choose the service, tell us what is damaged and pick a date. It takes about two minutes.</p>
            </li>
            <li>
                <span class="step-no">2</span>
                <h3>Free assessment</h3>
                <p>We confirm within 24 hours, inspect the work and give you a clear written quote.</p>
            </li>
            <li>
                <span class="step-no">3</span>
                <h3>Repair and guarantee</h3>
                <p>Our technicians do the work, and every job carries a six-month workmanship guarantee.</p>
            </li>
        </ol>
    </div>
</section>


<section class="section">
    <div class="container why-grid">
        <div class="why-panel">
            <img src="assets/img/mark.png" alt="Giant Auto Upholstery armchair logo" width="150" height="147">
            <dl>
                <div><dt>15+</dt><dd>years of experience</dd></div>
                <div><dt>5,000+</dt><dd>repairs completed</dd></div>
                <div><dt>98%</dt><dd>customer satisfaction</dd></div>
            </dl>
        </div>

        <div class="why-copy">
            <h2>Craftsmanship you can check for yourself</h2>
            <p>
                We combine years of hands-on experience with premium materials and close
                attention to detail. Whether a vehicle interior needs a full restoration or
                your favourite sofa needs a second life, we aim for results that last.
            </p>
            <ul class="check-list">
                <li><?= icon('check', 20) ?> Premium-grade upholstery materials</li>
                <li><?= icon('check', 20) ?> Experienced, certified technicians</li>
                <li><?= icon('check', 20) ?> Transparent written quotations</li>
                <li><?= icon('check', 20) ?> Six-month workmanship guarantee</li>
            </ul>
            <a href="about.php" class="btn btn-primary">About our workshop</a>
        </div>
    </div>
</section>


<section class="cta-band">
    <div class="container cta-inner">
        <div>
            <h2>Ready to bring your upholstery back to life?</h2>
            <p>Request a free assessment and we will recommend the right repair for your budget.</p>
        </div>
        <a href="booking.php" class="btn btn-thread btn-lg">Book your repair</a>
    </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
