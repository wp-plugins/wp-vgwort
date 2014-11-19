var wpvgw_admin_view_operations;

(function ($) {

	wpvgw_admin_view_operations = {

		init: function () {
			var textBoxManualMarkersRegex = $('#wpvgw_operation_import_old_manual_markers_regex');
			var checkBoxDeleteManualMarkers = $('#wpvgw_operation_import_old_manual_markers_delete');
			var checkBoxImportManualMarkers = $('#wpvgw_operation_import_old_manual_markers');

			textBoxManualMarkersRegex.prop('disabled', true);
			checkBoxDeleteManualMarkers.prop('disabled', true);

			checkBoxImportManualMarkers.click(function () {
				if ($(this).prop('checked')) {
					textBoxManualMarkersRegex.prop('disabled', false);
					checkBoxDeleteManualMarkers.prop('disabled', false);
				} else {
					textBoxManualMarkersRegex.prop('disabled', true);
					checkBoxDeleteManualMarkers.prop('disabled', true);
				}
			});
		}
	};

	$(document).ready(function () {
		wpvgw_admin_view_operations.init();
	});

}(jQuery));