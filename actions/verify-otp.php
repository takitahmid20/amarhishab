<?php
/**
 * Verify the 4-digit OTP against the one issued in the session.
 */

require_once __DIR__ . '/../includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/otp.php');
}

csrf_check();

$reset = $_SESSION['pwreset'] ?? null;
if (!$reset) {
	flash_set('error', 'Start by requesting a reset code.');
	redirect('../pages/forgot-password.php');
}

if (time() > ($reset['expires'] ?? 0)) {
	unset($_SESSION['pwreset']);
	flash_set('error', 'Your code has expired. Please request a new one.');
	redirect('../pages/forgot-password.php');
}

$entered = post('d1') . post('d2') . post('d3') . post('d4');

if (!hash_equals($reset['otp'], $entered)) {
	flash_set('error', 'Incorrect code. Please try again.');
	redirect('../pages/otp.php');
}

$_SESSION['pwreset']['verified'] = true;
redirect('../pages/reset-password.php');
