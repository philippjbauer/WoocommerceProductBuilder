<?php
/**
 * WooCommerce Product Builder Frontend Functions
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */

/**
* WC_Product_Builder Frontend Functions
*/
class WC_Product_Builder_Frontend extends WC_Product_Builder {
	
	function __construct() {
		$this->init();
	}

	/**
	 * Initialize frontend functions.
	 * @return void
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', array( &$this, 'add_stylesheets' ) );						// Add stylesheet function to WP script queue
		add_shortcode( 'wc_product_builder_page', array( &$this, 'get_wc_product_builder_page' ) );	// Add shortcode for frontend integration
	}

	/**
	 * Register and enqueue stylesheet from css directory.
	 * @return void
	 */
	public function add_stylesheets() {
	    wp_register_style( 'wcpb-main', plugins_url('assets/css/main.css', __FILE__ ) );
	    wp_enqueue_style( 'wcpb-main' );
	}

	/**
	 *	Get product builder from template and return it.
	 *
	 * @access public
	 * @param array $attr
	 * @return string
	 */
	public function get_wc_product_builder_page() {
		$this->include_template( 'templates/wcpb-builder-page.php' );
	}

}

$GLOBALS['wcpb_frontend'] = new WC_Product_Builder_Frontend();

?>