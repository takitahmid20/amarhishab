<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/transactions.php';
require_once __DIR__ . '/../includes/budget.php';
require_login();

$userId = current_user()['id'];
$books  = cashbooks_for_user($userId);

// Total balance across all cashbooks.
$totalBalance = 0.0;
foreach ($books as $b) {
	$totalBalance += (float) $b['balance'];
}

// Income / expense for the current month.
$monthStart = date('Y-m-01');
$today      = date('Y-m-d');
$monthTx    = transactions_for_user($userId, ['from' => $monthStart, 'to' => $today]);
$incomeMonth = 0.0;
$expenseMonth = 0.0;
foreach ($monthTx as $t) {
	if ($t['direction'] === 'in') $incomeMonth += (float) $t['amount'];
	else                          $expenseMonth += (float) $t['amount'];
}

// Recent transactions + budget summary.
$recent = array_slice(transactions_for_user($userId), 0, 5);
$summaryCats = array_slice(budget_categories_with_spent($userId), 0, 4);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Dashboard | AmarHishab</title>

	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
</head>

<body data-page-title="Dashboard Overview">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main">
				<section class="container dashboard-page">
					<div class="dashboard-hero">
						<div class="dashboard-hero-copy">
							<h1>Dashboard Overview</h1>
							<p>Track your finances and manage your budget</p>
						</div>
					</div>

					<div class="dashboard-summary">
						<article class="stat-card surface">
							<div class="stat-top">
								<div>
									<p class="stat-title">Total Balance</p>
									<p class="stat-value"><?= e(taka($totalBalance)) ?></p>
								</div>
								<div class="dashboard-stat-icon">
									<i data-lucide="dollar-sign" aria-hidden="true"></i>
								</div>
							</div>
							<p class="stat-sub stat-sub-success">Across <?= count($books) ?> cashbook<?= count($books) === 1 ? '' : 's' ?></p>
						</article>

						<article class="stat-card surface">
							<div class="stat-top">
								<div>
									<p class="stat-title">Income this Month</p>
									<p class="stat-value"><?= e(taka($incomeMonth)) ?></p>
								</div>
								<div class="dashboard-stat-icon dashboard-stat-icon--mint">
									<i data-lucide="trending-up" aria-hidden="true"></i>
								</div>
							</div>
						</article>

						<article class="stat-card surface">
							<div class="stat-top">
								<div>
									<p class="stat-title">Expenses this Month</p>
									<p class="stat-value"><?= e(taka($expenseMonth)) ?></p>
								</div>
								<div class="dashboard-stat-icon dashboard-stat-icon--red">
									<i data-lucide="trending-down" aria-hidden="true"></i>
								</div>
							</div>
							<p class="stat-sub <?= ($incomeMonth - $expenseMonth) >= 0 ? 'stat-sub-success' : 'stat-sub-danger' ?>">Net <?= e(taka($incomeMonth - $expenseMonth)) ?> this month</p>
						</article>
					</div>

					<div class="dashboard-grid">
						<div class="dashboard-left">
							<section class="surface dashboard-card">
								<div class="dashboard-card-header">
									<h3>Recent Transactions</h3>
									<a class="btn btn-secondary btn-sm" href="./transactions.php">View All</a>
								</div>
								<div class="dashboard-transaction-list">
									<?php if (empty($recent)): ?>
										<p class="dashboard-empty">No transactions yet.</p>
									<?php else: ?>
										<?php foreach ($recent as $t): ?>
											<?php
												$isIn  = $t['direction'] === 'in';
												$title = $t['details'] ?: ($t['bill'] ?: ($isIn ? 'Cash In' : 'Cash Out'));
												$meta  = trim(($t['category_name'] ? $t['category_name'] . ' • ' : '') . $t['cashbook_name'] . ' • ' . date('j M, Y', strtotime($t['occurred_at'])));
											?>
											<div class="transaction-card">
												<div class="tx-left">
													<div class="tx-icon" style="background:<?= $isIn ? '#ecfdf5' : '#fef2f2' ?>; color:<?= $isIn ? '#16a34a' : '#b91c1c' ?>;">
														<i data-lucide="<?= $isIn ? 'arrow-down-left' : 'arrow-up-right' ?>" aria-hidden="true"></i>
													</div>
													<div>
														<h4 class="tx-title"><?= e($title) ?></h4>
														<p class="tx-meta"><?= e($meta) ?></p>
													</div>
												</div>
												<div class="tx-amount <?= $isIn ? 'text-success' : 'text-danger' ?>"><?= $isIn ? '+' : '-' ?><?= e(taka($t['amount'])) ?></div>
											</div>
										<?php endforeach; ?>
									<?php endif; ?>
								</div>
							</section>
						</div>

						<div class="dashboard-right">
							<section class="surface dashboard-card quick-actions">
								<div class="dashboard-card-header">
									<h3>Quick Actions</h3>
								</div>
								<div class="action-buttons">
									<button class="btn btn-primary" type="button" data-modal-target="#dashboard-cash-in-modal" aria-haspopup="dialog" aria-controls="dashboard-cash-in-modal">
										<span>+ Add Income</span>
									</button>
									<button class="btn btn-secondary" type="button" data-modal-target="#dashboard-cash-out-modal" aria-haspopup="dialog" aria-controls="dashboard-cash-out-modal">
										<span>+ Add Expense</span>
									</button>
								</div>
						</section>
					<section class="surface dashboard-card budget-summary">
						<div class="dashboard-card-header">
							<h3>Budget Summary</h3>
						</div>
							<div class="budget-item">
									<div class="budget-item-head">
										<p>Food & Dining</p>
										<span>৳1200 / ৳1500</span>
									</div>
									<div class="budget-track">
										<span class="budget-fill budget-fill--purple"></span>
									</div>
								</div>

								<div class="budget-item">
									<div class="budget-item-head">
										<p>Transportation</p>
										<span>৳800 / ৳1000</span>
									</div>
									<div class="budget-track">
										<span class="budget-fill budget-fill--blue"></span>
									</div>
								</div>

								<div class="budget-item">
									<div class="budget-item-head">
										<p>Entertainment</p>
										<span>৳300 / ৳500</span>
									</div>
									<div class="budget-track">
										<span class="budget-fill budget-fill--green"></span>
									</div>
								</div>
							</section>
						</div>
					</div>
				</section>
			</main>
		</div>
	</div>

	<!-- Cash In Modal -->
	<div class="overlay modal-overlay" id="dashboard-cash-in-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
		<div class="modal" role="dialog" aria-modal="true" aria-labelledby="dashboard-cash-in-title">
			<div class="modal-head">
				<h2 class="modal-title" id="dashboard-cash-in-title">Add Income</h2>
				<button class="icon-btn" type="button" data-modal-close aria-label="Close cash in modal">
					<i data-lucide="x" aria-hidden="true"></i>
				</button>
			</div>
			<form class="modal-body">
				<label class="field">
					<span class="label">Amount (৳)</span>
					<input class="input" type="number" name="amount" min="0" step="0.01" placeholder="Enter amount" required />
				</label>
				<label class="field">
					<span class="label">Category</span>
					<select class="select" name="category" required>
						<option value="" selected disabled>Select category</option>
						<option>Salary</option>
						<option>Freelance</option>
						<option>Investment</option>
						<option>Sales</option>
						<option>Other Income</option>
					</select>
				</label>
				<label class="field">
					<span class="label">Date</span>
					<input class="input" type="date" name="date" required />
				</label>
				<label class="field">
					<span class="label">Note <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
					<input class="input" type="text" name="note" placeholder="Add a note..." />
				</label>
				<div class="modal-footer">
					<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
					<button class="btn btn-primary btn-sm" type="submit">Save Income</button>
				</div>
			</form>
		</div>
	</div>

	<!-- Cash Out Modal -->
	<div class="overlay modal-overlay" id="dashboard-cash-out-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
		<div class="modal" role="dialog" aria-modal="true" aria-labelledby="dashboard-cash-out-title">
			<div class="modal-head">
				<h2 class="modal-title" id="dashboard-cash-out-title">Add Expense</h2>
				<button class="icon-btn" type="button" data-modal-close aria-label="Close cash out modal">
					<i data-lucide="x" aria-hidden="true"></i>
				</button>
			</div>
			<form class="modal-body">
				<label class="field">
					<span class="label">Amount (৳)</span>
					<input class="input" type="number" name="amount" min="0" step="0.01" placeholder="Enter amount" required />
				</label>
				<label class="field">
					<span class="label">Category</span>
					<select class="select" name="category" required>
						<option value="" selected disabled>Select category</option>
						<option>Food & Dining</option>
						<option>Transportation</option>
						<option>Bills & Utilities</option>
						<option>Shopping</option>
						<option>Other Expense</option>
					</select>
				</label>
				<label class="field">
					<span class="label">Date</span>
					<input class="input" type="date" name="date" required />
				</label>
				<label class="field">
					<span class="label">Note <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
					<input class="input" type="text" name="note" placeholder="Add a note..." />
				</label>
				<div class="modal-footer">
					<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
					<button class="btn btn-primary btn-sm" type="submit">Save Expense</button>
				</div>
			</form>
		</div>
	</div>

	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
	<script>
		AmarHishabModal.initModalComponent();
	</script>
</body>
</html>