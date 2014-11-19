<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */
 

/**
 * Holds a set of static helper functions.
 */
class WPVGW_Helper {

	/**
	 * @var string Regex that matches shortcodes, but not content between shortcodes. It is escaping aware.
	 */
	public static $shortcodeRegex = '/(?<!\[)\[[^\[\]]+\](?!\])/i';
	/**
	 * @var string Regex that matches the caption shortcode and its content. It is escaping aware.
	 */
	public static $captionShortcodeRegex = '%(?<!\[)\[caption\s.*?\[/caption\](?!\])%i';
	/**
	 * @var string Regex that matches a HTML image tag and its content.
	 */
	public static $imageTagRegex = '/<img\s.*?>/i';


	/**
	 * @var string[] The array of other VG WORT plugins paths.
	 */
	private static $otherVgWortPlugins = array(
		'tl-vgwort/tl-vgwort.php', // "VG Wort" by Torben Leuschner
		'prosodia-vgw/prosodia-vgw.php' // "Prosodia VGW OS" by Ronny Harbich
	);

	/**
	 * @var string[]|null The array of all other active VG WORT plugins paths or null if not initialized.
	 */
	private static $otherActiveVgWortPlugins = null;


	/**
	 * Let WordPress die with cheating message. Use this function for security abort.
	 */
	public static function die_cheating() {
		wp_die( __( 'Cheatin&#8217; uh?' ) );
	}


	/**
	 * Gets all other VG WORT plugins (paths) that are active currently.
	 *
	 * @return string[] An array of paths of other VG WORT plugins that are active currently.
	 */
	public static function get_other_active_vg_wort_plugins() {
		// array of other active VG WORT plugin paths initialized?
		if ( self::$otherActiveVgWortPlugins !== null )
			return self::$otherActiveVgWortPlugins;


		// initialize the array of other active VG WORT plugin paths
		self::$otherActiveVgWortPlugins = array();

		// iterate other VG WORT plugins paths array
		foreach ( self::$otherVgWortPlugins as $otherPlugin ) {
			// if plugin is active add it to the array of other active VG WORT plugin paths
			if ( is_plugin_active( $otherPlugin ) )
				self::$otherActiveVgWortPlugins[] = $otherPlugin;
		}

		return self::$otherActiveVgWortPlugins;
	}


	/**
	 * Converts a specified value into a string.
	 *
	 * @param mixed $value The value to be converted.
	 *
	 * @return string The converted value.
	 */
	public static function convert_to_string( $value ) {
		return strval( $value );
	}

	/**
	 * Converts a specified value into an int.
	 *
	 * @param mixed $value The value to be converted.
	 *
	 * @return int The converted value.
	 */
	public static function convert_to_int( $value ) {
		return intval( $value );
	}

