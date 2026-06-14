<?php
/**
 * Change the logged-in user's password (verifies the current one first).
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/account.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/settings.php');
}

csrf_check();

$userId  = current_user()['id'];
$current = post('current_password');
$new     = post('new_password');
$confirm = post('confirm_password');

$user = get_user_full($userId);
if (!$user || !password_verify($current, $user['password_hash'])) {
	flash_set('error', 'Current password is incorrect.');
	redirect('../pages/settings.php');
}
if (strlen($new) < 6) {
	flash_set('error', 'New password must be at least 6 characters.');
	redirect('../pages/settings.php');
}
if ($new !== $confirm) {
	flash_set('error', 'New passwords do not match.');
	redirect('../pages/settings.php');
}

update_user_password($userId, password_hash($new, PASSWORD_DEFAULT));

flash_set('success', 'Password updated.');
redirect('../pages/settings.php');
