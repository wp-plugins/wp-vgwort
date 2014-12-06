var wpvgw_post_table_view;

(function ($) {

	wpvgw_post_table_view = {

		/**
		 * Initializes this object.
		 */
		init: function () {
			var element;

			// add add marker to post bulk action to both HTML options; add_marker_title is passed via wp_localize_script()
			element = $('<option>').val('wpvgw_add_markers').text(wpvgw_translations.add_markers_title);
			element.appendTo("select[name='action']");
			element.clone().appendTo("select[name='action2']");

			// show remove marker from post?
			if (wpvgw_translations.show_remove_markers) {
				// add remove marker from post bulk action to both HTML options;  remove_marker_title is passed via wp_localize_script()
				element = $('<option>').val('wpvgw_remove_markers').text(wpvgw_translations.remove_markers_title);
				element.appendTo("select[name='action']");
				element.clone().appendTo("select[name='action2']");
			}

			element = $('<option>').val('wpvgw_recalculate_post_character_count').text(wpvgw_translations.recalculate_post_character_count_title);
			element.appendTo("select[name='action']");
			element.clone().appendTo("select[name='action2']");
		}
	};

	// bind to document ready function
	$(document).ready(function () {
		wpvgw_post_table_view.init();
	});

}(jQuery));