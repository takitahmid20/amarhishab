(function () {
	if (localStorage.getItem("dark_mode") === "true") {
		document.documentElement.setAttribute("data-theme", "dark");
	} else {
		document.documentElement.removeAttribute("data-theme");
	}
})();

function normalizePath(pathname) {
	if (!pathname) {
		return "/";
	}

	if (pathname.length > 1 && pathname.endsWith("/")) {
		return pathname.slice(0, -1);
	}

	return pathname;
}


function syncNavbarTitle() {
	const pageTitle = document.body.getAttribute("data-page-title");
	if (!pageTitle) {
		return;
	}

	const navbarTitle = document.querySelector(".navbar-title");
	if (navbarTitle) {
		navbarTitle.textContent = pageTitle;
	}

	document.title = `${pageTitle} | AmarHishab`;
}

function syncActiveSidebarLink() {
	const links = Array.from(document.querySelectorAll(".sidebar .nav a[href]"));
	if (links.length === 0) {
		return;
	}

	const currentPath = normalizePath(window.location.pathname);

	for (const link of links) {
		const linkUrl = new URL(link.getAttribute("href"), window.location.href);
		const linkPath = normalizePath(linkUrl.pathname);
		link.classList.toggle("active", linkPath === currentPath);
	}
}

const cashbookDataState = new WeakMap();

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

async function readJsonFile(filePath) {
	if (!filePath || filePath.startsWith("#")) {
		return {
			didLoad: false,
			books: []
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
			books: Array.isArray(parsed) ? parsed : []
		};
	} catch (_error) {
		return {
			didLoad: false,
			books: []
		};
	}
}

function writeJsonSource(sourceSelector, value) {
	if (!sourceSelector || !Array.isArray(value)) {
		return;
	}

	const sourceNode = document.querySelector(sourceSelector);
	if (!sourceNode) {
		return;
	}

	sourceNode.textContent = JSON.stringify(value, null, 2);
}

function toSlug(value) {
	return value
		.toLowerCase()
		.trim()
		.replace(/[^a-z0-9]+/g, "-")
		.replace(/^-+|-+$/g, "");
}

function escapeHtml(value) {
	return String(value)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/\"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

function formatNumber(value) {
	const amount = Number(value);
	if (!Number.isFinite(amount)) {
		return "0";
	}

	return amount.toLocaleString("en-US");
}

function setCashbookCount(count) {
	const countNode = document.querySelector("[data-cashbooks-active-count]");
	if (!countNode) {
		return;
	}

	countNode.textContent = `${count} Active Books`;
}

async function resolveCashbookBooks(container) {
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
			writeJsonSource(fallbackSelector, sourceResult.books);
		}

		return sourceResult.books;
	}

	return readJsonSource(fallbackSelector);
}

function renderCashbookEntryRows(entryListNode, entries) {
	if (!(entryListNode instanceof HTMLElement)) {
		return;
	}

	if (!Array.isArray(entries) || entries.length === 0) {
		entryListNode.innerHTML = `
			<tr>
				<td colspan="8" style="text-align:center;color:var(--color-text-muted);">No entries found.</td>
			</tr>
		`.trim();
		return;
	}

	entryListNode.innerHTML = entries
		.map((entry) => {
			const direction = entry.direction === "out" ? "out" : "in";
			const amountClass = direction === "out" ? "cashbook-entry-amount--out" : "cashbook-entry-amount--in";

			return `
				<tr>
					<td class="cell-check"><input type="checkbox" aria-label="Select entry" /></td>
					<td>
						<div class="cashbook-entry-date">${escapeHtml(entry.dateLabel || "--")}</div>
						<div class="cashbook-entry-time">${escapeHtml(entry.time || "--")}</div>
					</td>
					<td>${escapeHtml(entry.details || "--")}</td>
					<td>${escapeHtml(entry.category || "--")}</td>
					<td>${escapeHtml(entry.mode || "--")}</td>
					<td>${escapeHtml(entry.bill || "--")}</td>
					<td class="cashbook-entry-amount ${amountClass}">${formatNumber(entry.amount)}</td>
					<td class="cashbook-entry-balance">${formatNumber(entry.balance)}</td>
				</tr>
			`.trim();
		})
		.join("\n");
}

function parseEntryDate(entry) {
	const label = String(entry.dateLabel || "").trim();
	if (!label) {
		return null;
	}

	if (/^today$/i.test(label)) {
		return new Date();
	}

	const parsed = Date.parse(label);
	return Number.isNaN(parsed) ? null : new Date(parsed);
}

