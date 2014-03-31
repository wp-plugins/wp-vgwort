<?php

// TODO: Wozu wird das hier benötigt?
if ( file_exists( $_SERVER["DOCUMENT_ROOT"] . 'wp-load.php' ) ) {
	include_once( $_SERVER["DOCUMENT_ROOT"] . 'wp-load.php' );
}

// TODO: Zwei mal der gleich Code (siehe Code zuvor)?
if ( file_exists( $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php' ) ) {
	include_once( $_SERVER["DOCUMENT_ROOT"] . '/wp-load.php' );
}

global $current_user;
get_currentuserinfo();

$accessArray = array( 'administrator' );
if ( in_array( $current_user->roles[0], $accessArray ) ) {

	$doAction = $_GET['action'];
	if ( isset( $_POST['action'] ) )
		$doAction = $_POST['action'];

		
	$exportDocument = '';
		
	switch ( $doAction ) {

		case"export":

			global $wpdb;

			
			$exportResult = $wpdb->get_results( $wpdb->prepare( 'SELECT WPP.ID , WPP.post_title, WPPM.meta_value  FROM ' . $wpdb->posts . ' WPP INNER JOIN ' . $wpdb->postmeta . ' WPPM ON WPP.ID = WPPM.post_id WHERE WPPM.meta_key  = %s', WP_VGWORT::get_instance()->get_vg_wort_meta() ), ARRAY_A );
			foreach ( $exportResult as &$result ) {
				$result['link'] = get_permalink( $result['ID'] );
				$exportDocument .= implode( ";", $result ) . "\n";
			}

			break;
			
			default:
	}
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private", false);
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"report.csv\";" );
header("Content-Transfer-Encoding: binary");

echo $exportDocument;

die();