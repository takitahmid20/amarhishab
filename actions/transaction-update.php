<?php
/**
 * Update a transaction owned by the user (amount, mode, category, bill,
 * details, date). Direction and cashbook are not changed here.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/transactions.php';
require_once __DIR__ . '/../includes/budget.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/transactions.php');
}

csrf_check();

$userId     = current_user()['id'];
$id         = (int) post('id');
$amount     = (float) post('amount');
$mode       = in_array(post('mode'), ['cash', 'bank', 'mobile'], true) ? post('mode') : 'cash';
$categoryId = (int) post('category_id') ?: null;
$bill       = post('bill');
$details    = post('details');
$date       = post('date');

if ($id <= 0 || $amount <= 0) {
	flash_set('error', 'A valid amount is required.');
	redirect('../pages/transactions.php');
}

if (!find_transaction($id, $userId)) {
	flash_set('error', 'Transaction not found.');
	redirect('../pages/transactions.php');
}

if ($categoryId && !find_category($categoryId, $userId)) {
	flash_set('error', 'Category not found.');
	redirect('../pages/transactions.php');
}

$occurredAt = ($date !== '' ? $date : date('Y-m-d')) . ' ' . date('H:i:s');

update_transaction(
	$id, $userId, $amount, $mode, $categoryId,
	$bill !== '' ? $bill : null, $details !== '' ? $details : null, $occurredAt
);

flash_set('success', 'Transaction updated.');
redirect('../pages/transactions.php');
