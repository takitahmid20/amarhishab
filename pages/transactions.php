<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/transactions.php';
require_login();

$userId    = current_user()['id'];
$books     = cashbooks_for_user($userId);
$categories = categories_for_user($userId);

// Filters from the query string.
$typeParam = $_GET['type'] ?? 'all';            // all | income | expense
$catParam  = (int) ($_GET['category'] ?? 0);
$fromParam = $_GET['from'] ?? '';
$toParam   = $_GET['to'] ?? '';
$searchParam = trim($_GET['search'] ?? '');

$filters = [];
if ($typeParam === 'income')  $filters['direction'] = 'in';
if ($typeParam === 'expense') $filters['direction'] = 'out';
if ($catParam > 0)            $filters['category_id'] = $catParam;
if ($fromParam !== '')        $filters['from'] = $fromParam;
if ($toParam !== '')          $filters['to'] = $toParam;
if ($searchParam !== '')      $filters['search'] = $searchParam;

$txns      = transactions_for_user($userId, $filters);
$hasFilters = $filters !== [];
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
					<form class="tx-filters surface" id="tx-filter-form" method="get">
						<div class="tx-filters-left">
							<div class="input-wrap">
								<i class="input-icon" data-lucide="search" aria-hidden="true"></i>
								<input id="tx-search" class="input" type="search" name="search" placeholder="Search details or bill..." value="<?= e($searchParam) ?>" />
							</div>
							<button
								class="btn btn-outline btn-sm"
								type="button"
								data-modal-target="#tx-date-modal"
								aria-haspopup="dialog"
								aria-controls="tx-date-modal"
							>
								<i data-lucide="calendar" aria-hidden="true"></i>
								<span data-tx-date-label><?= ($fromParam !== '' || $toParam !== '') ? e(($fromParam ?: '…') . ' → ' . ($toParam ?: '…')) : 'Date Range' ?></span>
							</button>
							<?php if ($hasFilters): ?>
								<a class="btn btn-outline btn-sm" href="./transactions.php">
									<i data-lucide="x" aria-hidden="true"></i><span>Clear</span>
								</a>
							<?php endif; ?>
						</div>
						<div class="tx-filters-right">
							<select class="select tx-select" name="category" onchange="this.form.submit()">
								<option value="0">All Categories</option>
								<?php foreach ($categories as $cat): ?>
									<option value="<?= e($cat['id']) ?>" <?= $catParam === (int) $cat['id'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option>
								<?php endforeach; ?>
							</select>
							<select class="select tx-select" name="type" onchange="this.form.submit()">
								<option value="all" <?= $typeParam === 'all' ? 'selected' : '' ?>>All Types</option>
								<option value="income" <?= $typeParam === 'income' ? 'selected' : '' ?>>Income</option>
								<option value="expense" <?= $typeParam === 'expense' ? 'selected' : '' ?>>Expense</option>
							</select>
						</div>
					</form>

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
									<article class="tx-item"
										data-details="<?= e(strtolower($tx['details'] ?? '')) ?>"
										data-bill="<?= e(strtolower($tx['bill'] ?? '')) ?>"
										data-category="<?= e(strtolower($tx['category_name'] ?? '')) ?>"
										data-cashbook="<?= e(strtolower($tx['cashbook_name'] ?? '')) ?>"
										data-amount="<?= (float)$tx['amount'] ?>">
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
				<?php if (empty($books)): ?>
					<div class="modal-body">
						<p>You need a cashbook first. <a class="auth-link" href="./cashbooks.php">Create one</a>.</p>
						<div class="modal-footer">
							<button class="btn btn-outline btn-sm" type="button" data-modal-close>Close</button>
						</div>
					</div>
				<?php else: ?>
				<form class="modal-body" action="../actions/transaction-create.php" method="post">
					<?= csrf_field() ?>
					<input type="hidden" name="return_to" value="transactions">
					<label class="field">
						<span class="label">Cashbook</span>
						<select class="select" name="cashbook_id" required>
							<?php foreach ($books as $book): ?>
								<option value="<?= e($book['id']) ?>"><?= e($book['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field">
						<span class="label">Type</span>
						<select class="select" name="direction" required>
							<option value="in">Income (Cash In)</option>
							<option value="out">Expense (Cash Out)</option>
						</select>
					</label>
					<label class="field">
						<span class="label">Amount (৳)</span>
						<input class="input" type="number" name="amount" min="0" step="0.01" placeholder="0" required />
					</label>
					<label class="field">
						<span class="label">Date</span>
						<input class="input" type="date" name="date" value="<?= date('Y-m-d') ?>" required />
					</label>
					<label class="field">
						<span class="label">Category <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
						<select class="select" name="category_id">
							<option value="">No category</option>
							<?php foreach ($categories as $cat): ?>
								<option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field">
						<span class="label">Payment Mode</span>
						<select class="select" name="mode">
							<option value="cash">Cash</option>
							<option value="bank">Bank</option>
							<option value="mobile">Mobile</option>
						</select>
					</label>
					<label class="field">
						<span class="label">Details <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
						<input class="input" type="text" name="details" placeholder="Add a note..." maxlength="255" />
					</label>
					<div class="modal-footer">
						<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
						<button class="btn btn-primary btn-sm" type="submit">Save Transaction</button>
					</div>
				</form>
				<?php endif; ?>
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
				<div class="modal-body">
					<label class="field">
						<span class="label">From</span>
						<input class="input" type="date" name="from" form="tx-filter-form" value="<?= e($fromParam) ?>" />
					</label>
					<label class="field">
						<span class="label">To</span>
						<input class="input" type="date" name="to" form="tx-filter-form" value="<?= e($toParam) ?>" />
					</label>
					<div class="modal-footer">
						<a class="btn btn-outline btn-sm" href="./transactions.php">Clear</a>
						<button class="btn btn-primary btn-sm" type="submit" form="tx-filter-form">Apply</button>
					</div>
				</div>
			</div>
		</div>

		<div class="overlay modal-overlay" id="tx-edit-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
			<div class="modal" role="dialog" aria-modal="true" aria-labelledby="tx-edit-title">
				<div class="modal-head">
					<h2 class="modal-title" id="tx-edit-title">Edit Transaction</h2>
					<button class="icon-btn" type="button" data-modal-close aria-label="Close edit transaction modal">
						<i data-lucide="x" aria-hidden="true"></i>
					</button>
				</div>
				<form class="modal-body" action="../actions/transaction-update.php" method="post">
					<?= csrf_field() ?>
					<input type="hidden" name="id" data-tx-field="id" value="">
					<label class="field">
						<span class="label">Amount (৳)</span>
						<input class="input" type="number" name="amount" data-tx-field="amount" min="0" step="0.01" required />
					</label>
					<label class="field">
						<span class="label">Date</span>
						<input class="input" type="date" name="date" data-tx-field="date" required />
					</label>
					<label class="field">
						<span class="label">Category <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
						<select class="select" name="category_id" data-tx-field="category">
							<option value="">No category</option>
							<?php foreach ($categories as $cat): ?>
								<option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
							<?php endforeach; ?>
						</select>
					</label>
					<label class="field">
						<span class="label">Payment Mode</span>
						<select class="select" name="mode" data-tx-field="mode">
							<option value="cash">Cash</option>
							<option value="bank">Bank</option>
							<option value="mobile">Mobile</option>
						</select>
					</label>
					<label class="field">
						<span class="label">Bill <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
						<input class="input" type="text" name="bill" data-tx-field="bill" maxlength="120" />
					</label>
					<label class="field">
						<span class="label">Details <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
						<input class="input" type="text" name="details" data-tx-field="details" maxlength="255" />
					</label>
					<div class="modal-footer">
						<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
						<button class="btn btn-primary btn-sm" type="submit">Save Changes</button>
					</div>
				</form>
			</div>
		</div>

		<script src="../js/components/modal.js"></script>
		<script src="../js/app.js"></script>
		<script>
			// Fill the edit modal from the clicked row's data-tx-* attributes.
			document.querySelectorAll('[data-tx-id]').forEach(function (btn) {
				btn.addEventListener('click', function () {
					var form = document.querySelector('#tx-edit-modal form');
					form.querySelector('[data-tx-field="id"]').value       = btn.getAttribute('data-tx-id');
					form.querySelector('[data-tx-field="amount"]').value   = btn.getAttribute('data-tx-amount');
					form.querySelector('[data-tx-field="date"]').value     = btn.getAttribute('data-tx-date');
					form.querySelector('[data-tx-field="category"]').value = btn.getAttribute('data-tx-category') || '';
					form.querySelector('[data-tx-field="mode"]').value     = btn.getAttribute('data-tx-mode');
					form.querySelector('[data-tx-field="bill"]').value     = btn.getAttribute('data-tx-bill') || '';
					form.querySelector('[data-tx-field="details"]').value  = btn.getAttribute('data-tx-details') || '';
				});
			});

			// Instant client-side search logic
			(function () {
				var searchInput = document.getElementById('tx-search');
				var items = Array.from(document.querySelectorAll('.tx-item'));
				var countLabel = document.querySelector('[data-tx-count]');

				if (searchInput) {
					searchInput.addEventListener('input', function () {
						var query = searchInput.value.toLowerCase().trim();
						var visibleCount = 0;

						items.forEach(function (item) {
							var details = item.getAttribute('data-details') || '';
							var bill = item.getAttribute('data-bill') || '';
							var cat = item.getAttribute('data-category') || '';
							var book = item.getAttribute('data-cashbook') || '';
							var amount = item.getAttribute('data-amount') || '';

							var matches = !query || 
								details.indexOf(query) !== -1 || 
								bill.indexOf(query) !== -1 || 
								cat.indexOf(query) !== -1 || 
								book.indexOf(query) !== -1 || 
								amount.indexOf(query) !== -1;

							if (matches) {
								item.style.display = '';
								visibleCount++;
							} else {
								item.style.display = 'none';
							}
						});

						if (countLabel) {
							countLabel.textContent = visibleCount + ' ' + (visibleCount === 1 ? 'record' : 'records');
						}
					});

					// Hotkey '/' to focus search
					document.addEventListener('keydown', function (e) {
						if (e.key === '/' && document.activeElement !== searchInput && 
							document.activeElement.tagName !== 'INPUT' && 
							document.activeElement.tagName !== 'SELECT' && 
							document.activeElement.tagName !== 'TEXTAREA') {
							e.preventDefault();
							searchInput.focus();
						}
					});
				}
			})();
		</script>
</body>
</html>