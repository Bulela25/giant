<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/bootstrap.php';

// Logging out requires the CSRF token so other sites cannot sign people out.
if (($_GET['t'] ?? '') === csrf_token()) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/']);
    }
    session_destroy();
}
header('Location: index.php');
exit;
