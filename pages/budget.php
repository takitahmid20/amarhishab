<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_once __DIR__ . '/../includes/budget.php';
require_login();

$userId   = current_user()['id'];
$books    = cashbooks_for_user($userId);
$bookFilter = (int) ($_GET['cashbook'] ?? 0);
$cats     = budget_categories_with_spent($userId, $bookFilter ?: null);

$totalBudget = 0.0;
$totalSpent  = 0.0;
foreach ($cats as $c) {
	$totalBudget += (float) $c['limit_amount'];
	$totalSpent  += (float) $c['spent'];
}
$remaining = $totalBudget - $totalSpent;

$palette = ['#8257e5', '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

$success = flash_get('success');
$error   = flash_get('error');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Budget Management | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/budget.css" />
</head>
<body data-page-title="Budget Management">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main budget-main">
				<section class="container-wide budget-page">
					<header class="budget-hero surface">
						<div class="budget-hero-copy">
							<h1>Budget Management</h1>
							<p>Set and track your spending budgets</p>
						</div>
					</header>

					<section class="budget-summary" aria-label="Budget summary cards">
						<article class="budget-stat-card surface">
							<div class="budget-stat-head">
								<p>Total Budget</p>
								<i data-lucide="wallet" aria-hidden="true"></i>
							</div>
							<strong data-budget-total><?= e(taka($totalBudget)) ?></strong>
						</article>

						<article class="budget-stat-card surface">
							<div class="budget-stat-head">
								<p>Total Spent</p>
								<i data-lucide="credit-card" aria-hidden="true"></i>
							</div>
							<strong class="budget-stat-danger" data-budget-spent><?= e(taka($totalSpent)) ?></strong>
						</article>

						<article class="budget-stat-card surface">
							<div class="budget-stat-head">
								<p>Remaining</p>
								<i data-lucide="target" aria-hidden="true"></i>
							</div>
							<strong class="<?= $remaining < 0 ? 'budget-stat-danger' : 'budget-stat-success' ?>" data-budget-remaining><?= e(taka($remaining)) ?></strong>
						</article>
					</section>

					<section class="budget-board surface">
						<header class="budget-board-head">
							<h2>Budget by Category</h2>
							<div class="budget-board-actions">
								<form class="budget-filter-wrap" method="get" id="budget-filter-form">
									<select id="budget-cashbook-filter" class="select budget-cashbook-filter" name="cashbook" onchange="this.form.submit()">
										<option value="0">All Cashbooks</option>
										<?php foreach ($books as $book): ?>
											<option value="<?= e($book['id']) ?>" <?= $bookFilter === (int) $book['id'] ? 'selected' : '' ?>><?= e($book['name']) ?></option>
										<?php endforeach; ?>
									</select>
								</form>
								<button
									class="btn btn-primary btn-sm"
									type="button"
									data-modal-target="#create-budget-category-modal"
									aria-haspopup="dialog"
									aria-controls="create-budget-category-modal"
								>
									<i data-lucide="plus" aria-hidden="true"></i>
									<span>Add Category</span>
								</button>
							</div>
						</header>

						<?php if ($success !== ''): ?>
							<p class="auth-success" role="status"><?= e($success) ?></p>
						<?php endif; ?>
						<?php if ($error !== ''): ?>
							<p class="auth-error" role="alert"><?= e($error) ?></p>
						<?php endif; ?>

						<div class="budget-category-list" data-component="budget-category-list">
							<?php if (empty($cats)): ?>
								<p class="budget-empty">No budget categories yet. Add one to start tracking limits.</p>
							<?php else: ?>
								<?php foreach ($cats as $i => $cat): ?>
									<?php
										$spent = (float) $cat['spent'];
										$limit = (float) $cat['limit_amount'];
										$usage = $limit > 0 ? (int) round(min(100, max(0, $spent / $limit * 100))) : 0;
										$fill  = 'budget-category-fill';
										if ($usage >= 90)      $fill .= ' budget-category-fill--danger';
										elseif ($usage >= 75)  $fill .= ' budget-category-fill--warn';
										$color = $cat['color'] ?: $palette[$i % count($palette)];
										$icon  = $cat['icon'] ?: 'wallet';
									?>
									<article class="budget-category-item" data-budget-category-id="<?= e($cat['id']) ?>">
										<div class="budget-category-top">
											<div class="budget-category-label">
												<span class="budget-category-icon" aria-hidden="true"><i data-lucide="<?= e($icon) ?>"></i></span>
												<strong><?= e($cat['name']) ?></strong>
											</div>
											<div class="budget-category-meta">
												<p class="budget-category-amount"><?= e(taka($spent)) ?> / <?= e(taka($limit)) ?> (<?= $usage ?>%)</p>
												<div class="budget-category-actions">
													<p class="budget-category-cashbook"><?= e($cat['cashbook_name'] ?? 'No cashbook') ?></p>
													<button class="budget-category-delete" type="button"
														data-modal-target="#edit-budget-category-modal"
														data-cat-id="<?= e($cat['id']) ?>"
														data-cat-name="<?= e($cat['name']) ?>"
														data-cat-limit="<?= e($cat['limit_amount']) ?>"
														data-cat-cashbook="<?= e($cat['cashbook_id'] ?? '') ?>"
														aria-label="Edit <?= e($cat['name']) ?>">
														<i data-lucide="pencil" aria-hidden="true"></i>
													</button>
													<form action="../actions/budget-delete.php" method="post" onsubmit="return confirm('Delete this category?');" style="display:inline">
														<?= csrf_field() ?>
														<input type="hidden" name="id" value="<?= e($cat['id']) ?>">
														<button class="budget-category-delete" type="submit" aria-label="Delete <?= e($cat['name']) ?>">
															<i data-lucide="trash-2" aria-hidden="true"></i>
														</button>
													</form>
												</div>
											</div>
										</div>
										<div class="budget-category-track">
											<span class="<?= $fill ?>" style="width: <?= $usage ?>%; --category-color: <?= e($color) ?>;"></span>
										</div>
									</article>
								<?php endforeach; ?>
							<?php endif; ?>
						</div>

						<button
							class="btn btn-outline btn-sm budget-custom-btn"
							type="button"
							data-modal-target="#create-budget-category-modal"
							aria-haspopup="dialog"
							aria-controls="create-budget-category-modal"
						>
							<span>+ Add Custom Category</span>
						</button>
					</section>

					<div class="overlay modal-overlay" id="create-budget-category-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="create-budget-category-title">
							<div class="modal-head">
								<h2 class="modal-title" id="create-budget-category-title">Create Budget Category</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close create budget category modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body" action="../actions/budget-create.php" method="post">
								<?= csrf_field() ?>
								<label class="field">
									<span class="label">Category Name</span>
									<input class="input" type="text" name="name" placeholder="Enter category name" required maxlength="60" autofocus />
								</label>

								<label class="field">
									<span class="label">Monthly Limit (৳)</span>
									<input class="input" type="number" name="limit" min="0" step="0.01" placeholder="e.g. 1500" required />
								</label>

								<label class="field">
									<span class="label">Cashbook <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<select class="select" name="cashbook_id">
										<option value="">No cashbook</option>
										<?php foreach ($books as $book): ?>
											<option value="<?= e($book['id']) ?>"><?= e($book['name']) ?></option>
										<?php endforeach; ?>
									</select>
								</label>

								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Create Category</button>
								</div>
							</form>
						</div>
					</div>

				</section>
			</main>
		</div>
	</div>
		<div class="overlay modal-overlay" id="edit-budget-category-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
			<div class="modal" role="dialog" aria-modal="true" aria-labelledby="edit-budget-category-title">
				<div class="modal-head">
					<h2 class="modal-title" id="edit-budget-category-title">Edit Budget Category</h2>
					<button class="icon-btn" type="button" data-modal-close aria-label="Close edit budget category modal">
						<i data-lucide="x" aria-hidden="true"></i>
					</button>
				</div>
				<form class="modal-body" action="../actions/budget-update.php" method="post">
					<?= csrf_field() ?>
					<input type="hidden" name="id" data-cat-field="id" value="">
					<label class="field">
						<span class="label">Category Name</span>
						<input class="input" type="text" name="name" data-cat-field="name" required maxlength="60" />
					</label>
					<label class="field">
						<span class="label">Monthly Limit (৳)</span>
						<input class="input" type="number" name="limit" data-cat-field="limit" min="0" step="0.01" required />
					</label>
					<label class="field">
						<span class="label">Cashbook <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
						<select class="select" name="cashbook_id" data-cat-field="cashbook">
							<option value="">No cashbook</option>
							<?php foreach ($books as $book): ?>
								<option value="<?= e($book['id']) ?>"><?= e($book['name']) ?></option>
							<?php endforeach; ?>
						</select>
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
		// Fill the edit modal from the clicked category's data-cat-* attributes.
		document.querySelectorAll('[data-cat-id]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var form = document.querySelector('#edit-budget-category-modal form');
				form.querySelector('[data-cat-field="id"]').value       = btn.getAttribute('data-cat-id');
				form.querySelector('[data-cat-field="name"]').value     = btn.getAttribute('data-cat-name');
				form.querySelector('[data-cat-field="limit"]').value    = btn.getAttribute('data-cat-limit');
				form.querySelector('[data-cat-field="cashbook"]').value = btn.getAttribute('data-cat-cashbook') || '';
			});
		});
	</script>
</body>
</html>
