<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_login();
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
								<h1>BanglaBusiness Workspace</h1>
								<p class="cashbooks-hero-sub">Manage books and track updates from one branded command area.</p>
							</div>
							<div class="cashbooks-hero-stats">
								<div class="cashbooks-stat-pill">
									<i data-lucide="book-copy" aria-hidden="true"></i>
									<div>
										<strong data-cashbooks-active-count>4 Active Books</strong>
										<span>2 updated today</span>
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

					<div class="cashbooks-grid">
						<section
							class="cashbooks-stream"
							data-component="cashbook-card-list"
							data-source="../data/cashbooks.json"
							data-source-fallback="#cashbooks-data"
						></section>
					</div>

					<div class="overlay modal-overlay" id="create-cashbook-modal" data-modal data-modal-close-overlay hidden aria-hidden="true">
						<div class="modal" role="dialog" aria-modal="true" aria-labelledby="create-cashbook-title">
							<div class="modal-head">
								<h2 class="modal-title" id="create-cashbook-title">Create Cashbook</h2>
								<button class="icon-btn" type="button" data-modal-close aria-label="Close create cashbook modal">
									<i data-lucide="x" aria-hidden="true"></i>
								</button>
							</div>
							<form class="modal-body" data-cashbook-create-form>
								<label class="field">
									<span class="label">Book Name</span>
									<input class="input" type="text" name="bookName" placeholder="Enter book name" required maxlength="60" autofocus />
								</label>
								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Create</button>
								</div>
							</form>
						</div>
					</div>

					<script id="cashbooks-data" type="application/json">
						[
							{
								"id": "b4",
								"name": "B4",
								"createdText": "Created about 1 hour ago",
								"statusLabel": "Live"
							},
							{
								"id": "b3",
								"name": "B3",
								"createdText": "Created about 1 hour ago",
								"statusLabel": "Live"
							},
							{
								"id": "b2",
								"name": "B2",
								"createdText": "Created about 1 hour ago",
								"statusLabel": "Review",
								"statusTone": "warn"
							}
						]
					</script>
				</section>
			</main>
		</div>
	</div>
	<script src="../js/components/cashbookCard.js"></script>
	<script src="../js/components/modal.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>
