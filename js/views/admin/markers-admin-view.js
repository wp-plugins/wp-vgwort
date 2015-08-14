var wpvgw_admin_view_markers;

(function ($) {

	wpvgw_admin_view_markers = {

		init: function () {
			// copy action links
			var linksCopyPostContent = $('.wpvgw-markers-view-copy-post-content');
			var linksCopyPostTitle = $('.wpvgw-markers-view-copy-post-title');
			var linksCopyPrivateMarker = $('.wpvgw-markers-view-copy-private-marker');
			var linksCopyPostLink = $('.wpvgw-markers-view-copy-post-link');

			// copy window
			var divBackgroundCopyWindow = $('#wpvgw-markers-view-copy-window-background');
			var divCopyWindow = $('#wpvgw-markers-view-copy-window');
			var linkCloseCopyWindow = $('#wpvgw-markers-view-copy-window-close');
			var divContentCopyWindow = $('#wpvgw-markers-view-copy-window-content');
			// get original post content HTML (a descriptive text)
			var divPostContentHtml = divContentCopyWindow.html();


			// prevent closing window
			divCopyWindow.click(function (e) {
				// prevent closing window
				e.stopPropagation();
			});

			// close window by button
			linkCloseCopyWindow.click(function (e) {
				e.preventDefault();

				// avoids click recursion with background click
				e.stopPropagation();

				// hide window
				divBackgroundCopyWindow.hide();

				// restore original HTML
				divContentCopyWindow.html(divPostContentHtml);
			});

			// close window by background click
			divBackgroundCopyWindow.click(function () {
				linkCloseCopyWindow.trigger('click');
			});

			// close window by ESC key
			$(document).on('keydown', function (e) {
				if (e.keyCode == 27) { // ESC key
					linkCloseCopyWindow.trigger('click');
				}
			});

			// close window if user copies (Strg + V) text
			divContentCopyWindow.on('copy', function () {
				// TODO: Seems to be a hack.
				// timeout to let the copy (Strg + V) process
				setTimeout(function () {
							// close window
							linkCloseCopyWindow.trigger('click');
						},
						100
				);
			});

			// copy post content
			linksCopyPostContent.click(function (e) {
				e.preventDefault();

				// show window
				divBackgroundCopyWindow.show();

				// collect AJAX post data
				var ajaxPostData = {
					action: 'wpvgw_get_post_content',
					wpvgw_post_id: $(this).data('object-id')
				};

				// post AJAX data to WordPress AJAX url
				$.post(wpvgw_ajax_object.ajax_url, ajaxPostData,
						/**
						 * @param response AJAX response as JSON string.
						 */
						function (response) {
							// be sure the window is shown
							if (divBackgroundCopyWindow.css('display') == 'none')
								return;

							var data = $.parseJSON(response);

							var content;

							// consider post excerpt?
							if (data.post_consider_excerpt)
								content = data.post_excerpt + '\n' + data.post_content;
							else
								content = data.post_content;

							// fill copy window
							wpvgw_admin_view_markers.fillCopyWindow(divContentCopyWindow, data.post_title, content);
						}
				);
			});

			// copy post title
			linksCopyPostTitle.click(function (e) {
				e.preventDefault();

				// get post ID
				var postId = $(this).data('object-id');
				// show window
				divBackgroundCopyWindow.show();
				// fill copy window
				wpvgw_admin_view_markers.fillCopyWindow(divContentCopyWindow, $('#wpvgw-markers-view-post-title-link-' + postId).text(), '');
			});

			// copy private marker
			linksCopyPrivateMarker.click(function (e) {
				e.preventDefault();

				// get marker ID
				var markerId = $(this).data('object-id');
				// show window
				divBackgroundCopyWindow.show();
				// fill copy window
				wpvgw_admin_view_markers.fillCopyWindow(divContentCopyWindow, $('#wpvgw-markers-view-private-marker-' + markerId).text(), '');
			});

			// copy post link
			linksCopyPostLink.click(function (e) {
				e.preventDefault();

				// get marker ID
				var postId = $(this).data('object-id');
				// show window
				divBackgroundCopyWindow.show();
				// fill copy window
				wpvgw_admin_view_markers.fillCopyWindow(divContentCopyWindow, $('#wpvgw-markers-view-post-title-link-' + postId).attr('href'), '');
			});


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

		},


		/**
		 * Fills a copy window with title and content. User can then copy title and content.
		 *
		 * @param copyWindow A HTML div that acts as popup window.
		 * @param title Text title of the window.
		 * @param content HTML content that is shown in the window.
		 */
		fillCopyWindow: function (copyWindow, title, content) {
			// show post content and title in window
			copyWindow.html('<div id="wpvgw-markers-view-post-title">' + title + '</div>' + content);

			// reset scroll positions of the window
			// we set it 2 times to work around a bug in FireFox
			copyWindow.scrollTop(1);
			copyWindow.scrollTop(0);
			copyWindow.scrollLeft(1);
			copyWindow.scrollLeft(0);

			// select text for copy and paste
			wpvgw_admin_view_markers.selectText(copyWindow.attr('id'));
		},


		/**
		 * Selects text for copy and paste.
		 *
		 * @param element An element ID.
		 */
		selectText: function (element) {
			var text = document.getElementById(element), range, selection;

			if (document.body.createTextRange) {
				range = document.body.createTextRange();
				range.moveToElementText(text);
				range.select();
			} else if (window.getSelection) {
				selection = window.getSelection();
				range = document.createRange();
				range.selectNodeContents(text);
				selection.removeAllRanges();
				selection.addRange(range);
			}
		}
	};


	$(document).ready(function () {
		wpvgw_admin_view_markers.init();
	});

}(jQuery));