function isEntryInDuration(entry, durationKey) {
	const entryDate = parseEntryDate(entry);
	if (!entryDate) {
		return durationKey === "all";
	}

	const now = new Date();
	const entryTime = entryDate.getTime();
	const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime();
	const dayMs = 24 * 60 * 60 * 1000;

	switch (durationKey) {
		case "today":
			return entryTime >= startOfToday && entryTime < startOfToday + dayMs;
		case "7":
			return entryTime >= startOfToday - 6 * dayMs && entryTime < startOfToday + dayMs;
		case "30":
			return entryTime >= startOfToday - 29 * dayMs && entryTime < startOfToday + dayMs;
		case "month":
			return entryDate.getFullYear() === now.getFullYear() && entryDate.getMonth() === now.getMonth();
		default:
			return true;
	}
}

function getUniqueValues(entries, key) {
	return Array.from(
		new Set(
			entries
				.map((entry) => String(entry[key] || "").trim())
				.filter((value) => value && value !== "--")
		)
	).sort((a, b) => a.localeCompare(b));
}

function populateSelect(select, options) {
	if (!(select instanceof HTMLSelectElement)) {
		return;
	}

	select.innerHTML = options
		.map((option) => `<option value="${escapeHtml(option.value)}">${escapeHtml(option.label)}</option>`)
		.join("\n");
}

function filterCashbookEntries(entries, filters) {
	return entries.filter((entry) => {
		if (filters.duration && filters.duration !== "all" && !isEntryInDuration(entry, filters.duration)) {
			return false;
		}

		if (filters.entryType && filters.entryType !== "all") {
			const direction = String(entry.direction || "").toLowerCase();
			if (filters.entryType === "in" && direction !== "in") {
				return false;
			}
			if (filters.entryType === "out" && direction !== "out") {
				return false;
			}
		}

		if (filters.category && filters.category !== "all") {
			if (String(entry.category || "").toLowerCase() !== filters.category.toLowerCase()) {
				return false;
			}
		}

		if (filters.payment && filters.payment !== "all") {
			if (String(entry.mode || "").toLowerCase() !== filters.payment.toLowerCase()) {
				return false;
			}
		}

		return true;
	});
}

