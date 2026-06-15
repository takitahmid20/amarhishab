<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/transactions.php';
require_login();

$userId   = current_user()['id'];
$id       = (int) ($_GET['id'] ?? 0);
$cashbook = $id > 0 ? find_cashbook($id, $userId) : null;
$categories = categories_for_user($userId);
$flashSuccess = flash_get('success');
$flashError   = flash_get('error');

if (!$cashbook) {
	flash_set('error', 'Cashbook not found.');
	redirect('./cashbooks.php');
}

$entries = cashbook_entries($id);

$filterCategories = [];
$filterModes      = [];
foreach ($entries as $entry) {
	if (!empty($entry['category_name'])) {
		$filterCategories[$entry['category_name']] = true;
	}
	if (!empty($entry['mode'])) {
		$filterModes[$entry['mode']] = true;
	}
}
$filterCategories = array_keys($filterCategories);
$filterModes      = array_keys($filterModes);

// Running balance (entries are oldest-first), plus totals.
$cashIn = 0.0;
$cashOut = 0.0;
$balance = 0.0;
foreach ($entries as $i => $entry) {
	$amt = (float) $entry['amount'];
	if ($entry['direction'] === 'in') {
		$cashIn  += $amt;
		$balance += $amt;
	} else {
		$cashOut += $amt;
		$balance -= $amt;
	}
	$entries[$i]['running_balance'] = $balance;
}
// Display newest first.
$entries = array_reverse($entries);
$entryCount = count($entries);
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Cashbook Ledger | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/cashbook-details.css" />
</head>
<body data-page-title="Cashbook Ledger">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main cashbook-details-main">
				<section class="container-wide cashbook-details-page" data-cashbook-details-page>
					<header class="cashbook-details-header surface">
						<div class="cashbook-details-header-left">
							<a class="btn btn-outline btn-sm cashbook-back-link" href="./cashbooks.php" aria-label="Back to cashbooks">
								<i data-lucide="arrow-left" aria-hidden="true"></i>
							</a>
							<div class="cashbook-details-copy">
								<p class="cashbook-details-eyebrow">Cashbook Ledger</p>
								<h1 data-cashbook-name><?= e($cashbook['name']) ?></h1>
								<p class="cashbook-details-sub">Monitor transactions, cash movement, and running balance from one branded workspace.</p>
							</div>
						</div>

						<div class="cashbook-details-header-right">
							<button class="btn btn-outline btn-sm cashbook-bulk-btn" type="button">
								<i data-lucide="cloud-upload" aria-hidden="true"></i>
								<span>Add Bulk Entries</span>
							</button>
							<button class="btn btn-outline btn-sm" type="button">
								<i data-lucide="download" aria-hidden="true"></i>
								<span>Reports</span>
							</button>
						</div>
					</header>

					<?php if ($flashSuccess !== ''): ?>
						<p class="auth-success" role="status"><?= e($flashSuccess) ?></p>
					<?php endif; ?>
					<?php if ($flashError !== ''): ?>
						<p class="auth-error" role="alert"><?= e($flashError) ?></p>
					<?php endif; ?>

					<section class="cashbook-filter-row surface" aria-label="Entry filters">
						<label>
							<select id="filter-duration" class="select">
								<option value="all">Duration: All Time</option>
								<option value="today">Today</option>
								<option value="this-month">This Month</option>
								<option value="this-year">This Year</option>
							</select>
						</label>
						<label>
							<select id="filter-direction" class="select">
								<option value="all">Entry Type: All</option>
								<option value="in">Cash In</option>
								<option value="out">Cash Out</option>
							</select>
						</label>
						<label>
							<select id="filter-category" class="select">
								<option value="all">Category: All</option>
								<?php foreach ($filterCategories as $catName): ?>
									<option value="<?= e(strtolower($catName)) ?>"><?= e($catName) ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label>
							<select id="filter-mode" class="select">
								<option value="all">Payment Mode: All</option>
								<?php foreach ($filterModes as $mName): ?>
									<option value="<?= e(strtolower($mName)) ?>"><?= e(ucfirst($mName)) ?></option>
								<?php endforeach; ?>
							</select>
						</label>
					</section>

					<section class="cashbook-search-row surface">
						<div class="input-wrap cashbook-search-wrap">
							<i class="input-icon" data-lucide="search" aria-hidden="true"></i>
							<input id="ledger-search" class="input" type="text" placeholder="Search by details, bill, category, or amount..." />
							<span class="cashbook-search-hint">/</span>
						</div>

						<div class="cashbook-quick-actions">
							<button
								class="btn btn-sm btn-mint cashbook-action-btn"
								type="button"
								data-modal-target="#cash-in-modal"
								aria-haspopup="dialog"
								aria-controls="cash-in-modal"
							>
								<i data-lucide="plus" aria-hidden="true"></i>
								<span>Cash In</span>
							</button>
							<button
								class="btn btn-sm btn-danger cashbook-action-btn"
								type="button"
								data-modal-target="#cash-out-modal"
								aria-haspopup="dialog"
								aria-controls="cash-out-modal"
							>
								<i data-lucide="minus" aria-hidden="true"></i>
								<span>Cash Out</span>
							</button>
						</div>
					</section>

					<section class="cashbook-summary" aria-label="Cashbook balance summary">
						<article class="cashbook-summary-item surface">
							<div class="cashbook-summary-icon cashbook-summary-icon--in"><i data-lucide="plus" aria-hidden="true"></i></div>
							<div>
								<p>Cash In</p>
								<strong data-summary-cash-in><?= e(taka($cashIn)) ?></strong>
							</div>
						</article>
						<article class="cashbook-summary-item surface">
							<div class="cashbook-summary-icon cashbook-summary-icon--out"><i data-lucide="minus" aria-hidden="true"></i></div>
							<div>
								<p>Cash Out</p>
								<strong data-summary-cash-out><?= e(taka($cashOut)) ?></strong>
							</div>
						</article>
						<article class="cashbook-summary-item surface">
							<div class="cashbook-summary-icon cashbook-summary-icon--balance"><i data-lucide="equal" aria-hidden="true"></i></div>
							<div>
								<p>Net Balance</p>
								<strong data-summary-net-balance><?= e(taka($balance)) ?></strong>
							</div>
						</article>
					</section>

					<section class="cashbook-ledger surface">
						<div class="cashbook-ledger-head">
							<div class="cashbook-ledger-copy">
								<h2>Ledger Entries</h2>
								<p>Every transaction log for this cashbook.</p>
							</div>
							<div class="cashbook-table-toolbar">
								<p class="cashbook-table-count" data-entry-count-label>Showing <?= $entryCount ?> <?= $entryCount === 1 ? 'entry' : 'entries' ?></p>
								<div class="cashbook-pagination">
									<label>
										<select class="select">
											<option>Page 1</option>
										</select>
									</label>
									<span>of 1</span>
									<button class="btn btn-outline btn-sm cashbook-page-btn" type="button" aria-label="Previous page">
										<i data-lucide="chevron-left" aria-hidden="true"></i>
									</button>
									<button class="btn btn-outline btn-sm cashbook-page-btn" type="button" aria-label="Next page">
										<i data-lucide="chevron-right" aria-hidden="true"></i>
									</button>
								</div>
							</div>
						</div>

						<div class="table-wrap cashbook-table-wrap">
							<table>
								<thead>
									<tr>
										<th class="cell-check"><input type="checkbox" aria-label="Select all entries" /></th>
										<th>Date &amp; Time</th>
										<th>Details</th>
										<th>Category</th>
										<th>Mode</th>
										<th>Bill</th>
										<th class="cell-amount">Amount</th>
										<th class="cell-balance">Balance</th>
									</tr>
								</thead>
								<tbody data-entry-list>
									<?php if ($entryCount === 0): ?>
										<tr><td colspan="8" class="cashbook-empty-row">No entries yet. Add a Cash In or Cash Out to get started.</td></tr>
									<?php else: ?>
										<?php foreach ($entries as $entry): ?>
											<?php
												$isIn = $entry['direction'] === 'in';
												$amountClass = $isIn ? 'cell-amount cell-amount--in' : 'cell-amount cell-amount--out';
												$sign = $isIn ? '+' : '-';
											?>
											<tr class="ledger-row"
												data-date="<?= date('Y-m-d', strtotime($entry['occurred_at'])) ?>"
												data-direction="<?= e($entry['direction']) ?>"
												data-category="<?= e(strtolower($entry['category_name'] ?: '')) ?>"
												data-mode="<?= e(strtolower($entry['mode'])) ?>"
												data-details="<?= e(strtolower($entry['details'] ?? '')) ?>"
												data-bill="<?= e(strtolower($entry['bill'] ?? '')) ?>"
												data-amount="<?= (float)$entry['amount'] ?>">
												<td class="cell-check"><input type="checkbox" aria-label="Select entry" /></td>
												<td>
													<?= e(date('M j, Y', strtotime($entry['occurred_at']))) ?>
													<span class="cashbook-cell-time"><?= e(date('h:i A', strtotime($entry['occurred_at']))) ?></span>
												</td>
												<td><?= e($entry['details'] ?: '—') ?></td>
												<td><?= e($entry['category_name'] ?: '—') ?></td>
												<td><?= e(ucfirst($entry['mode'])) ?></td>
												<td><?= e($entry['bill'] ?: '—') ?></td>
												<td class="<?= $amountClass ?>"><?= $sign ?> <?= e(taka($entry['amount'])) ?></td>
												<td class="cell-balance"><?= e(taka($entry['running_balance'])) ?></td>
											</tr>
										<?php endforeach; ?>
									<?php endif; ?>
								</tbody>
							</table>
						</div>
					</section>

					<div class="overlay modal-overlay" id="cash-in-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal cashbook-entry-modal" role="dialog" aria-modal="true" aria-labelledby="cash-in-modal-title">
							<div class="modal-head">
								<h2 class="modal-title" id="cash-in-modal-title">Add Cash In Entry</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close cash in modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body cashbook-entry-form" action="../actions/transaction-create.php" method="post">
								<?= csrf_field() ?>
								<input type="hidden" name="cashbook_id" value="<?= e($id) ?>">
								<input type="hidden" name="direction" value="in">
								<label class="field" for="cash-in-amount">
									<span class="label">Money Value</span>
									<input class="input" id="cash-in-amount" name="amount" type="number" min="0" step="0.01" placeholder="Enter amount" required />
								</label>
								<label class="field" for="cash-in-date">
									<span class="label">Date</span>
									<input class="input" id="cash-in-date" name="date" type="date" value="<?= date('Y-m-d') ?>" required />
								</label>
								<label class="field" for="cash-in-category">
									<span class="label">Category <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<select class="select" id="cash-in-category" name="category_id">
										<option value="">No category</option>
										<?php foreach ($categories as $cat): ?>
											<option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
										<?php endforeach; ?>
									</select>
								</label>
								<label class="field" for="cash-in-mode">
									<span class="label">Payment Mode</span>
									<select class="select" id="cash-in-mode" name="mode">
										<option value="cash">Cash</option>
										<option value="bank">Bank</option>
										<option value="mobile">Mobile</option>
									</select>
								</label>
								<label class="field" for="cash-in-details">
									<span class="label">Details <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<input class="input" id="cash-in-details" name="details" type="text" placeholder="What was this for?" maxlength="255" />
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Save Cash In</button>
								</div>
							</form>
						</div>
					</div>

					<div class="overlay modal-overlay" id="cash-out-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal cashbook-entry-modal" role="dialog" aria-modal="true" aria-labelledby="cash-out-modal-title">
							<div class="modal-head">
								<h2 class="modal-title" id="cash-out-modal-title">Add Cash Out Entry</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close cash out modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body cashbook-entry-form" action="../actions/transaction-create.php" method="post">
								<?= csrf_field() ?>
								<input type="hidden" name="cashbook_id" value="<?= e($id) ?>">
								<input type="hidden" name="direction" value="out">
								<label class="field" for="cash-out-amount">
									<span class="label">Money Value</span>
									<input class="input" id="cash-out-amount" name="amount" type="number" min="0" step="0.01" placeholder="Enter amount" required />
								</label>
								<label class="field" for="cash-out-date">
									<span class="label">Date</span>
									<input class="input" id="cash-out-date" name="date" type="date" value="<?= date('Y-m-d') ?>" required />
								</label>
								<label class="field" for="cash-out-category">
									<span class="label">Category <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<select class="select" id="cash-out-category" name="category_id">
										<option value="">No category</option>
										<?php foreach ($categories as $cat): ?>
											<option value="<?= e($cat['id']) ?>"><?= e($cat['name']) ?></option>
										<?php endforeach; ?>
									</select>
								</label>
								<label class="field" for="cash-out-mode">
									<span class="label">Payment Mode</span>
									<select class="select" id="cash-out-mode" name="mode">
										<option value="cash">Cash</option>
										<option value="bank">Bank</option>
										<option value="mobile">Mobile</option>
									</select>
								</label>
								<label class="field" for="cash-out-bill">
									<span class="label">Bill <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<input class="input" id="cash-out-bill" name="bill" type="text" placeholder="e.g. Internet, Rent" maxlength="120" />
								</label>
								<label class="field" for="cash-out-details">
									<span class="label">Details <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<input class="input" id="cash-out-details" name="details" type="text" placeholder="What was this for?" maxlength="255" />
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Save Cash Out</button>
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
	<script>
		// Live filtering, searching, and summary card recalculation for Ledger Entries
		(function () {
			var searchInput = document.getElementById('ledger-search');
			var durationSelect = document.getElementById('filter-duration');
			var directionSelect = document.getElementById('filter-direction');
			var categorySelect = document.getElementById('filter-category');
			var modeSelect = document.getElementById('filter-mode');
			var rows = Array.from(document.querySelectorAll('.ledger-row'));
			var countLabel = document.querySelector('[data-entry-count-label]');
			
			var cashInCard = document.querySelector('[data-summary-cash-in]');
			var cashOutCard = document.querySelector('[data-summary-cash-out]');
			var netBalanceCard = document.querySelector('[data-summary-net-balance]');

			function formatTaka(val) {
				return '৳ ' + val.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
			}

			function updateLedger() {
				var query = searchInput.value.toLowerCase().trim();
				var duration = durationSelect.value;
				var direction = directionSelect.value;
				var category = categorySelect.value;
				var mode = modeSelect.value;

				var now = new Date();
				var todayStr = now.toISOString().slice(0, 10);
				
				// Calculate start of current month
				var currentYear = now.getFullYear();
				var currentMonth = now.getMonth(); // 0-indexed
				var thisMonthStr = currentYear + '-' + String(currentMonth + 1).padStart(2, '0');
				var thisYearStr = String(currentYear);

				var totalIn = 0;
				var totalOut = 0;
				var visibleCount = 0;

				rows.forEach(function (row) {
					var rDate = row.getAttribute('data-date') || '';
					var rDir = row.getAttribute('data-direction') || '';
					var rCat = row.getAttribute('data-category') || '';
					var rMode = row.getAttribute('data-mode') || '';
					var rDetails = row.getAttribute('data-details') || '';
					var rBill = row.getAttribute('data-bill') || '';
					var rAmount = parseFloat(row.getAttribute('data-amount')) || 0;

					// Duration match
					var matchesDuration = true;
					if (duration === 'today') {
						matchesDuration = (rDate === todayStr);
					} else if (duration === 'this-month') {
						matchesDuration = rDate.indexOf(thisMonthStr) === 0;
					} else if (duration === 'this-year') {
						matchesDuration = rDate.indexOf(thisYearStr) === 0;
					}

					// Direction match
					var matchesDirection = (direction === 'all' || rDir === direction);

					// Category match
					var matchesCategory = (category === 'all' || rCat === category);

					// Mode match
					var matchesMode = (mode === 'all' || rMode === mode);

					// Search query match
					var matchesQuery = !query || 
						rDetails.indexOf(query) !== -1 || 
						rBill.indexOf(query) !== -1 || 
						rCat.indexOf(query) !== -1 || 
						rAmount.toString().indexOf(query) !== -1;

					var isVisible = matchesDuration && matchesDirection && matchesCategory && matchesMode && matchesQuery;

					if (isVisible) {
						row.style.display = '';
						visibleCount++;
						if (rDir === 'in') {
							totalIn += rAmount;
						} else {
							totalOut += rAmount;
						}
					} else {
						row.style.display = 'none';
					}
				});

				// Update summary totals
				if (cashInCard) cashInCard.textContent = formatTaka(totalIn);
				if (cashOutCard) cashOutCard.textContent = formatTaka(totalOut);
				if (netBalanceCard) netBalanceCard.textContent = formatTaka(totalIn - totalOut);

				// Update count label
				if (countLabel) {
					countLabel.textContent = 'Showing ' + visibleCount + ' ' + (visibleCount === 1 ? 'entry' : 'entries');
				}
			}

			if (searchInput) searchInput.addEventListener('input', updateLedger);
			if (durationSelect) durationSelect.addEventListener('change', updateLedger);
			if (directionSelect) directionSelect.addEventListener('change', updateLedger);
			if (categorySelect) categorySelect.addEventListener('change', updateLedger);
			if (modeSelect) modeSelect.addEventListener('change', updateLedger);

			// Support keyboard shortcut "/" to focus search
			document.addEventListener('keydown', function (e) {
				if (e.key === '/' && document.activeElement !== searchInput && 
					document.activeElement.tagName !== 'INPUT' && 
					document.activeElement.tagName !== 'SELECT' && 
					document.activeElement.tagName !== 'TEXTAREA') {
					e.preventDefault();
					searchInput.focus();
				}
			});
		})();
	</script>
</body>
</html>
