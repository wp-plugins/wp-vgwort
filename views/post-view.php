<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * The markers view in the post edit/new page.
 */
class WPVGW_PostView extends WPVGW_ViewBase {
	/**
	 * @var string The name (slug) of the settings error.
	 */
	private $errorSetting;


	/**
	 * Creates a new instance of {@link WPVGW_PostView}.
	 *
	 * @param WPVGW_MarkersManager $markers_manager A markers manager.
	 * @param WPVGW_Options $options The options.
	 */
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_Options $options ) {
		parent::__construct( $markers_manager, $options, null );

		$this->errorSetting = WPVGW . '-edit-post-error-setting';

		// hook ajax get character count action
		add_action( 'wp_ajax_' . WPVGW . '_get_character_count', array( $this, 'ajax_get_character_count' ) );
	}

	/**
	 * Initializes the view. This function should be called before using the view.
	 */
	public function init() {
		// has to be called
		parent::init_base(
		// javascript data
			array(
				array(
					'file'         => 'views/post-view.js',
					'slug'         => 'post-view',
					'dependencies' => array( 'jquery' ),
					'localize'     => array(
						'object_name' => 'ajax_object',
						'data'        => array(
							'ajax_url' => admin_url( 'admin-ajax.php' )
						)
					)
				)
			)
		);

		// hook admin notices
		add_action( 'admin_notices', array( $this, 'on_admin_notice' ) );
		// hook meta box for the new/edit page (adds a option section for this plugin)
		add_action( 'add_meta_boxes', array( $this, 'on_add_meta_box' ), 10, 2 );
	}


	/**
	 * Adds a mata box to the main column on the Post and Page edit screens.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 *
	 * @param string $post_type The type of the post for which the new/edit page is called.
	 * @param WP_Post $post The post for which the new/edit page is called.
	 */
	public function on_add_meta_box( $post_type, $post ) {
		// check if current user has the permission to edit this post
		$postTypeObject = get_post_type_object( $post->post_type );
		if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
			WPVGW_Helper::die_cheating();

		// show markers meta box for allowed users (authors) only
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) )
			return;

		// add VG WORT meta box
		add_meta_box(
			WPVGW . '-meta-box',
			__( 'Zählmarken für VG WORT', WPVGW_TEXT_DOMAIN ),
			array( $this, 'render' ),
			$post_type,
			'advanced',
			'high'
		);
	}

	/**
	 * Get the character count for a post title and a post content for an AJAX request.
	 * The parameters are retrieved by HTTP post: string wpvgw_source ('tinymce' or ''), string wpvgw_post_title, string wpvgw_post_content
	 * Echos a JSON string that contains: int character_count, bool character_count_sufficient, int missing_character_count, int minimum_character_count
	 * This function terminates the script execution.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function ajax_get_character_count() {
		$postTitle = isset( $_POST['wpvgw_post_title'] ) ? stripslashes( $_POST['wpvgw_post_title'] ) : '';
		$postContent = isset( $_POST['wpvgw_post_content'] ) ? stripslashes( $_POST['wpvgw_post_content'] ) : '';

		// calculate character count
		$characterCount = $this->markersManager->calculate_character_count( $postTitle, $postContent );
		$minimumCharacterCount = $this->options->get_vg_wort_minimum_character_count();

		// collect output data
		$data = array(
			'character_count'            => number_format_i18n( $characterCount ),
			'character_count_sufficient' => $this->markersManager->is_character_count_sufficient( $characterCount, $minimumCharacterCount ) ? __( '<strong>ja</strong>', WPVGW_TEXT_DOMAIN ) : __( 'nein', WPVGW_TEXT_DOMAIN ),
			'missing_character_count'    => number_format_i18n( $this->markersManager->calculate_missing_character_count( $characterCount, $minimumCharacterCount ) ),
			'minimum_character_count'    => number_format_i18n( $minimumCharacterCount ),
		);

		// echo output as JSON string
		echo( json_encode( $data ) );

		exit();
	}

	/**
	 * Renders WordPress admin notices.
	 * Warning: This function is called by a WordPress hook. Do not call it directly.
	 */
	public function on_admin_notice() {
		// get saved errors
		$settingErrors = get_transient( $this->errorSetting );

		// return if there are no errors
		if ( $settingErrors !== false ) {

			// iterate errors
			foreach ( $settingErrors as $settingError ) {
				// delegate error to WordPress
				add_settings_error( $this->errorSetting, $settingError['code'], $settingError['message'], $settingError['type'] );
			}

			// render errors
			settings_errors( $this->errorSetting );

			// delete stored errors
			delete_transient( $this->errorSetting );
		}
	}

	/**
	 * Adds an admin message.
	 *
	 * @param string $slug A slug for the admin message.
	 * @param string $message An admin message, e. g., an error message.
	 * @param string $type The type of message: 'error' or 'updated'.
	 */
	private function add_admin_message( $slug, $message, $type = 'error' ) {
		add_settings_error( $this->errorSetting, WPVGW . '-' . $slug, $message, $type );
	}

	/**
	 * Create admin messages from a specified marker update result.
	 *
	 * @param int $updateResult One of the constants from {@link WPVGW_UpdateMarkerResults}.
	 *
	 * @see add_admin_message()
	 */
	private function create_error_from_update_result( $updateResult ) {
		switch ( $updateResult ) {
			/*case WPVGW_UpdateMarkerResults::PostIdExists:
				$this->add_admin_message( 'post-id-exists', __( 'Der Beitrag wurde bereits einer anderen Zählmarke zugeordnet.', WPVGW_TEXT_DOMAIN ) );
				break;
			case WPVGW_UpdateMarkerResults::PublicMarkerExists:
				$this->add_admin_message( 'public-marker-exists', __( 'Die öffentliche Zählmarke wird bereits bei einem anderen Beitrag verwendet.', WPVGW_TEXT_DOMAIN ) );
				break;
			case WPVGW_UpdateMarkerResults::PrivateMarkerExists:
				$this->add_admin_message( 'private-marker-exists', __( 'Die private Zählmarke wird bereits bei einem anderen Beitrag verwendet.', WPVGW_TEXT_DOMAIN ) );
				break;*/
			case WPVGW_UpdateMarkerResults::PostIdNotNull:
				$this->add_admin_message( 'marker-not-free', __( 'Die Zählmarke entsprechend Ihrer Vorgaben ist bereits einem anderem Beitrag zugeordnet.', WPVGW_TEXT_DOMAIN ) );
				break;
			case WPVGW_UpdateMarkerResults::MarkerNotFound:
				$this->add_admin_message( 'marker-not-found',
					sprintf(
						__( 'Es wurde keine Zählmarke entsprechend Ihrer Vorgaben gefunden. Die Zählmarke muss ggf. zunächst importiert werden. %s',
							WPVGW_TEXT_DOMAIN
						),
						sprintf( '<a href="%s">%s</a>',
							esc_attr( WPVGW_AdminViewsManger::create_admin_view_url( WPVGW_ImportAdminView::get_slug_static() ) ),
							__( 'Zählmarken hier importieren.', WPVGW_TEXT_DOMAIN )
						)
					)
				);
				break;
			case WPVGW_UpdateMarkerResults::UserNotAllowed:
				$this->add_admin_message( 'marker-user-not-allowed', __( 'Sie dürfen die Zählmarke entsprechend Ihrer Vorgaben nicht verwenden, da sie für einen anderen Benutzer bestimmt ist.', WPVGW_TEXT_DOMAIN ) );
				break;
			default:
				break;
		}
	}

	/**
	 * Renders this view.
	 *
	 * @param WP_Post $post The post for which this view will be rendered.
	 */
	public function render( WP_Post $post ) {
		// security: use nonce for verification
		wp_nonce_field( 'postview', '_wpvgwpostiewnonce' );

		// get marker for the post
		$marker = $this->markersManager->get_marker_from_db( $post->ID, 'post_id' );

		?>

		<?php if ( $marker === false ) : ?>
			<p>
				<?php _e( 'Diesem Beitrag ist keine Zählmarke zugeordnet.', WPVGW_TEXT_DOMAIN ) ?>
			</p>
		<?php else : ?>
			<p>
				<?php _e( 'Diesem Beitrag ist eine Zählmarke zugeordnet. In der Regel sollte die Zuordnung nicht mehr aufgehoben werden.', WPVGW_TEXT_DOMAIN ) ?>
			</p>
		<?php endif; ?>

		<table class="form-table">
			<tbody>
				<tr>
					<th><?php _e( 'Zeichenanzahl im Text' ) ?></th>
					<td>
						<p>
							<?php
							echo( sprintf(
								__( 'Genügend: %1$s, %2$s fehlen', WPVGW_TEXT_DOMAIN ),
								'<span id="wpvgw_character_count_sufficient">–</span>',
								'<span id="wpvgw_missing_character_count">–</span>'
							) );
							?>
						</p>
						<p>
							<?php
							echo( sprintf(
								__( 'Vorhanden: %1$s von %2$s nötigen', WPVGW_TEXT_DOMAIN ),
								'<span id="wpvgw_character_count">–</span>',
								'<span id="wpvgw_minimum_character_count">–</span>'
							) );
							?>
						</p>
						<p>
							<?php
							echo( sprintf(
								'<a id="wpvgw_refresh_character_count" href="#">%s</a> %s <span id="wpvgw_refresh_character_count_spinner" class="spinner"></span>',
								__( 'jetzt aktualisieren', WPVGW_TEXT_DOMAIN ),
								__( '(sonst alle paar Sekunden automatisch)', WPVGW_TEXT_DOMAIN )
							) );
							?>
						</p>
					</td>
				</tr>
				<?php if ( $marker === false ) : ?>
					<tr>
						<th><?php _e( 'Aktion' ) ?></th>
						<td>
							<p>
								<input type="checkbox" name="wpvgw_set_marker" id="wpvgw_set_marker" value="1" class="checkbox"/>
								<label for="wpvgw_set_marker"><?php _e( 'Diesem Beitrag eine Zählmarke zuordnen' ) ?></label>
								<br/>
								<span class="description"><?php _e( 'Weisen Sie diesem Beitrag eine Zählmarke zu, um Ihre Leser bei der VG WORT zählen zu lassen.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
					<tr id="wpvgw_add_marker_to_post">
						<th><?php _e( 'Zählmarken-Zuordnung', WPVGW_TEXT_DOMAIN ) ?></th>
						<td>
							<p>
								<input type="checkbox" name="wpvgw_auto_marker" id="wpvgw_auto_marker" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_view_auto_marker() ) ) ?>/>
								<label for="wpvgw_auto_marker"><?php _e( 'Zählmarke automatisch zuordnen' ) ?></label>
								<br/>
								<span class="description"><?php _e( 'Aktivieren, um dem Beitrag automatisch eine unbenutzte Zählmarke zuordnen zu lassen (empfohlen), ansonsten manuell.', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
							<div id="wpvgw_manual_marker">
								<p class="description">
									<?php _e( 'Nur bereits importierte Zählmarken können manuell zugeordnet werden. Es mindestens eine Marke angegeben werden.', WPVGW_TEXT_DOMAIN ) ?>
								</p>
								<p>
									<label for="wpvgw_public_marker"><?php _e( 'Öffentliche Zählmarke manuell zuordnen', WPVGW_TEXT_DOMAIN ) ?></label>
									<br/>
									<input type="text" name="wpvgw_public_marker" id="wpvgw_public_marker" class="regular-text" value=""/>
								</p>
								<p>
									<label for="wpvgw_private_marker"><?php _e( 'Private Zählmarke manuell zuordnen', WPVGW_TEXT_DOMAIN ) ?></label>
									<br/>
									<input type="text" name="wpvgw_private_marker" id="wpvgw_private_marker" class="regular-text" value=""/>
								</p>
							</div>
							<p>
								<input type="checkbox" name="wpvgw_marker_disabled" id="wpvgw_marker_disabled" value="1" class="checkbox"/>
								<label for="wpvgw_marker_disabled"><?php _e( 'Inaktiv' ) ?></label>
								<br/>
								<span class="description"><?php _e( 'Inaktive Zählmarken werden für den zugeordneten Beitrag nicht mehr ausgegeben (keine Zählung mehr bei VG WORT).', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
				<?php else : ?>
					<tr>
						<th><?php _e( 'Zählmarken-Zuordnung', WPVGW_TEXT_DOMAIN ) ?></th>
						<td>
							<p>
								<?php echo( __( 'Öffentlich: ', WPVGW_TEXT_DOMAIN ) . esc_html( $marker['public_marker'] ) ) ?>
							</p>
							<p>
								<?php echo( __( 'Privat: ', WPVGW_TEXT_DOMAIN ) . esc_html( $marker['private_marker'] ) ) ?>
							</p>
								<p>
								<?php echo( __( 'Server: ', WPVGW_TEXT_DOMAIN ) . esc_html( $marker['server'] ) ) ?>
							</p>
							<p>
								<input type="checkbox" name="wpvgw_marker_disabled" id="wpvgw_marker_disabled" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $marker['is_marker_disabled'] ) ) ?>/>
								<label for="wpvgw_marker_disabled"><?php _e( 'Inaktiv', WPVGW_TEXT_DOMAIN ); ?></label>
								<br/>
								<span class="description"><?php _e( 'Inaktive Zählmarken werden für den zugeordneten Beitrag nicht mehr ausgegeben (keine Zählung mehr bei VG WORT).', WPVGW_TEXT_DOMAIN ) ?></span>
							</p>
						</td>
					</tr>
					<?php
					// allow admin users only
					if ( current_user_can( 'manage_options' ) ) : ?>
						<tr>
							<th><?php _e( 'Aktion', WPVGW_TEXT_DOMAIN ) ?></th>
							<td>
								<p>
									<input type="checkbox" name="wpvgw_remove_post_from_marker" id="wpvgw_remove_post_from_marker" value="1" class="checkbox"/>
									<label for="wpvgw_remove_post_from_marker"><?php _e( 'Zählmarken-Zuordnung aufheben', WPVGW_TEXT_DOMAIN ); ?></label>
									<br/>
									<span class="description"><?php _e( 'In der Regel sollte die Zuordnung nicht aufgehoben, sondern die Zählmarke inaktiv gesetzt werden.', WPVGW_TEXT_DOMAIN ) ?></span>
								</p>
							</td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>
			</tbody>
		</table>
	<?php
	}

	/**
	 * Adds an admin message if the character count of a specified post is not sufficient.
	 *
	 * @param WP_Post $post The post to check the character count for.
	 */
	private function check_post_character_count( WP_Post $post ) {

		$postCharacterCount = $this->markersManager->calculate_character_count( $post->post_title, $post->post_content );
		$minimumCharacterCount = $this->options->get_vg_wort_minimum_character_count();

		if ( !$this->markersManager->is_character_count_sufficient( $postCharacterCount, $minimumCharacterCount ) )
			$this->add_admin_message(
				'too-few-characters',
				sprintf( __( 'Dieser Beitrag enthält weniger als %s Zeichen (es fehlen noch %s) und verstößt damit gegen die von der VG WORT vorgegebene Mindestzeichenanzahl. Eine Zählmarke wurde dennoch zugeordnet.', WPVGW_TEXT_DOMAIN ),
					number_format_i18n( $minimumCharacterCount ),
					number_format_i18n( $this->markersManager->calculate_missing_character_count( $postCharacterCount, $minimumCharacterCount ) )
				)
			);
	}

	/**
	 * Do view action for a specified post.
	 *
	 * @param WP_Post $post The post to do the action for.
	 *
	 * @throws Exception Thrown if some error occurred.
	 */
	public function do_action( WP_Post $post ) {
		if ( !isset( $_POST['_wpvgwpostiewnonce'] ) )
			return;

		// security: verify this came from our screen and with proper authorization
		if ( !wp_verify_nonce( $_POST['_wpvgwpostiewnonce'], 'postview' ) )
			WPVGW_Helper::die_cheating();


		// check if current user has the permission to edit this post
		$postTypeObject = get_post_type_object( $post->post_type );
		if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
			WPVGW_Helper::die_cheating();

		// check if post author is one of the allowed users (authors)
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) )
			return;


		$setMarker = isset( $_POST['wpvgw_set_marker'] ) ? true : false;
		$isMarkerDisabled = isset( $_POST['wpvgw_marker_disabled'] ) ? true : false;


		// if a marker should be add to the post
		if ( $setMarker ) {
			// add admin message if character count of the post is not sufficient
			$this->check_post_character_count( $post );


			$isAutoMarker = isset( $_POST['wpvgw_auto_marker'] );
			$publicMarker = isset( $_POST['wpvgw_public_marker'] ) ? trim( $_POST['wpvgw_public_marker'] ) : '';
			$privateMarker = isset( $_POST['wpvgw_private_marker'] ) ? trim( $_POST['wpvgw_private_marker'] ) : '';

			// remember auto marker checkbox status
			$this->options->set_post_view_auto_marker( $isAutoMarker );


			// the do-while makes the code easier to read ^^
			do {
				// get post author/user
				$postUserId = (int)$post->post_author;

				// try to find next free marker automatically
				if ( $isAutoMarker ) {
					// get free marker for the author
					$marker = $this->markersManager->get_free_marker_from_db( $postUserId );

					if ( $marker === false )
						// get free marker for arbitrary author
						$marker = $this->markersManager->get_free_marker_from_db();

					if ( $marker === false )
						// no free marker found
						$this->add_admin_message( '-no-free-marker',
							sprintf(
								__( 'Eine Zählmarke konnte nicht automatisch zugeordnet werden, da für den Beitrags-Autor keine mehr verfügbar sind. Bitte importieren Sie zunächst neue Zählmarken für den Beitrags-Autor. %s',
									WPVGW_TEXT_DOMAIN
								),
								sprintf( '<a href="%s">%s</a>',
									esc_attr( WPVGW_AdminViewsManger::create_admin_view_url( WPVGW_ImportAdminView::get_slug_static() ) ),
									__( 'Zählmarken hier importieren.', WPVGW_TEXT_DOMAIN )
								)
							)
						);
					else {
						$this->create_error_from_update_result(
						// add post to the found marker
							$this->markersManager->update_marker_in_db(
								$marker['public_marker'], // key
								'public_marker', // column
								array( // marker
									'post_id'            => $post->ID,
									'is_marker_disabled' => $isMarkerDisabled
								),
								$postUserId, // post author
								array( // conditions (just to be safe that the old post id is null)
									'post_id' => null
								)
							)
						);
					}
				}
				// try to find the marker that was specified by user
				else {
					// validation
					if ( !$this->markersManager->public_marker_validator( $publicMarker ) && $publicMarker !== '' ) {
						$this->add_admin_message( 'public-marker-invalid-format', __( 'Die öffentliche Zählmarke hat ein ungültiges Format. Bitte nehmen Sie eine Korrektur vor.', WPVGW_TEXT_DOMAIN ) );
						break;
					}
					if ( $publicMarker === '' )
						$publicMarker = null;

					if ( !$this->markersManager->private_marker_validator( $privateMarker ) && $privateMarker !== '' ) {
						$this->add_admin_message( 'private-marker-invalid-format', __( 'Die private Zählmarke hat ein ungültiges Format. Bitte nehmen Sie eine Korrektur vor.', WPVGW_TEXT_DOMAIN ) );
						break;
					}
					if ( $privateMarker === '' )
						$privateMarker = null;


					if ( $publicMarker === null && $privateMarker === null ) {
						$this->add_admin_message( 'public-and-private-marker-empty', __( 'Öffentliche und private Zählmarke dürfen nicht gleichzeitig leer sein, da sonst keine Zählmarke zugeordnet werden kann.', WPVGW_TEXT_DOMAIN ) );
						break;
					}


					// create update marker
					$marker = array(
						'post_id'            => $post->ID,
						'public_marker'      => $publicMarker,
						'private_marker'     => $privateMarker,
						'is_marker_disabled' => $isMarkerDisabled
					);

					//$oldMarker = false;
					$updateResult = false;

					// add post to the user specified marker
					if ( $publicMarker !== null && $privateMarker !== null ) {
						$updateResult = $this->markersManager->update_marker_in_db( $publicMarker, 'public_marker', $marker, $postUserId, array( 'private_marker' => $privateMarker ) );
					}
					elseif ( $publicMarker !== null ) {
						unset( $marker['private_marker'] );
						$updateResult = $this->markersManager->update_marker_in_db( $publicMarker, 'public_marker', $marker, $postUserId );
					}
					elseif ( $privateMarker !== null ) {
						unset( $marker['public_marker'] );
						$updateResult = $this->markersManager->update_marker_in_db( $privateMarker, 'private_marker', $marker, $postUserId );
					}
					/*else {
						// case not possible, handled above
					}*/


					// add admin error message
					if ( $updateResult !== false )
						$this->create_error_from_update_result( $updateResult );
				}
			} while ( false );
		}
		// if a marker should be updated
		else {
			// the do-while makes the code easier to read ^^
			do {
				$removePostFromMarker = isset( $_POST['wpvgw_remove_post_from_marker'] ) ? true : false;

				// get marker of the post
				$marker = $this->markersManager->get_marker_from_db( $post->ID, 'post_id' );

				if ( $marker === false ) {
					//$this->add_admin_message( 'marker-not-found', __( 'Die dem Beitrag zugeordnete Zählmarke konnte nicht gefunden werden.', WPVGW_TEXT_DOMAIN ) );
					break;
				}

				// remove post from marker? allow admin users only
				if ( $removePostFromMarker ) {
					// allow admin users only
					if ( !current_user_can( 'manage_options' ) )
						WPVGW_Helper::die_cheating();

					// remove post from marker
					if ( !$this->markersManager->remove_post_from_marker_in_db( $post->ID ) )
						$this->add_admin_message( 'could-not-remove-marker-from-post', __( 'Die Zuordnung zwischen Zählmarke und Beitrag konnte nicht aufgehoben werden.', WPVGW_TEXT_DOMAIN ) );
					break;
				}
				else {
					// add admin message if character count of the post is not sufficient
					$this->check_post_character_count( $post );
				}

				// update marker disabled status
				$this->create_error_from_update_result(
					$this->markersManager->update_marker_in_db(
						$post->ID,
						'post_id',
						array(
							'is_marker_disabled' => $isMarkerDisabled
						)
					)
				);
			} while ( false );
		}

		// save errors for next page
		set_transient( $this->errorSetting, get_settings_errors( $this->errorSetting ), 30 );
	}

}
