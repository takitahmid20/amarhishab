<?php
/**
 * Update the logged-in user's profile (name, email).
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/account.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/settings.php');
}

csrf_check();

$userId = current_user()['id'];
$name   = post('name');
$email  = post('email');

if ($name === '' || $email === '') {
	flash_set('error', 'Name and email are required.');
	redirect('../pages/settings.php');
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	flash_set('error', 'Please enter a valid email address.');
	redirect('../pages/settings.php');
}
if (email_taken_by_other($email, $userId)) {
	flash_set('error', 'That email is already in use.');
	redirect('../pages/settings.php');
}

update_user_profile($userId, $name, $email);

// Keep the session in sync with the new details.
$_SESSION['user']['name']  = $name;
$_SESSION['user']['email'] = $email;

flash_set('success', 'Profile updated.');
redirect('../pages/settings.php');
