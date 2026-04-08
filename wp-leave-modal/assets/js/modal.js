/**
 * Leave Modal — delegate triggers, fill dialog from config, focus trap, redirect.
 */
(function () {
	'use strict';

	var config = typeof wpLeaveModal === 'object' && wpLeaveModal !== null ? wpLeaveModal : {};
	var modals = config.modals && typeof config.modals === 'object' ? config.modals : {};
	var noUrlHint = typeof config.noUrlHint === 'string' ? config.noUrlHint : '';

	var dialog = document.getElementById('wp-leave-modal');
	if (!dialog) {
		return;
	}

	var titleEl = dialog.querySelector('.wp-leave-modal__title');
	var section1El = document.getElementById('wp-leave-modal-section-1');
	var destLabelEl = document.getElementById('wp-leave-modal-destination-label');
	var urlLinkEl = document.getElementById('wp-leave-modal-url-link');
	var cancelBtn = dialog.querySelector('[data-wp-leave-modal-cancel]');
	var continueBtn = dialog.querySelector('[data-wp-leave-modal-continue]');

	var lastFocused = null;
	var activeRedirectUrl = '';

	function getFocusable() {
		var selectors = [
			'button:not([disabled])',
			'[href]',
			'input:not([disabled])',
			'select:not([disabled])',
			'textarea:not([disabled])',
			'[tabindex]:not([tabindex="-1"])',
		];
		var nodes = dialog.querySelectorAll(selectors.join(','));
		return Array.prototype.filter.call(nodes, function (el) {
			return el.offsetParent !== null || el.getClientRects().length > 0;
		});
	}

	function trapFocus(e) {
		if (e.key !== 'Tab') {
			return;
		}
		var items = getFocusable();
		if (items.length === 0) {
			return;
		}
		var first = items[0];
		var last = items[items.length - 1];
		if (e.shiftKey) {
			if (document.activeElement === first) {
				e.preventDefault();
				last.focus();
			}
		} else {
			if (document.activeElement === last) {
				e.preventDefault();
				first.focus();
			}
		}
	}

	function isSafeRedirectUrl(url) {
		if (!url) {
			return false;
		}
		try {
			var u = new URL(url, window.location.href);
			return u.protocol === 'http:' || u.protocol === 'https:';
		} catch (err) {
			return false;
		}
	}

	function applyModalData(data) {
		if (titleEl) {
			titleEl.textContent = data.title || '';
		}
		if (section1El) {
			section1El.innerHTML = data.section1Html || '';
		}
		if (destLabelEl) {
			destLabelEl.textContent = data.section2Label || '';
		}
		var url = typeof data.redirectUrl === 'string' ? data.redirectUrl.trim() : '';
		activeRedirectUrl = url;
		if (urlLinkEl) {
			urlLinkEl.textContent = url;
			urlLinkEl.setAttribute('href', url || '#');
		}
		if (cancelBtn) {
			cancelBtn.textContent = data.buttonCancel || '';
		}
		if (continueBtn) {
			continueBtn.textContent = data.buttonContinue || '';
			continueBtn.title = '';
		}
		updateContinueState();
	}

	function updateContinueState() {
		if (!continueBtn) {
			return;
		}
		var ok = isSafeRedirectUrl(activeRedirectUrl);
		continueBtn.disabled = !ok;
		continueBtn.title = ok ? '' : noUrlHint;
	}

	function setOpen(open) {
		if (open) {
			dialog.removeAttribute('hidden');
			document.addEventListener('keydown', onKeydown);
			document.body.style.overflow = 'hidden';
			var items = getFocusable();
			if (items.length > 0) {
				items[0].focus();
			}
		} else {
			dialog.setAttribute('hidden', 'hidden');
			document.removeEventListener('keydown', onKeydown);
			document.body.style.overflow = '';
			if (lastFocused && typeof lastFocused.focus === 'function') {
				lastFocused.focus();
			}
			lastFocused = null;
		}
	}

	function onKeydown(e) {
		if (e.key === 'Escape') {
			e.preventDefault();
			setOpen(false);
			return;
		}
		trapFocus(e);
	}

	function readSlug(el) {
		var v = el.getAttribute('data-wp-leave-modal');
		return v ? String(v).trim() : '';
	}

	function onDocumentClick(e) {
		var el = e.target.closest('[data-wp-leave-modal]');
		if (!el) {
			return;
		}
		var slug = readSlug(el);
		if (!slug || !modals[slug]) {
			return;
		}
		e.preventDefault();
		e.stopPropagation();
		lastFocused = el;
		applyModalData(modals[slug]);
		setOpen(true);
	}

	function onCloseClick(e) {
		var t = e.target.closest('[data-wp-leave-modal-close], [data-wp-leave-modal-cancel]');
		if (t) {
			e.preventDefault();
			setOpen(false);
		}
	}

	function onContinue(e) {
		e.preventDefault();
		if (!isSafeRedirectUrl(activeRedirectUrl)) {
			return;
		}
		window.location.href = activeRedirectUrl;
	}

	document.addEventListener('click', onDocumentClick, true);
	dialog.addEventListener('click', onCloseClick);

	if (continueBtn) {
		continueBtn.addEventListener('click', onContinue);
	}
})();
