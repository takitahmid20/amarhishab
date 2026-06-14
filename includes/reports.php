<?php
/**
 * Reporting aggregation queries. All scoped to the owning user.
 */

require_once __DIR__ . '/../config/db.php';

/** Resolve a period key to [from, to, label] date strings. */
function report_period(string $period): array
{
	switch ($period) {
		case 'last_month':
			return [date('Y-m-01', strtotime('first day of last month')),
			        date('Y-m-t', strtotime('last day of last month')), 'Last Month'];
		case 'last_3_months':
			return [date('Y-m-01', strtotime('first day of -2 months')), date('Y-m-d'), 'Last 3 Months'];
		case 'this_year':
			return [date('Y-01-01'), date('Y-m-d'), 'This Year'];
		case 'this_month':
		default:
			return [date('Y-m-01'), date('Y-m-d'), 'This Month'];
	}
}

/** Income / expense / net within a date range. */
function report_totals(int $userId, string $from, string $to): array
{
	$stmt = db()->prepare(
		'SELECT
			COALESCE(SUM(CASE WHEN direction = \'in\'  THEN amount END), 0) AS income,
			COALESCE(SUM(CASE WHEN direction = \'out\' THEN amount END), 0) AS expense
		FROM transactions
		WHERE user_id = ? AND occurred_at BETWEEN ? AND ?'
	);
	$stmt->execute([$userId, $from . ' 00:00:00', $to . ' 23:59:59']);
	$row = $stmt->fetch();
	$row['net'] = (float) $row['income'] - (float) $row['expense'];
	return $row;
}

/** Expense total per month for the last N months (oldest first). */
function monthly_expense_trend(int $userId, int $months = 6): array
{
	$stmt = db()->prepare(
		'SELECT DATE_FORMAT(occurred_at, \'%Y-%m\') AS ym, SUM(amount) AS total
		FROM transactions
		WHERE user_id = ? AND direction = \'out\'
			AND occurred_at >= DATE_SUB(DATE_FORMAT(CURDATE(), \'%Y-%m-01\'), INTERVAL ? MONTH)
		GROUP BY ym'
	);
	$stmt->execute([$userId, $months - 1]);
	$byMonth = [];
	foreach ($stmt->fetchAll() as $r) {
		$byMonth[$r['ym']] = (float) $r['total'];
	}

	// Fill every month in the window so the chart always has N bars.
	$out = [];
	for ($i = $months - 1; $i >= 0; $i--) {
		$ts = strtotime("first day of -$i month");
		$ym = date('Y-m', $ts);
		$out[] = ['label' => date('M', $ts), 'total' => $byMonth[$ym] ?? 0.0];
	}
	return $out;
}

/** Expense per category within a range, with transaction count, sorted desc. */
function category_breakdown(int $userId, string $from, string $to): array
{
	$stmt = db()->prepare(
		'SELECT COALESCE(bc.name, \'Uncategorized\') AS name,
				COUNT(*) AS txn_count, SUM(t.amount) AS spent
		FROM transactions t
		LEFT JOIN budget_categories bc ON bc.id = t.category_id
		WHERE t.user_id = ? AND t.direction = \'out\'
			AND t.occurred_at BETWEEN ? AND ?
		GROUP BY name
		ORDER BY spent DESC'
	);
	$stmt->execute([$userId, $from . ' 00:00:00', $to . ' 23:59:59']);
	$rows  = $stmt->fetchAll();
	$total = array_sum(array_map(fn($r) => (float) $r['spent'], $rows));
	foreach ($rows as &$r) {
		$r['share'] = $total > 0 ? round((float) $r['spent'] / $total * 100) : 0;
	}
	return $rows;
}
