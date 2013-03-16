<?php
/**
 * WooCommerce Product Builder Backend Functions
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
 */

/**
* WC_Product_Builder Backend Functions
*/
class WC_Product_Builder_Backend extends WC_Product_Builder {

	/**
	 * Constructor.
	 */
	public function __construct() 	{
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
	 * Update Settings in Database.
	 * @param  array $array
	 * @return void
	 */
	public function settings_update( $array ) {
		$arr_settings = get_option( $this->get_settings_option_name() );
		if ( ! is_array( $arr_settings ) )
			$arr_settings = array();
		if ( is_array( $array ) ) {
			$arr_updates = array_merge( $arr_settings, $array );
			if ( $arr_settings != $arr_updates )
				if ( ! update_option( $this->get_settings_option_name(), $arr_updates ) )
					die( _e( "Settings couldn't be updated! Update to DB failed.", 'wcpb' ) );
		}
		else die( _e( "Settings couldn't be updated! No array given for settings_update.", 'wcpb' ) );
	}

	/**
	 * Delete Settings from Database.
	 * @return boolean
	 */
	public function settings_delete() {
		return delete_option( $this->get_settings_option_name() );
	}
		
	/**
	 * Returns option category order.
	 * @return array
	 */
	public function optioncat_order() {
		global $wpdb;
		$arr_optioncat_order = $wpdb->get_results("SELECT t.slug
											FROM wp_terms AS t
											INNER JOIN wp_term_taxonomy AS tt
											ON t.term_id = tt.term_id
											LEFT JOIN wp_woocommerce_termmeta AS tm
											ON (t.term_id = tm.woocommerce_term_id AND tm.meta_key = 'order')
											WHERE tt.taxonomy IN ('product_cat')
											AND tt.parent != 0
											ORDER BY CAST(tm.meta_value AS SIGNED) ASC, t.name ASC", ARRAY_A);
		return $arr_optioncat_order;
	}

	/**
	 * Returns optioncat titles in WooCommerce order (see: WooCommerce -> Products / Categories).
	 * @param  array $arr_optioncat_titles
	 * @return array
	 */
	public function optioncat_sort( $arr_optioncat_titles ) {
		$arr_temp = array();
		foreach ( $this->optioncat_order() as $arr_optioncat )
			$arr_temp[$arr_optioncat['slug']] = $arr_optioncat_titles[$arr_optioncat['slug']];
		return $arr_temp;
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