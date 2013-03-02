<?php

/**
 *	Get product builder from template and return it.
 *
 * @access public
 * @param array $attr
 * @return string
 */
function get_wc_product_builder_page( $attr ) {
	ob_start();
	include( 'templates/wcpb-builder-page.php' );
	return ob_get_clean();
}

/**
 * Shortcode creation.
 */
add_shortcode( 'wc_product_builder_page', 'get_wc_product_builder_page' );

?>