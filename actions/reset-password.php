<?php
/**
 * Set a new password after a verified OTP. Updates the user, clears the
 * reset session, and sends them to login.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/reset-password.php');
}

csrf_check();

$reset = $_SESSION['pwreset'] ?? null;
if (!$reset || empty($reset['verified'])) {
	flash_set('error', 'Please verify your code first.');
	redirect('../pages/forgot-password.php');
}

$password = post('password');
$confirm  = post('confirm_password');

if (strlen($password) < 6) {
	flash_set('error', 'Password must be at least 6 characters.');
	redirect('../pages/reset-password.php');
}
if ($password !== $confirm) {
	flash_set('error', 'Passwords do not match.');
	redirect('../pages/reset-password.php');
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE email = ?');
$stmt->execute([$hash, $reset['email']]);

unset($_SESSION['pwreset']);
flash_set('success', 'Password updated. You can now sign in.');
redirect('../pages/login.php');
