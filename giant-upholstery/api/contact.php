<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$user    = current_user();
$name    = post_str('full_name', 160);
$email   = strtolower(post_str('email', 190));
$subject = post_str('subject', 190);
$message = post_str('message', 4000);

$errors = [];
if (mb_strlen($name) < 2)       $errors['full_name'] = 'Enter your full name.';
if (!valid_email($email))       $errors['email']     = 'Enter a valid email address.';
if (mb_strlen($subject) < 3)    $errors['subject']   = 'Add a short subject.';
if (mb_strlen($message) < 10)   $errors['message']   = 'Write at least a sentence so we can help.';
if ($errors) {
    json_out(['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $errors], 422);
}

db()->prepare(
    'INSERT INTO contact_messages (user_id, full_name, email, subject, message, ip_address)
     VALUES (?, ?, ?, ?, ?, ?)'
)->execute([$user['id'] ?? null, $name, $email, $subject, $message, client_ip()]);

json_out(['ok' => true, 'message' => 'Message sent. We reply within 24 hours.']);
