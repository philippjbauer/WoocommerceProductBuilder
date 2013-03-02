<?php
/**
 * Plugin Name: WooCommerce Product Builder
 * Plugin URI: http://www.thegermanwebfellow.com/wp-plugins
 * Description: Lets users build their own products.
 * Version: 0.1
 * Author: Philipp Bauer
 * Author URI: http://www.thegermanwebfellow.com
 *
 * Text Domain: wcpb
 * Domain Path: /languages/
 *
 * @package WooCommerce Product Builder
 * @category Extension
 * @author Philipp Bauer
 */

/**
 * Check if WooCommerce is active
 **/
$woocommerce_active = false;
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	$woocommerce_active = true;
}

/**
 * Check if Class already exists and WooCommerce is active
 */
if ( ! class_exists( 'WC_Product_Builder' ) && $woocommerce_active ) {
	
	class WC_Product_Builder {
		
		/**
		 * @var string
		 */
		var $str_version = "0.1";
		
		/**
		 * @var array
		 */
		var $arr_session_data;
		
		/**
		 * @var int
		 */
		var $int_active_product_cat;
		
		/**
		 * @var array
		 */
		var $arr_product_option_amounts;
		
		/**
		 * @var array
		 */
		var $arr_subcat_custom_titles;
		
		/**
		 * WCPB Constructor.
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			// Execute install routine
			if ( is_admin() ) $this->install();
			// Initialize WC_Product_Builder
			add_action( 'woocommerce_init', array( &$this, 'init' ) );
		}
		
		/**
		 * Init WooCommerce Product Builder.
		 * 
		 * @access public
		 * @return void
		 */
		public function init() {
			global $woocommerce;
			
			/* SESSION ACTIONS */
			add_action( 'init', array( &$this, 'start_session', 1 ) );		// Start session if none is started yet.
			add_action( 'wp_login', array( &$this, 'destroy_session' ) );	// Destroy session on wp_login
			add_action( 'wp_logout', array( &$this, 'destroy_session' ) );	// Destroy session on wp_logout
			
			// Reserve namespace in session
			if ( ! is_array( $_SESSION['wcpb'] ) ) {
				$_SESSION['wcpb'] = array();
				$this->update_session_data();
			}
			
			/* LOCALIZATION */
			$this->load_localization();
			
			/* BACKEND ACTIONS */
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );											// Create backend menu links.
			$this->int_active_product_cat = get_option( 'wcpb_active_product_cat' );							// Save active product cat.
			$this->arr_product_option_amounts = unserialize( get_option( 'wcpb_product_option_amounts' ) ); 	// Get how many options may be chosen (per category) by the user
			$this->arr_subcat_custom_titles = unserialize( get_option( 'wcpb_subcat_custom_titles' ) ); 		// Get custom titles for product builder subcategories

			/* FRONTEND ACTIONS */
			add_action( 'wcpb_before_product_builder', array( $woocommerce, 'show_messages' ) );
			add_action( 'wcpb_before_product_builder', array( &$this, 'user_actions' ) );
			add_action( 'wcpb_before_product_builder', array( &$this, 'product_actions' ) );
			
			/* BACKEND INCLUDES */
			if ( is_admin() ) $this->admin_includes();
			
			/* FRONTEND INCLUDES */
			$this->frontend_includes();
		}
		
		/**
		 * Include frontend files.
		 *
		 * @access public
		 * @return void
		 */
		public function frontend_includes() {
			include( 'wcpb-shortcodes.php' );		// Init shortcodes
		}
		
		/**
		 * Include admin files.
		 *
		 * @access public
		 * @return void
		 */
		public function admin_includes() {
			// nothing right now
		}
		
		/**
		 * Install upon activation.
		 *
		 * Check if new version is available and install / update WooCommerce Product Builder
		 *
		 * @access public
		 * @return void
		 */
		public function install() {
			if ( get_option( 'wcpb_version' ) != $this->str_version ) {
				include( 'admin/wcpb-install.php' );	
				add_action( 'init', 'install_wc_product_builder', 1 );
			}
		}
		
		/**
		 * Start session.
		 *
		 * @access public
		 * @return void
		 */
		public function start_session() {
			if ( session_id() == "" )
				session_start();
		}
		
		/**
		 * Destroy session.
		 * 
		 * @access public
		 * @return void
		 */
		public function destroy_session() {
			if ( session_id() != "" ) {
				session_unset();	// destroys variables
				session_destroy();	// destroys session
			}
		}
		
		/**
		 * Clear WooCommerce Product Builder Session.
		 *
		 * @access public
		 * @return void
		 */
		public function clear_session() {
			if ( isset( $_SESSION['wcpb'] ) )
				unset( $_SESSION['wcpb'] );	// destroys variables from WCPB plugin only
		}
		
		/**
		 * Update Session Data.
		 *
		 * @access public
		 * @return void
		 */
		public function update_session_data() {
			if ( isset( $_SESSION['wcpb'] ) )
				$this->arr_session_data = $_SESSION['wcpb'];
		}
		
		/**
		 * Localization.
		 * 
		 * @access public
		 * @return void
		 */
		public function load_localization() {
			load_plugin_textdomain( 'wcpb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * Build the Admin Menu Entries in Wordpress backend.
		 *
		 * @access public
		 * @return void
		 */
		public function admin_menu() {
			add_menu_page( 'WooCommerce Product Builder', 'Product Builder', 'manage_woocommerce', 'wcpb-admin', array( &$this, 'admin_menu_main' ), null, 58 );
			add_submenu_page( 'wcpb-admin', 'Export Orders', 'Export Orders', 'view_woocommerce_reports', 'wcpb-export', array( &$this, 'admin_menu_export' ), null );
		}
		
		/**
		 * Admin Menu Main
		 *
		 * @access public
		 * @return void
		 */
		public function admin_menu_main() {
			if ( !current_user_can( 'manage_woocommerce_products' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			include( 'admin/wcpb-main.php' );
		}
		
		/**
		 * Admin Menu Export
		 *
		 * @access public
		 * @return void
		 */
		public function admin_menu_export() {
			if ( !current_user_can( 'manage_woocommerce_products' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			include( 'admin/wcpb-export.php' );
		}
		
		/**
		 * Handle user actions.
		 * 
		 * @access public
		 * @return void
		 */
		public function user_actions() {
		
		}
		
		/**
		 * Handle product actions.
		 * 
		 * @access public
		 * @return void
		 */
		public function product_actions() {
		
		}
		
		/**
		 * Activate the plugin.
		 * 
		 * @access public
		 * @return void
		 */
		public static function activate() {
			// do nothing
		}
		
		/**
		 * Deactivate the plugin.
		 * 
		 * @access public
		 * @return void
		 */
		public static function deactivate() {
			// do nothing
		}
		
	} // END class WC_Product_Builder
	
	/**
	 * Register De / Activation Hooks.
	 */
	register_activation_hook( __FILE__, array( 'wc_product_builder', 'activate' ) );
	register_deactivation_hook( __FILE__, array( 'wc_product_builder', 'deactivate' ) );
	
	/**
	 * Init wc_product_builder class and globalize it.
	 */
	$GLOBALS['wcpb'] = new WC_Product_Builder();
	
} // END class_exist check

?>