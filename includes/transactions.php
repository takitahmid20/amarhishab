<?php
/**
 * Transaction data access. Every query is scoped to the owning user.
 */

require_once __DIR__ . '/../config/db.php';

/** Insert a transaction. Returns the new id. */
function add_transaction(
	int $userId,
	int $cashbookId,
	string $direction,
	float $amount,
	string $mode,
	?int $categoryId,
	?string $bill,
	?string $details,
	string $occurredAt
): int {
	$sql = 'INSERT INTO transactions
				(user_id, cashbook_id, category_id, direction, amount, mode, bill, details, occurred_at)
			VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
	$stmt = db()->prepare($sql);
	$stmt->execute([
		$userId, $cashbookId, $categoryId,
		$direction === 'in' ? 'in' : 'out',
		$amount, $mode, $bill, $details, $occurredAt,
	]);
	return (int) db()->lastInsertId();
}

/**
 * Transactions for a user with optional filters:
 *   cashbook_id, direction ('in'|'out'), from (Y-m-d), to (Y-m-d), search.
 */
function transactions_for_user(int $userId, array $filters = []): array
{
	$where  = ['t.user_id = ?'];
	$params = [$userId];

	if (!empty($filters['cashbook_id'])) {
		$where[] = 't.cashbook_id = ?';
		$params[] = (int) $filters['cashbook_id'];
	}
	if (!empty($filters['direction']) && in_array($filters['direction'], ['in', 'out'], true)) {
		$where[] = 't.direction = ?';
		$params[] = $filters['direction'];
	}
	if (!empty($filters['category_id'])) {
		$where[] = 't.category_id = ?';
		$params[] = (int) $filters['category_id'];
	}
	if (!empty($filters['from'])) {
		$where[] = 't.occurred_at >= ?';
		$params[] = $filters['from'] . ' 00:00:00';
	}
	if (!empty($filters['to'])) {
		$where[] = 't.occurred_at <= ?';
		$params[] = $filters['to'] . ' 23:59:59';
	}
	if (!empty($filters['search'])) {
		$where[] = '(t.details LIKE ? OR t.bill LIKE ?)';
		$params[] = '%' . $filters['search'] . '%';
		$params[] = '%' . $filters['search'] . '%';
	}

	$sql = 'SELECT t.*, c.name AS cashbook_name, bc.name AS category_name
			FROM transactions t
			JOIN cashbooks c ON c.id = t.cashbook_id
			LEFT JOIN budget_categories bc ON bc.id = t.category_id
			WHERE ' . implode(' AND ', $where) . '
			ORDER BY t.occurred_at DESC, t.id DESC';
	$stmt = db()->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll();
}

/** A single transaction owned by the user, or null. */
function find_transaction(int $id, int $userId): ?array
{
	$stmt = db()->prepare('SELECT * FROM transactions WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** Update a transaction owned by the user. Returns affected row count. */
function update_transaction(
	int $id,
	int $userId,
	float $amount,
	string $mode,
	?int $categoryId,
	?string $bill,
	?string $details,
	string $occurredAt
): int {
	$sql = 'UPDATE transactions
			SET amount = ?, mode = ?, category_id = ?, bill = ?, details = ?, occurred_at = ?
			WHERE id = ? AND user_id = ?';
	$stmt = db()->prepare($sql);
	$stmt->execute([$amount, $mode, $categoryId, $bill, $details, $occurredAt, $id, $userId]);
	return $stmt->rowCount();
}

/** Delete a transaction owned by the user. Returns affected row count. */
function delete_transaction(int $id, int $userId): int
{
	$stmt = db()->prepare('DELETE FROM transactions WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	return $stmt->rowCount();
}

/** Budget categories belonging to a user (for category dropdowns). */
function categories_for_user(int $userId): array
{
	$stmt = db()->prepare('SELECT id, name FROM budget_categories WHERE user_id = ? ORDER BY name');
	$stmt->execute([$userId]);
	return $stmt->fetchAll();
}
