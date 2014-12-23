<?php
/**
 * Product: Prosodia VGW OS
 * URL: http://prosodia.de/
 * Author: Ronny Harbich
 * Copyright: Ronny Harbich
 * License: GPLv2 or later
 */



class WPVGW_Options {

	
	private static $allowedPostTypes = 'allowed_post_types';
	
	private static $removedPostTypes = 'removed_post_types';
	
	private static $outputFormat = 'output_format';
	
	private static $defaultServer = 'default_server';
	
	private static $metaName = 'meta_name';
	
	private static $vgWortMinimumCharacterCount = 'vg_wort_minimum_character_count';
	
	private static $numberOfMarkersPerPage = 'number_of_markers_per_page';
	
	private static $removeDataOnUninstall = 'remove_data_on_uninstall';
	
	private static $exportCsvOutputHeadlines = 'export_csv_output_headlines';
	
	private static $exportCsvDelimiter = 'export_csv_delimiter';
	
	private static $exportCsvEnclosure = 'export_csv_enclosure';
	
	private static $importFromPostRegex = 'import_from_post_regex';
	
	private static $importIsAuthorCsv = 'import_is_author_csv';
	
	private static $privacyHideWarning = 'privacy_hide_warning';
	
	private static $showOtherActiveVgWortPluginsWarning = 'show_other_active_vg_wort_plugins_warning';
	
	private static $operationPostCharacterCountRecalculationsNecessary = 'operations_post_character_count_recalculations_necessary';
	
	private static $operationMaxExecutionTime = 'operations_max_execution_time';
	
	private static $operationOldPluginImportNecessary = 'operation_old_plugin_import_necessary';
	
	private static $doShortcodesForCharacterCountCalculation = 'do_shortcodes_for_character_count_calculation';
	
	private static $postViewAutoMarker = 'post_view_auto_marker';


	
	private static $instance = null;

	
	private $options = null;

	
	private $defaultOptions = null;

	
	private $optionsChanged = false;

	
	private $optionDBSlug = null;


	
	private $allowedUserRoles = array( 'contributor', 'author', 'editor', 'administrator' );


	
	public static function get_instance() {

		
		if ( self::$instance === null ) {
			self::$instance = new WPVGW_Options();
		}

		return self::$instance;
	}


	
	private function __construct() {
	}

	
	public function init( $option_db_slug ) {
		
		if ( $this->defaultOptions !== null )
			return;

		
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
			self::$importFromPostRegex                                => '%<img\s[^<>]*?src\s*=\s*"http://vg[0-9]+\.met\.vgwort\.de/na/[a-z0-9]+"[^<>]*?>%im',
			self::$importIsAuthorCsv                                  => true,
			self::$privacyHideWarning                                 => false,
			self::$showOtherActiveVgWortPluginsWarning                => true,
			self::$operationPostCharacterCountRecalculationsNecessary => false,
			self::$operationOldPluginImportNecessary                  => false,
			self::$operationMaxExecutionTime                          => 300, 
			self::$doShortcodesForCharacterCountCalculation           => false,
			self::$postViewAutoMarker                                 => true,
		);

		
		$options = get_option( $option_db_slug, array() );

		if ( is_array( $options ) ) {
			
			foreach ( $this->defaultOptions as $optionKey => $defaultOption ) {
				if ( array_key_exists( $optionKey, $options ) && gettype( $options[$optionKey] ) === gettype( $defaultOption ) )
					$this->options[$optionKey] = $options[$optionKey];
				else
					
					$this->options[$optionKey] = $defaultOption;
			}
		}
		else
			
			$this->options = $this->defaultOptions;

