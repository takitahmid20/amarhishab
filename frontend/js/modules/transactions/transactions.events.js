(function setupTransactionsPrototype(globalScope) {
	const seedTransactions = [
		{
			id: "tx-1",
			title: "Grocery Shopping",
			category: "Food & Dining",
			type: "expense",
			amount: 450,
			date: "2026-03-02",
			icon: "shopping-cart",
			iconClass: "tx-icon--pink"
		},
		{
			id: "tx-2",
			title: "Salary Deposit",
			category: "Salary",
			type: "income",
			amount: 5000,
			date: "2026-03-01",
			icon: "dollar-sign",
			iconClass: "tx-icon--mint"
		},
		{
			id: "tx-3",
			title: "Electric Bill",
			category: "Bills & Utilities",
			type: "expense",
			amount: 320,
			date: "2026-02-28",
			icon: "plug",
			iconClass: "tx-icon--cream"
		},
		{
			id: "tx-4",
			title: "Freelance Work",
			category: "Freelance",
			type: "income",
			amount: 1200,
			date: "2026-02-25",
			icon: "briefcase",
			iconClass: "tx-icon--mint-soft"
		}
	];

	const categoryIconMap = {
		"Food & Dining": { icon: "shopping-cart", iconClass: "tx-icon--pink" },
		"Bills & Utilities": { icon: "plug", iconClass: "tx-icon--cream" },
		Salary: { icon: "dollar-sign", iconClass: "tx-icon--mint" },
		Freelance: { icon: "briefcase", iconClass: "tx-icon--mint-soft" }
	};

	const state = {
		transactions: seedTransactions.slice(),
		filters: {
			category: "all",
			type: "all",
			from: "",
			to: ""
		},
		editingId: null
	};

	function formatCurrency(value) {
		const amount = Number(value);
		if (!Number.isFinite(amount)) {
			return "0";
		}
		return Math.abs(amount).toLocaleString("en-US");
	}

	function formatDateLabel(value) {
		const date = new Date(value);
		if (Number.isNaN(date.getTime())) {
			return "--";
		}
		const parts = date.toLocaleDateString("en-GB", {
			day: "numeric",
			month: "short",
			year: "numeric"
		}).split(" ");
		return `${parts[0]} ${parts[1]}, ${parts[2]}`;
	}

	function formatDateRangeLabel(from, to) {
		if (!from && !to) {
			return "Date Range";
		}
		const fromLabel = from ? formatDateLabel(from) : "Any";
		const toLabel = to ? formatDateLabel(to) : "Any";
		return `${fromLabel} - ${toLabel}`;
	}

	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/\"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	function getIconConfig(transaction) {
		const mapped = categoryIconMap[transaction.category];
		if (mapped) {
			return mapped;
		}
		if (transaction.type === "income") {
			return { icon: "trending-up", iconClass: "tx-icon--mint" };
		}
		return { icon: "trending-down", iconClass: "tx-icon--cream" };
	}

	function matchesDateRange(transaction, from, to) {
		if (!from && !to) {
			return true;
		}
		const txDate = new Date(transaction.date);
		if (Number.isNaN(txDate.getTime())) {
			return false;
		}
		if (from) {
			const fromDate = new Date(from);
			if (!Number.isNaN(fromDate.getTime()) && txDate < fromDate) {
				return false;
			}
		}
		if (to) {
			const toDate = new Date(to);
			if (!Number.isNaN(toDate.getTime()) && txDate > toDate) {
				return false;
			}
		}
		return true;
	}

	function applyFilters(transactions) {
		return transactions.filter((transaction) => {
			if (state.filters.category !== "all" && transaction.category !== state.filters.category) {
				return false;
			}
			if (state.filters.type !== "all" && transaction.type !== state.filters.type) {
				return false;
			}
			return matchesDateRange(transaction, state.filters.from, state.filters.to);
		});
	}

	function renderTransaction(transaction) {
		const iconConfig = getIconConfig(transaction);
		const isExpense = transaction.type === "expense";
		const amountClass = isExpense ? "tx-amount--negative" : "tx-amount--positive";
		const amountSign = isExpense ? "-" : "+";
		const amountText = `${amountSign}৳ ${formatCurrency(transaction.amount)}`;
		const title = escapeHtml(transaction.title);
		const category = escapeHtml(transaction.category);
		const dateLabel = escapeHtml(formatDateLabel(transaction.date));

		return `
<article class="tx-item" data-tx-id="${escapeHtml(transaction.id)}">
	<div class="tx-item-left">
		<div class="tx-icon ${iconConfig.iconClass}">
			<i data-lucide="${iconConfig.icon}" aria-hidden="true"></i>
		</div>
		<div class="tx-item-info">
			<h4 class="tx-item-title">${title}</h4>
			<p class="tx-item-meta">${category} · ${dateLabel}</p>
		</div>
	</div>
	<div class="tx-item-right">
		<span class="tx-amount ${amountClass}">${amountText}</span>
		<div class="tx-item-actions">
			<button class="icon-btn" type="button" data-tx-action="edit" title="Edit">
				<i data-lucide="pencil" aria-hidden="true"></i>
			</button>
			<button class="icon-btn icon-btn--danger" type="button" data-tx-action="delete" title="Delete">
				<i data-lucide="trash-2" aria-hidden="true"></i>
			</button>
		</div>
	</div>
</article>`.trim();
	}

	function renderList(elements) {
		const filtered = applyFilters(state.transactions);
		if (filtered.length === 0) {
			elements.list.innerHTML = '<p class="tx-empty">No transactions match current filters.</p>';
		} else {
			elements.list.innerHTML = filtered.map(renderTransaction).join("\n");
		}
		elements.count.textContent = `${filtered.length} records`;

		if (globalScope.lucide && typeof globalScope.lucide.createIcons === "function") {
			globalScope.lucide.createIcons();
		}
	}

	function syncCategoryOptions(elements) {
		const categories = Array.from(
			new Set(state.transactions.map((transaction) => transaction.category).filter(Boolean))
		).sort((a, b) => a.localeCompare(b));

		const current = elements.categoryFilter.value;
		elements.categoryFilter.innerHTML = [
			'<option value="all">All Categories</option>',
			...categories.map((category) => `<option value="${escapeHtml(category)}">${escapeHtml(category)}</option>`)
		].join("\n");

		if (categories.includes(current)) {
			elements.categoryFilter.value = current;
		} else {
			elements.categoryFilter.value = "all";
			state.filters.category = "all";
		}
	}

	function getFormData(form) {
		const data = new FormData(form);
		return {
			title: String(data.get("title") || "").trim(),
			category: String(data.get("category") || "").trim(),
			type: String(data.get("type") || "").trim(),
			amount: Number(data.get("amount")),
			date: String(data.get("date") || "").trim(),
			note: String(data.get("note") || "").trim()
		};
	}

	function setFormValues(form, transaction) {
		form.querySelector('[name="title"]').value = transaction?.title || "";
		form.querySelector('[name="category"]').value = transaction?.category || "";
		form.querySelector('[name="type"]').value = transaction?.type || "";
		form.querySelector('[name="amount"]').value = transaction ? String(transaction.amount) : "";
		form.querySelector('[name="date"]').value = transaction?.date || "";
		form.querySelector('[name="note"]').value = transaction?.note || "";
	}

	function resetForm(form, submitButton, titleNode) {
		form.reset();
		submitButton.textContent = "Save Transaction";
		titleNode.textContent = "Add Transaction";
		state.editingId = null;
	}

	function openModal(selector, triggerElement) {
		if (!globalScope.AmarHishabModal) {
			return;
		}
		globalScope.AmarHishabModal.openModalBySelector(selector, triggerElement);
	}

	function closeModal(selector) {
		if (!globalScope.AmarHishabModal) {
			return;
		}
		globalScope.AmarHishabModal.closeModalBySelector(selector);
	}

	function handleListAction(event, elements) {
		const actionButton = event.target.closest("[data-tx-action]");
		if (!actionButton) {
			return;
		}

		const item = actionButton.closest("[data-tx-id]");
		if (!item) {
			return;
		}

		const transactionId = item.getAttribute("data-tx-id");
		const transaction = state.transactions.find((entry) => entry.id === transactionId);
		if (!transaction) {
			return;
		}

		const action = actionButton.getAttribute("data-tx-action");
		if (action === "delete") {
			state.transactions = state.transactions.filter((entry) => entry.id !== transactionId);
			syncCategoryOptions(elements);
			renderList(elements);
			return;
		}

		if (action === "edit") {
			state.editingId = transactionId;
			setFormValues(elements.form, transaction);
			elements.submitButton.textContent = "Save Changes";
			elements.formTitle.textContent = "Edit Transaction";
			openModal("#tx-form-modal", actionButton);
		}
	}

	function handleFormSubmit(event, elements) {
		event.preventDefault();
		const payload = getFormData(elements.form);
		if (!payload.title || !payload.category || !payload.type || !payload.date || !Number.isFinite(payload.amount)) {
			return;
		}

		if (state.editingId) {
			state.transactions = state.transactions.map((entry) => {
				if (entry.id !== state.editingId) {
					return entry;
				}
				const iconConfig = getIconConfig(payload);
				return {
					...entry,
					...payload,
					icon: iconConfig.icon,
					iconClass: iconConfig.iconClass
				};
			});
		} else {
			const iconConfig = getIconConfig(payload);
			state.transactions.unshift({
				id: `tx-${Date.now()}`,
				...payload,
				icon: iconConfig.icon,
				iconClass: iconConfig.iconClass
			});
		}

		syncCategoryOptions(elements);
		renderList(elements);
		resetForm(elements.form, elements.submitButton, elements.formTitle);
		closeModal("#tx-form-modal");
	}

	function handleFilters(elements) {
		state.filters.category = elements.categoryFilter.value;
		state.filters.type = elements.typeFilter.value;
		renderList(elements);
	}

	function handleDateApply(event, elements) {
		event.preventDefault();
		const data = new FormData(elements.dateForm);
		state.filters.from = String(data.get("from") || "");
		state.filters.to = String(data.get("to") || "");
		elements.dateLabel.textContent = formatDateRangeLabel(state.filters.from, state.filters.to);
		renderList(elements);
		closeModal("#tx-date-modal");
	}

	function handleDateClear(elements) {
		state.filters.from = "";
		state.filters.to = "";
		elements.dateForm.reset();
		elements.dateLabel.textContent = "Date Range";
		renderList(elements);
	}

	function init() {
		const list = document.querySelector("[data-tx-list]");
		if (!list) {
			return;
		}

		const elements = {
			list,
			count: document.querySelector("[data-tx-count]"),
			categoryFilter: document.querySelector("[data-tx-category-filter]"),
			typeFilter: document.querySelector("[data-tx-type-filter]"),
			form: document.querySelector("[data-tx-form]"),
			formTitle: document.querySelector("#tx-form-title"),
			submitButton: document.querySelector("[data-tx-submit]"),
			dateForm: document.querySelector("[data-tx-date-form]"),
			dateLabel: document.querySelector("[data-tx-date-label]"),
			filterTrigger: document.querySelector("[data-tx-filter-trigger]"),
			dateClear: document.querySelector("[data-tx-date-clear]")
		};

		if (!elements.count || !elements.categoryFilter || !elements.typeFilter || !elements.form || !elements.dateForm) {
			return;
		}

		if (globalScope.AmarHishabModal) {
			globalScope.AmarHishabModal.initModalComponent();
		}

		list.addEventListener("click", (event) => handleListAction(event, elements));
		elements.categoryFilter.addEventListener("change", () => handleFilters(elements));
		elements.typeFilter.addEventListener("change", () => handleFilters(elements));
		elements.form.addEventListener("submit", (event) => handleFormSubmit(event, elements));
		elements.dateForm.addEventListener("submit", (event) => handleDateApply(event, elements));
		elements.dateClear.addEventListener("click", () => handleDateClear(elements));

		if (elements.filterTrigger) {
			elements.filterTrigger.addEventListener("click", () => {
				elements.categoryFilter.focus();
			});
		}

		syncCategoryOptions(elements);
		renderList(elements);
	}

	document.addEventListener("DOMContentLoaded", init);
})(window);
