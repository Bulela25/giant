<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$first   = post_str('first_name', 80);
$last    = post_str('last_name', 80);
$email   = strtolower(post_str('email', 190));
$phoneIn = post_str('phone', 30);
$pass    = (string)($_POST['password'] ?? '');
$confirm = (string)($_POST['confirm_password'] ?? '');
$promo   = !empty($_POST['marketing_opt_in']) ? 1 : 0;
$terms   = !empty($_POST['terms']);

$errors = [];
if (mb_strlen($first) < 2)  $errors['first_name'] = 'Enter your first name.';
if (mb_strlen($last) < 2)   $errors['last_name']  = 'Enter your last name.';
if (!valid_email($email))   $errors['email']      = 'Enter a valid email address.';
$phone = normalise_sa_phone($phoneIn);
if ($phone === null)        $errors['phone']      = 'Enter a valid South African mobile number, e.g. 071 234 5678.';
if (strlen($pass) < 8)      $errors['password']   = 'Use at least 8 characters.';
if (strlen($pass) > 200)    $errors['password']   = 'Password is too long.';
if ($pass !== $confirm)     $errors['confirm_password'] = 'Passwords do not match.';
if (!$terms)                $errors['terms']      = 'You need to accept the terms to create an account.';

if ($errors) {
    json_out(['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $errors], 422);
}

$pdo = db();

$exists = $pdo->prepare('SELECT id FROM users WHERE email = ?');
$exists->execute([$email]);
if ($exists->fetch()) {
    json_out(['ok' => false, 'message' => 'Please fix the highlighted fields.',
              'errors' => ['email' => 'An account with this email already exists. Log in instead.']], 409);
}

$stmt = $pdo->prepare(
    'INSERT INTO users (first_name, last_name, email, phone, password_hash, marketing_opt_in,
                        terms_accepted_at, registration_ip, user_agent)
     VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?)'
);
$stmt->execute([
    $first, $last, $email, $phone,
    password_hash($pass, PASSWORD_DEFAULT),
    $promo, client_ip(), client_agent(),
]);
$userId = (int)$pdo->lastInsertId();

// Attach any earlier guest bookings made with this email address
$pdo->prepare('UPDATE bookings SET user_id = ? WHERE user_id IS NULL AND email = ?')->execute([$userId, $email]);

login_session($userId);

json_out(['ok' => true, 'message' => 'Account created.', 'redirect' => 'dashboard.php']);
