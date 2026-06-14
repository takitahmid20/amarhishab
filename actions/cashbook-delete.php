<?php
/**
 * Delete a cashbook owned by the logged-in user (cascades its transactions).
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/cashbooks.php');
}

csrf_check();

$id = (int) post('id');
if ($id > 0) {
	$deleted = delete_cashbook($id, current_user()['id']);
	flash_set($deleted ? 'success' : 'error', $deleted ? 'Cashbook deleted.' : 'Cashbook not found.');
}

redirect('../pages/cashbooks.php');
