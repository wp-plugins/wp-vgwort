var wpvgw_post_view;

(function ($) {

	wpvgw_post_view = {

		/**
		 * Initializes this object.
		 */
		init: function () {
			this.hideShowControls();

			this.refreshCharacterCountLinkDisabled = false;

			// refresh character count link (button like)
			this.spinnerRefreshCharacterCount = $('#wpvgw_refresh_character_count_spinner');
			this.linkRefreshCharacterCount = $('#wpvgw_refresh_character_count');
			this.linkRefreshCharacterCount.click(function (e) {
				e.preventDefault();

				if (!wpvgw_post_view.linkRefreshCharacterCount.hasClass('wpvgw-disabled')) {
					// disable refresh character count link
					wpvgw_post_view.linkRefreshCharacterCount.addClass('wpvgw-disabled');
					// show spinner
					wpvgw_post_view.spinnerRefreshCharacterCount.show();
				}

				// update link disabled?
				if (wpvgw_post_view.refreshCharacterCountLinkDisabled)
					return;

				// reset timer
				wpvgw_post_view.printCharacterCount(false);
			});

			// show spinner
			this.spinnerRefreshCharacterCount.show();
			// disable refresh character count link
			this.linkRefreshCharacterCount.addClass('wpvgw-disabled');
			// start timer after 2 s
			//this.printCharacterCount(true);
			//this.printCharacterCountTimer();
			// TODO: hack, 2 s delay to initialize tiny mce or textarea
			setTimeout(function () {
				wpvgw_post_view.printCharacterCount(true)
			}, 2000);
		},

		/**
		 * Hides or shows controls in the VG WORT meta box depend on marker check box.
		 */
		hideShowControls: function () {
			// find marker check box
			var checkBoxSetMarker = $('#wpvgw_set_marker');
			var checkBoxAutoMarker = $('#wpvgw_auto_marker');

			// test if marker check box was found
			if (checkBoxSetMarker.length === 0)
				return;

			// find controls to hide by CSS class
			var trAddMarkerToPost = $('#wpvgw_add_marker_to_post');

			// hide controls if marker check box is not checked
			if (!checkBoxSetMarker.prop('checked'))
				trAddMarkerToPost.hide();

			// bind show and hide controls to marker check box click event
			checkBoxSetMarker.click(function () {
				if ($(this).prop('checked')) {
					trAddMarkerToPost.show();
				} else {
					trAddMarkerToPost.hide();
				}
			});


			// show hide manual marker
			var divManualMarker = $('#wpvgw_manual_marker');
			var hideManualMarkerFunction = function () {
				if (checkBoxAutoMarker.prop('checked')) {
					divManualMarker.hide();
				} else {
					divManualMarker.show();
				}
			};

			// initial hide
			hideManualMarkerFunction();

			// bind show and hide controls to auto marker check box click event
			checkBoxAutoMarker.click(hideManualMarkerFunction);
		},

		/**
		 * Sets a timer to refresh the character count for the current post.
		 */
		printCharacterCountTimer: function () {
			setTimeout(function () {
				wpvgw_post_view.printCharacterCount(true)
			}, 10000);
		},

		/**
		 * Outputs the character count for the current post.
		 */
		printCharacterCount: function (resetTimer) {
			// disable update link
			this.refreshCharacterCountLinkDisabled = true;

			var postTitle = $('#title').val();

			// get post content from tiny mce
			if (typeof tinyMCE !== 'undefined' && tinyMCE.activeEditor && !tinyMCE.activeEditor.isHidden()) {
				// save tiny mce content to textarea
				tinyMCE.triggerSave();
			}

			// get post content
			var postContent = $('#content').val();

			// collect AJAX post data
			var ajaxPostData = {
				action: 'wpvgw_get_character_count',
				wpvgw_post_title: postTitle,
				wpvgw_post_content: postContent
			};

			// post AJAX data to WordPress AJAX url
			$.post(wpvgw_ajax_object.ajax_url, ajaxPostData,
					/**
					 * @param response AJAX response as JSON string.
					 */
							function (response) {
						var data = $.parseJSON(response);

						// output post character count data
						$('#wpvgw_character_count').html(data.character_count);
						$('#wpvgw_character_count_sufficient').html(data.character_count_sufficient);
						$('#wpvgw_missing_character_count').html(data.missing_character_count);
						$('#wpvgw_minimum_character_count').html(data.minimum_character_count);


						// reset timer
						if (resetTimer)
							wpvgw_post_view.printCharacterCountTimer();

						// enable update link
						wpvgw_post_view.refreshCharacterCountLinkDisabled = false;
						// enable refresh character count link
						wpvgw_post_view.linkRefreshCharacterCount.removeClass('wpvgw-disabled');
						// hide spinner
						wpvgw_post_view.spinnerRefreshCharacterCount.hide();
					}
			);
		}

	};

	// bind to document ready function
	$(document).ready(function () {
		wpvgw_post_view.init();
	});

}(jQuery));
