<?php
/**
 * Delete a borrow/lend record owned by the user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/borrow_lend.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/borrow-lend.php');
}

csrf_check();

$id = (int) post('id');
if ($id > 0) {
	$deleted = delete_borrow_lend($id, current_user()['id']);
	flash_set($deleted ? 'success' : 'error', $deleted ? 'Record deleted.' : 'Record not found.');
}

redirect('../pages/borrow-lend.php');
