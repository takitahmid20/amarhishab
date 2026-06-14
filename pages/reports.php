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
								<p>Current month</p>
							</header>
							<div class="donut reports-donut" style="--donut-food:#8257e5;--donut-transport:#3b82f6;--donut-bills:#10b981;--donut-shopping:#f59e0b;--donut-other:#ef4444;background:conic-gradient(var(--donut-food) 0 32%,var(--donut-transport) 32% 53%,var(--donut-bills) 53% 71%,var(--donut-shopping) 71% 85%,var(--donut-other) 85% 100%);"></div>
							<div class="legend">
								<div class="legend-item">
									<div class="legend-left"><span class="dot" style="background:#8257e5;"></span><span>Food & Dining</span></div>
									<strong>32%</strong>
								</div>
								<div class="legend-item">
									<div class="legend-left"><span class="dot" style="background:#3b82f6;"></span><span>Transportation</span></div>
									<strong>21%</strong>
								</div>
								<div class="legend-item">
									<div class="legend-left"><span class="dot" style="background:#10b981;"></span><span>Bills & Utilities</span></div>
									<strong>18%</strong>
								</div>
								<div class="legend-item">
									<div class="legend-left"><span class="dot" style="background:#f59e0b;"></span><span>Shopping</span></div>
									<strong>14%</strong>
								</div>
								<div class="legend-item">
									<div class="legend-left"><span class="dot" style="background:#ef4444;"></span><span>Other</span></div>
									<strong>15%</strong>
								</div>
							</div>
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
									<tr>
										<td>Food & Dining</td>
										<td>42</td>
										<td>৳ 9,280</td>
										<td>32%</td>
									</tr>
									<tr>
										<td>Transportation</td>
										<td>30</td>
										<td>৳ 5,964</td>
										<td>21%</td>
									</tr>
									<tr>
										<td>Bills & Utilities</td>
										<td>12</td>
										<td>৳ 5,112</td>
										<td>18%</td>
									</tr>
									<tr>
										<td>Shopping</td>
										<td>18</td>
										<td>৳ 3,976</td>
										<td>14%</td>
									</tr>
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
										<option value="b4">B4</option>
										<option value="b3">B3</option>
										<option value="b2">B2</option>
										<option value="business-book">Business Book</option>
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

								<p class="reports-modal-note">Report options are ready. PDF generation will be connected in a later step.</p>

								<div class="modal-footer reports-modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="button" data-modal-close>
										<span>Generate PDF (Soon)</span>
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