async function initCashbookDetailsPage() {
	const pageNode = document.querySelector("[data-cashbook-details-page]");
	if (!(pageNode instanceof HTMLElement)) {
		return;
	}

	const params = new URLSearchParams(window.location.search);
	const selectedCashbookId = params.get("cashbookId") || "";
	const selectedCashbookName = params.get("cashbookName") || "";

	const sourcePath = (pageNode.getAttribute("data-source") || "").trim();
	const fallbackSelector = (pageNode.getAttribute("data-source-fallback") || "").trim();

	const sourceResult = sourcePath.startsWith("#") ? { didLoad: true, books: readJsonSource(sourcePath) } : await readJsonFile(sourcePath);
	const records = sourceResult.didLoad ? sourceResult.books : readJsonSource(fallbackSelector);

	if (sourceResult.didLoad && fallbackSelector.startsWith("#")) {
		writeJsonSource(fallbackSelector, records);
	}

	if (!Array.isArray(records) || records.length === 0) {
		return;
	}

	function hasRealEntryValues(record) {
		return Array.isArray(record.entries) && record.entries.some((entry) => {
			const category = String(entry.category || "").trim();
			const mode = String(entry.mode || "").trim();
			return category && category !== "--" && mode && mode !== "--";
		});
	}

	const matchedRecord =
		records.find((record) => String(record.cashbookId || "") === selectedCashbookId) ||
		records.find((record) => String(record.cashbookName || "") === selectedCashbookName) ||
		records.find(hasRealEntryValues) ||
		records[0];

	if (!matchedRecord) {
		return;
	}

	const cashbookName = selectedCashbookName || matchedRecord.cashbookName || "Cashbook";
	for (const node of document.querySelectorAll("[data-cashbook-name]")) {
		node.textContent = cashbookName;
	}

	const summary = matchedRecord.summary || {};
	const cashInNode = document.querySelector("[data-summary-cash-in]");
	if (cashInNode) {
		cashInNode.textContent = formatNumber(summary.cashIn);
	}

	const cashOutNode = document.querySelector("[data-summary-cash-out]");
	if (cashOutNode) {
		cashOutNode.textContent = formatNumber(summary.cashOut);
	}

	const netBalanceNode = document.querySelector("[data-summary-net-balance]");
	if (netBalanceNode) {
		netBalanceNode.textContent = formatNumber(summary.netBalance);
	}

	const entries = Array.isArray(matchedRecord.entries) ? matchedRecord.entries : [];
	const allEntries = records.flatMap((record) => (Array.isArray(record.entries) ? record.entries : []));
	const filterRow = document.querySelector(".cashbook-filter-row");
	const selects = filterRow instanceof HTMLElement ? Array.from(filterRow.querySelectorAll("select")) : [];
	const [durationSelect, entryTypeSelect, categorySelect, paymentSelect] = selects;

	const durationOptions = [
		{ value: "all", label: "All Time" },
		{ value: "today", label: "Today" },
		{ value: "7", label: "Last 7 Days" },
		{ value: "30", label: "Last 30 Days" },
		{ value: "month", label: "This Month" }
	];

	const entryTypeOptions = [
		{ value: "all", label: "All" },
		{ value: "in", label: "Cash In" },
		{ value: "out", label: "Cash Out" }
	];

	const categoryValues = getUniqueValues(entries, "category").length > 0
		? getUniqueValues(entries, "category")
		: getUniqueValues(allEntries, "category");
	const categoryOptions = [
		{ value: "all", label: "All" },
		...categoryValues.map((value) => ({ value, label: value }))
	];

	const paymentValues = getUniqueValues(entries, "mode").length > 0
		? getUniqueValues(entries, "mode")
		: getUniqueValues(allEntries, "mode");
	const paymentOptions = [
		{ value: "all", label: "All" },
		...paymentValues.map((value) => ({ value, label: value }))
	];

	populateSelect(durationSelect, durationOptions);
	populateSelect(entryTypeSelect, entryTypeOptions);
	populateSelect(categorySelect, categoryOptions);
	populateSelect(paymentSelect, paymentOptions);

	const countLabelNode = document.querySelector("[data-entry-count-label]");
	const entryListNode = document.querySelector("[data-entry-list]");

	function updateEntries() {
		const filters = {
			duration: durationSelect?.value || "all",
			entryType: entryTypeSelect?.value || "all",
			category: categorySelect?.value || "all",
			payment: paymentSelect?.value || "all"
		};

		const filteredEntries = filterCashbookEntries(entries, filters);
		renderCashbookEntryRows(entryListNode, filteredEntries);

		if (countLabelNode) {
			countLabelNode.textContent = filteredEntries.length > 0 ? `Showing 1 - ${filteredEntries.length} of ${filteredEntries.length} entries` : "Showing 0 entries";
		}
	}

	for (const select of [durationSelect, entryTypeSelect, categorySelect, paymentSelect]) {
		if (select instanceof HTMLSelectElement) {
			select.addEventListener("change", updateEntries);
		}
	}

	updateEntries();
}

async function initCashbookCardComponents() {
	if (!window.AmarHishabCashbookCard || typeof window.AmarHishabCashbookCard.renderCashbookCardList !== "function") {
		return;
	}

	const containers = Array.from(document.querySelectorAll('[data-component="cashbook-card-list"]'));
	if (containers.length === 0) {
		return;
	}

	for (const container of containers) {
		const books = await resolveCashbookBooks(container);
		cashbookDataState.set(container, books);
		window.AmarHishabCashbookCard.renderCashbookCardList(container, books);
		setCashbookCount(books.length);
	}
}

function initModalComponents() {
	if (!window.AmarHishabModal || typeof window.AmarHishabModal.initModalComponent !== "function") {
		return;
	}

	window.AmarHishabModal.initModalComponent();
}

function initModalFromHash() {
	const hash = window.location.hash;
	if (!hash) {
		return;
	}

	const modal = document.querySelector(hash);
	if (!(modal instanceof HTMLElement) || !modal.hasAttribute("data-modal")) {
		return;
	}

	if (window.AmarHishabModal && typeof window.AmarHishabModal.openModalBySelector === "function") {
		window.AmarHishabModal.openModalBySelector(hash);
	}
}

