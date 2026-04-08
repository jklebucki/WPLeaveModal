/**
 * Admin: add/remove modal rows and reindex field names.
 */
(function () {
	'use strict';

	var cfg = typeof wpLeaveModalAdmin === 'object' && wpLeaveModalAdmin !== null ? wpLeaveModalAdmin : {};
	var optKey = cfg.optionKey || 'wp_leave_modal_settings';
	var needOne = (cfg.i18n && cfg.i18n.needOne) || 'At least one modal is required.';

	var container = document.getElementById('wp-leave-modal-rows');
	if (!container) {
		return;
	}

	var addBtn = document.getElementById('wp-leave-modal-add-row');
	if (!addBtn) {
		return;
	}

	function reindexRows() {
		var rows = container.querySelectorAll('.wp-leave-modal-row');
		var head = optKey + '[modals]';
		rows.forEach(function (row, i) {
			row.querySelectorAll('[name]').forEach(function (el) {
				var n = el.getAttribute('name');
				if (!n || n.indexOf(head) !== 0) {
					return;
				}
				var next = n.replace(/\[modals\]\[\d+\]/, '[modals][' + i + ']');
				el.setAttribute('name', next);
			});
		});
	}

	function clearRowInputs(row) {
		row.querySelectorAll('input[type="text"], input[type="url"], textarea').forEach(function (el) {
			var nm = el.getAttribute('name') || '';
			if (nm.indexOf('[slug]') !== -1) {
				el.value = 'modal-' + Date.now();
				return;
			}
			el.value = '';
		});
	}

	addBtn.addEventListener('click', function () {
		var rows = container.querySelectorAll('.wp-leave-modal-row');
		var last = rows[rows.length - 1];
		if (!last) {
			return;
		}
		var clone = last.cloneNode(true);
		clearRowInputs(clone);
		container.appendChild(clone);
		reindexRows();
	});

	container.addEventListener('click', function (e) {
		var btn = e.target.closest('.wp-leave-modal-remove-row');
		if (!btn) {
			return;
		}
		e.preventDefault();
		var rows = container.querySelectorAll('.wp-leave-modal-row');
		if (rows.length <= 1) {
			window.alert(needOne);
			return;
		}
		var row = btn.closest('.wp-leave-modal-row');
		if (row) {
			row.remove();
		}
		reindexRows();
	});
})();
