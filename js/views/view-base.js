var wpvgw_view_base;

(function ($) {

	wpvgw_view_base = {

		/**
		 * Initializes this object.
		 */
		init: function () {

			// get all description spans
			var descriptions = $("span.wpvgw-description");

			// hide descriptions
			descriptions.hide();

			// add description icon to each description
			descriptions.each(function( index, element ){
				var spanDescription = $(element);
				// add description icon (by icon font)
				var button = $('<a class="wpvgw-description-button" href="#">&#xE601;</a>');
				button.insertBefore(spanDescription);

				// add toggle function to description icon
				button.click(function (e) {
					e.preventDefault();
					// hide message
					spanDescription.toggle();
				});

			});
		}
	};

	// bind to document ready function
	$(document).ready(function () {
		wpvgw_view_base.init();
	});

}(jQuery));