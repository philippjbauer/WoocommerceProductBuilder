<?php
/**
 * WooCommerce Product Builder Install Routine
 * Install WCPB builder page, create export folder and update version in DB
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */

/**
 * Install the product builder page.
 * @return void
 */
function install_page() {
	global $wcpb;
	// Check if page already exists
	$mix_page = get_page_by_title( __( 'Product Builder', 'wcpb' ) );	// check if builder page already exists
	if ( null === $mix_page ) {
		// Prepare page
		$arr_page_data = array(
			'post_status' 		=> 'publish',
			'post_type' 		=> 'page',
			'post_author' 		=> 1,
			'post_name' 		=> 'product-builder',
			'post_title' 		=> __( 'Product Builder', 'wcpb' ),
			'post_content' 		=> '[wc_product_builder_page]',
			'post_parent' 		=> '',
			'comment_status' 	=> 'closed'
		);
		// Create page
		$int_page_id = wp_insert_post( $arr_page_data );
		// Update page_id
		update_option( $wcpb->get_pageid_option_name(), $int_page_id );
	}
}

/**
 * Install the product builder category.
 * @return void
 */
function install_product_cat() {
	global $wcpb;
	// check if product category exists
	$mix_term_exists = term_exists( $wcpb->get_productcat_term() );
	if ( 0 == $mix_term_exists ) {
		$mix_term_result = wp_insert_term( $wcpb->get_productcat_term(), 'product_cat', array( 'slug' => $wcpb->get_productcat_slug() ) );
		if ( is_array( $mix_term_result ) )
			update_option( $wcpb->get_productcat_term_id_option_name(), intval( $mix_term_result['term_id'] ) );
		else
			update_option( $wcpb->get_productcat_term_id_option_name(), false );
	}
}

/**
 * Install the export directory.
 * @return void
 */
function install_export_dir() {
	$arr_upload_dir =  wp_upload_dir();
	$str_export_url = $arr_upload_dir['basedir'] . '/wcpb_exports';

	if ( wp_mkdir_p( $str_export_url ) && ! file_exists( $str_export_url.'/.htaccess' ) ) {
		if ( $fh = @fopen( $str_export_url . '/.htaccess', 'w' ) ) {
			fwrite($fh, 'deny from all');
			fclose($fh);
		}
	}
}

/**
 * WooCommerce Product Builder Install Routine.
 * @access public
 * @return void
 */
function install_wc_product_builder() {
	global $wcpb;

	// Install product page
	install_page();
	// Install product category
	install_product_cat();
	// Install folder for Product Builder exports
	install_export_dir();
	
	// Update version
	update_option( $wcpb->get_version_option_name(), $wcpb->get_version() );
}
 
?>