<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/transactions.php';
require_login();

$userId    = current_user()['id'];
$books     = cashbooks_for_user($userId);
$categories = categories_for_user($userId);
$txns      = transactions_for_user($userId);
$success   = flash_get('success');
$error     = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Transactions | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/transactions.css" />
</head>
<body data-page-title="Transactions">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main tx-main">
				<section class="container-wide tx-page">

					<!-- Hero -->
					<header class="tx-hero surface">
						<div class="tx-hero-copy">
							<h1>Transactions</h1>
							<p>View and manage your transaction history</p>
						</div>
						<button
							class="btn btn-primary btn-sm"
							type="button"
							data-modal-target="#tx-form-modal"
							aria-haspopup="dialog"
							aria-controls="tx-form-modal"
						>
							<i data-lucide="plus" aria-hidden="true"></i>
							<span>Add Transaction</span>
						</button>
					</header>

					<!-- Filters -->
					<div class="tx-filters surface">
						<div class="tx-filters-left">
							<button class="btn btn-outline btn-sm" type="button" data-tx-filter-trigger>
								<i data-lucide="sliders-horizontal" aria-hidden="true"></i>
								<span>Filter</span>
							</button>
							<button
								class="btn btn-outline btn-sm"
								type="button"
								data-modal-target="#tx-date-modal"
								aria-haspopup="dialog"
								aria-controls="tx-date-modal"
							>
								<i data-lucide="calendar" aria-hidden="true"></i>
								<span data-tx-date-label>Date Range</span>
							</button>
						</div>
						<div class="tx-filters-right">
							<select class="select tx-select" data-tx-category-filter>
								<option value="all">All Categories</option>
							</select>
							<select class="select tx-select" data-tx-type-filter>
								<option value="all">All Types</option>
								<option value="income">Income</option>
								<option value="expense">Expense</option>
							</select>
						</div>
					</div>

					<?php if ($success !== ''): ?>
						<p class="auth-success" role="status"><?= e($success) ?></p>
					<?php endif; ?>
					<?php if ($error !== ''): ?>
						<p class="auth-error" role="alert"><?= e($error) ?></p>
					<?php endif; ?>

					<!-- Transaction List -->
					<div class="tx-board surface">
						<div class="tx-board-head">
							<h2>All Transactions</h2>
							<span class="tx-count" data-tx-count><?= count($txns) ?> records</span>
						</div>

						<div class="tx-list" data-tx-list>
							<?php if (empty($txns)): ?>
								<p class="tx-empty">No transactions yet. Add one to get started.</p>
							<?php else: ?>
								<?php foreach ($txns as $tx): ?>
									<?php
										$isIn  = $tx['direction'] === 'in';
										$label = $tx['details'] ?: ($tx['bill'] ?: ($isIn ? 'Cash In' : 'Cash Out'));
										$meta  = trim(($tx['category_name'] ?? '') . ($tx['category_name'] ? ' · ' : '') . $tx['cashbook_name'] . ' · ' . date('j M, Y', strtotime($tx['occurred_at'])));
									?>
									<article class="tx-item">
										<div class="tx-item-left">
											<div class="tx-icon <?= $isIn ? 'tx-icon--mint' : 'tx-icon--pink' ?>">
												<i data-lucide="<?= $isIn ? 'arrow-down-left' : 'arrow-up-right' ?>" aria-hidden="true"></i>
											</div>
											<div class="tx-item-info">
												<h4 class="tx-item-title"><?= e($label) ?></h4>
												<p class="tx-item-meta"><?= e($meta) ?></p>
											</div>
										</div>
										<div class="tx-item-right">
											<span class="tx-amount <?= $isIn ? 'tx-amount--positive' : 'tx-amount--negative' ?>"><?= $isIn ? '+' : '-' ?><?= e(taka($tx['amount'])) ?></span>
											<div class="tx-item-actions">
												<button class="icon-btn" type="button" title="Edit"
													data-modal-target="#tx-edit-modal"
													data-tx-id="<?= e($tx['id']) ?>"
													data-tx-amount="<?= e($tx['amount']) ?>"
													data-tx-mode="<?= e($tx['mode']) ?>"
													data-tx-category="<?= e($tx['category_id'] ?? '') ?>"
													data-tx-bill="<?= e($tx['bill'] ?? '') ?>"
													data-tx-details="<?= e($tx['details'] ?? '') ?>"
													data-tx-date="<?= e(date('Y-m-d', strtotime($tx['occurred_at']))) ?>">
													<i data-lucide="pencil" aria-hidden="true"></i>
												</button>
												<form action="../actions/transaction-delete.php" method="post" onsubmit="return confirm('Delete this transaction?');" style="display:inline">
													<?= csrf_field() ?>
													<input type="hidden" name="id" value="<?= e($tx['id']) ?>">
													<button class="icon-btn icon-btn--danger" type="submit" title="Delete">
														<i data-lucide="trash-2" aria-hidden="true"></i>
													</button>
												</form>
											</div>
										</div>
									</article>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>
					</div>

				</section>
			</main>
		</div>
	</div>

		<div class="overlay modal-overlay" id="tx-form-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
			<div class="modal" role="dialog" aria-modal="true" aria-labelledby="tx-form-title">
				<div class="modal-head">
					<h2 class="modal-title" id="tx-form-title">Add Transaction</h2>
					<button class="icon-btn" type="button" data-modal-close aria-label="Close transaction modal">
						<i data-lucide="x" aria-hidden="true"></i>
					</button>
				</div>
				<form class="modal-body" data-tx-form>
					<label class="field">
						<span class="label">Title</span>
						<input class="input" type="text" name="title" placeholder="e.g. Grocery Shopping" required maxlength="80" autofocus />
					</label>
					<label class="field">
						<span class="label">Category</span>
						<input class="input" type="text" name="category" placeholder="e.g. Food & Dining" required maxlength="60" />
					</label>
					<label class="field">
						<span class="label">Type</span>
						<select class="select" name="type" required>
							<option value="" disabled selected>Select type</option>
							<option value="income">Income</option>
							<option value="expense">Expense</option>
						</select>
					</label>
					<label class="field">
						<span class="label">Amount (৳)</span>
						<input class="input" type="number" name="amount" min="0" step="0.01" placeholder="0" required />
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
						<button class="btn btn-primary btn-sm" type="submit" data-tx-submit>Save Transaction</button>
					</div>
				</form>
			</div>
		</div>

		<div class="overlay modal-overlay" id="tx-date-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
			<div class="modal" role="dialog" aria-modal="true" aria-labelledby="tx-date-title">
				<div class="modal-head">
					<h2 class="modal-title" id="tx-date-title">Date Range</h2>
					<button class="icon-btn" type="button" data-modal-close aria-label="Close date range modal">
						<i data-lucide="x" aria-hidden="true"></i>
					</button>
				</div>
				<form class="modal-body" data-tx-date-form>
					<label class="field">
						<span class="label">From</span>
						<input class="input" type="date" name="from" />
					</label>
					<label class="field">
						<span class="label">To</span>
						<input class="input" type="date" name="to" />
					</label>
					<div class="modal-footer">
						<button class="btn btn-outline btn-sm" type="button" data-tx-date-clear>Clear</button>
						<button class="btn btn-primary btn-sm" type="submit">Apply</button>
					</div>
				</form>
			</div>
		</div>

		<script src="../js/components/modal.js"></script>
		<script src="../js/app.js"></script>
</body>
</html>