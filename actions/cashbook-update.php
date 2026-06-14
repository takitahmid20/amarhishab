<?php
/**
 * Update a cashbook owned by the logged-in user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/cashbooks.php');
}

csrf_check();

$id          = (int) post('id');
$name        = post('name');
$description = post('description');
$status      = post('status') === 'review' ? 'review' : 'live';

if ($id <= 0 || $name === '') {
	flash_set('error', 'Cashbook name is required.');
	redirect('../pages/cashbooks.php');
}

update_cashbook($id, current_user()['id'], $name, $description !== '' ? $description : null, $status);

flash_set('success', 'Cashbook updated.');
redirect('../pages/cashbooks.php');