	/**
	 * Outputs a HTTP header for CSV files (coma separated values files).
	 *
	 * @param string $file_name Name of the CSV file. Should be end with ".csv".
	 */
	public static function http_header_csv( $file_name ) {
		// http header for CSV output
		header( "Pragma: public" );
		header( "Expires: 0" );
		header( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header( "Cache-Control: private", false );
		header( "Content-Type: application/octet-stream" );
		header( "Content-Disposition: attachment; filename=\"$file_name\";" );
		header( "Content-Transfer-Encoding: binary" );
	}

	/**
	 * Returns the checked attribute for a HTML input tag (a checkbox).
	 *
	 * @param bool $checked If HTML input (a checkbox) should be checked or not.
	 *
	 * @return string checked="checked" if $checked is true.
	 */
	public static function get_html_checkbox_checked( $checked ) {
		return $checked === true ? 'checked="checked"' : ''; // option selected?
	}

	/**
	 * Returns the selected attribute for a HTML option tag.
	 *
	 * @param bool $selected If HTML option should be selected or not.
	 *
	 * @return string selected="selected" if $selected is true.
	 */
	public static function get_html_option_selected( $selected ) {
		return $selected === true ? 'selected="selected"' : ''; // option selected?
	}

	/**
	 * Renders a complete HTML select with options.
	 * It sets the selected state automatically according to the current HTTP request.
	 *
	 * @param array $html_selects An array of HTML selects: select_id => array('label' => 'a option label', 'value' => 'the actual value of the option').
	 */
	public static function render_html_selects( array $html_selects ) {
		// iterate selects
		foreach ( $html_selects as $htmlSelect => $options ) {
			?>
			<select id="<?php echo( $htmlSelect ) ?>" name="<?php echo( $htmlSelect ) ?>">
			<?php

			// get current option index of the select
			$currentOptionIndex = isset( $_REQUEST[$htmlSelect] ) ? intval( $_REQUEST[$htmlSelect] ) : 0;

			// iterate options of the select
			foreach ( $options as $optionIndex => $option ) {
				// echo the option
				echo( sprintf( '<option value="%s" %s>%s</option>',
					esc_attr( $optionIndex ), // option value
					self::get_html_option_selected( $currentOptionIndex === $optionIndex ), // option selected?
					esc_html( $option['label'] ) // the label of the option
				) );
			}

			?>
			</select>
		<?php
		}
	}

	/**
	 * Renders a WordPress admin message (only shown in the admin interface).
	 *
	 * @param string $html_message The message (HTML) for a user in the admin interface.
	 * @param int $type One of the constants defined in {@link WPVGW_ErrorType}.
	 * @param bool $escape If true, $message will be HTML escaped, otherwise it’s not escaped.
	 */
	public static function render_admin_message( $html_message, $type, $escape = true ) {
		// escape HTML message?
		$htmlMessage = $escape ? esc_html( $html_message ) : $html_message;

		// create dismiss link
		$dismissLink = sprintf( '<a class="wpvgw-admin-message-dismiss" href="#" title="%s">%s</a>',
			__( 'Nachricht schließen', WPVGW_TEXT_DOMAIN ),
			__( '[Schließen]', WPVGW_TEXT_DOMAIN )
		);

		// error type
		if ( $type == WPVGW_ErrorType::Error ) {
			?>
			<div class='error settings-error'>
				<p class="wpvgw-admin-message-dismiss-paragraph"><?php echo( $dismissLink ) ?></p>
				<p><strong><?php echo( $htmlMessage ) ?></strong></p>
			</div>
		<?php
		}
		// update type
		elseif ( $type == WPVGW_ErrorType::Update ) {
			?>
			<div class='updated settings-error'>
				<p class="wpvgw-admin-message-dismiss-paragraph"><?php echo( $dismissLink ) ?></p>
				<p><?php echo( esc_html( $htmlMessage ) ) ?></p>
			</div>
		<?php
		}
	}

	/**
	 * Renders multiple admin messages. See {@link render_admin_message()}.
	 *
	 * @param array $admin_messages An array of admin messages: [] => array( 'message' => 'a HTML message', 'type' =>  a constant of WPVGW_ErrorType).
	 */
	public static function render_admin_messages( array $admin_messages ) {
		foreach ( $admin_messages as $adminMessage ) {
			self::render_admin_message( $adminMessage['message'], $adminMessage['type'] );
		}
	}


	/*
	/**
	 * @param string $separator
	 * @param string $connector
	 * @param array $array
	 *
	 * @return string
	 */
	/*public static function implode_keys_values( $separator, $connector, array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			$output .= $separator_internal . $key . $connector . $value;
			$separator_internal = $separator;
		}

		return $output;
	}*/

	/**
	 * Implodes the keys of an array, i. e., it converts an array to a string list.
	 *
	 * @param string $separator The separator of the imploded keys.
	 * @param array $array An array which keys are imploded.
	 *
	 * @return string The imploded keys of $array.
	 */
	public static function implode_keys( $separator, array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			// separator is empty at first iteration, ', key'
			$output .= $separator_internal . $key;
			// set separator
			$separator_internal = $separator;
		}

		return $output;
	}

