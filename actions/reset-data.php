<?php
/**
 * Delete all of the logged-in user's financial data. Keeps the account.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/account.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/settings.php');
}

csrf_check();

reset_user_data(current_user()['id']);

flash_set('success', 'All your data has been reset.');
redirect('../pages/settings.php');
