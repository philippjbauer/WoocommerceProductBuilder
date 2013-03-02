<?php

/**
 * Install the product builder page.
 *
 * @access public
 * @return void
 */
function install_wc_product_builder_page() {
	global $wpdb;
	$mix_page_found = $wpdb->get_var( "SELECT ID FROM $wpdb->posts WHERE post_name = 'product-builder' LIMIT 1" );	// check if builder page already exists
	if ( $mix_page_found == null ) {
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
		$int_page_id = wp_insert_post( $arr_page_data );
		
		update_option( 'woocommerce_product_builder_page_id', $int_page_id );
	}
}

/**
 * WooCommerce Product Builder Install Routine.
 *
 * @access public
 * @return void
 */
function install_wc_product_builder() {
	global $wcpb;

	// Install product page if it doesn't already exist
	install_wc_product_builder_page();
	
	// Install folder for Product Builder exports
	$arr_upload_dir =  wp_upload_dir();
	$str_export_url = $arr_upload_dir['basedir'] . '/wcpb_exports';

	if ( wp_mkdir_p( $str_export_url ) && ! file_exists( $str_export_url.'/.htaccess' ) ) {
		if ( $fh = @fopen( $str_export_url . '/.htaccess', 'w' ) ) {
			fwrite($fh, 'deny from all');
			fclose($fh);
		}
	}
	
	// update version
	update_option( 'wcpb_version', $wcpb->str_version );
}
 
?>