function initCashbookCreateFlow() {
	const form = document.querySelector("[data-cashbook-create-form]");
	const container = document.querySelector('[data-component="cashbook-card-list"]');

	if (
		!(form instanceof HTMLFormElement) ||
		!(container instanceof HTMLElement) ||
		!window.AmarHishabCashbookCard ||
		typeof window.AmarHishabCashbookCard.renderCashbookCardList !== "function"
	) {
		return;
	}

	form.addEventListener("submit", (event) => {
		event.preventDefault();

		const formData = new FormData(form);
		const bookName = String(formData.get("bookName") || "").trim();
		if (!bookName) {
			const input = form.querySelector('input[name="bookName"]');
			if (input instanceof HTMLElement) {
				input.focus();
			}
			return;
		}

		const sourceSelector = container.getAttribute("data-source") || "";
		const currentBooks = cashbookDataState.get(container) || [];
		const nextBooks = [
			{
				id: `${toSlug(bookName) || "cashbook"}-${Date.now()}`,
				name: bookName,
				createdText: "Created just now",
				statusLabel: "Live"
			},
			...currentBooks
		];

		cashbookDataState.set(container, nextBooks);
		if (sourceSelector.startsWith("#")) {
			writeJsonSource(sourceSelector, nextBooks);
		}
		window.AmarHishabCashbookCard.renderCashbookCardList(container, nextBooks);

		setCashbookCount(nextBooks.length);

		form.reset();

		if (window.AmarHishabModal && typeof window.AmarHishabModal.closeModalBySelector === "function") {
			window.AmarHishabModal.closeModalBySelector("#create-cashbook-modal");
		}

		void ensureLucideIcons();
	});
}

async function ensureLucideIcons() {
	if (!document.querySelector("[data-lucide]")) {
		return;
	}

	if (window.lucide && typeof window.lucide.createIcons === "function") {
		window.lucide.createIcons();
		return;
	}

	await new Promise((resolve, reject) => {
		const existing = document.querySelector("script[data-lucide-cdn='true']");
		if (existing) {
			existing.addEventListener("load", resolve, { once: true });
			existing.addEventListener("error", reject, { once: true });
			return;
		}

		const script = document.createElement("script");
		script.src = "https://unpkg.com/lucide@latest";
		script.async = true;
		script.setAttribute("data-lucide-cdn", "true");
		script.addEventListener("load", resolve, { once: true });
		script.addEventListener("error", reject, { once: true });
		document.head.appendChild(script);
	});

	if (window.lucide && typeof window.lucide.createIcons === "function") {
		window.lucide.createIcons();
	}
}

async function initAppShell() {
	try {
		initNavbarNotifications();
		syncNavbarTitle();
		syncActiveSidebarLink();
		await initCashbookCardComponents();
		await initCashbookDetailsPage();
		initModalComponents();
		initModalFromHash();
		initCashbookCreateFlow();
		await ensureLucideIcons();
		const user = JSON.parse(localStorage.getItem('user') || '{}');
		if (user && user.name) {
			document.querySelectorAll('[data-user-name]').forEach(el => el.textContent = user.name);
			document.querySelectorAll('[data-user-avatar]').forEach(el => el.textContent = user.name.charAt(0).toUpperCase());
		}
	} catch (error) {
		console.error(error);
	}
}

function initNavbarNotifications() {
	const toggles = Array.from(document.querySelectorAll("[data-notification-toggle]"));
	const panels = Array.from(document.querySelectorAll("[data-notification-panel]"));

	if (toggles.length === 0 || panels.length === 0) {
		return;
	}

	const closeAll = () => {
		panels.forEach((panel) => {
			panel.hidden = true;
		});
		toggles.forEach((toggle) => {
			toggle.setAttribute("aria-expanded", "false");
		});
	};

	const openPanel = (toggle, panel) => {
		panel.hidden = false;
		toggle.setAttribute("aria-expanded", "true");
	};

	toggles.forEach((toggle) => {
		const wrap = toggle.closest(".navbar-notification-wrap");
		const panel = wrap ? wrap.querySelector("[data-notification-panel]") : null;
		if (!(panel instanceof HTMLElement)) {
			return;
		}

		toggle.addEventListener("click", (event) => {
			event.stopPropagation();
			const isOpen = !panel.hidden;
			closeAll();
			if (!isOpen) {
				openPanel(toggle, panel);
			}
		});
	});

	document.addEventListener("click", (event) => {
		const isInside = event.target.closest(".navbar-notification-wrap");
		if (!isInside) {
			closeAll();
		}
	});

	document.addEventListener("keydown", (event) => {
		if (event.key === "Escape") {
			closeAll();
		}
	});
}

document.addEventListener("DOMContentLoaded", () => {
	void initAppShell();
});


// Show logged in user name

