<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/reminders.php';
require_once __DIR__ . '/../includes/budget.php';
require_login();

$userId  = current_user()['id'];
$allowed = ['all', 'pending', 'paid', 'overdue'];
$fp      = $_GET['filter'] ?? 'all';
$filter  = in_array($fp, $allowed, true) ? $fp : 'all';

$reminders = reminders_for_user($userId, $filter);
$totals    = reminder_totals($userId);
$today     = date('Y-m-d');

// Budget alerts: categories at or above 75% of their limit.
$alerts = array_filter(budget_categories_with_spent($userId), function ($c) {
	return $c['limit_amount'] > 0 && ($c['spent'] / $c['limit_amount']) >= 0.75;
});

$success = flash_get('success');
$error   = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Reminders | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/reminders.css" />
</head>
<body data-page-title="Reminders">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main reminders-main">
				<section class="container-wide reminders-page">

					<!-- Hero -->
					<header class="reminders-hero surface">
						<div class="reminders-hero-copy">
							<h1>Reminders</h1>
							<p>Track your bill payments and budget alerts</p>
						</div>
						<button
							class="btn btn-primary btn-sm"
							type="button"
							data-modal-target="#add-reminder-modal"
							aria-haspopup="dialog"
							aria-controls="add-reminder-modal"
						>
							<i data-lucide="plus" aria-hidden="true"></i>
							<span>Add Reminder</span>
						</button>
					</header>

					<!-- Summary Cards -->
					<section class="reminders-summary" aria-label="Reminder summary">
						<article class="reminder-stat-card surface">
							<div class="reminder-stat-head">
								<p>Total Reminders</p>
								<i data-lucide="bell" aria-hidden="true"></i>
							</div>
							<strong data-reminder-total><?= (int) $totals['total'] ?></strong>
						</article>
						<article class="reminder-stat-card surface">
							<div class="reminder-stat-head">
								<p>Due Soon</p>
								<i data-lucide="clock" aria-hidden="true"></i>
							</div>
							<strong class="reminder-stat-warning" data-reminder-due-soon><?= (int) $totals['due_soon'] ?></strong>
						</article>
						<article class="reminder-stat-card surface">
							<div class="reminder-stat-head">
								<p>Overdue</p>
								<i data-lucide="alert-circle" aria-hidden="true"></i>
							</div>
							<strong class="reminder-stat-danger" data-reminder-overdue><?= (int) $totals['overdue'] ?></strong>
						</article>
					</section>

					<!-- Bill Payment Reminders -->
					<section class="reminders-board surface">
						<header class="reminders-board-head">
							<h2>
								<i data-lucide="receipt" aria-hidden="true"></i>
								Bill Payment Reminders
							</h2>
							<div class="reminders-board-actions">
								<form method="get" id="reminders-filter-form">
									<select class="select reminders-filter" name="filter" aria-label="Filter reminders" onchange="this.form.submit()">
										<option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
										<option value="pending" <?= $filter === 'pending' ? 'selected' : '' ?>>Pending</option>
										<option value="paid" <?= $filter === 'paid' ? 'selected' : '' ?>>Paid</option>
										<option value="overdue" <?= $filter === 'overdue' ? 'selected' : '' ?>>Overdue</option>
									</select>
								</form>
								<button
									class="btn btn-primary btn-sm"
									type="button"
									data-modal-target="#add-reminder-modal"
									aria-haspopup="dialog"
									aria-controls="add-reminder-modal"
								>
									<i data-lucide="plus" aria-hidden="true"></i>
									<span>Add</span>
								</button>
							</div>
						</header>

						<?php if ($success !== ''): ?>
							<p class="auth-success" role="status"><?= e($success) ?></p>
						<?php endif; ?>
						<?php if ($error !== ''): ?>
							<p class="auth-error" role="alert"><?= e($error) ?></p>
						<?php endif; ?>

						<div class="reminder-list">
							<?php if (empty($reminders)): ?>
								<p class="reminder-empty">No reminders here. Add one to stay on top of bills.</p>
							<?php else: ?>
								<?php foreach ($reminders as $r): ?>
									<?php
										$done = (int) $r['is_done'] === 1;
										if ($done) {
											$itemMod = ' reminder-item--paid'; $iconMod = ' reminder-item-icon--success';
											$badge = 'reminder-badge--paid'; $badgeText = 'Paid';
										} elseif ($r['due_date'] < $today) {
											$itemMod = ' reminder-item--overdue'; $iconMod = ' reminder-item-icon--danger';
											$badge = 'reminder-badge--overdue'; $badgeText = 'Overdue';
										} elseif ($r['due_date'] <= date('Y-m-d', strtotime('+7 days'))) {
											$itemMod = ' reminder-item--warning'; $iconMod = ' reminder-item-icon--warning';
											$badge = 'reminder-badge--warning'; $badgeText = 'Due soon';
										} else {
											$itemMod = ''; $iconMod = '';
											$badge = 'reminder-badge--pending'; $badgeText = 'Pending';
										}
									?>
									<article class="reminder-item<?= $itemMod ?>">
										<div class="reminder-item-icon<?= $iconMod ?>">
											<i data-lucide="bell" aria-hidden="true"></i>
										</div>
										<div class="reminder-item-body">
											<div class="reminder-item-top">
												<strong class="reminder-item-title"><?= e($r['title']) ?></strong>
												<span class="reminder-badge <?= $badge ?>"><?= $badgeText ?></span>
											</div>
											<p class="reminder-item-date">
												<i data-lucide="calendar" aria-hidden="true"></i>
												Due: <?= e(date('j M Y', strtotime($r['due_date']))) ?>
												<?php if ($r['category']): ?> · <?= e($r['category']) ?><?php endif; ?>
												<?php if ($r['repeat_cycle'] !== 'none'): ?> · <?= e(ucfirst($r['repeat_cycle'])) ?><?php endif; ?>
											</p>
										</div>
										<div class="reminder-item-right">
											<strong class="reminder-item-amount"><?= $r['amount'] !== null ? e(taka($r['amount'])) : '—' ?></strong>
											<div class="reminder-item-actions">
												<?php if (!$done): ?>
													<form action="../actions/reminder-done.php" method="post" style="display:inline">
														<?= csrf_field() ?>
														<input type="hidden" name="id" value="<?= e($r['id']) ?>">
														<button class="icon-btn reminder-action-btn" type="submit" title="Mark as paid">
															<i data-lucide="check-circle" aria-hidden="true"></i>
														</button>
													</form>
												<?php endif; ?>
												<form action="../actions/reminder-delete.php" method="post" onsubmit="return confirm('Delete this reminder?');" style="display:inline">
													<?= csrf_field() ?>
													<input type="hidden" name="id" value="<?= e($r['id']) ?>">
													<button class="icon-btn reminder-action-btn reminder-action-btn--danger" type="submit" title="Delete">
														<i data-lucide="trash-2" aria-hidden="true"></i>
													</button>
												</form>
											</div>
										</div>
									</article>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</section>

					<!-- Budget Alert Section -->
					<section class="reminders-board surface">
						<header class="reminders-board-head">
							<h2>
								<i data-lucide="triangle-alert" aria-hidden="true"></i>
								Budget Alerts
							</h2>
						</header>
						<div class="reminder-list">
							<?php if (empty($alerts)): ?>
								<p class="reminder-empty">No budget alerts. You're within your limits.</p>
							<?php else: ?>
								<?php foreach ($alerts as $a): ?>
									<?php
										$spent = (float) $a['spent'];
										$limit = (float) $a['limit_amount'];
										$over  = $spent > $limit;
										$pct   = (int) round($spent / $limit * 100);
									?>
									<article class="reminder-item <?= $over ? 'reminder-item--overdue' : 'reminder-item--warning' ?>">
										<div class="reminder-item-icon <?= $over ? 'reminder-item-icon--danger' : 'reminder-item-icon--warning' ?>">
											<i data-lucide="trending-up" aria-hidden="true"></i>
										</div>
										<div class="reminder-item-body">
											<div class="reminder-item-top">
												<strong class="reminder-item-title"><?= e($a['name']) ?> <?= $over ? 'Budget Exceeded' : 'Limit Alert' ?></strong>
												<span class="reminder-badge <?= $over ? 'reminder-badge--overdue' : 'reminder-badge--warning' ?>"><?= $over ? 'Exceeded' : $pct . '% Used' ?></span>
											</div>
											<p class="reminder-item-date">
												<i data-lucide="trending-up" aria-hidden="true"></i>
												Spent <?= e(taka($spent)) ?> of <?= e(taka($limit)) ?> limit
											</p>
										</div>
										<div class="reminder-item-right">
											<strong class="reminder-item-amount <?= $over ? 'reminder-item-amount--danger' : 'reminder-item-amount--warning' ?>">
												<?= $over ? '+' . e(taka($spent - $limit)) : e(taka($limit - $spent)) . ' left' ?>
											</strong>
										</div>
									</article>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</section>

					<!-- Add Reminder Modal -->
					<div class="overlay modal-overlay" id="add-reminder-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="add-reminder-title">
							<div class="modal-head">
								<h2 class="modal-title" id="add-reminder-title">Add Reminder</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body" data-add-reminder-form>
								<label class="field">
									<span class="label">Title</span>
									<input class="input" type="text" name="title" placeholder="e.g. Electricity Bill" required maxlength="80" autofocus />
								</label>
								<label class="field">
									<span class="label">Amount (৳)</span>
									<input class="input" type="number" name="amount" placeholder="0" min="0" required />
								</label>
								<label class="field">
									<span class="label">Due Date</span>
									<input class="input" type="date" name="dueDate" required />
								</label>
								<label class="field">
									<span class="label">Category</span>
									<select class="select" name="category" required>
										<option value="" disabled selected>Select category</option>
										<option value="electricity">Electricity</option>
										<option value="internet">Internet</option>
										<option value="rent">Rent</option>
										<option value="water">Water</option>
										<option value="gas">Gas</option>
										<option value="other">Other</option>
									</select>
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Add Reminder</button>
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