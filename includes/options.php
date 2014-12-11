<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: 2014 Ronny Harbich
 * License: GPLv2 or later
 */


/**
 * Handles plugin options and settings. This class is a singleton.
 */
class WPVGW_Options {

	/**
	 * @var string
	 */
	private static $allowedPostTypes = 'allowed_post_types';
	/**
	 * @var string
	 */
	private static $removedPostTypes = 'removed_post_types';
	/**
	 * @var string
	 */
	private static $outputFormat = 'output_format';
	/**
	 * @var string
	 */
	private static $defaultServer = 'default_server';
	/**
	 * @var string
	 */
	private static $metaName = 'meta_name';
	/**
	 * @var string
	 */
	private static $vgWortMinimumCharacterCount = 'vg_wort_minimum_character_count';
	/**
	 * @var string
	 */
	private static $numberOfMarkersPerPage = 'number_of_markers_per_page';
	/**
	 * @var string
	 */
	private static $removeDataOnUninstall = 'remove_data_on_uninstall';
	/**
	 * @var string
	 */
	private static $exportCsvOutputHeadlines = 'export_csv_output_headlines';
	/**
	 * @var string
	 */
	private static $exportCsvDelimiter = 'export_csv_delimiter';
	/**
	 * @var string
	 */
	private static $exportCsvEnclosure = 'export_csv_enclosure';
	/**
	 * @var string
	 */
	private static $importFromPostRegex = 'import_from_post_regex';
	/**
	 * @var string
	 */
	private static $importIsAuthorCsv = 'import_is_author_csv';
	/**
	 * @var string
	 */
	private static $privacyHideWarning = 'privacy_hide_warning';
	/**
	 * @var string
	 */
	private static $showOtherActiveVgWortPluginsWarning = 'show_other_active_vg_wort_plugins_warning';
	/**
	 * @var string
	 */
	private static $operationPostCharacterCountRecalculationsNecessary = 'operations_post_character_count_recalculations_necessary';
	/**
	 * @var string
	 */
	private static $operationMaxExecutionTime = 'operations_max_execution_time';
	/**
	 * @var string
	 */
	private static $operationOldPluginImportNecessary = 'operation_old_plugin_import_necessary';
	/**
	 * @var string
	 */
	private static $doShortcodesForCharacterCountCalculation = 'do_shortcodes_for_character_count_calculation';
	/**
	 * @var string
	 */
	private static $postViewAutoMarker = 'post_view_auto_marker';


	/**
	 * @var WPVGW_Options Holds the unique instance of this class.
	 */
	private static $instance = null;

	/**
	 * @var array|null Array of options. Valid keys are the constants of {@link WPVGW_Options}.
	 */
	private $options = null;

	/**
	 * @var array|null Array of default options. Valid keys are the constants of {@link WPVGW_Options}.
	 */
	private $defaultOptions = null;

	/**
	 * @var bool Indicates whether an option was changed.
	 */
	private $optionsChanged = false;

	/**
	 * @var string The name of this option for the WordPress database.
	 */
	private $optionDBSlug = null;


	/**
	 * @var string[] The allowed WordPress user roles.
	 */
	private $allowedUserRoles = array( 'contributor', 'author', 'editor', 'administrator' );


	/**
	 * Return an instance of this class.
	 *
	 * @return WPVGW_Options
	 */
	public static function get_instance() {

		// if the single instance hasn't been set, set it now.
		if ( self::$instance === null ) {
			self::$instance = new WPVGW_Options();
		}

		return self::$instance;
	}


