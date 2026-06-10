(function setupCashbookCardComponent(globalScope) {
	function escapeHtml(value) {
		return String(value)
			.replace(/&/g, "&amp;")
			.replace(/</g, "&lt;")
			.replace(/>/g, "&gt;")
			.replace(/\"/g, "&quot;")
			.replace(/'/g, "&#039;");
	}

	function renderCashbookCard(book) {
		const safeName = escapeHtml(book.name || "Untitled Book");
		const safeCreatedText = escapeHtml(book.createdText || "Created recently");
		const safeStatusLabel = escapeHtml(book.statusLabel || "Live");
		const statusClass = book.statusTone === "warn" ? " cashbook-health--warn" : "";
		const detailsHref = `./cashbook-details.html?cashbookId=${encodeURIComponent(book.id || "")}&cashbookName=${encodeURIComponent(book.name || "")}`;

		return `
<article class="cashbook-card cashbook-card--book surface" data-cashbook-id="${escapeHtml(book.id || "")}">
	<div class="cashbook-card-head">
		<div class="cashbook-main">
			<div class="cashbook-badge"><i data-lucide="book-copy" aria-hidden="true"></i></div>
			<div>
				<h3>${safeName}</h3>
				<p>${safeCreatedText}</p>
			</div>
		</div>
		<span class="cashbook-health${statusClass}">${safeStatusLabel}</span>
	</div>
	<div class="cashbook-card-foot">
		<div class="cashbook-actions">
			<button class="cashbook-icon-btn" type="button" aria-label="Edit ${safeName}"><i data-lucide="pencil" aria-hidden="true"></i></button>
			<a class="cashbook-icon-btn" href="${detailsHref}" aria-label="Open ${safeName}"><i data-lucide="external-link" aria-hidden="true"></i></a>
			<button class="cashbook-icon-btn cashbook-icon-btn--danger" type="button" aria-label="Archive ${safeName}"><i data-lucide="archive" aria-hidden="true"></i></button>
		</div>
	</div>
</article>`.trim();
	}

	function renderCashbookCardList(container, books) {
		if (!container || !Array.isArray(books)) {
			return;
		}

		const markup = books.map((book) => renderCashbookCard(book)).join("\n");
		container.innerHTML = markup;
	}

	globalScope.AmarHishabCashbookCard = {
		renderCashbookCard,
		renderCashbookCardList
	};
})(window);
