<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$email    = strtolower(post_str('email', 190));
$password = (string)($_POST['password'] ?? '');
$remember = !empty($_POST['remember']);

$errors = [];
if (!valid_email($email)) $errors['email'] = 'Enter a valid email address.';
if ($password === '')     $errors['password'] = 'Enter your password.';
if ($errors) {
    json_out(['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $errors], 422);
}

$pdo = db();
$ip  = client_ip();

// Throttle: 5 failed attempts in 15 minutes for the same email or IP
$fails = $pdo->prepare(
    'SELECT COUNT(*) FROM login_log
     WHERE success = 0 AND created_at > (NOW() - INTERVAL 15 MINUTE)
       AND (email = ? OR ip_address = ?)'
);
$fails->execute([$email, $ip]);
if ((int)$fails->fetchColumn() >= 5) {
    json_out(['ok' => false, 'message' => 'Too many failed attempts. Please wait 15 minutes and try again.'], 429);
}

$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

$log = $pdo->prepare('INSERT INTO login_log (user_id, email, success, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)');

if (!$user || !password_verify($password, $user['password_hash'])) {
    $log->execute([$user['id'] ?? null, $email, 0, $ip, client_agent()]);
    json_out(['ok' => false, 'message' => 'Email or password is incorrect.'], 401);
}

$log->execute([$user['id'], $email, 1, $ip, client_agent()]);

if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
    $pdo->prepare('UPDATE users SET password_hash = ? WHERE id = ?')
        ->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
}
$pdo->prepare('UPDATE users SET last_login_at = NOW() WHERE id = ?')->execute([$user['id']]);

login_session((int)$user['id'], $remember);

json_out(['ok' => true, 'message' => 'Welcome back.', 'redirect' => 'dashboard.php']);
