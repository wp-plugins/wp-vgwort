var wpvgw_main;

(function ($) {

	wpvgw_main = {

		/**
		 * Initializes this object.
		 */
		init: function () {
			// add dismiss to admin messages
			$('a.wpvgw-admin-message-dismiss').click(function (e) {
				e.preventDefault();
				// hide message
				$(this).closest('div.settings-error').hide();
			});
		}
	};

	// bind to document ready function
	$(document).ready(function () {
		wpvgw_main.init();
	});

}(jQuery));