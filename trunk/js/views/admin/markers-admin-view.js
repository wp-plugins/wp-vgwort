var wpvgw_admin_view_markers;

(function ($) {

	wpvgw_admin_view_markers = {

		init: function () {
			/* settings for bulk action section  */
			var markerDisabledCheckBox = $('#wpvgw_e_marker_disabled');
			var markerDisabledSetCheckBox = $('#wpvgw_e_marker_disabled_set');

			markerDisabledCheckBox.prop('disabled', true);

			markerDisabledSetCheckBox.click(function () {
				if ($(this).prop('checked')) {
					markerDisabledCheckBox.prop('disabled', false);
				} else {
					markerDisabledCheckBox.prop('disabled', true);
				}
			});


			/* settings for bulk action section  */
			var textBoxServer = $('#wpvgw_e_server');
			var checkBoxServerSet = $('#wpvgw_e_server_set');

			textBoxServer.prop('disabled', true);

			checkBoxServerSet.click(function () {
				if ($(this).prop('checked')) {
					textBoxServer.prop('disabled', false);
				} else {
					textBoxServer.prop('disabled', true);
				}
			});

			/* manage visibility of the markers bulk edit div */
			var divMarkersBulkEdit = $('#wpvgw-markers-bulk-edit');
			divMarkersBulkEdit.addClass('wpvgw-markers-bulk-edit');
			divMarkersBulkEdit.hide();

			// markers table
			var tableMarkers = $('#wpvgw-markers').find('> table.wpvgw_markers');
			// cancel button
			var linkCancelBulkEdit = divMarkersBulkEdit.find('a.cancel');

			// move bulk edit div before markers table
			tableMarkers.before(divMarkersBulkEdit);

			// hide bulk edit div if cancel button is clicked
			linkCancelBulkEdit.click(function (e) {
						e.preventDefault();
						divMarkersBulkEdit.hide();
					}
			);

			// copied from core (wordpress/wp-admin/js/inline-edit-post.js) and modified
			$('#doaction, #doaction2').click(function (e) {
				// first or second action
				var n = $(this).attr('id').substr(2);

				// if edit action is selected
				if ('edit' === $('select[name="' + n + '"]').val()) {
					// prevent button submit
					e.preventDefault();

					var oneIdChecked = false;
					// find all checkboxes in the markers table
					tableMarkers.find('th.check-column input[type="checkbox"]').each(function () {
						if ($(this).prop('checked')) {
							// at least one checkbox is checked
							oneIdChecked = true;
						}
					});

					// at least one checkbox checked?
					if (oneIdChecked) {
						// show bulk edit div
						divMarkersBulkEdit.show();
						// scroll to window top
						$('html, body').animate({scrollTop: 0}, 'fast');

					}
					else {
						// hide bulk edit div
						divMarkersBulkEdit.hide();
					}
				}
			});

		}
	};

	$(document).ready(function () {
		wpvgw_admin_view_markers.init();
	});

}(jQuery));