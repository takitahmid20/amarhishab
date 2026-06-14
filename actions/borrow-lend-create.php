<?php
/**
 * Create a borrow/lend record for the logged-in user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/borrow_lend.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/borrow-lend.php');
}

csrf_check();

$userId  = current_user()['id'];
$type    = post('type') === 'lent' ? 'lend' : 'borrow';
$person  = post('person');
$amount  = (float) post('amount');
$note    = post('note');
$dueDate = post('date');

if ($person === '' || $amount <= 0) {
	flash_set('error', 'Person and a valid amount are required.');
	redirect('../pages/borrow-lend.php');
}

create_borrow_lend($userId, $type, $person, $amount, $note !== '' ? $note : null, $dueDate !== '' ? $dueDate : null);

flash_set('success', 'Record added.');
redirect('../pages/borrow-lend.php');