	/**
	 * Implodes an array of keys and values to 'key1 = value1, key2 = value2, …'.
	 * This is useful for the SET part of a SQL query.
	 *
	 * @param array $array An array which keys and values are imploded.
	 *
	 * @return string A string of imploded keys and values of $array that is compatible to the SET part of SQL queries.
	 */
	public static function sql_setters( array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			// separator is empty at first iteration, ', key = value'
			$output .= $separator_internal . $key . ' = ' . self::get_format_literal( $value );
			// set separator
			$separator_internal = ', ';
		}

		return $output;
	}

	/**
	 * Implodes an array of values to 'value1, value2, …'.
	 * This is useful for the VALUES part of a SQL query.
	 *
	 * @param array $array An array which values are imploded.
	 *
	 * @return string A string of imploded values of $array that is compatible to the VALUES part of SQL queries.
	 */
	public static function sql_values( array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $value ) {
			// separator is empty at first iteration, ', value'
			$output .= $separator_internal . self::get_format_literal( $value );
			// set separator
			$separator_internal = ', ';
		}

		return $output;
	}

	/**
	 * Implodes an array of values to 'key1 = VALUES(key1), key2 = VALUES(key2), …'.
	 * This is useful for the ON DUPLICATE KEY UPDATE part of a SQL query.
	 *
	 * @param array $array An array which keys are imploded.
	 *
	 * @return string A string of imploded keys of $array that is compatible to the ON DUPLICATE KEY UPDATE part of SQL queries.
	 */
	public static function sql_columns_on_duplicate( array $array ) {
		$separator_internal = '';
		$output = '';

		foreach ( $array as $key => $value ) {
			// separator is empty at first iteration, ', key = VALUES(key)'
			$output .= $separator_internal . $key . ' = VALUES(' . $key . ')';
			// set separator
			$separator_internal = ', ';
		}

		return $output;
	}

	/**
	 * Implodes an array of values to 'column1 relation1 format_literal_of_value1 $logical_operator column1 relation1 format_literal_of_value2 …'.
	 * This is useful for the WHERE part of a SQL query.
	 *
	 * @param string $logical_operator A logical SQL operator that combines the elements of $array, e. g., 'OR', 'AND'.
	 * @param array $array An array which values are imploded to where statements: array(0 => column_name, 1 => relation, 2 => value)
	 *
	 * @return string A string of imploded keys of $array that is compatible to the WHERE part of SQL queries.
	 */
	public static function sql_where_logic( $logical_operator, array $array ) {
		$logical_operator = ' ' . $logical_operator . ' ';
		$separator_internal = '';
		$output = '';

		foreach ( $array as $value ) {
			// separator is empty at first iteration, 'logical_operator column relation format_literal_of_value'
			$output .= $separator_internal . $value[0] . ' ' . $value[1] . ' ' . self::get_format_literal( $value[2] );
			// set separator
			$separator_internal = $logical_operator;
		}

		return $output;
	}


	/**
	 * Throws an exception containing the last database error message of {@link $wpdb}.
	 *
	 * @throws Exception Always throws an exception containing the last database error message of {@link $wpdb}.
	 */
	public static function throw_database_exception() {
		/** @var wpdb $wpdb */
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

	/**
	 * Throws an exception if a regex function result was false, i. e., failed.
	 *
	 * @param mixed $regex_result The result of a PHP regex function.
	 *
	 * @return mixed The value of $regex_result if $regex_result is not false.
	 * @throws Exception Thrown if $regex_result is false.
	 */
	public static function validate_regex_result( $regex_result ) {
		if ( $regex_result === false )
			throw new Exception( __( 'Ein regulärer Ausdruck ist ungültig. Bitte wenden Sie sich an die VG-WORT-Plugin-Entwickler.' ), WPVGW_TEXT_DOMAIN );

		return $regex_result;
	}

	/**
	 * Always throws an exception. Used if a result is no known, e. g., for switch statements.
	 *
	 * @throws Exception Thrown for unknown result.
	 */
	public static function throw_unknown_result_exception() {
		throw new Exception( 'Unknown result.' );
	}

	/**
	 * Removes a specified prefix of a given text if found.
	 *
	 * @param string $text A text.
	 * @param string $prefix A prefix.
	 * @param bool|null $found Returns true if the prefix was found, otherwise false.
	 *
	 * @return string The text without the prefix.
	 */
	public static function remove_prefix( $text, $prefix, &$found = null ) {
		$found = false;

		// try to find prefix
		if ( 0 === stripos( $text, $prefix ) ) {
			// prefix found
			$found = true;
			// get text without prefix
			$text = substr( $text, strlen( $prefix ) );
		}

		return $text;
	}

	/**
	 * Removes a specified suffix of a given text if found.
	 *
	 * @param string $text A text.
	 * @param string $suffix A suffix.
	 *
	 * @return string The text without the suffix.
	 */
	public static function remove_suffix( $text, $suffix ) {
		$textLength = strlen( $text );
		$suffixLength = strlen( $suffix );

		if ( $suffixLength > $textLength )
			return $text;

		// try to find the suffix
		if ( substr_compare( $text, $suffix, -$suffixLength, $textLength, true ) === 0 )
			// get text without suffix
			return substr( $text, 0, -$suffixLength );

		return $text;
	}

	/**
	 * Checks if one array is contained in another one, i. e., if each key and value of $array1 is contained in $array2 (but not the other way round).
	 *
	 * @param array $array1 The first array.
	 * @param array $array2 The second array.
	 *
	 * @return bool True if the $array1 is contained in $array2.
	 */
	public static function array_contains( array $array1, array $array2 ) {
		// iterate array 1
		foreach ( $array1 as $key => $value ) {
			// key exist in array 2
			if ( array_key_exists( $key, $array2 ) )
				// value of key in array 1 is different to value of key in array 2
				if ( $array1[$key] !== $array2[$key] )
					return false;
		}

		return true;
	}

	/**
	 * Returns the format literal for a specified value (e. g. %d for an int value).
	 *
	 * @param mixed $value An arbitrary value.
	 *
	 * @return string The format literal (%s, %d etc.).
	 */
	public static function get_format_literal( $value ) {
		if ( is_int( $value ) )
			return '%d';

		if ( is_float( $value ) )
			return '%f';

		return '%s';
	}

	/**
	 * Works like (and uses) {@see wpdb::prepare}, but handles null values properly.
	 *
	 * @param string $query A SQL query with format literals.
	 * @param array|mixed $args The arguments that will replace the literals in the SQL query.
	 *
	 * @return false|null|string The same like {@link $wpdb->prepare()}.
	 */
	public static function prepare_with_null( $query, $args ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		if ( $query === '' )
			return '';

		$arguments = func_get_args();
		array_shift( $arguments );
		// If args were passed as an array (as in vsprintf), move them up
		if ( isset( $arguments[0] ) && is_array( $arguments[0] ) )
			$arguments = $arguments[0];

		//$argumentCount = count( $arguments );
		$argumentCounter = count( $arguments ) - 1;

		$argumentsWithoutNull = array();
		$queryWithNull = '';

		// find each format literal that needs translation and replace it by its translation
		// respects the escaping
		// iterate $query from ending to beginning
		for ( $i = strlen( $query ) - 1; $i > -1; $i-- ) {

			$addCurrentCharToResult = true;
			$currentChar = $query[$i];

			// test if current char is format literal that needs translation
			// %d, %f, and %s
			if ( $currentChar == 's' || $currentChar == 'd' || $currentChar == 'f' ) {
				// counts the slashes (the escape char) in front of the current char
				$percentCounter = 0;

				// count all percent chars left-hand side of the current char
				for ( $j = $i - 1; $j > -1; $j-- ) {
					if ( $query[$j] == '%' )
						$percentCounter++;
					else
						break;
				}

				// did we find a percent char?
				if ( $percentCounter > 0 ) {
					// current char is escaped
					if ( $percentCounter % 2 == 0 )
						// just add the current char to the result query string
						$queryWithNull = $currentChar . $queryWithNull;
					else { // current char is not escaped
						// if $argumentCounter is valid
						if ( $argumentCounter > -1 ) {
							// get current argument
							$currentArgument = $arguments[$argumentCounter];
							// if current argument is null
							if ( $currentArgument === null ) {
								// add sql NULL to the result query string
								$queryWithNull = 'NULL' . $queryWithNull;
								// jump over the found percent chars
								$i = $i - $percentCounter;
								$addCurrentCharToResult = false;
							}
							else
								// add current argument to $argumentsWithoutNull; we do not add if $argumentsWithoutNull is null
								array_unshift( $argumentsWithoutNull, $currentArgument = $arguments[$argumentCounter] );
						}

						// next argument index
						$argumentCounter--;
					}
				}
			}

			if ( $addCurrentCharToResult )
				// just add the current char to the result query string
				$queryWithNull = $currentChar . $queryWithNull;
		}

		if ( count( $argumentsWithoutNull ) == 0 )
			// no need to execute a "normal" prepare because there is nothing to replace
			return $queryWithNull;
		else
			// execute "normal" prepare
			return $wpdb->prepare( $queryWithNull, $argumentsWithoutNull );
	}


	/**
	 * Fetches all user ids from database that have one of the specified WordPress user roles.
	 * The users are obtained for the current blog id.
	 *
	 * @param string[] $roles An array of WordPress user roles.
	 *
	 * @throws Exception Thrown if a database error occurred.
	 * @return int[] An array of user ids that have one of the specified roles.
	 */
	public static function get_user_ids_from_db( array $roles ) {
		// TODO: We should use get_users(), but it allows to filter by role only at the moment.

		/** @var wpdb $wpdb */
		global $wpdb;

		// get current blog id
		$blogId = get_current_blog_id();
		// get name of the meta key
		$metaKey = $wpdb->get_blog_prefix( $blogId ) . 'capabilities';

		// array of replacements in the SQL query (replaces format literals)
		$replacements = array( $metaKey );

		// need for the SQL WHERE part
		$whereArray = array();

		// iterate user roles
		foreach ( $roles as $role ) {
			// add percentages for SQL like relation, e. g., %author%
			$role = '%' . $role . '%';
			// add to where array; meta value like role name
			$whereArray[] = array( 'mt.meta_value', 'like', $role );
			$replacements[] = $role;
		}

		// create SQL WHERE
		$whereSql = WPVGW_Helper::sql_where_logic( 'OR', $whereArray );

		// retrieve user ids from database
		$usersInDb = $wpdb->get_col( WPVGW_Helper::prepare_with_null(
				"SELECT DISTINCT u.ID FROM {$wpdb->users} AS u INNER JOIN {$wpdb->usermeta} AS mt ON u.ID = mt.user_id WHERE mt.meta_key = %s AND ($whereSql)",
				$replacements
			)
		);

		if ( $wpdb->last_error !== '' )
			WPVGW_Helper::throw_database_exception();

		$users = array();

		// cast string user ids from database to integer ids
		foreach ( $usersInDb as $userInDb ) {
			$users[] = (int)$userInDb;
		}


		return $users;
	}

}


/**
 * Holds constants of error types.
 */
class WPVGW_ErrorType {
	/**
	 * Constant that indicates an error.
	 */
	const Error = 0;
	/**
	 * Constant that indicates an update.
	 */
	const Update = 1;
}