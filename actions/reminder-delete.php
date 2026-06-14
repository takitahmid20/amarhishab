<?php
/**
 * Delete a reminder owned by the user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/reminders.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/reminders.php');
}

csrf_check();

$id = (int) post('id');
if ($id > 0) {
	$deleted = delete_reminder($id, current_user()['id']);
	flash_set($deleted ? 'success' : 'error', $deleted ? 'Reminder deleted.' : 'Reminder not found.');
}

redirect('../pages/reminders.php');
