<?php
/**
 * Cashbook data access. All queries are scoped to a user id so one user can
 * never read or change another user's books.
 */

require_once __DIR__ . '/../config/db.php';

/** All cashbooks for a user, with computed cash in/out/balance. */
function cashbooks_for_user(int $userId): array
{
	$sql = 'SELECT c.id, c.name, c.description, c.status, c.created_at,
				COALESCE(SUM(CASE WHEN t.direction = \'in\'  THEN t.amount END), 0) AS cash_in,
				COALESCE(SUM(CASE WHEN t.direction = \'out\' THEN t.amount END), 0) AS cash_out
			FROM cashbooks c
			LEFT JOIN transactions t ON t.cashbook_id = c.id
			WHERE c.user_id = ?
			GROUP BY c.id
			ORDER BY c.created_at DESC';
	$stmt = db()->prepare($sql);
	$stmt->execute([$userId]);
	$rows = $stmt->fetchAll();

	foreach ($rows as &$row) {
		$row['balance'] = (float) $row['cash_in'] - (float) $row['cash_out'];
	}
	return $rows;
}

/** A single cashbook owned by the user, or null. */
function find_cashbook(int $id, int $userId): ?array
{
	$stmt = db()->prepare('SELECT * FROM cashbooks WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** Create a cashbook, returns the new id. */
function create_cashbook(int $userId, string $name, ?string $description = null, string $status = 'live'): int
{
	$stmt = db()->prepare('INSERT INTO cashbooks (user_id, name, description, status) VALUES (?, ?, ?, ?)');
	$stmt->execute([$userId, $name, $description, $status]);
	return (int) db()->lastInsertId();
}

/** Update a cashbook owned by the user. Returns affected row count. */
function update_cashbook(int $id, int $userId, string $name, ?string $description, string $status): int
{
	$stmt = db()->prepare('UPDATE cashbooks SET name = ?, description = ?, status = ? WHERE id = ? AND user_id = ?');
	$stmt->execute([$name, $description, $status, $id, $userId]);
	return $stmt->rowCount();
}

/** Delete a cashbook owned by the user. Returns affected row count. */
function delete_cashbook(int $id, int $userId): int
{
	$stmt = db()->prepare('DELETE FROM cashbooks WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	return $stmt->rowCount();
}
