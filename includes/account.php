<?php
/**
 * Account data access: profile, password, and full-data reset.
 */

require_once __DIR__ . '/../config/db.php';

/** Full user row including password hash, or null. */
function get_user_full(int $id): ?array
{
	$stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
	$stmt->execute([$id]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** True if the email belongs to a different user. */
function email_taken_by_other(string $email, int $exceptId): bool
{
	$stmt = db()->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
	$stmt->execute([$email, $exceptId]);
	return (bool) $stmt->fetch();
}

/** Update a user's name and email. */
function update_user_profile(int $id, string $name, string $email): void
{
	$stmt = db()->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
	$stmt->execute([$name, $email, $id]);
}

/** Update a user's password hash. */
function update_user_password(int $id, string $hash): void
{
	$stmt = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
	$stmt->execute([$hash, $id]);
}

/** Delete all of a user's financial data (keeps the account). */
function reset_user_data(int $userId): void
{
	foreach (['reminders', 'borrow_lend', 'transactions', 'budget_categories', 'cashbooks'] as $table) {
		$stmt = db()->prepare("DELETE FROM {$table} WHERE user_id = ?");
		$stmt->execute([$userId]);
	}
}
