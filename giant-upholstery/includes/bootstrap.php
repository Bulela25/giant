<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/config.php';

if (APP_DEBUG) {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
}

date_default_timezone_set('Africa/Johannesburg');

/* ---------- Session ---------- */
ini_set('session.gc_maxlifetime', (string)(60 * 60 * 24 * 30));
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => !empty($_SERVER['HTTPS']),
    ]);
    session_name('giant_sid');
    session_start();
}

/* ---------- Database ---------- */
function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4';
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            $pdo->exec("SET time_zone = '+02:00'");
        } catch (PDOException $e) {
            http_response_code(500);
            if (strpos($_SERVER['SCRIPT_NAME'] ?? '', '/api/') !== false) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => 'The database is not available right now. Please try again shortly.']);
            } else {
                echo '<h1>Database connection failed</h1><p>Start MySQL in XAMPP, import <code>database/giant_upholstery.sql</code> in phpMyAdmin, and check <code>config/config.php</code>.</p>';
                if (APP_DEBUG) echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
            }
            exit;
        }
    }
    return $pdo;
}

/* ---------- Output helpers ---------- */
function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money_from(?string $price): string
{
    return $price === null ? 'Quote on request' : 'From R' . number_format((float)$price, 0, '.', ',');
}

function json_out(array $data, int $status = 200)
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(): bool
{
    $sent = $_POST['csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    return is_string($sent) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/* ---------- API guard: POST + CSRF + honeypot ---------- */
function api_guard(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_out(['ok' => false, 'message' => 'Method not allowed.'], 405);
    }
    if (!csrf_valid()) {
        json_out(['ok' => false, 'message' => 'Your session expired. Refresh the page and try again.'], 419);
    }
    // Honeypot: real users never fill this hidden field.
    if (!empty($_POST['website'])) {
        json_out(['ok' => true, 'message' => 'Thank you.']);
    }
}

/* ---------- Request info ---------- */
function client_ip(): string
{
    return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}

function client_agent(): string
{
    return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255);
}

function post_str(string $key, int $max = 500): string
{
    $v = $_POST[$key] ?? '';
    if (!is_string($v)) return '';
    $v = trim($v);
    return function_exists('mb_substr') ? mb_substr($v, 0, $max) : substr($v, 0, $max);
}

/* ---------- Validation ---------- */
function valid_email(string $email): bool
{
    return strlen($email) <= 190 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Returns the number as +27XXXXXXXXX, or null if it is not a valid SA mobile number. */
function normalise_sa_phone(string $raw): ?string
{
    $clean = preg_replace('/[\s()\-]/', '', $raw);
    if (preg_match('/^(?:\+27|0)([6-8]\d{8})$/', $clean, $m)) {
        return '+27' . $m[1];
    }
    return null;
}

/* ---------- Auth ---------- */
function current_user(): ?array
{
    static $cache = false;
    if ($cache !== false) return $cache;
    if (empty($_SESSION['user_id'])) return $cache = null;
    $stmt = db()->prepare('SELECT id, first_name, last_name, email, phone, marketing_opt_in, created_at FROM users WHERE id = ?');
    $stmt->execute([(int)$_SESSION['user_id']]);
    return $cache = ($stmt->fetch() ?: null);
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        header('Location: login.php');
        exit;
    }
    return $user;
}

function login_session(int $userId, bool $remember = false): void
{
    session_regenerate_id(true);
    $_SESSION['user_id'] = $userId;
    if ($remember) {
        // Keep the session cookie for 30 days
        setcookie(session_name(), session_id(), [
            'expires'  => time() + 60 * 60 * 24 * 30,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => !empty($_SERVER['HTTPS']),
        ]);
    }
}

/* ---------- Data ---------- */
function get_services(): array
{
    static $rows = null;
    if ($rows === null) {
        $rows = db()->query('SELECT * FROM services WHERE is_active = 1 ORDER BY sort_order, id')->fetchAll();
    }
    return $rows;
}

