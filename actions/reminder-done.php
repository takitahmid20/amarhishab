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
	$userId = current_user()['id'];
	$reminder = find_reminder($id, $userId);
	if ($reminder) {
		$updated = set_reminder_done($id, $userId, true);
		if ($updated && $reminder['repeat_cycle'] !== 'none') {
			$nextDueDate = null;
			if ($reminder['repeat_cycle'] === 'weekly') {
				$nextDueDate = date('Y-m-d', strtotime('+1 week', strtotime($reminder['due_date'])));
			} elseif ($reminder['repeat_cycle'] === 'monthly') {
				$nextDueDate = date('Y-m-d', strtotime('+1 month', strtotime($reminder['due_date'])));
			}
			if ($nextDueDate) {
				create_reminder(
					$userId,
					$reminder['title'],
					$reminder['category'],
					$reminder['amount'] !== null ? (float) $reminder['amount'] : null,
					$nextDueDate,
					$reminder['repeat_cycle']
				);
			}
		}
		flash_set($updated ? 'success' : 'error', $updated ? 'Marked as paid.' : 'Reminder not found.');
	} else {
		flash_set('error', 'Reminder not found.');
	}
}

redirect('../pages/reminders.php');