		$this->optionDBSlug = $option_db_slug;
	}


	
	public function store_in_db() {
		if ( $this->optionsChanged )
			update_option( $this->optionDBSlug, $this->options );
	}


	
	public function get_allowed_user_roles() {
		return $this->allowedUserRoles;
	}


	
	public function set_allowed_post_types( array $value ) {
		if ( $this->options[self::$allowedPostTypes] !== $value ) {
			$this->options[self::$allowedPostTypes] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_allowed_post_types() {
		return $this->options[self::$allowedPostTypes];
	}

	
	public function default_allowed_post_types() {
		return $this->defaultOptions[self::$allowedPostTypes];
	}


	
	public function set_removed_post_types( array $value ) {
		if ( $this->options[self::$removedPostTypes] !== $value ) {
			$this->options[self::$removedPostTypes] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_removed_post_types() {
		return $this->options[self::$removedPostTypes];
	}

	
	public function default_removed_post_types() {
		return $this->defaultOptions[self::$removedPostTypes];
	}


	
	public function set_default_server( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		if ( $this->options[self::$defaultServer] !== $value ) {
			$this->options[self::$defaultServer] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_default_server() {
		return $this->options[self::$defaultServer];
	}

	
	public function default_default_server() {
		return $this->defaultOptions[self::$defaultServer];
	}


	
	public function set_meta_name( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		
		$value = trim( $value );

		if ( $value === '' )
			throw new Exception( 'Value must not be empty or whitespaces only.' );

		if ( $this->options[self::$metaName] !== $value ) {
			$this->options[self::$metaName] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_meta_name() {
		return $this->options[self::$metaName];
	}

	
	public function default_meta_name() {
		return $this->defaultOptions[self::$metaName];
	}


	
	public function set_output_format( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		if ( $this->options[self::$outputFormat] !== $value ) {
			$this->options[self::$outputFormat] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_output_format() {
		return $this->options[self::$outputFormat];
	}

	
	public function default_output_format() {
		return $this->defaultOptions[self::$outputFormat];
	}


	
	public function set_vg_wort_minimum_character_count( $value ) {
		if ( !is_int( $value ) && $value < 0 )
			throw new Exception( 'Value is not a non-negative integer.' );

		if ( $this->options[self::$vgWortMinimumCharacterCount] !== $value ) {
			$this->options[self::$vgWortMinimumCharacterCount] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_vg_wort_minimum_character_count() {
		return $this->options[self::$vgWortMinimumCharacterCount];
	}

	
	public function default_vg_wort_minimum_character_count() {
		return $this->defaultOptions[self::$vgWortMinimumCharacterCount];
	}


	
	public function set_number_of_markers_per_page( $value ) {
		if ( !is_int( $value ) && $value < 1 )
			throw new Exception( 'Value is not an integer greater than 0.' );

		if ( $this->options[self::$numberOfMarkersPerPage] !== $value ) {
			$this->options[self::$numberOfMarkersPerPage] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_number_of_markers_per_page() {
		return $this->options[self::$numberOfMarkersPerPage];
	}

	
	public function default_number_of_markers_per_page() {
		return $this->defaultOptions[self::$numberOfMarkersPerPage];
	}


	
	public function set_remove_data_on_uninstall( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$removeDataOnUninstall] !== $value ) {
			$this->options[self::$removeDataOnUninstall] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_remove_data_on_uninstall() {
		return $this->options[self::$removeDataOnUninstall];
	}

	
	public function default_remove_data_on_uninstall() {
		return $this->defaultOptions[self::$removeDataOnUninstall];
	}


	
	public function set_export_csv_output_headlines( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$exportCsvOutputHeadlines] !== $value ) {
			$this->options[self::$exportCsvOutputHeadlines] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_export_csv_output_headlines() {
		return $this->options[self::$exportCsvOutputHeadlines];
	}

	
	public function default_export_csv_output_headlines() {
		return $this->defaultOptions[self::$exportCsvOutputHeadlines];
	}


	
	public function get_export_csv_delimiter() {
		return $this->default_export_csv_delimiter();
	}

	
	public function default_export_csv_delimiter() {
		return $this->defaultOptions[self::$exportCsvDelimiter];
	}


	
	public function get_export_csv_enclosure() {
		return $this->default_export_csv_enclosure();
	}

	
	public function default_export_csv_enclosure() {
		return $this->defaultOptions[self::$exportCsvEnclosure];
	}


	
	public function set_import_from_post_regex( $value ) {
		if ( !is_string( $value ) )
			throw new Exception( 'Value is not a string.' );

		
		if ( @preg_match( $value, '' ) === false )
			throw new Exception( 'Value has to be a valid Regular Expression.' );

		if ( $this->options[self::$importFromPostRegex] !== $value ) {
			$this->options[self::$importFromPostRegex] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_import_from_post_regex() {
		return $this->options[self::$importFromPostRegex];
	}

	
	public function default_import_from_post_regex() {
		return $this->defaultOptions[self::$importFromPostRegex];
	}


	
	public function set_is_author_csv( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$importIsAuthorCsv] !== $value ) {
			$this->options[self::$importIsAuthorCsv] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_is_author_csv() {
		return $this->options[self::$importIsAuthorCsv];
	}

	
	public function default_is_author_csv() {
		return $this->defaultOptions[self::$importIsAuthorCsv];
	}


	
	public function set_privacy_hide_warning( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$privacyHideWarning] !== $value ) {
			$this->options[self::$privacyHideWarning] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_privacy_hide_warning() {
		return $this->options[self::$privacyHideWarning];
	}

	
	public function default_privacy_hide_warning() {
		return $this->defaultOptions[self::$privacyHideWarning];
	}


	
	public function set_show_other_active_vg_wort_plugins_warning( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$showOtherActiveVgWortPluginsWarning] !== $value ) {
			$this->options[self::$showOtherActiveVgWortPluginsWarning] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_show_other_active_vg_wort_plugins_warning() {
		return $this->options[self::$showOtherActiveVgWortPluginsWarning];
	}

	
	public function default_show_other_active_vg_wort_plugins_warning() {
		return $this->defaultOptions[self::$showOtherActiveVgWortPluginsWarning];
	}


	
	public function set_operations_post_character_count_recalculations_necessary( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$operationPostCharacterCountRecalculationsNecessary] !== $value ) {
			$this->options[self::$operationPostCharacterCountRecalculationsNecessary] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_operations_post_character_count_recalculations_necessary() {
		return $this->options[self::$operationPostCharacterCountRecalculationsNecessary];
	}

	
	public function default_operations_post_character_count_recalculations_necessary() {
		return $this->defaultOptions[self::$operationPostCharacterCountRecalculationsNecessary];
	}


	
	public function set_operation_old_plugin_import_necessary( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$operationOldPluginImportNecessary] !== $value ) {
			$this->options[self::$operationOldPluginImportNecessary] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_operation_old_plugin_import_necessary() {
		return $this->options[self::$operationOldPluginImportNecessary];
	}

	
	public function default_operation_old_plugin_import_necessary() {
		return $this->defaultOptions[self::$operationOldPluginImportNecessary];
	}


	
	public function set_operation_max_execution_time( $value ) {
		if ( !is_int( $value ) && $value < 1 )
			throw new Exception( 'Value is not a positive integer.' );

		if ( $this->options[self::$operationMaxExecutionTime] !== $value ) {
			$this->options[self::$operationMaxExecutionTime] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_operation_max_execution_time() {
		return $this->options[self::$operationMaxExecutionTime];
	}

	
	public function default_operation_max_execution_time() {
		return $this->defaultOptions[self::$operationMaxExecutionTime];
	}


	
	public function set_do_shortcodes_for_character_count_calculation( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$doShortcodesForCharacterCountCalculation] !== $value ) {
			$this->options[self::$doShortcodesForCharacterCountCalculation] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_do_shortcodes_for_character_count_calculation() {
		return $this->options[self::$doShortcodesForCharacterCountCalculation];
	}

	
	public function default_do_shortcodes_for_character_count_calculation() {
		return $this->defaultOptions[self::$doShortcodesForCharacterCountCalculation];
	}


	
	public function set_post_view_auto_marker( $value ) {
		if ( !is_bool( $value ) )
			throw new Exception( 'Value is not a bool.' );

		if ( $this->options[self::$postViewAutoMarker] !== $value ) {
			$this->options[self::$postViewAutoMarker] = $value;
			$this->optionsChanged = true;
		}
	}

	
	public function get_view_auto_marker() {
		return $this->options[self::$postViewAutoMarker];
	}

	
	public function default_view_auto_marker() {
		return $this->defaultOptions[self::$postViewAutoMarker];
	}
}