<?php
header("content-type: text/csv");
header("content-disposition: attachment; filename=\"export.csv\"");

if( file_exists( $_SERVER["DOCUMENT_ROOT"] . 'wp-blog-header.php' ) ) {
	include_once( $_SERVER["DOCUMENT_ROOT"] . 'wp-blog-header.php' );
}

if( file_exists( $_SERVER["DOCUMENT_ROOT"] . '/wp-blog-header.php' ) ) {
	include_once( $_SERVER["DOCUMENT_ROOT"] . '/wp-blog-header.php' );
}

global $current_user;
get_currentuserinfo();

$accessArray = array( 'administrator' );
if( in_array ( $current_user->roles[0], $accessArray ) ) {

	$doAction = $_GET['action'];
	if( isset( $_POST['action'] ) )
		$doAction = $_POST['action'];

	switch( $doAction ) {

		case"export":

			global $wpdb;

			$exportDocument = '';
			$exportResult = $wpdb->get_results( $wpdb->prepare( 'SELECT WPP.ID , WPP.post_title, WPPM.meta_value  FROM ' . $wpdb->posts . ' WPP INNER JOIN ' . $wpdb->postmeta . ' WPPM ON WPP.ID = WPPM.post_id WHERE WPPM.meta_key  = %s', WP_VGWORT::get_instance()->get_vg_wort_meta() ), ARRAY_A );
			foreach( $exportResult as &$result ) {
				$result['link'] = get_permalink( $result['ID'] );
				$exportDocument .= implode( ";", $result ) . "\n";
			}

			echo $exportDocument;

			break;
	}
}