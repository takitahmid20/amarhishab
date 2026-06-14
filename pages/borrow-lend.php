<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/borrow_lend.php';
require_login();

$userId = current_user()['id'];
$allowed = ['all', 'borrow', 'lend', 'pending'];
$filterParam = $_GET['filter'] ?? 'all';
$filter  = in_array($filterParam, $allowed, true) ? $filterParam : 'all';
$records = borrow_lend_records($userId, $filter);
$totals  = borrow_lend_totals($userId);

$success = flash_get('success');
$error   = flash_get('error');
?>
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
								<h2 class="bl-stat-amount bl-stat-amount--borrowed"><?= e(taka($totals['borrowed'])) ?></h2>
								<p class="bl-stat-sub">Money you owe (unsettled)</p>
							</div>
						</div>
						<div class="bl-stat-card surface">
							<div class="bl-stat-icon bl-stat-icon--lent">
								<i data-lucide="arrow-down" aria-hidden="true"></i>
							</div>
							<div class="bl-stat-body">
								<p class="bl-stat-label">Total Lent</p>
								<h2 class="bl-stat-amount bl-stat-amount--lent"><?= e(taka($totals['lent'])) ?></h2>
								<p class="bl-stat-sub">Money owed to you (unsettled)</p>
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
							<a class="bl-tab <?= $filter === 'all' ? 'active' : '' ?>" href="?filter=all">
								All <span class="bl-tab-count"><?= (int) $totals['borrow_count'] + (int) $totals['lend_count'] ?></span>
							</a>
							<a class="bl-tab <?= $filter === 'borrow' ? 'active' : '' ?>" href="?filter=borrow">
								Borrowed <span class="bl-tab-count"><?= (int) $totals['borrow_count'] ?></span>
							</a>
							<a class="bl-tab <?= $filter === 'lend' ? 'active' : '' ?>" href="?filter=lend">
								Lent <span class="bl-tab-count"><?= (int) $totals['lend_count'] ?></span>
							</a>
							<a class="bl-tab <?= $filter === 'pending' ? 'active' : '' ?>" href="?filter=pending">
								Pending <span class="bl-tab-count"><?= (int) $totals['pending_count'] ?></span>
							</a>
						</div>

						<?php if ($success !== ''): ?>
							<p class="auth-success" role="status"><?= e($success) ?></p>
						<?php endif; ?>
						<?php if ($error !== ''): ?>
							<p class="auth-error" role="alert"><?= e($error) ?></p>
						<?php endif; ?>

						<div class="bl-list">
							<?php if (empty($records)): ?>
								<p class="bl-empty">No records here yet. Add one to start tracking.</p>
							<?php else: ?>
								<?php foreach ($records as $rec): ?>
									<?php
										$isBorrow = $rec['type'] === 'borrow';
										$settled  = (int) $rec['is_settled'] === 1;
										$initial  = strtoupper(mb_substr($rec['person'], 0, 1));
									?>
									<div class="bl-list-item" data-type="<?= $isBorrow ? 'borrowed' : 'lent' ?>" data-status="<?= $settled ? 'paid' : 'pending' ?>">
										<div class="bl-item-left">
											<div class="bl-avatar"><?= e($initial) ?></div>
											<div class="bl-item-info">
												<h4 class="bl-item-name"><?= e($rec['person']) ?></h4>
												<div class="bl-item-meta">
													<span class="bl-item-type bl-item-type--<?= $isBorrow ? 'borrowed' : 'lent' ?>"><?= $isBorrow ? 'Borrowed' : 'Lent' ?></span>
													<span class="bl-meta-dot">·</span>
													<span class="bl-item-date"><?= $rec['due_date'] ? e(date('M j, Y', strtotime($rec['due_date']))) : 'No due date' ?></span>
													<span class="bl-meta-dot">·</span>
													<span class="badge <?= $settled ? 'badge-success' : 'badge-warning' ?>"><?= $settled ? 'Settled' : 'Pending' ?></span>
													<?php if ($rec['note']): ?>
														<span class="bl-meta-dot">·</span>
														<span class="bl-item-note"><?= e($rec['note']) ?></span>
													<?php endif; ?>
												</div>
											</div>
										</div>
										<div class="bl-item-right">
											<span class="bl-item-amount bl-item-amount--<?= $isBorrow ? 'borrowed' : 'lent' ?>"><?= e(taka($rec['amount'])) ?></span>
											<div class="bl-item-actions">
												<?php if (!$settled): ?>
													<form action="../actions/borrow-lend-settle.php" method="post" style="display:inline">
														<?= csrf_field() ?>
														<input type="hidden" name="id" value="<?= e($rec['id']) ?>">
														<button class="icon-btn" type="submit" title="Mark as settled" aria-label="Mark <?= e($rec['person']) ?> settled">
															<i data-lucide="check" aria-hidden="true"></i>
														</button>
													</form>
												<?php endif; ?>
												<form action="../actions/borrow-lend-delete.php" method="post" onsubmit="return confirm('Delete this record?');" style="display:inline">
													<?= csrf_field() ?>
													<input type="hidden" name="id" value="<?= e($rec['id']) ?>">
													<button class="icon-btn icon-btn--danger" type="submit" aria-label="Delete <?= e($rec['person']) ?>'s record">
														<i data-lucide="trash-2" aria-hidden="true"></i>
													</button>
												</form>
											</div>
										</div>
									</div>
								<?php endforeach; ?>
							<?php endif; ?>
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
			<form class="modal-body" action="../actions/borrow-lend-create.php" method="post">
				<?= csrf_field() ?>
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
						<input class="input" id="bl-amount" type="number" name="amount" placeholder="0" min="0" step="0.01" required />
					</div>
					<div class="field">
						<label class="label" for="bl-date">Due Date <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></label>
						<input class="input" id="bl-date" type="date" name="date" />
					</div>
				</div>
				<div class="field">
					<label class="label" for="bl-note">
						Note <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span>
					</label>
					<input class="input" id="bl-note" type="text" name="note" placeholder="What is it for?" maxlength="255" />
				</div>
				<div class="modal-footer">
					<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
					<button class="btn btn-primary btn-sm" type="submit">Save Record</button>
				</div>
			</form>
		</div>
	</div>

	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>