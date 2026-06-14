<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Borrow / Lend | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/borrow-lend.css" />
</head>

<body data-page-title="Borrow / Lend">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>
		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main bl-main">
				<section class="container-wide bl-page">

					<!-- Hero -->
					<header class="bl-hero surface">
						<div class="bl-hero-copy">
							<h1>Borrow / Lend</h1>
							<p>Track money you owe and money owed to you</p>
						</div>
						
					</header>

					<!-- Stat Cards -->
					<div class="bl-stat-grid">
						<div class="bl-stat-card surface">
							<div class="bl-stat-icon bl-stat-icon--borrowed">
								<i data-lucide="arrow-up" aria-hidden="true"></i>
							</div>
							<div class="bl-stat-body">
								<p class="bl-stat-label">Total Borrowed</p>
								<h2 class="bl-stat-amount bl-stat-amount--borrowed">৳ 500</h2>
								<p class="bl-stat-sub">Money you owe · 2 pending</p>
							</div>
						</div>
						<div class="bl-stat-card surface">
							<div class="bl-stat-icon bl-stat-icon--lent">
								<i data-lucide="arrow-down" aria-hidden="true"></i>
							</div>
							<div class="bl-stat-body">
								<p class="bl-stat-label">Total Lent</p>
								<h2 class="bl-stat-amount bl-stat-amount--lent">৳ 500</h2>
								<p class="bl-stat-sub">Money owed to you · 1 cleared</p>
							</div>
						</div>
					</div>

					<!-- Records Card -->
					<div class="bl-records-card surface">
						<div class="bl-records-head">
							<h3>All Records</h3>
							<div class="bl-records-controls">
								<button
									class="btn btn-primary btn-sm"
									type="button"
									data-modal-target="#add-record-modal"
									aria-haspopup="dialog"
									aria-controls="add-record-modal"
								>
									<i data-lucide="plus" aria-hidden="true"></i>
									<span>Add Record</span>
								</button>
							</div>
						</div>

						<div class="bl-tabs">
							<button class="bl-tab active" type="button" data-bl-filter="all">
								All <span class="bl-tab-count">3</span>
							</button>
							<button class="bl-tab" type="button" data-bl-filter="borrowed">
								Borrowed <span class="bl-tab-count">2</span>
							</button>
							<button class="bl-tab" type="button" data-bl-filter="lent">
								Lent <span class="bl-tab-count">1</span>
							</button>
							<button class="bl-tab" type="button" data-bl-filter="pending">
								Pending <span class="bl-tab-count">2</span>
							</button>
						</div>

						<div class="bl-list">

							<div class="bl-list-item" data-type="borrowed" data-status="pending">
								<div class="bl-item-left">
									<div class="bl-avatar">K</div>
									<div class="bl-item-info">
										<h4 class="bl-item-name">Karim</h4>
										<div class="bl-item-meta">
											<span class="bl-item-type bl-item-type--borrowed">Borrowed</span>
											<span class="bl-meta-dot">·</span>
											<span class="bl-item-date">Apr 1, 2026</span>
											<span class="bl-meta-dot">·</span>
											<span class="badge badge-warning">Pending</span>
										</div>
									</div>
								</div>
								<div class="bl-item-right">
									<span class="bl-item-amount bl-item-amount--borrowed">৳ 300</span>
									<div class="bl-item-actions">
										<button class="icon-btn" type="button" title="Mark as paid" aria-label="Mark Karim as paid">
											<i data-lucide="check" aria-hidden="true"></i>
										</button>
										<button class="icon-btn icon-btn--danger" type="button" aria-label="Delete Karim's record">
											<i data-lucide="trash-2" aria-hidden="true"></i>
										</button>
									</div>
								</div>
							</div>

							<div class="bl-list-item" data-type="lent" data-status="paid">
								<div class="bl-item-left">
									<div class="bl-avatar">S</div>
									<div class="bl-item-info">
										<h4 class="bl-item-name">Sarah</h4>
										<div class="bl-item-meta">
											<span class="bl-item-type bl-item-type--lent">Lent</span>
											<span class="bl-meta-dot">·</span>
											<span class="bl-item-date">Mar 28, 2026</span>
											<span class="bl-meta-dot">·</span>
											<span class="badge badge-success">Paid</span>
										</div>
									</div>
								</div>
								<div class="bl-item-right">
									<span class="bl-item-amount bl-item-amount--lent">৳ 500</span>
									<div class="bl-item-actions">
										<button class="icon-btn icon-btn--danger" type="button" aria-label="Delete Sarah's record">
											<i data-lucide="trash-2" aria-hidden="true"></i>
										</button>
									</div>
								</div>
							</div>

							<div class="bl-list-item" data-type="borrowed" data-status="pending">
								<div class="bl-item-left">
									<div class="bl-avatar">A</div>
									<div class="bl-item-info">
										<h4 class="bl-item-name">Ahmed</h4>
										<div class="bl-item-meta">
											<span class="bl-item-type bl-item-type--borrowed">Borrowed</span>
											<span class="bl-meta-dot">·</span>
											<span class="bl-item-date">Mar 25, 2026</span>
											<span class="bl-meta-dot">·</span>
											<span class="badge badge-warning">Pending</span>
										</div>
									</div>
								</div>
								<div class="bl-item-right">
									<span class="bl-item-amount bl-item-amount--borrowed">৳ 200</span>
									<div class="bl-item-actions">
										<button class="icon-btn" type="button" aria-label="Mark Ahmed as paid">
											<i data-lucide="check" aria-hidden="true"></i>
										</button>
										<button class="icon-btn icon-btn--danger" type="button" aria-label="Delete Ahmed's record">
											<i data-lucide="trash-2" aria-hidden="true"></i>
										</button>
									</div>
								</div>
							</div>

						</div>
					</div>

				</section>
			</main>
		</div>
	</div>

	<!-- Add Record Modal -->
	<div class="overlay modal-overlay" id="add-record-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
		<div class="modal" role="dialog" aria-modal="true" aria-labelledby="add-record-title">
			<div class="modal-head">
				<h2 class="modal-title" id="add-record-title">Add Record</h2>
				<button class="icon-btn" type="button" data-modal-close aria-label="Close modal">
					<i data-lucide="x" aria-hidden="true"></i>
				</button>
			</div>
			<div class="modal-body">
				<div class="field">
					<label class="label" for="bl-type">Type</label>
					<select class="select" id="bl-type" name="type">
						<option value="borrowed">Borrowed (I owe)</option>
						<option value="lent">Lent (They owe me)</option>
					</select>
				</div>
				<div class="field">
					<label class="label" for="bl-person">Person Name</label>
					<input class="input" id="bl-person" type="text" name="person" placeholder="e.g. Rahim, Sarah..." required />
				</div>
				<div class="modal-grid-2">
					<div class="field">
						<label class="label" for="bl-amount">Amount (৳)</label>
						<input class="input" id="bl-amount" type="number" name="amount" placeholder="0" min="0" required />
					</div>
					<div class="field">
						<label class="label" for="bl-date">Date</label>
						<input class="input" id="bl-date" type="date" name="date" required />
					</div>
				</div>
				<div class="field">
					<label class="label" for="bl-note">
						Note <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span>
					</label>
					<input class="input" id="bl-note" type="text" name="note" placeholder="What is it for?" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
				<button class="btn btn-primary btn-sm" type="button">Save Record</button>
			</div>
		</div>
	</div>

	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>