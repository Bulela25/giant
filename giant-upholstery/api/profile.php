<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$user = current_user();
if (!$user) json_out(['ok' => false, 'message' => 'Please log in again.', 'redirect' => 'login.php'], 401);

$first = post_str('first_name', 80);
$last  = post_str('last_name', 80);
$phone = normalise_sa_phone(post_str('phone', 30));
$promo = !empty($_POST['marketing_opt_in']) ? 1 : 0;

$errors = [];
if (mb_strlen($first) < 2) $errors['first_name'] = 'Enter your first name.';
if (mb_strlen($last) < 2)  $errors['last_name']  = 'Enter your last name.';
if ($phone === null)       $errors['phone']      = 'Enter a valid South African mobile number.';
if ($errors) {
    json_out(['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $errors], 422);
}

db()->prepare('UPDATE users SET first_name = ?, last_name = ?, phone = ?, marketing_opt_in = ? WHERE id = ?')
    ->execute([$first, $last, $phone, $promo, $user['id']]);

json_out(['ok' => true, 'message' => 'Profile saved.']);
