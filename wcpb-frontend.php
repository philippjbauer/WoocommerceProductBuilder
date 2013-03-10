<?php
/**
 * WooCommerce Product Builder Stylesheets from CSS directory
 *
 * @author	Philipp Bauer
 * @version	0.1
 */

/**
 * Register and enqueue stylesheet from css directory.
 *
 * @access public
 * @return void
 */
function add_stylesheets() {
    wp_register_style( 'wcpb-main', plugins_url('assets/css/main.css', __FILE__ ) );
    wp_enqueue_style( 'wcpb-main' );
}

/**
 * Add stylesheet function to WP script queue
 */
add_action( 'wp_enqueue_scripts', 'add_stylesheets' );
?>