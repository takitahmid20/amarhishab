<?php
/**
 * Mark a reminder as paid/done.
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
	$updated = set_reminder_done($id, current_user()['id'], true);
	flash_set($updated ? 'success' : 'error', $updated ? 'Marked as paid.' : 'Reminder not found.');
}

redirect('../pages/reminders.php');
