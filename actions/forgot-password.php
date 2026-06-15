<?php
/**
 * Forgot-password handler: issue a 4-digit OTP for a known email.
 *
 * No mail server in this project, so the code is shown on screen (dev mode).
 * In production it would be emailed instead.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/forgot-password.php');
}

csrf_check();

$email = post('email');
old_set(['email' => $email]);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	flash_set('error', 'Please enter a valid email address.');
	redirect('../pages/forgot-password.php');
}

$stmt = db()->prepare('SELECT id FROM users WHERE email = ?');
$stmt->execute([$email]);
if (!$stmt->fetch()) {
	flash_set('error', 'No account found with that email.');
	redirect('../pages/forgot-password.php');
}

$existingReset = $_SESSION['pwreset'] ?? null;
if ($existingReset && (time() - ($existingReset['created_at'] ?? 0)) < 60) {
	flash_set('error', 'Please wait 60 seconds before requesting another code.');
	redirect('../pages/forgot-password.php');
}

$otp = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
$_SESSION['pwreset'] = [
	'email'      => $email,
	'otp'        => $otp,
	'expires'    => time() + 600, // 10 minutes
	'created_at' => time(),
	'attempts'   => 0,
	'verified'   => false,
];

old_clear();
// Dev: surface the code since there's no email delivery.
flash_set('success', "Your verification code is {$otp} (shown for development).");
redirect('../pages/otp.php');
