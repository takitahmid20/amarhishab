<?php
/**
 * Budget category data access. "Spent" is computed live from cash-out
 * transactions tagged with the category. All queries scoped to the user.
 */

require_once __DIR__ . '/../config/db.php';

/**
 * Categories for a user with computed spent. Optional cashbook filter.
 */
function budget_categories_with_spent(int $userId, ?int $cashbookId = null): array
{
	$where  = ['bc.user_id = ?'];
	$params = [$userId];
	if ($cashbookId) {
		$where[] = 'bc.cashbook_id = ?';
		$params[] = $cashbookId;
	}

	$sql = 'SELECT bc.id, bc.name, bc.icon, bc.color, bc.limit_amount, bc.cashbook_id,
				c.name AS cashbook_name,
				COALESCE((
					SELECT SUM(t.amount) FROM transactions t
					WHERE t.category_id = bc.id AND t.direction = \'out\'					  AND t.occurred_at BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01 00:00:00') AND DATE_FORMAT(LAST_DAY(CURDATE()), '%Y-%m-%d 23:59:59')				), 0) AS spent
			FROM budget_categories bc
			LEFT JOIN cashbooks c ON c.id = bc.cashbook_id
			WHERE ' . implode(' AND ', $where) . '
			ORDER BY bc.name';
	$stmt = db()->prepare($sql);
	$stmt->execute($params);
	return $stmt->fetchAll();
}

/** A single category owned by the user, or null. */
function find_category(int $id, int $userId): ?array
{
	$stmt = db()->prepare('SELECT * FROM budget_categories WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	$row = $stmt->fetch();
	return $row ?: null;
}

/** Create a budget category. Returns the new id. */
function create_category(int $userId, string $name, ?int $cashbookId, float $limit, ?string $icon, ?string $color): int
{
	$stmt = db()->prepare('INSERT INTO budget_categories (user_id, cashbook_id, name, icon, color, limit_amount) VALUES (?, ?, ?, ?, ?, ?)');
	$stmt->execute([$userId, $cashbookId, $name, $icon, $color, $limit]);
	return (int) db()->lastInsertId();
}

/** Update a category owned by the user. Returns affected row count. */
function update_category(int $id, int $userId, string $name, ?int $cashbookId, float $limit): int
{
	$stmt = db()->prepare('UPDATE budget_categories SET name = ?, cashbook_id = ?, limit_amount = ? WHERE id = ? AND user_id = ?');
	$stmt->execute([$name, $cashbookId, $limit, $id, $userId]);
	return $stmt->rowCount();
}

/** Delete a category owned by the user. Returns affected row count. */
function delete_category(int $id, int $userId): int
{
	$stmt = db()->prepare('DELETE FROM budget_categories WHERE id = ? AND user_id = ?');
	$stmt->execute([$id, $userId]);
	return $stmt->rowCount();
}
