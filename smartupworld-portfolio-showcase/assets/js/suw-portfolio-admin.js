/* SmartUpWorld Portfolio Showcase — confirm before deleting demo content. */
(function () {
	'use strict';
	document.addEventListener('submit', function (e) {
		var form = e.target;
		if (form && form.classList && form.classList.contains('suwpf-delete-form')) {
			if (!window.confirm(form.getAttribute('data-confirm'))) {
				e.preventDefault();
			}
		}
	});
})();
