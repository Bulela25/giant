<?php
/** Expects: $pageKey (string), $pageTitle (string), optional $pageDesc, $bodyClass */
$pageDesc  = $pageDesc  ?? 'Premium vehicle upholstery and sofa repair in Johannesburg. Free assessments, transparent quotes and a six-month workmanship guarantee.';
$bodyClass = $bodyClass ?? '';
$authUser  = current_user();
$nav = [
    'home'     => ['index.php',    'Home'],
    'about'    => ['about.php',    'About'],
    'services' => ['services.php', 'Services'],
    'booking'  => ['booking.php',  'Book a repair'],
    'contact'  => ['contact.php',  'Contact'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($pageDesc) ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta name="theme-color" content="#0B1530">
    <title><?= e($pageTitle) ?> | Giant Auto Upholstery</title>
    <link rel="icon" href="assets/img/mark.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="<?= e($bodyClass) ?>" data-page="<?= e($pageKey) ?>">
<a class="skip-link" href="#main">Skip to content</a>

<header class="site-header" id="siteHeader">
    <div class="container header-inner">
        <a href="index.php" class="brand" aria-label="Giant Auto Upholstery home">
            <img src="assets/img/mark.png" alt="" width="36" height="36">
            <span class="brand-text">Giant <b>Auto</b> Upholstery</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mainNav">
            <?= icon('menu', 24) ?>
        </button>

        <nav class="main-nav" id="mainNav" aria-label="Main">
            <?php foreach ($nav as $key => [$href, $label]): ?>
                <a href="<?= $href ?>" <?= $key === $pageKey ? 'class="active" aria-current="page"' : '' ?>><?= e($label) ?></a>
            <?php endforeach; ?>

            <div class="nav-auth">
                <?php if ($authUser): ?>
                    <a href="dashboard.php" class="btn btn-ghost">My account</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-ghost">Log in</a>
                    <a href="register.php" class="btn btn-thread">Sign up</a>
                <?php endif; ?>
            </div>
        </nav>
    </div>
</header>

<main id="main">
