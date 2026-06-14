<?php
/**
 * Mark a borrow/lend record as settled.
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
	$updated = set_borrow_lend_settled($id, current_user()['id'], true);
	flash_set($updated ? 'success' : 'error', $updated ? 'Marked as settled.' : 'Record not found.');
}

redirect('../pages/borrow-lend.php');
