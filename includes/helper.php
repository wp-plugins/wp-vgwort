<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_Helper {

	
	public static $shortcodeRegex = '/(?<!\[)\[[^\[\]]+\](?!\])/i';
	
	public static $captionShortcodeRegex = '%(?<!\[)\[caption\s.*?\[/caption\](?!\])%i';
	
	public static $imageTagRegex = '/<img\s.*?>/i';


	
	private static $otherVgWortPlugins = array(
		'vgw-vg-wort-zahlpixel-plugin/vgw.php', 
		'tl-vgwort/tl-vgwort.php', 
		'prosodia-vgw/prosodia-vgw.php', 
		'wp-worthy/wp-worthy.php', 
	);

	
	private static $otherActiveVgWortPlugins = null;


	
	public static function die_cheating() {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}


	
	public static function get_other_active_vg_wort_plugins() {
		
		if ( self::$otherActiveVgWortPlugins !== null )
			return self::$otherActiveVgWortPlugins;


		
		self::$otherActiveVgWortPlugins = array();

		
		foreach ( self::$otherVgWortPlugins as $otherPlugin ) {
			
			if ( is_plugin_active( $otherPlugin ) )
				self::$otherActiveVgWortPlugins[] = $otherPlugin;
		}

		return self::$otherActiveVgWortPlugins;
	}


	
	public static function convert_to_string( $value ) {
		return strval( $value );
	}

	
	public static function convert_to_int( $value ) {
		return intval( $value );
	}

	
	public static function http_header_csv( $file_name ) {
		
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Cache-Control: private", false );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Disposition: attachment; filename=\"$file_name\";" );
		header( "Content-Transfer-Encoding: binary" );
	}

	
	public static function get_html_checkbox_checked( $checked ) {
		return $checked === true ? 'checked="checked"' : ''; 
	}

	
	public static function get_html_option_selected( $selected ) {
		return $selected === true ? 'selected="selected"' : ''; 
	}

	
	public static function render_html_selects( array $html_selects ) {
		
		foreach ( $html_selects as $htmlSelect => $options ) {
			?>
			<select id="<?php echo( $htmlSelect ) ?>" name="<?php echo( $htmlSelect ) ?>">
			<?php

			
			$currentOptionIndex = isset( $_REQUEST[$htmlSelect] ) ? intval( $_REQUEST[$htmlSelect] ) : 0;

			
			foreach ( $options as $optionIndex => $option ) {
				
				echo( sprintf( '<option value="%s" %s>%s</option>',
					esc_attr( $optionIndex ), 
					self::get_html_option_selected( $currentOptionIndex === $optionIndex ), 
					esc_html( $option['label'] ) 
				) );
			}

			?>
			</select>
		<?php
		}
	}

	
	public static function render_admin_message( $html_message, $type, $escape = true ) {
		
		$htmlMessage = $escape ? esc_html( $html_message ) : $html_message;

		
		$dismissLink = sprintf( '<a class="wpvgw-admin-message-dismiss" href="#" title="%s">%s</a>',
			__( 'Nachricht schließen', WPVGW_TEXT_DOMAIN ),
			__( '[Schließen]', WPVGW_TEXT_DOMAIN )
		);

		
		if ( $type == WPVGW_ErrorType::Error ) {
			?>
			<div class='error settings-error'>
				<p class="wpvgw-admin-message-dismiss-paragraph"><?php echo( $dismissLink ) ?></p>
				<p><strong><?php echo( $htmlMessage ) ?></strong></p>
			</div>
		<?php
		}
		
		elseif ( $type == WPVGW_ErrorType::Update ) {
			?>
			<div class='updated settings-error'>
				<p class="wpvgw-admin-message-dismiss-paragraph"><?php echo( $dismissLink ) ?></p>
				<p><?php echo( esc_html( $htmlMessage ) ) ?></p>
			</div>
		<?php
		}
	}

	
	public static function render_admin_messages( array $admin_messages ) {
		foreach ( $admin_messages as $adminMessage ) {
			self::render_admin_message( $adminMessage['message'], $adminMessage['type'], $adminMessage['escape'] );
		}
	}


	
	

	
	public static function implode_keys( $separator, array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			
			$output .= $separator_internal . $key;
			
			$separator_internal = $separator;
		}

		return $output;
	}

	
	public static function sql_setters( array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			
			$output .= $separator_internal . $key . ' = ' . self::get_format_literal( $value );
			
			$separator_internal = ', ';
		}

		return $output;
	}

	
	public static function sql_values( array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $value ) {
			
			$output .= $separator_internal . self::get_format_literal( $value );
			
			$separator_internal = ', ';
		}

		return $output;
	}

	
	public static function sql_columns_on_duplicate( array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			
			$output .= $separator_internal . $key . ' = VALUES(' . $key . ')';
			
			$separator_internal = ', ';
		}

		return $output;
	}

	
	public static function sql_where_logic( $logical_operator, array $array ) {
		$logical_operator = ' ' . $logical_operator . ' ';
		$separator_internal = '';
		$output = '';

		foreach ( $array as $value ) {
			
			$output .= $separator_internal . $value[0] . ' ' . $value[1] . ' ' . self::get_format_literal( $value[2] );
			
			$separator_internal = $logical_operator;
		}

		return $output;
	}


	
	public static function throw_database_exception() {
		
		global $wpdb;

		if ( $wpdb->show_errors )
			$errorText = $wpdb->last_error;
		else
			$errorText = __( 'Fehlerdetails dürfen nur im Debug-Modus angezeigt werden. Bitte kontaktieren Sie ihren Administrator oder die VG-WORT-Plugin-Entwickler.', WPVGW_TEXT_DOMAIN );

		throw new Exception( sprintf( __( 'Datenbankfehler: %s', WPVGW_TEXT_DOMAIN ),
				$errorText
			)
		);
	}

	
	public static function validate_regex_result( $regex_result ) {
		if ( $regex_result === false )
			throw new Exception( __( 'Ein regulärer Ausdruck ist ungültig. Bitte wenden Sie sich an die VG-WORT-Plugin-Entwickler.' ), WPVGW_TEXT_DOMAIN );

		return $regex_result;
	}

	
	public static function throw_unknown_result_exception() {
		throw new Exception( 'Unknown result.' );
	}

	
	public static function remove_prefix( $text, $prefix, &$found = null ) {
		$found = false;

		
		if ( 0 === stripos( $text, $prefix ) ) {
			
			$found = true;
			
			$text = substr( $text, strlen( $prefix ) );
		}

		return $text;
	}

	
	public static function remove_suffix( $text, $suffix ) {
		$textLength = strlen( $text );
		$suffixLength = strlen( $suffix );

		if ( $suffixLength > $textLength )
			return $text;

		
		if ( substr_compare( $text, $suffix, -$suffixLength, $textLength, true ) === 0 )
			
			return substr( $text, 0, -$suffixLength );

		return $text;
	}

	
	public static function array_contains( array $array1, array $array2 ) {
		
		foreach ( $array1 as $key => $value ) {
			
			if ( array_key_exists( $key, $array2 ) )
				
				if ( $array1[$key] !== $array2[$key] )
					return false;
		}

		return true;
	}

	
	public static function get_format_literal( $value ) {
		if ( is_int( $value ) )
			return '%d';

		if ( is_float( $value ) )
			return '%f';

		return '%s';
	}

	
	public static function prepare_with_null( $query, $args ) {
		
		global $wpdb;

		if ( $query === '' )
			return '';

		$arguments = func_get_args();
		array_shift( $arguments );
		
		if ( isset( $arguments[0] ) && is_array( $arguments[0] ) )
			$arguments = $arguments[0];

		
		$argumentCounter = count( $arguments ) - 1;

		$argumentsWithoutNull = array();
		$queryWithNull = '';

		
		
		
		for ( $i = strlen( $query ) - 1; $i > -1; $i-- ) {

			$addCurrentCharToResult = true;
			$currentChar = $query[$i];

			
			
			if ( $currentChar == 's' || $currentChar == 'd' || $currentChar == 'f' ) {
				
				$percentCounter = 0;

				
				for ( $j = $i - 1; $j > -1; $j-- ) {
					if ( $query[$j] == '%' )
						$percentCounter++;
					else
						break;
				}

				
				if ( $percentCounter > 0 ) {
					
					if ( $percentCounter % 2 == 0 )
						
						$queryWithNull = $currentChar . $queryWithNull;
					else { 
						
						if ( $argumentCounter > -1 ) {
							
							$currentArgument = $arguments[$argumentCounter];
							
							if ( $currentArgument === null ) {
								
								$queryWithNull = 'NULL' . $queryWithNull;
								
								$i = $i - $percentCounter;
								$addCurrentCharToResult = false;
							}
							else
								
								array_unshift( $argumentsWithoutNull, $currentArgument = $arguments[$argumentCounter] );
						}

						
						$argumentCounter--;
					}
				}
			}

			if ( $addCurrentCharToResult )
				
				$queryWithNull = $currentChar . $queryWithNull;
		}

		if ( count( $argumentsWithoutNull ) == 0 )
			
			return $queryWithNull;
		else
			
			return $wpdb->prepare( $queryWithNull, $argumentsWithoutNull );
	}


	
	public static function get_user_ids_from_db( array $roles ) {
		

		
		global $wpdb;

		
		$blogId = get_current_blog_id();
		
		$metaKey = $wpdb->get_blog_prefix( $blogId ) . 'capabilities';

		
		$replacements = array( $metaKey );

		
		$whereArray = array();

		
		foreach ( $roles as $role ) {
			
			$role = '%' . $role . '%';
			
			$whereArray[] = array( 'mt.meta_value', 'like', $role );
			$replacements[] = $role;
		}

		
		$whereSql = WPVGW_Helper::sql_where_logic( 'OR', $whereArray );

		
		$usersInDb = $wpdb->get_col( WPVGW_Helper::prepare_with_null(
			"SELECT DISTINCT u.ID FROM {$wpdb->users} AS u INNER JOIN {$wpdb->usermeta} AS mt ON u.ID = mt.user_id WHERE mt.meta_key = %s AND ($whereSql)",
			$replacements
		)
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		$users = array();

		
		foreach ( $usersInDb as $userInDb ) {
			$users[] = (int)$userInDb;
		}


		return $users;
	}

}



class WPVGW_ErrorType {
	
	const Error = 0;
	
	const Update = 1;
}