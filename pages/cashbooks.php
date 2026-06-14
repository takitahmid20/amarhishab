<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../includes/cashbooks.php';
require_login();

$userId = current_user()['id'];
$books  = cashbooks_for_user($userId);
$error   = flash_get('error');
$success = flash_get('success');
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<title>Cashbooks | AmarHishab</title>
	<link rel="stylesheet" href="../styles/main.css" />
	<link rel="stylesheet" href="../styles/pages/dashboard.css" />
	<link rel="stylesheet" href="../styles/pages/cashbooks.css" />
</head>
<body data-page-title="Cashbooks">
	<div class="dashboard-layout">
		<?php include __DIR__ . '/../partials/navbar.php'; ?>

		<div class="dashboard-body">
			<?php include __DIR__ . '/../partials/sidebar.php'; ?>

			<main class="dashboard-main cashbooks-main">
				<section class="container-wide cashbooks-page">
					<header class="cashbooks-hero surface">
						<div class="cashbooks-hero-main">
							<div class="cashbooks-hero-copy">
								<p class="cashbooks-eyebrow">Cashbook Control Center</p>
								<h1>Your Cashbooks</h1>
								<p class="cashbooks-hero-sub">Manage books and track updates from one place.</p>
							</div>
							<div class="cashbooks-hero-stats">
								<div class="cashbooks-stat-pill">
									<i data-lucide="book-copy" aria-hidden="true"></i>
									<div>
										<strong data-cashbooks-active-count><?= count($books) ?> Active Books</strong>
										<span>Total cash tracked</span>
									</div>
								</div>
							</div>
						</div>

						<div class="cashbooks-hero-cta">
							<button
								class="btn btn-primary btn-sm cashbooks-add-btn"
								type="button"
								data-modal-target="#create-cashbook-modal"
								aria-haspopup="dialog"
								aria-controls="create-cashbook-modal"
							>
								<i data-lucide="plus" aria-hidden="true"></i>
								<span>Create Cashbook</span>
							</button>
						</div>
					</header>

					<div class="cashbooks-controls surface">
						<div class="input-wrap cashbooks-search">
							<i class="input-icon" data-lucide="search" aria-hidden="true"></i>
							<input class="input" type="text" placeholder="Search books, owners, tags..." />
						</div>

						<div class="cashbooks-control-actions">
							<label class="cashbooks-sort-wrap">
								<select class="select">
									<option>Last Updated</option>
									<option>Name A-Z</option>
									<option>Recently Created</option>
								</select>
							</label>
							<button class="btn btn-outline btn-sm" type="button">
								<i data-lucide="sliders-horizontal" aria-hidden="true"></i>
								<span>Filters</span>
							</button>
						</div>
					</div>

					<?php if ($success !== ''): ?>
						<p class="auth-success" role="status"><?= e($success) ?></p>
					<?php endif; ?>
					<?php if ($error !== ''): ?>
						<p class="auth-error" role="alert"><?= e($error) ?></p>
					<?php endif; ?>

					<div class="cashbooks-grid">
						<section class="cashbooks-stream" data-component="cashbook-card-list">
							<?php if (empty($books)): ?>
								<p class="cashbooks-empty">No cashbooks yet. Create your first one to get started.</p>
							<?php else: ?>
								<?php foreach ($books as $book): ?>
									<?php $warn = $book['status'] === 'review' ? ' cashbook-health--warn' : ''; ?>
									<article class="cashbook-card cashbook-card--book surface" data-cashbook-id="<?= e($book['id']) ?>">
										<div class="cashbook-card-head">
											<div class="cashbook-main">
												<div class="cashbook-badge"><i data-lucide="book-copy" aria-hidden="true"></i></div>
												<div>
													<h3><?= e($book['name']) ?></h3>
													<p>Created <?= e(time_ago($book['created_at'])) ?> · Balance <?= e(taka($book['balance'])) ?></p>
												</div>
											</div>
											<span class="cashbook-health<?= $warn ?>"><?= $book['status'] === 'review' ? 'Review' : 'Live' ?></span>
										</div>
										<div class="cashbook-card-foot">
											<div class="cashbook-actions">
												<button class="cashbook-icon-btn" type="button"
													data-modal-target="#edit-cashbook-modal"
													data-edit-id="<?= e($book['id']) ?>"
													data-edit-name="<?= e($book['name']) ?>"
													data-edit-description="<?= e($book['description'] ?? '') ?>"
													data-edit-status="<?= e($book['status']) ?>"
													aria-label="Edit <?= e($book['name']) ?>"><i data-lucide="pencil" aria-hidden="true"></i></button>
												<a class="cashbook-icon-btn" href="./cashbook-details.php?id=<?= e($book['id']) ?>" aria-label="Open <?= e($book['name']) ?>"><i data-lucide="external-link" aria-hidden="true"></i></a>
												<form class="cashbook-delete-form" action="../actions/cashbook-delete.php" method="post" onsubmit="return confirm('Delete this cashbook and all its transactions?');">
													<?= csrf_field() ?>
													<input type="hidden" name="id" value="<?= e($book['id']) ?>">
													<button class="cashbook-icon-btn cashbook-icon-btn--danger" type="submit" aria-label="Delete <?= e($book['name']) ?>"><i data-lucide="trash-2" aria-hidden="true"></i></button>
												</form>
											</div>
										</div>
									</article>
								<?php endforeach; ?>
							<?php endif; ?>
						</section>
					</div>

					<div class="overlay modal-overlay" id="create-cashbook-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="create-cashbook-title">
							<div class="modal-head">
								<h2 class="modal-title" id="create-cashbook-title">Create Cashbook</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close create cashbook modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body" action="../actions/cashbook-create.php" method="post">
								<?= csrf_field() ?>
								<label class="field">
									<span class="label">Book Name</span>
									<input class="input" type="text" name="name" placeholder="Enter book name" required maxlength="60" autofocus />
								</label>
								<label class="field">
									<span class="label">Description <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<input class="input" type="text" name="description" placeholder="What is this book for?" maxlength="255" />
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Create</button>
								</div>
							</form>
						</div>
					</div>

				</section>
					<div class="overlay modal-overlay" id="edit-cashbook-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="edit-cashbook-title">
							<div class="modal-head">
								<h2 class="modal-title" id="edit-cashbook-title">Edit Cashbook</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close edit cashbook modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body" action="../actions/cashbook-update.php" method="post">
								<?= csrf_field() ?>
								<input type="hidden" name="id" data-edit-field="id" value="">
								<label class="field">
									<span class="label">Book Name</span>
									<input class="input" type="text" name="name" data-edit-field="name" placeholder="Enter book name" required maxlength="60" />
								</label>
								<label class="field">
									<span class="label">Description <span style="font-weight:400;color:var(--color-text-muted)">(optional)</span></span>
									<input class="input" type="text" name="description" data-edit-field="description" placeholder="What is this book for?" maxlength="255" />
								</label>
								<label class="field">
									<span class="label">Status</span>
									<select class="select" name="status" data-edit-field="status">
										<option value="live">Live</option>
										<option value="review">Review</option>
									</select>
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Save Changes</button>
								</div>
							</form>
						</div>
					</div>

			</main>
		</div>
	</div>
	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
	<script>
		// Fill the edit modal from the clicked card's data-edit-* attributes.
		document.querySelectorAll('[data-edit-id]').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var form = document.querySelector('#edit-cashbook-modal form');
				form.querySelector('[data-edit-field="id"]').value          = btn.getAttribute('data-edit-id');
				form.querySelector('[data-edit-field="name"]').value        = btn.getAttribute('data-edit-name');
				form.querySelector('[data-edit-field="description"]').value = btn.getAttribute('data-edit-description');
				form.querySelector('[data-edit-field="status"]').value      = btn.getAttribute('data-edit-status');
			});
		});
	</script>
</body>
</html>
