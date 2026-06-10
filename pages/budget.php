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
		<div data-partial="../partials/navbar.html" data-partial-class="dashboard-topbar"></div>

		<div class="dashboard-body">
			<div data-partial="../partials/sidebar.html" data-partial-class="dashboard-sidebar"></div>

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
							<strong data-budget-total>৳ 5,300</strong>
						</article>

						<article class="budget-stat-card surface">
							<div class="budget-stat-head">
								<p>Total Spent</p>
								<i data-lucide="credit-card" aria-hidden="true"></i>
							</div>
							<strong class="budget-stat-danger" data-budget-spent>৳ 4,050</strong>
						</article>

						<article class="budget-stat-card surface">
							<div class="budget-stat-head">
								<p>Remaining</p>
								<i data-lucide="target" aria-hidden="true"></i>
							</div>
							<strong class="budget-stat-success" data-budget-remaining>৳ 1,250</strong>
						</article>
					</section>

					<section class="budget-board surface">
						<header class="budget-board-head">
							<h2>Budget by Category</h2>
							<div class="budget-board-actions">
								<label class="budget-filter-wrap" for="budget-cashbook-filter">
									<select id="budget-cashbook-filter" class="select budget-cashbook-filter" data-budget-cashbook-filter>
										<option value="all">All Cashbooks</option>
										<option value="b4">B4</option>
										<option value="b3">B3</option>
										<option value="b2">B2</option>
										<option value="business-book">Business Book</option>
									</select>
								</label>
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

						<div
							class="budget-category-list"
							data-component="budget-category-list"
							data-source="../data/budget-categories.json"
							data-source-fallback="#budget-categories-data"
						></div>

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
							<form class="modal-body" data-budget-category-form>
								<label class="field">
									<span class="label">Category Name</span>
									<input
										class="input"
										type="text"
										name="categoryName"
										placeholder="Enter category name"
										required
										maxlength="60"
										autofocus
									/>
								</label>

								<label class="field">
									<span class="label">Cashbook</span>
									<select class="select" name="cashbookId" required>
										<option value="" selected disabled>Select a cashbook</option>
										<option value="b4">B4</option>
										<option value="b3">B3</option>
										<option value="b2">B2</option>
										<option value="business-book">Business Book</option>
									</select>
								</label>

								<div class="modal-footer">
									<button class="btn btn-outline btn-sm" type="button" data-modal-close>Cancel</button>
									<button class="btn btn-primary btn-sm" type="submit">Create Category</button>
								</div>
							</form>
						</div>
					</div>

					<script id="budget-categories-data" type="application/json">
						[
							{
								"id": "food-dining",
								"name": "Food & Dining",
								"icon": "utensils-crossed",
								"spent": 1200,
								"limit": 1500,
								"color": "#8257e5",
								"cashbookId": "b4",
								"cashbookName": "B4"
							},
							{
								"id": "transportation",
								"name": "Transportation",
								"icon": "car",
								"spent": 800,
								"limit": 1000,
								"color": "#3b82f6",
								"cashbookId": "b3",
								"cashbookName": "B3"
							},
							{
								"id": "entertainment",
								"name": "Entertainment",
								"icon": "clapperboard",
								"spent": 300,
								"limit": 500,
								"color": "#10b981",
								"cashbookId": "b4",
								"cashbookName": "B4"
							},
							{
								"id": "shopping",
								"name": "Shopping",
								"icon": "shopping-bag",
								"spent": 600,
								"limit": 800,
								"color": "#f59e0b",
								"cashbookId": "business-book",
								"cashbookName": "Business Book"
							},
							{
								"id": "bills-utilities",
								"name": "Bills & Utilities",
								"icon": "receipt-text",
								"spent": 950,
								"limit": 1000,
								"color": "#ef4444",
								"cashbookId": "b2",
								"cashbookName": "B2"
							},
							{
								"id": "healthcare",
								"name": "Healthcare",
								"icon": "heart-pulse",
								"spent": 200,
								"limit": 500,
								"color": "#8b5cf6",
								"cashbookId": "b3",
								"cashbookName": "B3"
							}
						]
					</script>
				</section>
			</main>
		</div>
	</div>
	<script src="../js/components/modal.js"></script>
	<script src="../js/components/budgetProgress.js"></script>
	<script src="../js/app.js"></script>
</body>
</html>
