/* SmartUpWorld Portfolio Showcase — filter tabs. No dependencies. */
(function () {
	'use strict';

	function init(root) {
		if (!root || root.getAttribute('data-suwpf-ready')) return;
		root.setAttribute('data-suwpf-ready', '1');

		var tabs = root.querySelectorAll('.suwpf__tab');
		var cards = root.querySelectorAll('.suwpf__card');

		Array.prototype.forEach.call(tabs, function (tab) {
			tab.addEventListener('click', function () {
				var filter = tab.getAttribute('data-filter');
				Array.prototype.forEach.call(tabs, function (t) {
					t.setAttribute('aria-pressed', t === tab ? 'true' : 'false');
				});
				Array.prototype.forEach.call(cards, function (card) {
					var show = filter === '*' || card.getAttribute('data-type') === filter;
					card.hidden = !show;
					card.classList.remove('is-entering');
					if (show) {
						void card.offsetWidth; // restart the fade-in
						card.classList.add('is-entering');
					}
				});
			});
		});
	}

	function initAll(scope) {
		Array.prototype.forEach.call((scope || document).querySelectorAll('.suwpf'), init);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', function () { initAll(); });
	} else {
		initAll();
	}

	// Elementor editor re-renders widgets without reloading the page.
	function hookElementor() {
		if (window.elementorFrontend && window.elementorFrontend.hooks) {
			window.elementorFrontend.hooks.addAction('frontend/element_ready/suw-portfolio.default', function ($scope) {
				initAll($scope && $scope[0] ? $scope[0] : document);
			});
		}
	}
	if (window.jQuery) {
		// Elementor fires this through jQuery, not as a native DOM event.
		window.jQuery(window).on('elementor/frontend/init', hookElementor);
	}
})();
