<?php
/**
 * Borrow/Lend data access. type is 'borrow' (I owe) or 'lend' (owed to me).
 * Scoped to the owning user.
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Records for a user. Optional filter: 'borrow' | 'lend' | 'pending' | 'all'.
 */
function borrow_lend_records(int $userId, string $filter = 'all'): array
{
	$where  = ['user_id = ?'];
	$params = [$userId];

	if ($filter === 'borrow' || $filter === 'lend') {
		$where[] = 'type = ?';
		$params[] = $filter;
	} elseif ($filter === 'pending') {
		$where[] = 'is_settled = 0';
	}

	$sql = 'SELECT * FROM borrow_lend WHERE ' . implode(' AND ', $where)
		. ' ORDER BY is_settled ASC, due_date IS NULL, due_date ASC, id DESC';
	$stmt = db()->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll();
}

/** Totals + counts for the summary cards. */
function borrow_lend_totals(int $userId): array
{
	$stmt = db()->prepare(
		'SELECT
			COALESCE(SUM(CASE WHEN type = \'borrow\' AND is_settled = 0 THEN amount END), 0) AS borrowed,
			COALESCE(SUM(CASE WHEN type = \'lend\'   AND is_settled = 0 THEN amount END), 0) AS lent,
			SUM(type = \'borrow\') AS borrow_count,
			SUM(type = \'lend\')   AS lend_count,
			SUM(is_settled = 0)    AS pending_count
		FROM borrow_lend WHERE user_id = ?'
	);
	$stmt->execute([$userId]);
	return $stmt->fetch();
}

/** A single record owned by the user, or null. */
function find_borrow_lend(int $id, int $userId): ?array
{
	$stmt = db()->prepare('SELECT * FROM borrow_lend WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** Create a record. Returns the new id. */
function create_borrow_lend(int $userId, string $type, string $person, float $amount, ?string $note, ?string $dueDate): int
{
	$stmt = db()->prepare('INSERT INTO borrow_lend (user_id, type, person, amount, note, due_date) VALUES (?, ?, ?, ?, ?, ?)');
	$stmt->execute([$userId, $type === 'lend' ? 'lend' : 'borrow', $person, $amount, $note, $dueDate ?: null]);
	return (int) db()->lastInsertId();
}

/** Mark a record settled or unsettled. Returns affected row count. */
function set_borrow_lend_settled(int $id, int $userId, bool $settled): int
{
	$stmt = db()->prepare('UPDATE borrow_lend SET is_settled = ? WHERE id = ? AND user_id = ?');
	$stmt->execute([$settled ? 1 : 0, $id, $userId]);
	return $stmt->rowCount();
}

/** Delete a record owned by the user. Returns affected row count. */
function delete_borrow_lend(int $id, int $userId): int
{
	$stmt = db()->prepare('DELETE FROM borrow_lend WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	return $stmt->rowCount();
}
