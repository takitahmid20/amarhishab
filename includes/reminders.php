<?php
/**
 * Reminder data access. Status is derived: a reminder is "paid" when is_done,
 * "overdue" when its due date has passed, otherwise pending. Scoped to user.
 */

require_once __DIR__ . '/../config/db.php';

/** Reminders for a user. filter: all | pending | paid | overdue. */
function reminders_for_user(int $userId, string $filter = 'all'): array
{
	$where  = ['user_id = ?'];
	$params = [$userId];
	$today  = date('Y-m-d');

	if ($filter === 'paid') {
		$where[] = 'is_done = 1';
	} elseif ($filter === 'pending') {
		$where[] = 'is_done = 0 AND due_date >= ?';
		$params[] = $today;
	} elseif ($filter === 'overdue') {
		$where[] = 'is_done = 0 AND due_date < ?';
		$params[] = $today;
	}

	$sql = 'SELECT * FROM reminders WHERE ' . implode(' AND ', $where)
		. ' ORDER BY is_done ASC, due_date ASC, id DESC';
	$stmt = db()->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll();
}

/** Summary counts: total, due soon (next 7 days), overdue. */
function reminder_totals(int $userId): array
{
	$stmt = db()->prepare(
		'SELECT
			COUNT(*) AS total,
			SUM(is_done = 0 AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)) AS due_soon,
			SUM(is_done = 0 AND due_date < CURDATE()) AS overdue
		FROM reminders WHERE user_id = ?'
	);
	$stmt->execute([$userId]);
	return $stmt->fetch();
}

/** A single reminder owned by the user, or null. */
function find_reminder(int $id, int $userId): ?array
{
	$stmt = db()->prepare('SELECT * FROM reminders WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** Create a reminder. Returns the new id. */
function create_reminder(int $userId, string $title, ?string $category, ?float $amount, string $dueDate, string $repeat = 'none'): int
{
	$stmt = db()->prepare('INSERT INTO reminders (user_id, title, category, amount, due_date, repeat_cycle) VALUES (?, ?, ?, ?, ?, ?)');
	$stmt->execute([$userId, $title, $category, $amount, $dueDate, in_array($repeat, ['none', 'weekly', 'monthly'], true) ? $repeat : 'none']);
	return (int) db()->lastInsertId();
}

/** Mark a reminder done/undone. Returns affected row count. */
function set_reminder_done(int $id, int $userId, bool $done): int
{
	$stmt = db()->prepare('UPDATE reminders SET is_done = ? WHERE id = ? AND user_id = ?');
	$stmt->execute([$done ? 1 : 0, $id, $userId]);
	return $stmt->rowCount();
}

/** Delete a reminder owned by the user. Returns affected row count. */
function delete_reminder(int $id, int $userId): int
{
	$stmt = db()->prepare('DELETE FROM reminders WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	return $stmt->rowCount();
}
