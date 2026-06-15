<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/reports.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_login();

$userId = current_user()['id'];
$periods = ['this_month' => 'This Month', 'last_month' => 'Last Month', 'last_3_months' => 'Last 3 Months', 'this_year' => 'This Year'];
$period  = array_key_exists($_GET['period'] ?? '', $periods) ? $_GET['period'] : 'this_month';
[$from, $to, $periodLabel] = report_period($period);

$totals    = report_totals($userId, $from, $to);
$trend     = monthly_expense_trend($userId, 6);
$breakdown = category_breakdown($userId, $from, $to);
$maxTrend  = max(array_map(fn($m) => $m['total'], $trend)) ?: 1;
$palette   = ['#8257e5', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];
$books     = cashbooks_for_user($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Financial Reports | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/reports.css" />
</head>
<body data-page-title="Reports">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main reports-main">
				<section class="container-wide reports-page">
					<header class="reports-hero surface">
						<div class="reports-hero-copy">
							<h1>Financial Reports</h1>
							<p>A minimal snapshot of spending, trends, and category performance.</p>
						</div>
						<div class="reports-hero-actions">
							<form class="reports-filter-wrap" method="get" id="reports-period-form">
								<select id="reports-period-filter" class="select reports-period-filter" name="period" onchange="this.form.submit()">
									<?php foreach ($periods as $key => $label): ?>
										<option value="<?= e($key) ?>" <?= $period === $key ? 'selected' : '' ?>><?= e($label) ?></option>
									<?php endforeach; ?>
								</select>
							</form>
							<button
								class="btn btn-primary btn-sm reports-download-btn"
								type="button"
								data-modal-target="#reports-download-modal"
								aria-haspopup="dialog"
								aria-controls="reports-download-modal"
								aria-label="Download PDF report"
							>
								<i data-lucide="file-down" aria-hidden="true"></i>
								<span>Download PDF</span>
							</button>
						</div>
					</header>

					<section class="quick-grid" aria-label="Report KPIs">
						<article class="stat-card">
							<div class="stat-top">
								<span class="stat-title">Total Income</span>
								<i data-lucide="trending-up" aria-hidden="true"></i>
							</div>
							<div class="stat-value"><?= e(taka($totals['income'])) ?></div>
							<div class="stat-sub"><?= e($periodLabel) ?></div>
						</article>
						<article class="stat-card">
							<div class="stat-top">
								<span class="stat-title">Total Expense</span>
								<i data-lucide="trending-down" aria-hidden="true"></i>
							</div>
							<div class="stat-value" style="color:var(--color-danger)"><?= e(taka($totals['expense'])) ?></div>
							<div class="stat-sub"><?= e($periodLabel) ?></div>
						</article>
						<article class="stat-card">
							<div class="stat-top">
								<span class="stat-title">Net Savings</span>
								<i data-lucide="piggy-bank" aria-hidden="true"></i>
							</div>
							<div class="stat-value" style="color:<?= $totals['net'] >= 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>"><?= e(taka($totals['net'])) ?></div>
							<div class="stat-sub"><?= e($periodLabel) ?></div>
						</article>
					</section>

					<section class="reports-insights-grid" aria-label="Report visual insights">
						<article class="chart-card">
							<header class="reports-section-head">
								<h2>Monthly Expense Trend</h2>
								<p>Last 6 months</p>
							</header>
							<div class="chart-area">
								<?php foreach ($trend as $m): ?>
									<?php $h = (int) round($m['total'] / $maxTrend * 100); ?>
									<div class="bar" style="height: <?= max($h, 2) ?>%" title="<?= e(taka($m['total'])) ?>"><span><?= e($m['label']) ?></span></div>
								<?php endforeach; ?>
							</div>
						</article>
						<article class="chart-card">
							<header class="reports-section-head">
								<h2>Category Breakdown</h2>
								<p><?= e($periodLabel) ?></p>
							</header>
							<?php
								$top = array_slice($breakdown, 0, 5);
								// Build the conic-gradient stops + legend from real shares.
								$stops = [];
								$cursor = 0;
								foreach ($top as $i => $row) {
									$color = $palette[$i % count($palette)];
									$end   = $cursor + (float) $row['share'];
									$stops[] = "$color {$cursor}% {$end}%";
									$cursor  = $end;
								}
								if ($cursor < 100 && !empty($stops)) {
									$stops[] = "#e5e7eb {$cursor}% 100%";
								}
								$gradient = empty($stops) ? '#e5e7eb 0 100%' : implode(',', $stops);
							?>
							<?php if (empty($top)): ?>
								<p class="reports-empty">No expenses in this period.</p>
							<?php else: ?>
								<div class="donut reports-donut" style="background:conic-gradient(<?= $gradient ?>);"></div>
								<div class="legend">
									<?php foreach ($top as $i => $row): ?>
										<div class="legend-item">
											<div class="legend-left"><span class="dot" style="background:<?= $palette[$i % count($palette)] ?>;"></span><span><?= e($row['name']) ?></span></div>
											<strong><?= (int) $row['share'] ?>%</strong>
										</div>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
						</article>
					</section>

					<section class="reports-table-card surface">
						<header class="reports-section-head reports-table-head">
							<h2>Top Expense Categories</h2>
							<p>Sorted by amount</p>
						</header>
						<div class="table-wrap">
							<table>
								<thead>
									<tr>
										<th>Category</th>
										<th>Transactions</th>
										<th>Spent</th>
										<th>Share</th>
									</tr>
								</thead>
								<tbody>
									<?php if (empty($breakdown)): ?>
										<tr><td colspan="4" class="reports-empty-row">No expenses in this period.</td></tr>
									<?php else: ?>
										<?php foreach ($breakdown as $row): ?>
											<tr>
												<td><?= e($row['name']) ?></td>
												<td><?= (int) $row['txn_count'] ?></td>
												<td><?= e(taka($row['spent'])) ?></td>
												<td><?= (int) $row['share'] ?>%</td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</section>

					<div class="overlay modal-overlay" id="reports-download-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal reports-modal" role="dialog" aria-modal="true" aria-labelledby="reports-download-title">
							<div class="modal-head">
								<h2 class="modal-title" id="reports-download-title">Customize PDF Report</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close download report modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body reports-modal-form">
								<label class="field">
									<span class="label">Cashbook Scope</span>
									<select class="select" name="cashbookScope">
										<option value="all" selected>All Cashbooks</option>
										<?php foreach ($books as $b): ?>
											<option value="<?= e($b['id']) ?>"><?= e($b['name']) ?></option>
										<?php endforeach; ?>
									</select>
								</label>

								<div class="reports-modal-range">
									<label class="field">
										<span class="label">From Month</span>
										<input class="input" type="month" name="fromMonth" value="2026-01" required />
									</label>
									<label class="field">
										<span class="label">To Month</span>
										<input class="input" type="month" name="toMonth" value="2026-04" required />
									</label>
								</div>

								<p class="reports-modal-note">Print / Save as PDF uses your browser's native print engine, styled beautifully for export.</p>

								<div class="modal-footer reports-modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="button" onclick="window.print()" data-modal-close>
										<i data-lucide="printer" aria-hidden="true"></i>
										<span>Print / Save PDF</span>
									</button>
								</div>
							</form>
						</div>
					</div>
				</section>
			</main>
		</div>
	</div>
	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>
