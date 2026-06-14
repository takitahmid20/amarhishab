<?php
/**
 * Create a reminder for the logged-in user.
 */

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/reminders.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	redirect('../pages/reminders.php');
}

csrf_check();

$userId   = current_user()['id'];
$title    = post('title');
$amount   = post('amount');
$dueDate  = post('dueDate');
$category = post('category');
$repeat   = post('repeat');

if ($title === '' || $dueDate === '') {
	flash_set('error', 'Title and due date are required.');
	redirect('../pages/reminders.php');
}

create_reminder(
	$userId,
	$title,
	$category !== '' ? $category : null,
	$amount !== '' ? (float) $amount : null,
	$dueDate,
	$repeat !== '' ? $repeat : 'none'
);

flash_set('success', 'Reminder added.');
redirect('../pages/reminders.php');
