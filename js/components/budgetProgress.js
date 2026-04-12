(function setupBudgetProgressComponent(globalScope) {
	const categoryState = new WeakMap();
	const fallbackColors = ["#8257e5", "#3b82f6", "#10b981", "#f59e0b", "#ef4444", "#8b5cf6"];

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/\"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	function toSlug(value) {
		return String(value || "")
			.toLowerCase()
			.trim()
			.replace(/[^a-z0-9]+/g, "-")
			.replace(/^-+|-+$/g, "");
	}

	function clampPercentage(value) {
		const numericValue = Number(value);
		if (!Number.isFinite(numericValue)) {
			return 0;
		}

		return Math.max(0, Math.min(100, Math.round(numericValue)));
	}

	function formatCurrency(value) {
		const amount = Number(value);
		const safeAmount = Number.isFinite(amount) ? amount : 0;
		return `৳ ${safeAmount.toLocaleString("en-US")}`;
	}

	function calculateCategoryUsage(spent, limit) {
		const safeSpent = Number(spent);
		const safeLimit = Number(limit);

		if (!Number.isFinite(safeSpent) || !Number.isFinite(safeLimit) || safeLimit <= 0) {
			return 0;
		}

		return (safeSpent / safeLimit) * 100;
	}

	function readJsonSource(sourceSelector) {
		if (!sourceSelector || !sourceSelector.startsWith("#")) {
			return [];
		}

		try {
			const sourceNode = document.querySelector(sourceSelector);
			if (!sourceNode) {
				return [];
			}

			const parsed = JSON.parse(sourceNode.textContent || "[]");
			return Array.isArray(parsed) ? parsed : [];
		} catch (_error) {
			return [];
		}
	}

	function writeJsonSource(sourceSelector, value) {
		if (!sourceSelector || !sourceSelector.startsWith("#") || !Array.isArray(value)) {
			return;
		}

		const sourceNode = document.querySelector(sourceSelector);
		if (!sourceNode) {
			return;
		}

		sourceNode.textContent = JSON.stringify(value, null, 2);
	}

	async function readJsonFile(filePath) {
		if (!filePath || filePath.startsWith("#")) {
			return {
				didLoad: false,
				categories: []
			};
		}

		try {
			const response = await fetch(filePath, { cache: "no-store" });
			if (!response.ok) {
				throw new Error(`Failed to load data source: ${filePath}`);
			}

			const parsed = await response.json();
			return {
				didLoad: true,
				categories: Array.isArray(parsed) ? parsed : []
			};
		} catch (_error) {
			return {
				didLoad: false,
				categories: []
			};
		}
	}

	async function resolveBudgetCategories(container) {
		if (!(container instanceof HTMLElement)) {
			return [];
		}

		const sourcePath = (container.getAttribute("data-source") || "").trim();
		const fallbackSelector = (container.getAttribute("data-source-fallback") || "").trim();

		if (sourcePath.startsWith("#")) {
			return readJsonSource(sourcePath);
		}

		const sourceResult = await readJsonFile(sourcePath);
		if (sourceResult.didLoad) {
			if (fallbackSelector.startsWith("#")) {
				writeJsonSource(fallbackSelector, sourceResult.categories);
			}

			return sourceResult.categories;
		}

		return readJsonSource(fallbackSelector);
	}

	function updateBudgetSummary(categories) {
		const summary = categories.reduce(
			(accumulator, category) => {
				const spent = Number(category.spent) || 0;
				const limit = Number(category.limit) || 0;

				accumulator.total += limit;
				accumulator.spent += spent;
				return accumulator;
			},
			{ total: 0, spent: 0 }
		);

		summary.remaining = Math.max(summary.total - summary.spent, 0);

		const totalNode = document.querySelector("[data-budget-total]");
		const spentNode = document.querySelector("[data-budget-spent]");
		const remainingNode = document.querySelector("[data-budget-remaining]");

		if (totalNode) {
			totalNode.textContent = formatCurrency(summary.total);
		}

		if (spentNode) {
			spentNode.textContent = formatCurrency(summary.spent);
		}

		if (remainingNode) {
			remainingNode.textContent = formatCurrency(summary.remaining);
		}
	}

	function renderBudgetCategory(category, index) {
		const name = escapeHtml(category.name || "Untitled Category");
		const icon = escapeHtml(category.icon || "💼");
		const cashbookName = escapeHtml(category.cashbookName || "Unassigned");
		const spent = Number(category.spent) || 0;
		const limit = Number(category.limit) || 0;
		const usage = clampPercentage(calculateCategoryUsage(spent, limit));
		const color = escapeHtml(category.color || fallbackColors[index % fallbackColors.length]);

		let usageClassName = "budget-category-fill";
		if (usage >= 90) {
			usageClassName += " budget-category-fill--danger";
		} else if (usage >= 75) {
			usageClassName += " budget-category-fill--warn";
		}

		return `
<article class="budget-category-item" data-budget-category-id="${escapeHtml(category.id || "")}">
	<div class="budget-category-top">
		<div class="budget-category-label">
			<span class="budget-category-emoji" aria-hidden="true">${icon}</span>
			<strong>${name}</strong>
		</div>
		<div class="budget-category-meta">
			<p class="budget-category-amount">${formatCurrency(spent)} / ${formatCurrency(limit)} (${usage}%)</p>
			<p class="budget-category-cashbook">${cashbookName}</p>
		</div>
	</div>
	<div class="budget-category-track">
		<span class="${usageClassName}" style="width: ${usage}%; --category-color: ${color};"></span>
	</div>
</article>`.trim();
	}

	function renderBudgetCategoryList(container, categories) {
		if (!(container instanceof HTMLElement) || !Array.isArray(categories)) {
			return;
		}

		if (categories.length === 0) {
			container.innerHTML = '<p class="budget-empty">No categories available yet.</p>';
			updateBudgetSummary([]);
			return;
		}

		container.innerHTML = categories.map((category, index) => renderBudgetCategory(category, index)).join("\n");
		updateBudgetSummary(categories);
	}

	async function initBudgetCategories() {
		const containers = Array.from(document.querySelectorAll('[data-component="budget-category-list"]'));
		if (containers.length === 0) {
			return;
		}

		for (const container of containers) {
			const categories = await resolveBudgetCategories(container);
			categoryState.set(container, categories);
			renderBudgetCategoryList(container, categories);
		}
	}

	function createCategoryPayload(formData, colorIndex) {
		const categoryName = String(formData.get("categoryName") || "").trim();
		const cashbookId = String(formData.get("cashbookId") || "").trim();

		if (!categoryName || !cashbookId) {
			return null;
		}

		const cashbookSelect = document.querySelector('[data-budget-category-form] select[name="cashbookId"]');
		const cashbookName = cashbookSelect instanceof HTMLSelectElement
			? (cashbookSelect.selectedOptions[0]?.textContent || cashbookId)
			: cashbookId;

		return {
			id: `${toSlug(categoryName) || "category"}-${Date.now()}`,
			name: categoryName,
			icon: "📝",
			spent: 0,
			limit: 1000,
			cashbookId,
			cashbookName,
			color: fallbackColors[colorIndex % fallbackColors.length]
		};
	}

	function initBudgetCreateFlow() {
		const form = document.querySelector("[data-budget-category-form]");
		const container = document.querySelector('[data-component="budget-category-list"]');

		if (!(form instanceof HTMLFormElement) || !(container instanceof HTMLElement)) {
			return;
		}

		form.addEventListener("submit", (event) => {
			event.preventDefault();

			const formData = new FormData(form);
			const currentCategories = categoryState.get(container) || [];
			const nextCategory = createCategoryPayload(formData, currentCategories.length);

			if (!nextCategory) {
				const invalidInput = form.querySelector('input[name="categoryName"], select[name="cashbookId"]');
				if (invalidInput instanceof HTMLElement) {
					invalidInput.focus();
				}
				return;
			}

			const nextCategories = [nextCategory, ...currentCategories];
			categoryState.set(container, nextCategories);
			renderBudgetCategoryList(container, nextCategories);

			const fallbackSelector = (container.getAttribute("data-source-fallback") || "").trim();
			if (fallbackSelector.startsWith("#")) {
				writeJsonSource(fallbackSelector, nextCategories);
			}

			form.reset();

			if (globalScope.AmarHishabModal && typeof globalScope.AmarHishabModal.closeModalBySelector === "function") {
				globalScope.AmarHishabModal.closeModalBySelector("#create-budget-category-modal");
			}
		});
	}

	function initBudgetProgressComponent() {
		void initBudgetCategories();
		initBudgetCreateFlow();
	}

	globalScope.AmarHishabBudgetProgress = {
		renderBudgetCategoryList
	};

	document.addEventListener("DOMContentLoaded", () => {
		initBudgetProgressComponent();
	});
})(window);
