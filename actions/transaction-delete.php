<?php
/**
 * Delete a transaction owned by the logged-in user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/transactions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/transactions.php');
}

csrf_check();

$id = (int) post('id');
if ($id > 0) {
	$deleted = delete_transaction($id, current_user()['id']);
	flash_set($deleted ? 'success' : 'error', $deleted ? 'Transaction deleted.' : 'Transaction not found.');
}

redirect('../pages/transactions.php');
