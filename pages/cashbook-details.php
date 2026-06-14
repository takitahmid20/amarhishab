<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
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
				<section
					class="container-wide cashbook-details-page"
					data-cashbook-details-page
					data-source="../data/cashbook-transactions.json"
					data-source-fallback="#cashbook-transaction-fallback"
				>
					<header class="cashbook-details-header surface">
						<div class="cashbook-details-header-left">
							<a class="btn btn-outline btn-sm cashbook-back-link" href="./cashbooks.php" aria-label="Back to cashbooks">
								<i data-lucide="arrow-left" aria-hidden="true"></i>
							</a>
							<div class="cashbook-details-copy">
								<p class="cashbook-details-eyebrow">Cashbook Ledger</p>
								<h1 data-cashbook-name>ABC</h1>
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

					<section class="cashbook-filter-row surface" aria-label="Entry filters">
						<label><select class="select"><option>Duration: All Time</option></select></label>
						<label><select class="select"><option>Entry Type: All</option></select></label>
						<label><select class="select"><option>Category: All</option></select></label>
						<label><select class="select"><option>Payment Mode: All</option></select></label>
					</section>

					<section class="cashbook-search-row surface">
						<div class="input-wrap cashbook-search-wrap">
							<i class="input-icon" data-lucide="search" aria-hidden="true"></i>
							<input class="input" type="text" placeholder="Search by details, bill, category, or amount..." />
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
								<strong data-summary-cash-in>1,300</strong>
							</div>
						</article>
						<article class="cashbook-summary-item surface">
							<div class="cashbook-summary-icon cashbook-summary-icon--out"><i data-lucide="minus" aria-hidden="true"></i></div>
							<div>
								<p>Cash Out</p>
								<strong data-summary-cash-out>400</strong>
							</div>
						</article>
						<article class="cashbook-summary-item surface">
							<div class="cashbook-summary-icon cashbook-summary-icon--balance"><i data-lucide="equal" aria-hidden="true"></i></div>
							<div>
								<p>Net Balance</p>
								<strong data-summary-net-balance>900</strong>
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
								<p class="cashbook-table-count" data-entry-count-label>Showing 1 - 3 of 3 entries</p>
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
							<form class="modal-body cashbook-entry-form">
								<label class="field" for="cash-in-amount">
									<span class="label">Money Value</span>
									<input class="input" id="cash-in-amount" name="amount" type="number" min="0" step="0.01" placeholder="Enter amount" required />
								</label>
								<label class="field" for="cash-in-date">
									<span class="label">Date</span>
									<input class="input" id="cash-in-date" name="date" type="date" required />
								</label>
								<label class="field" for="cash-in-category">
									<span class="label">Category</span>
									<select class="select" id="cash-in-category" name="category" required>
										<option value="" selected>Select category</option>
										<option>Sales</option>
										<option>Service</option>
										<option>Investment</option>
										<option>Other Income</option>
									</select>
								</label>
								<label class="field" for="cash-in-attachment">
									<span class="label">File Attach</span>
									<input class="input-file" id="cash-in-attachment" name="attachment" type="file" />
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit" data-modal-close>Save Cash In</button>
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
							<form class="modal-body cashbook-entry-form">
								<label class="field" for="cash-out-amount">
									<span class="label">Money Value</span>
									<input class="input" id="cash-out-amount" name="amount" type="number" min="0" step="0.01" placeholder="Enter amount" required />
								</label>
								<label class="field" for="cash-out-date">
									<span class="label">Date</span>
									<input class="input" id="cash-out-date" name="date" type="date" required />
								</label>
								<label class="field" for="cash-out-category">
									<span class="label">Category</span>
									<select class="select" id="cash-out-category" name="category" required>
										<option value="" selected>Select category</option>
										<option>Purchase</option>
										<option>Salary</option>
										<option>Bills</option>
										<option>Other Expense</option>
									</select>
								</label>
								<label class="field" for="cash-out-attachment">
									<span class="label">File Attach</span>
									<input class="input-file" id="cash-out-attachment" name="attachment" type="file" />
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit" data-modal-close>Save Cash Out</button>
								</div>
							</form>
						</div>
					</div>

					<script id="cashbook-transaction-fallback" type="application/json">
						[
							{
								"cashbookId": "b4",
								"cashbookName": "B4",
								"summary": {
									"cashIn": 1300,
									"cashOut": 400,
									"netBalance": 900
								},
								"entries": [
									{
										"dateLabel": "Today",
										"time": "08:36 PM",
										"details": "--",
										"category": "--",
										"mode": "--",
										"bill": "--",
										"amount": 400,
										"balance": 900,
										"direction": "out"
									},
									{
										"dateLabel": "Today",
										"time": "08:36 PM",
										"details": "--",
										"category": "--",
										"mode": "--",
										"bill": "--",
										"amount": 500,
										"balance": 1300,
										"direction": "in"
									},
									{
										"dateLabel": "10 Apr, 2026",
										"time": "08:36 PM",
										"details": "--",
										"category": "--",
										"mode": "--",
										"bill": "--",
										"amount": 800,
										"balance": 800,
										"direction": "in"
									}
								]
							}
						]
					</script>
				</section>
			</main>
		</div>
	</div>
	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>
