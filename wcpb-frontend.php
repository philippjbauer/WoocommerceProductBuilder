<?php

/**
 * Include Stylesheets from CSS directory
 */
function add_stylesheets() {
	// Respects SSL, main.css is relative to the current file
    wp_register_style( 'wcpb-main', plugins_url('css/main.css', __FILE__) );
    wp_enqueue_style( 'wcpb-main' );
}

add_action( 'wp_enqueue_scripts', 'add_stylesheets' );

?>