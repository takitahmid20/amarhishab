const INLINE_PARTIALS = {
	"navbar.html": `
<header class="navbar app-topbar">
	<div class="navbar-left">
		<div class="navbar-meta">
			<h3 class="navbar-title">Dashboard</h3>
		</div>
	</div>
	<div class="navbar-right">
		<div class="navbar-search search-wrap">
			<span class="search-icon">Q</span>
			<input class="input" type="text" value="Search" />
		</div>
		<button class="navbar-user" aria-label="Open profile menu">
			<span class="navbar-user-avatar">TT</span>
			<span class="navbar-user-meta">
				<span class="navbar-user-name">Taki Tahmid</span>
			</span>
		</button>
	</div>
</header>
`.trim(),
	"sidebar.html": `
<aside class="sidebar app-sidebar">
	<div class="brand">
		<img class="brand-logo" src="../assets/logos/amarhishab-logo.png" alt="AmarHishab" />
	</div>

	<div class="sidebar-section">
		<div class="nav-group-title">Main</div>
		<nav class="nav">
			<a class="active" href="./dashboard.html"><i class="nav-icon" data-lucide="home" aria-hidden="true"></i><span>Dashboard</span></a>
			<a href="./transactions.html"><i class="nav-icon" data-lucide="arrow-left-right" aria-hidden="true"></i><span>Transactions</span></a>
			<a href="./budget.html"><i class="nav-icon" data-lucide="wallet" aria-hidden="true"></i><span>Budget</span></a>
			<a href="./reports.html"><i class="nav-icon" data-lucide="bar-chart-3" aria-hidden="true"></i><span>Reports</span></a>
		</nav>
	</div>

	<div class="sidebar-section">
		<div class="nav-group-title">Manage</div>
		<nav class="nav">
			<a href="./cashbooks.html"><i class="nav-icon" data-lucide="book-open" aria-hidden="true"></i><span>Cashbooks</span></a>
			<a href="./borrow-lend.html"><i class="nav-icon" data-lucide="handshake" aria-hidden="true"></i><span>Borrow / Lend</span></a>
			<a href="./reminders.html"><i class="nav-icon" data-lucide="bell" aria-hidden="true"></i><span>Reminders</span></a>
			<a href="./settings.html"><i class="nav-icon" data-lucide="settings" aria-hidden="true"></i><span>Settings</span></a>
		</nav>
	</div>
</aside>
`.trim()
};

function normalizePath(pathname) {
	if (!pathname) {
		return "/";
	}

	if (pathname.length > 1 && pathname.endsWith("/")) {
		return pathname.slice(0, -1);
	}

	return pathname;
}

function getInlinePartial(partialPath) {
	if (!partialPath) {
		return "";
	}

	const fileName = partialPath.split("/").pop() || "";
	return INLINE_PARTIALS[fileName] || "";
}

async function getPartialMarkup(partialPath) {
	try {
		const response = await fetch(partialPath);
		if (!response.ok) {
			throw new Error(`Failed to load partial: ${partialPath}`);
		}

		return (await response.text()).trim();
	} catch (_error) {
		return getInlinePartial(partialPath);
	}
}

async function loadPartial(placeholder) {
	const partialPath = placeholder.getAttribute("data-partial");

	if (!partialPath) {
		return;
	}

	const markup = await getPartialMarkup(partialPath);
	if (!markup) {
		return;
	}

	const template = document.createElement("template");
	template.innerHTML = markup;

	const node = template.content.firstElementChild;
	if (!node) {
		return;
	}

	const extraClasses = (placeholder.getAttribute("data-partial-class") || "")
		.split(/\s+/)
		.filter(Boolean);

	if (extraClasses.length > 0) {
		node.classList.add(...extraClasses);
	}

	if (placeholder.id && !node.id) {
		node.id = placeholder.id;
	}

	placeholder.replaceWith(node);
}

async function loadPartials() {
	const placeholders = Array.from(document.querySelectorAll("[data-partial]"));

	for (const placeholder of placeholders) {
		await loadPartial(placeholder);
	}
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
		await loadPartials();
		syncNavbarTitle();
		syncActiveSidebarLink();
		await initCashbookCardComponents();
		initModalComponents();
		initCashbookCreateFlow();
		await ensureLucideIcons();
	} catch (error) {
		console.error(error);
	}
}

document.addEventListener("DOMContentLoaded", () => {
	void initAppShell();
});
