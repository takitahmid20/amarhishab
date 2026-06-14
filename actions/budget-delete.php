<?php
/**
 * Delete a budget category owned by the user. Transactions keep their record;
 * their category_id is set null by the schema's ON DELETE SET NULL.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/budget.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/budget.php');
}

csrf_check();

$id = (int) post('id');
if ($id > 0) {
	$deleted = delete_category($id, current_user()['id']);
	flash_set($deleted ? 'success' : 'error', $deleted ? 'Category deleted.' : 'Category not found.');
}

redirect('../pages/budget.php');
