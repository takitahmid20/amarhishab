<?php
/**
 * Add a transaction (cash in or out) to a cashbook owned by the user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/transactions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/cashbooks.php');
}

csrf_check();

$userId     = current_user()['id'];
$cashbookId = (int) post('cashbook_id');
$direction  = post('direction') === 'in' ? 'in' : 'out';
$amount     = (float) post('amount');
$mode       = in_array(post('mode'), ['cash', 'bank', 'mobile'], true) ? post('mode') : 'cash';
$categoryId = (int) post('category_id') ?: null;
$bill       = post('bill');
$details    = post('details');
$date       = post('date');

$back = '../pages/cashbook-details.php?id=' . $cashbookId;

// Cashbook must belong to the user.
if (!find_cashbook($cashbookId, $userId)) {
	flash_set('error', 'Cashbook not found.');
	redirect('../pages/cashbooks.php');
}

if ($amount <= 0) {
	flash_set('error', 'Amount must be greater than zero.');
	redirect($back);
}

$occurredAt = ($date !== '' ? $date : date('Y-m-d')) . ' ' . date('H:i:s');

add_transaction(
	$userId, $cashbookId, $direction, $amount, $mode,
	$categoryId, $bill !== '' ? $bill : null, $details !== '' ? $details : null, $occurredAt
);

flash_set('success', $direction === 'in' ? 'Cash in recorded.' : 'Cash out recorded.');
redirect($back);
