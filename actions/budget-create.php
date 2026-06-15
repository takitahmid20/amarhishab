<?php
/**
 * Create a budget category for the logged-in user.
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
$name       = post('name');
$limit      = (float) post('limit');
$cashbookId = (int) post('cashbook_id') ?: null;

if ($name === '') {
	flash_set('error', 'Category name is required.');
	redirect('../pages/budget.php');
}
if ($limit < 0) {
	flash_set('error', 'Budget limit cannot be negative.');
	redirect('../pages/budget.php');
}

// If a cashbook was chosen, it must belong to the user.
if ($cashbookId && !find_cashbook($cashbookId, $userId)) {
	flash_set('error', 'Cashbook not found.');
	redirect('../pages/budget.php');
}

create_category($userId, $name, $cashbookId, $limit, null, null);

flash_set('success', 'Budget category created.');
redirect('../pages/budget.php');