/**
 * Picture for a service. A real photo wins: drop assets/img/services/<slug>.jpg
 * (or .jpeg / .png / .webp) and it is used instead of the built-in illustration.
 */
function service_image(string $slug): string
{
    foreach (['jpg', 'jpeg', 'png', 'webp', 'svg'] as $ext) {
        $rel = 'assets/img/services/' . $slug . '.' . $ext;
        if (is_file(__DIR__ . '/../' . $rel)) {
            return $rel;
        }
    }
    return 'assets/img/mark.png';
}

/* ---------- Icons (inline SVG, stroke style) ---------- */
function icon(string $name, int $size = 22): string
{
    $paths = [
        'car'      => '<path d="M5 16V11l1.6-4.2A2 2 0 0 1 8.5 5.5h7a2 2 0 0 1 1.9 1.3L19 11v5M5 16h14M5 16v2.5M19 16v2.5"/><circle cx="8" cy="13.5" r=".8"/><circle cx="16" cy="13.5" r=".8"/>',
        'sofa'     => '<path d="M5 11V8.5A2.5 2.5 0 0 1 7.5 6h9A2.5 2.5 0 0 1 19 8.5V11"/><path d="M3 13a2 2 0 0 1 4 0v1.5h10V13a2 2 0 0 1 4 0v4.5H3z"/><path d="M5 17.5V19M19 17.5V19"/>',
        'wand'     => '<path d="M14 6l4 4M4 20l9.5-9.5M13 4l.7 1.8L15.5 6.5l-1.8.7L13 9l-.7-1.8L10.5 6.5l1.8-.7z"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14" rx="2.5"/><path d="M4 10h16M8.5 3.5v4M15.5 3.5v4"/>',
        'phone'    => '<path d="M6.5 4h3l1.5 4-2 1.3a10 10 0 0 0 5.7 5.7L16 13l4 1.5v3a2 2 0 0 1-2.2 2A15.5 15.5 0 0 1 4.5 6.2 2 2 0 0 1 6.5 4z"/>',
        'pin'      => '<path d="M12 20.5s6-5.2 6-10a6 6 0 1 0-12 0c0 4.8 6 10 6 10z"/><circle cx="12" cy="10.5" r="2.2"/>',
        'mail'     => '<rect x="3.5" y="5.5" width="17" height="13" rx="2.5"/><path d="M4 7.5l8 6 8-6"/>',
        'clock'    => '<circle cx="12" cy="12" r="8.5"/><path d="M12 7.5V12l3 2"/>',
        'check'    => '<path d="M5 12.5l4.5 4.5L19 7.5"/>',
        'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'shield'   => '<path d="M12 3.5l7 2.8v5.2c0 4.3-2.9 7.6-7 9-4.1-1.4-7-4.7-7-9V6.3z"/><path d="M9 12l2.2 2.2L15.2 10"/>',
        'layers'   => '<path d="M12 4l8.5 4.5L12 13 3.5 8.5z"/><path d="M3.5 12.5L12 17l8.5-4.5M3.5 16.5L12 21l8.5-4.5"/>',
        'home'     => '<path d="M4 11l8-6.5 8 6.5v8a1 1 0 0 1-1 1h-4.5v-5.5h-5V20H5a1 1 0 0 1-1-1z"/>',
        'list'     => '<path d="M8.5 6.5H20M8.5 12H20M8.5 17.5H20"/><circle cx="4.5" cy="6.5" r=".9"/><circle cx="4.5" cy="12" r=".9"/><circle cx="4.5" cy="17.5" r=".9"/>',
        'user'     => '<circle cx="12" cy="8.5" r="3.7"/><path d="M4.5 20a7.5 7.5 0 0 1 15 0"/>',
        'logout'   => '<path d="M10 4.5H6.5a2 2 0 0 0-2 2v11a2 2 0 0 0 2 2H10M15 8l4 4-4 4M19 12H9.5"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'menu'     => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close'    => '<path d="M6 6l12 12M18 6L6 18"/>',
    ];
    $p = $paths[$name] ?? '';
    return '<svg class="icon" width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $p . '</svg>';
}
