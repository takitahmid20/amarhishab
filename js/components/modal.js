(function setupModalComponent(globalScope) {
	let activeModal = null;
	let lastFocusedElement = null;
	let isInitialized = false;

	function getFocusableElement(modal) {
		return modal.querySelector("[autofocus], input, select, textarea, button, [tabindex]:not([tabindex='-1'])");
	}

	function openModal(modal, triggerElement) {
		if (!(modal instanceof HTMLElement)) {
			return;
		}

		lastFocusedElement = triggerElement || document.activeElement;
		modal.hidden = false;
		modal.setAttribute("aria-hidden", "false");
		activeModal = modal;
		document.body.classList.add("modal-open");

		const focusableElement = getFocusableElement(modal);
		if (focusableElement) {
			focusableElement.focus();
		}
	}

	function closeModal(modal) {
		if (!(modal instanceof HTMLElement)) {
			return;
		}

		modal.hidden = true;
		modal.setAttribute("aria-hidden", "true");

		if (activeModal === modal) {
			activeModal = null;
		}

		const hasOpenModal = document.querySelector("[data-modal]:not([hidden])");
		if (!hasOpenModal) {
			document.body.classList.remove("modal-open");
		}

		if (lastFocusedElement instanceof HTMLElement) {
			lastFocusedElement.focus();
		}
		lastFocusedElement = null;
	}

	function openModalBySelector(selector, triggerElement) {
		if (!selector) {
			return;
		}

		const modal = document.querySelector(selector);
		openModal(modal, triggerElement);
	}

	function closeModalBySelector(selector) {
		if (!selector) {
			return;
		}

		const modal = document.querySelector(selector);
		closeModal(modal);
	}

	function handleClick(event) {
		const trigger = event.target.closest("[data-modal-target]");
		if (trigger) {
			event.preventDefault();
			openModalBySelector(trigger.getAttribute("data-modal-target"), trigger);
			return;
		}

		const closeButton = event.target.closest("[data-modal-close]");
		if (closeButton) {
			event.preventDefault();
			const modal = closeButton.closest("[data-modal]") || activeModal;
			closeModal(modal);
			return;
		}

		const clickedModalOverlay = event.target.closest("[data-modal]");
		if (
			clickedModalOverlay &&
			clickedModalOverlay.hasAttribute("data-modal-close-overlay") &&
			event.target === clickedModalOverlay
		) {
			closeModal(clickedModalOverlay);
		}
	}

	function handleEscape(event) {
		if (event.key !== "Escape" || !activeModal) {
			return;
		}

		closeModal(activeModal);
	}

	function initModalComponent() {
		if (isInitialized) {
			return;
		}

		document.addEventListener("click", handleClick);
		document.addEventListener("keydown", handleEscape);
		isInitialized = true;
	}

	globalScope.AmarHishabModal = {
		initModalComponent,
		openModalBySelector,
		closeModalBySelector
	};
})(window);