	/**
	 * Initialize the plugin by setting localization, filters, and administration functions.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
	}

	/**
	 * Initialize the options. The options will be initialized only once (multiple calls have no effect).
	 *
	 * @param string $option_db_slug The name of this option for the WordPress database.
	 */
	public function init( $option_db_slug ) {
		// options already initialized?
		if ( $this->defaultOptions !== null )
			return;

		// set default values of the options
		$this->defaultOptions = array(
			self::$allowedPostTypes                                   => array( 'post', 'page' ),
			self::$removedPostTypes                                   => array(),
			self::$outputFormat                                       => '<img src="http://%1$s/%2$s" width="1" height="1" alt="" style="display:none" />',
			self::$defaultServer                                      => 'vg02.met.vgwort.de/na',
			self::$metaName                                           => 'wp_vgwortmarke',
			self::$vgWortMinimumCharacterCount                        => 1800,
			self::$numberOfMarkersPerPage                             => 10,
			self::$removeDataOnUninstall                              => false,
			self::$exportCsvOutputHeadlines                           => true,
			self::$exportCsvDelimiter                                 => ';',
			self::$exportCsvEnclosure                                 => '"',
			self::$importFromPostRegex                                => '%<img.*?src\s*=\s*"http://vg[0-9]+\.met\.vgwort.de/na/[a-z0-9]+".*?>%si',
			self::$importIsAuthorCsv                                  => true,
			self::$privacyHideWarning                                 => false,
			self::$showOtherActiveVgWortPluginsWarning                => true,
			self::$operationPostCharacterCountRecalculationsNecessary => false,
			self::$operationOldPluginImportNecessary                  => false,
			self::$operationMaxExecutionTime                          => 300, // 5 minutes
			self::$doShortcodesForCharacterCountCalculation           => false,
			self::$postViewAutoMarker                                 => true,
		);

		// get options from WordPress database
		$options = get_option( $option_db_slug, array() );

		if ( is_array( $options ) ) {
			// obtain only valid options (key and type of value must be correct)
			foreach ( $this->defaultOptions as $optionKey => $defaultOption ) {
				if ( array_key_exists( $optionKey, $options ) && gettype( $options[$optionKey] ) === gettype( $defaultOption ) )
					$this->options[$optionKey] = $options[$optionKey];
				else
					// fallback to default value
					$this->options[$optionKey] = $defaultOption;
			}
		}
		else
			// fallback to default options
			$this->options = $this->defaultOptions;

		$this->optionDBSlug = $option_db_slug;
	}


	/**
	 * Store the options in the WordPress database.
	 */
	public function store_in_db() {
		if ( $this->optionsChanged )
			update_option( $this->optionDBSlug, $this->options );
	}


	/**
	 * Gets the user roles that are allowed to work with the plugin.
	 *
	 * @return string[] An array of user roles.
	 */
	public function get_allowed_user_roles() {
		return $this->allowedUserRoles;
	}


