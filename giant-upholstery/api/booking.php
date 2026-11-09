<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/bootstrap.php';
api_guard();

$user = current_user();

$serviceId = (int)($_POST['service_id'] ?? 0);
$first     = post_str('first_name', 80);
$last      = post_str('last_name', 80);
$email     = strtolower(post_str('email', 190));
$phone     = normalise_sa_phone(post_str('phone', 30));
$address   = post_str('address', 500);
$date      = post_str('preferred_date', 10);
$time      = post_str('preferred_time', 40);
$damage    = post_str('damage_description', 2000);
$terms     = !empty($_POST['terms']);

$slots = ['Morning (08:00-12:00)', 'Afternoon (12:00-16:00)', 'Evening (16:00-18:00)'];

$errors = [];

$svc = db()->prepare('SELECT id, name FROM services WHERE id = ? AND is_active = 1');
$svc->execute([$serviceId]);
$service = $svc->fetch();
if (!$service) $errors['service_id'] = 'Choose a service.';

if (mb_strlen($first) < 2)  $errors['first_name'] = 'Enter your first name.';
if (mb_strlen($last) < 2)   $errors['last_name']  = 'Enter your last name.';
if (!valid_email($email))   $errors['email']      = 'Enter a valid email address.';
if ($phone === null)        $errors['phone']      = 'Enter a valid South African mobile number.';
if (mb_strlen($address) < 10) $errors['address']  = 'Enter the address where the work will happen.';

$d = DateTime::createFromFormat('Y-m-d', $date);
if (!$d || $d->format('Y-m-d') !== $date) {
    $errors['preferred_date'] = 'Choose a preferred date.';
} elseif ($date < date('Y-m-d')) {
    $errors['preferred_date'] = 'The date cannot be in the past.';
} elseif ($d->format('w') === '0') {
    $errors['preferred_date'] = 'We are closed on Sundays. Please pick another day.';
}
if (!in_array($time, $slots, true)) $errors['preferred_time'] = 'Choose a time slot.';
if (!$terms) $errors['terms'] = 'Please agree so we can contact you about this booking.';

if ($errors) {
    json_out(['ok' => false, 'message' => 'Please fix the highlighted fields.', 'errors' => $errors], 422);
}

$pdo = db();
$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        'INSERT INTO bookings (user_id, service_id, first_name, last_name, email, phone, address,
                               preferred_date, preferred_time, damage_description, consent_at, ip_address)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)'
    );
    $stmt->execute([
        $user['id'] ?? null, $serviceId, $first, $last, $email, $phone, $address,
        $date, $time, $damage !== '' ? $damage : null, client_ip(),
    ]);
    $id  = (int)$pdo->lastInsertId();
    $ref = 'GU-' . date('Y') . '-' . str_pad((string)$id, 5, '0', STR_PAD_LEFT);
    $pdo->prepare('UPDATE bookings SET reference = ? WHERE id = ?')->execute([$ref, $id]);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    json_out(['ok' => false, 'message' => 'We could not save your booking. Please try again.'], 500);
}

json_out([
    'ok'        => true,
    'message'   => 'Booking request received.',
    'reference' => $ref,
    'service'   => $service['name'],
    'logged_in' => (bool)$user,
]);
