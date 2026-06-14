<?php
/**
 * Create a cashbook for the logged-in user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/cashbooks.php');
}

csrf_check();

$name        = post('name');
$description = post('description');

if ($name === '') {
	flash_set('error', 'Cashbook name is required.');
	redirect('../pages/cashbooks.php');
}

create_cashbook(current_user()['id'], $name, $description !== '' ? $description : null);

flash_set('success', 'Cashbook created.');
redirect('../pages/cashbooks.php');
