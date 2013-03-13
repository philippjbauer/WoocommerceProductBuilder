<?php
/**
 * WooCommerce Product Builder Backend Functions
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.6
 */

/**
* WC_Product_Builder Backend Functions
*/
class WC_Product_Builder_Backend extends WC_Product_Builder {
	
	function __construct() 	{
		$this->init();
	}

	/**
	 * Initialize backend functions.
	 * @return void
	 */
	public function init() {
		$this->settings_refresh();
		add_action( 'admin_print_styles', array( &$this, 'add_stylesheets' ) );	// Add stylesheet function to WP script queue
	}

	/**
	 * Fuzzy Key Search.
	 * Searches for a key in an array that does not need to exactly match the $needle
	 * @param  array $haystack
	 * @param  string $needle
	 * @return boolean
	 */
	public function fuzzy_key_search( $haystack, $needle ) {
		foreach ( $haystack as $key => $value ) {
			if ( false !== strpos( $key, $needle ) ) {
				return true;
				exit;
			}
		}
		return false;
	}

	/**
	 * Update Settings in Database.
	 * @param  array $array
	 * @return void
	 */
	public function settings_update_db( $array ) {
		$arr_settings = get_option( 'wcpb_settings' );
		if ( ! is_array( $arr_settings ) )
			$arr_settings = array();
		if ( is_array( $array ) ) {
			$arr_updates = array_merge( $arr_settings, $array );
			if ( $arr_settings != $arr_updates )
				if ( ! update_option( 'wcpb_settings', $arr_updates ) )
					die( _e( "Settings couldn't be updated! Update to DB failed.", 'wcpb' ) );
		}
		else die( _e( "Settings couldn't be updated! No array given for settings_update_db.", 'wcpb' ) );
	}

	/**
	 * Register and enqueue stylesheet from css directory.
	 * @return void
	 */
	public function add_stylesheets() {
	    wp_register_style( 'wcpb-admin', plugins_url( 'assets/css/admin.css', __FILE__ ) );
	    wp_enqueue_style( 'wcpb-admin' );
	}

}

$GLOBALS['wcpb_backend'] = new WC_Product_Builder_Backend();

?>