<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_PostView extends WPVGW_ViewBase {
	
	private $errorSetting;


	
	public function __construct( WPVGW_MarkersManager $markers_manager, WPVGW_Options $options ) {
		parent::__construct( $markers_manager, $options, null );

		$this->errorSetting = WPVGW . '-edit-post-error-setting';

		
		add_action( 'wp_ajax_' . WPVGW . '_get_character_count', array( $this, 'ajax_get_character_count' ) );
	}

	
	public function init() {
		
		parent::init_base(
		
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

		
		add_action( 'admin_notices', array( $this, 'on_admin_notice' ) );
		
		add_action( 'add_meta_boxes', array( $this, 'on_add_meta_box' ), 10, 2 );
	}


	
	public function on_add_meta_box( $post_type, $post ) {
		
		$postTypeObject = get_post_type_object( $post->post_type );
		if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
			WPVGW_Helper::die_cheating();

		
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) )
			return;

		
		add_meta_box(
			WPVGW . '-meta-box',
			__( 'Zählmarken für VG WORT', WPVGW_TEXT_DOMAIN ),
			array( $this, 'render' ),
			$post_type,
			'advanced',
			'high'
		);
	}

	
	public function ajax_get_character_count() {
		$postTitle = isset( $_POST['wpvgw_post_title'] ) ? stripslashes( $_POST['wpvgw_post_title'] ) : '';
		$postContent = isset( $_POST['wpvgw_post_content'] ) ? stripslashes( $_POST['wpvgw_post_content'] ) : '';
		$postExcerpt = isset( $_POST['wpvgw_post_excerpt'] ) ? stripslashes( $_POST['wpvgw_post_excerpt'] ) : '';

		
		$characterCount = $this->markersManager->calculate_character_count( $postTitle, $postContent, $postExcerpt );
		$minimumCharacterCount = $this->options->get_vg_wort_minimum_character_count();

		
		$data = array(
			'character_count'            => number_format_i18n( $characterCount ),
			'character_count_sufficient' => $this->markersManager->is_character_count_sufficient( $characterCount, $minimumCharacterCount ) ? __( '<strong>ja</strong>', WPVGW_TEXT_DOMAIN ) : __( 'nein', WPVGW_TEXT_DOMAIN ),
			'missing_character_count'    => number_format_i18n( $this->markersManager->calculate_missing_character_count( $characterCount, $minimumCharacterCount ) ),
			'minimum_character_count'    => number_format_i18n( $minimumCharacterCount ),
		);

		
		echo( json_encode( $data ) );

		exit();
	}

	
	public function on_admin_notice() {
		
		$settingErrors = get_transient( $this->errorSetting );

		
		if ( $settingErrors !== false ) {

			
			foreach ( $settingErrors as $settingError ) {
				
				add_settings_error( $this->errorSetting, $settingError['code'], $settingError['message'], $settingError['type'] );
			}

			
			settings_errors( $this->errorSetting );

			
			delete_transient( $this->errorSetting );
		}
	}

	
	private function add_admin_message( $slug, $message, $type = 'error' ) {
		add_settings_error( $this->errorSetting, WPVGW . '-' . $slug, $message, $type );
	}

	
	private function create_error_from_update_result( $updateResult ) {
		switch ( $updateResult ) {
			
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

	
	public function render( WP_Post $post ) {
		
		wp_nonce_field( 'postview', '_wpvgwpostiewnonce' );

		
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
								<input type="checkbox" name="wpvgw_set_marker" id="wpvgw_set_marker" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_post_view_set_marker_by_default() ) ) ?>/>
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
								<input type="checkbox" name="wpvgw_auto_marker" id="wpvgw_auto_marker" value="1" class="checkbox" <?php echo( WPVGW_Helper::get_html_checkbox_checked( $this->options->get_post_view_auto_marker() ) ) ?>/>
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

	
	private function check_post_character_count( WP_Post $post ) {

		$postCharacterCount = $this->markersManager->calculate_character_count( $post->post_title, $post->post_content, $post->post_excerpt );
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

	
	public function do_action( WP_Post $post ) {
		if ( !isset( $_POST['_wpvgwpostiewnonce'] ) )
			return;

		
		if ( !wp_verify_nonce( $_POST['_wpvgwpostiewnonce'], 'postview' ) )
			WPVGW_Helper::die_cheating();


		
		$postTypeObject = get_post_type_object( $post->post_type );
		if ( !current_user_can( $postTypeObject->cap->edit_post, $post->ID ) )
			WPVGW_Helper::die_cheating();

		
		if ( !$this->markersManager->is_user_allowed( (int)$post->post_author ) )
			return;


		$setMarker = isset( $_POST['wpvgw_set_marker'] ) ? true : false;
		$isMarkerDisabled = isset( $_POST['wpvgw_marker_disabled'] ) ? true : false;


		
		if ( $setMarker ) {
			
			$this->check_post_character_count( $post );


			$isAutoMarker = isset( $_POST['wpvgw_auto_marker'] );
			$publicMarker = isset( $_POST['wpvgw_public_marker'] ) ? trim( $_POST['wpvgw_public_marker'] ) : '';
			$privateMarker = isset( $_POST['wpvgw_private_marker'] ) ? trim( $_POST['wpvgw_private_marker'] ) : '';

			
			$this->options->set_post_view_auto_marker( $isAutoMarker );


			
			do {
				
				$postUserId = (int)$post->post_author;

				
				if ( $isAutoMarker ) {
					
					$marker = $this->markersManager->get_free_marker_from_db( $postUserId );

					if ( $marker === false )
						
						$marker = $this->markersManager->get_free_marker_from_db();

					if ( $marker === false )
						
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
						
							$this->markersManager->update_marker_in_db(
								$marker['public_marker'], 
								'public_marker', 
								array( 
									'post_id'            => $post->ID,
									'is_marker_disabled' => $isMarkerDisabled
								),
								$postUserId, 
								array( 
									'post_id' => null
								)
							)
						);
					}
				}
				
				else {
					
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


					
					$marker = array(
						'post_id'            => $post->ID,
						'public_marker'      => $publicMarker,
						'private_marker'     => $privateMarker,
						'is_marker_disabled' => $isMarkerDisabled
					);

					
					$updateResult = false;

					
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
					


					
					if ( $updateResult !== false )
						$this->create_error_from_update_result( $updateResult );
				}
			} while ( false );
		}
		
		else {
			
			do {
				$removePostFromMarker = isset( $_POST['wpvgw_remove_post_from_marker'] ) ? true : false;

				
				$marker = $this->markersManager->get_marker_from_db( $post->ID, 'post_id' );

				if ( $marker === false ) {
					
					break;
				}

				
				if ( $removePostFromMarker ) {
					
					if ( !current_user_can( 'manage_options' ) )
						WPVGW_Helper::die_cheating();

					
					if ( !$this->markersManager->remove_post_from_marker_in_db( $post->ID ) )
						$this->add_admin_message( 'could-not-remove-marker-from-post', __( 'Die Zuordnung zwischen Zählmarke und Beitrag konnte nicht aufgehoben werden.', WPVGW_TEXT_DOMAIN ) );
					break;
				}
				else {
					
					$this->check_post_character_count( $post );
				}

				
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

		
		set_transient( $this->errorSetting, get_settings_errors( $this->errorSetting ), 30 );
	}

}
