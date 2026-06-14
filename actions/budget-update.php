<?php
/**
 * Update a budget category owned by the user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/budget.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/budget.php');
}

csrf_check();

$userId     = current_user()['id'];
$id         = (int) post('id');
$name       = post('name');
$limit      = (float) post('limit');
$cashbookId = (int) post('cashbook_id') ?: null;

if ($id <= 0 || $name === '' || $limit <= 0) {
	flash_set('error', 'A valid name and limit are required.');
	redirect('../pages/budget.php');
}

if (!find_category($id, $userId)) {
	flash_set('error', 'Category not found.');
	redirect('../pages/budget.php');
}

if ($cashbookId && !find_cashbook($cashbookId, $userId)) {
	flash_set('error', 'Cashbook not found.');
	redirect('../pages/budget.php');
}

update_category($id, $userId, $name, $cashbookId, $limit);

flash_set('success', 'Budget category updated.');
redirect('../pages/budget.php');