	/**
	 * Sets the custom post types that are allowed to work with the plugin.
	 *
	 * @param string[] $value An array of custom post type strings.
	 */
	public function set_allowed_post_types( array $value ) {
		if ( $this->options[self::$allowedPostTypes] !== $value ) {
			$this->options[self::$allowedPostTypes] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the custom post types that are allowed to work with the plugin.
	 *
	 * @return string[] An array of custom post types strings.
	 */
	public function get_allowed_post_types() {
		return $this->options[self::$allowedPostTypes];
	}

	/**
	 * Gets the default custom post types that are allowed to work with the plugin.
	 *
	 * @return string[] An array of custom post types strings.
	 */
	public function default_allowed_post_types() {
		return $this->defaultOptions[self::$allowedPostTypes];
	}


	/**
	 * Sets the removed WordPress post types, i. e., post types that were set but are not possible any more or just now.
	 *
	 * @param string[] $value An array of removed custom post type strings.
	 */
	public function set_removed_post_types( array $value ) {
		if ( $this->options[self::$removedPostTypes] !== $value ) {
			$this->options[self::$removedPostTypes] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the removed WordPress post types, i. e., post types that were set but are not possible any more or just now.
	 *
	 * @return string[] An array of removed custom post types strings.
	 */
	public function get_removed_post_types() {
		return $this->options[self::$removedPostTypes];
	}

	/**
	 * Gets the removed WordPress post types, i. e., post types that were set but are not possible any more or just now.
	 *
	 * @return string[] An array of removed custom post types strings.
	 */
	public function default_removed_post_types() {
		return $this->defaultOptions[self::$removedPostTypes];
	}


	/**
	 * Sets the name of the default VG WORT server.
	 *
	 * @param string $value The name of the default VG WORT server.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_default_server( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		if ( $this->options[self::$defaultServer] !== $value ) {
			$this->options[self::$defaultServer] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the name of the default VG WORT server.
	 *
	 * @return string The name of the default VG WORT server.
	 */
	public function get_default_server() {
		return $this->options[self::$defaultServer];
	}

	/**
	 * Gets the default name of the default VG WORT server.
	 *
	 * @return string The default name of the default VG WORT server.
	 */
	public function default_default_server() {
		return $this->defaultOptions[self::$defaultServer];
	}


	/**
	 * Sets the meta name. This is need to retrieve options for old versions of the plugin.
	 *
	 * @param string $value The meta name. Must not be empty or whitespaces only.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_meta_name( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		// remove whitespace form beginning and end of value
		$value = trim( $value );

		if ( $value === '' )
			throw new Exception( 'Value must not be empty or whitespaces only.' );

		if ( $this->options[self::$metaName] !== $value ) {
			$this->options[self::$metaName] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the meta name. This is need to retrieve options for old versions of the plugin.
	 *
	 * @return string The meta name.
	 */
	public function get_meta_name() {
		return $this->options[self::$metaName];
	}

	/**
	 * Gets the default meta name. This is need to retrieve options for old versions of the plugin.
	 *
	 * @return string The default meta name.
	 */
	public function default_meta_name() {
		return $this->defaultOptions[self::$metaName];
	}


	/**
	 * Sets the output format for a VG WORT marker.
	 *
	 * @param string $value The output format, i. e., a string that contains %1$s and %2$s that will be replaced by server and public marker.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_output_format( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		if ( $this->options[self::$outputFormat] !== $value ) {
			$this->options[self::$outputFormat] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the output format for a VG WORT marker.
	 *
	 * @return string The output format, i. e., a string that contains %1$s and %2$s that will be replaced by server and public marker.
	 */
	public function get_output_format() {
		return $this->options[self::$outputFormat];
	}

	/**
	 * Gets the default output format for a VG WORT marker.
	 *
	 * @return string The default output format, i. e., a string that contains %1$s and %2$s that will be replaced by server and public marker.
	 */
	public function default_output_format() {
		return $this->defaultOptions[self::$outputFormat];
	}


	/**
	 * Sets the minimum number of characters of an article that is necessary to participate VG WORT.
	 *
	 * @param int $value The minimum number of characters, i. e., a non-negative integer.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_vg_wort_minimum_character_count( $value ) {
		if ( !is_int( $value ) && $value < 0 )
			throw new Exception( 'Value is not a non-negative integer.' );

		if ( $this->options[self::$vgWortMinimumCharacterCount] !== $value ) {
			$this->options[self::$vgWortMinimumCharacterCount] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the minimum number of characters of an article that is necessary to participate VG WORT.
	 *
	 * @return int The minimum number of characters, i. e., a non-negative integer.
	 */
	public function get_vg_wort_minimum_character_count() {
		return $this->options[self::$vgWortMinimumCharacterCount];
	}

	/**
	 * Gets the default minimum number of characters of an article that is necessary to participate VG WORT.
	 *
	 * @return int The default minimum number of characters, i. e., a non-negative integer.
	 */
	public function default_vg_wort_minimum_character_count() {
		return $this->defaultOptions[self::$vgWortMinimumCharacterCount];
	}


	/**
	 * Sets the number of markers that will be shown per page in the marker overview.
	 *
	 * @param int $value The number of markers per page, i. e., an negative greater than 0.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_number_of_markers_per_page( $value ) {
		if ( !is_int( $value ) && $value < 1 )
			throw new Exception( 'Value is not an integer greater than 0.' );

		if ( $this->options[self::$numberOfMarkersPerPage] !== $value ) {
			$this->options[self::$numberOfMarkersPerPage] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the number of markers that will be shown per page in the marker overview.
	 *
	 * @return int The number of markers per page, i. e., an negative greater than 0.
	 */
	public function get_number_of_markers_per_page() {
		return $this->options[self::$numberOfMarkersPerPage];
	}

	/**
	 * Gets the default number of markers that will be shown per page in the marker overview..
	 *
	 * @return int The default number of markers per page, i. e., an negative greater than 0.
	 */
	public function default_number_of_markers_per_page() {
		return $this->defaultOptions[self::$numberOfMarkersPerPage];
	}


	/**
	 * Sets whether the plugin data (markers, options etc.) should be removed on uninstall.
	 *
	 * @param bool $value If true, the plugin data will be removed on uninstall, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_remove_data_on_uninstall( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$removeDataOnUninstall] !== $value ) {
			$this->options[self::$removeDataOnUninstall] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether the plugin data (markers, options etc.) should be removed on uninstall.
	 *
	 * @return bool If true, the plugin data will be removed on uninstall, otherwise not.
	 */
	public function get_remove_data_on_uninstall() {
		return $this->options[self::$removeDataOnUninstall];
	}

	/**
	 * Gets the default whether the plugin data (markers, options etc.) should be removed on uninstall.
	 *
	 * @return bool If true, the plugin data will be removed on uninstall, otherwise not.
	 */
	public function default_remove_data_on_uninstall() {
		return $this->defaultOptions[self::$removeDataOnUninstall];
	}


	/**
	 * Sets whether to output the headline in CSV export.
	 *
	 * @param bool $value If true, headlines will be output, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_export_csv_output_headlines( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$exportCsvOutputHeadlines] !== $value ) {
			$this->options[self::$exportCsvOutputHeadlines] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether to output the headline in CSV export.
	 *
	 * @return bool If true, headlines will be output, otherwise not.
	 */
	public function get_export_csv_output_headlines() {
		return $this->options[self::$exportCsvOutputHeadlines];
	}

	/**
	 * Gets the default whether to output the headline in CSV export.
	 *
	 * @return bool If true, headlines will be output, otherwise not.
	 */
	public function default_export_csv_output_headlines() {
		return $this->defaultOptions[self::$exportCsvOutputHeadlines];
	}


	/**
	 * Gets the delimiter for CSV export.
	 * The delimiter separates the columns.
	 *
	 * @return string A string of length exactly 1 (byte length).
	 */
	public function get_export_csv_delimiter() {
		return $this->default_export_csv_delimiter();
	}

	/**
	 * Gets the default delimiter for CSV export.
	 * The delimiter separates the columns.
	 *
	 * @return string A string of length exactly 1 (byte length).
	 */
	public function default_export_csv_delimiter() {
		return $this->defaultOptions[self::$exportCsvDelimiter];
	}


	/**
	 * Gets the enclosure for CSV export.
	 * The enclosure encapsulates a data in a column, e. g., "Some text with whitespaces" (" is the enclosure).
	 *
	 * @return string A string of length exactly 1 (byte length).
	 */
	public function get_export_csv_enclosure() {
		return $this->default_export_csv_enclosure();
	}

	/**
	 * Gets the default enclosure for CSV export.
	 * The enclosure encapsulates a data in a column, e. g., "Some text with whitespaces" (" is the enclosure).
	 *
	 * @return string A string of length exactly 1 (byte length).
	 */
	public function default_export_csv_enclosure() {
		return $this->defaultOptions[self::$exportCsvEnclosure];
	}


	/**
	 * Sets the Regular Expression (Regex) to match old markers in posts.
	 *
	 * @param string $value A Regex to match markers.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_import_from_post_regex( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		// test if value is a valid regex (suppress warning with @)
		if ( @preg_match( $value, '' ) === false )
			throw new Exception( 'Value has to be a valid Regular Expression.' );

		if ( $this->options[self::$importFromPostRegex] !== $value ) {
			$this->options[self::$importFromPostRegex] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the Regular Expression (Regex) to match old markers in posts.
	 *
	 * @return string A Regex to match markers.
	 */
	public function get_import_from_post_regex() {
		return $this->options[self::$importFromPostRegex];
	}

	/**
	 * Gets the default enclosure for CSV export.
	 *
	 * @return string A Regex to match markers..
	 */
	public function default_import_from_post_regex() {
		return $this->defaultOptions[self::$importFromPostRegex];
	}


	/**
	 * Sets whether CSV data is formatted for authors (otherwise for publishers) for marker import.
	 *
	 * @param bool $value If true, CSV data is formatted for authors for marker import, otherwise for publishers.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_is_author_csv( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$importIsAuthorCsv] !== $value ) {
			$this->options[self::$importIsAuthorCsv] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether CSV data is formatted for authors (otherwise for publishers) for marker import.
	 *
	 * @return bool If true, CSV data is formatted for authors for marker import, otherwise for publishers.
	 */
	public function get_is_author_csv() {
		return $this->options[self::$importIsAuthorCsv];
	}

	/**
	 * Gets the default whether CSV data is formatted for authors (otherwise for publishers) for marker import.
	 *
	 * @return bool If true, CSV data is formatted for authors for marker import, otherwise for publishers.
	 */
	public function default_is_author_csv() {
		return $this->defaultOptions[self::$importIsAuthorCsv];
	}


	/**
	 * Sets whether to hide privacy warning in admin area.
	 *
	 * @param bool $value If true, privacy warning in admin area will be hidden, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_privacy_hide_warning( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$privacyHideWarning] !== $value ) {
			$this->options[self::$privacyHideWarning] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether to hide privacy warning in admin area.
	 *
	 * @return bool If true, privacy warning in admin area will be hidden, otherwise not.
	 */
	public function get_privacy_hide_warning() {
		return $this->options[self::$privacyHideWarning];
	}

	/**
	 * Gets the default whether to hide privacy warning in admin area.
	 *
	 * @return bool If true, privacy warning in admin area will be hidden, otherwise not.
	 */
	public function default_privacy_hide_warning() {
		return $this->defaultOptions[self::$privacyHideWarning];
	}


	/**
	 * Sets whether to show other active VG WORT plugins warning in admin area.
	 *
	 * @param bool $value If true, other active VG WORT plugins warning will be shown, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_show_other_active_vg_wort_plugins_warning( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$showOtherActiveVgWortPluginsWarning] !== $value ) {
			$this->options[self::$showOtherActiveVgWortPluginsWarning] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether to show other active VG WORT plugins warning in admin area.
	 *
	 * @return bool If true, other active VG WORT plugins warning will be shown, otherwise not.
	 */
	public function get_show_other_active_vg_wort_plugins_warning() {
		return $this->options[self::$showOtherActiveVgWortPluginsWarning];
	}

	/**
	 * Gets the default whether to show other active VG WORT plugins warning in admin area.
	 *
	 * @return bool If true, other active VG WORT plugins warning will be shown, otherwise not.
	 */
	public function default_show_other_active_vg_wort_plugins_warning() {
		return $this->defaultOptions[self::$showOtherActiveVgWortPluginsWarning];
	}


	/**
	 * Sets whether post character count recalculations operations are necessary.
	 *
	 * @param bool $value If true, post character count recalculations operations are necessary, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_operations_post_character_count_recalculations_necessary( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$operationPostCharacterCountRecalculationsNecessary] !== $value ) {
			$this->options[self::$operationPostCharacterCountRecalculationsNecessary] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether post character count recalculations operations are necessary.
	 *
	 * @return bool If true, post character count recalculations operations are necessary, otherwise not.
	 */
	public function get_operations_post_character_count_recalculations_necessary() {
		return $this->options[self::$operationPostCharacterCountRecalculationsNecessary];
	}

	/**
	 * Gets the default whether post character count recalculations operations are necessary.
	 *
	 * @return bool If true, post character count recalculations operations are necessary, otherwise not.
	 */
	public function default_operations_post_character_count_recalculations_necessary() {
		return $this->defaultOptions[self::$operationPostCharacterCountRecalculationsNecessary];
	}


	/**
	 * Sets whether import markers from old plugin is necessary.
	 *
	 * @param bool $value If true, import markers from old plugin is necessary, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_operation_old_plugin_import_necessary( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$operationOldPluginImportNecessary] !== $value ) {
			$this->options[self::$operationOldPluginImportNecessary] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether import markers from old plugin is necessary.
	 *
	 * @return bool If true, import markers from old plugin is necessary, otherwise not.
	 */
	public function get_operation_old_plugin_import_necessary() {
		return $this->options[self::$operationOldPluginImportNecessary];
	}

	/**
	 * Gets the default whether import markers from old plugin is necessary.
	 *
	 * @return bool If true, import markers from old plugin is necessary, otherwise not.
	 */
	public function default_operation_old_plugin_import_necessary() {
		return $this->defaultOptions[self::$operationOldPluginImportNecessary];
	}


	/**
	 * Sets the maximum number of seconds operations can be executed.
	 *
	 * @param int $value The maximum number of seconds operations can be executed, i. e., a positive integer.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_operation_max_execution_time( $value ) {
		if ( !is_int( $value ) && $value < 1 )
			throw new Exception( 'Value is not a positive integer.' );

		if ( $this->options[self::$operationMaxExecutionTime] !== $value ) {
			$this->options[self::$operationMaxExecutionTime] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets the maximum number of seconds operations can be executed.
	 *
	 * @return int The maximum number of seconds operations can be executed, i. e., a positive integer.
	 */
	public function get_operation_max_execution_time() {
		return $this->options[self::$operationMaxExecutionTime];
	}

	/**
	 * Gets the maximum number of seconds operations can be executed.
	 *
	 * @return int The maximum number of seconds operations can be executed, i. e., a positive integer.
	 */
	public function default_operation_max_execution_time() {
		return $this->defaultOptions[self::$operationMaxExecutionTime];
	}


	/**
	 * Sets whether shortcodes will be parsed if character count is calculated.
	 *
	 * @param bool $value If true, shortcodes will be parsed if character count is calculated, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_do_shortcodes_for_character_count_calculation( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$doShortcodesForCharacterCountCalculation] !== $value ) {
			$this->options[self::$doShortcodesForCharacterCountCalculation] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether shortcodes will be parsed if character count is calculated.
	 *
	 * @return bool If true, shortcodes will be parsed if character count is calculated, otherwise not.
	 */
	public function get_do_shortcodes_for_character_count_calculation() {
		return $this->options[self::$doShortcodesForCharacterCountCalculation];
	}

	/**
	 * Gets the default whether shortcodes will be parsed if character count is calculated.
	 *
	 * @return bool If true, shortcodes will be parsed if character count is calculated, otherwise not.
	 */
	public function default_do_shortcodes_for_character_count_calculation() {
		return $this->defaultOptions[self::$doShortcodesForCharacterCountCalculation];
	}


	/**
	 * Sets whether auto marker is enabled on post view.
	 *
	 * @param bool $value If true, auto marker is enabled on post view, otherwise not.
	 *
	 * @throws Exception Thrown if $value is invalid.
	 */
	public function set_post_view_auto_marker( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$postViewAutoMarker] !== $value ) {
			$this->options[self::$postViewAutoMarker] = $value;
			$this->optionsChanged = true;
		}
	}

	/**
	 * Gets whether auto marker in enabled os post view.
	 *
	 * @return bool If true, auto marker is enabled on post view, otherwise not.
	 */
	public function get_view_auto_marker() {
		return $this->options[self::$postViewAutoMarker];
	}

	/**
	 * Gets the default whether auto marker is enabled on post view.
	 *
	 * @return bool If true, auto marker is enabled on post view, otherwise not.
	 */
	public function default_view_auto_marker() {
		return $this->defaultOptions[self::$postViewAutoMarker];
	}
}