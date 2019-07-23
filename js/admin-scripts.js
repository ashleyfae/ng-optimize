/**
 * Admin Scripts
 *
 * @package ng-optimize
 * @copyright Copyright (c) 2018, Ashley Gibson
 * @license   GPL2+
 */

(function ($) {

	var NG_Optimize = {

		/**
		 * Initialize
		 */
		init: function () {
			$(document).on('click', '.ng-optimize-button', this.runOptimization);
		},

		/**
		 * Run optimization via ajax
		 */
		runOptimization: function () {

			var button = $(this);
			var action = button.data('action');
			var row = $(this).parent().parent();
			var number = row.find('.ng-optimize-number');

			button.empty().html('<span class="spinner is-active"></span>');
			button.attr('disabled', 'disabled');

			var data = {
				action: action,
				nonce: button.data('nonce')
			};

			$.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				dataType: "json",
				success: function (response) {

					if (response.success) {
						button.empty().text('Complete');
						number.text('0');
					} else {
						button.empty().text(response.data);
					}

				}
			}).fail(function (response) {
				if (window.console && window.console.log) {
					console.log(response);
				}
			});

		}

	};

	NG_Optimize.init();

})(jQuery);