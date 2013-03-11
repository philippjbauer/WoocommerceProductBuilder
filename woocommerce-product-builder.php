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
 * @version 0.1
 */

/**
 * Check if the WooCommerce plugin is active and include it.
 */
$bool_woocommerce_active = false;
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	$bool_woocommerce_active = true;
}

/**
 * Check if Class already exists and WooCommerce is active
 */
if ( ! class_exists( 'WC_Product_Builder' ) && $bool_woocommerce_active ) {
	
	/**
	 * WooCommerce Product Builder Class
	 */
	class WC_Product_Builder {
		/**
		 * Current WooCommerce Product Builder Version
		 * @var string
		 */
		var $str_version = "0.1";
		
		/**
		 * Contains the WooCommerce Product Builder options fetched from the database
		 * @var array
		 */
		var $arr_settings;
		
		/**
		 * Contains the max amounts of options a customer can choose per category
		 * @var array
		 */
		var $arr_optioncat_amounts;
		
		/**
		 * Contains the category titles ('slug' => 'post_title')
		 * @var array
		 */
		var $arr_optioncat_titles;
		
		/**
		 * Contains the raw session data from $_SESSION['wcpb']
		 * @var array
		 */
		var $arr_session_data;

		/**
		 * Message Stack
		 * @var array
		 */
		var $arr_messages = array();

		/**
		 * Error Message Stack
		 * @var array
		 */
		var $arr_errors = array();

		/**
		 * WCPB Constructor.
		 * @return void
		 */
		public function __construct() {
			if ( is_admin() ) $this->install();							// Execute install routine
			add_action( 'woocommerce_init', array( &$this, 'init' ) );	// Initialize WC_Product_Builder
		}
		
		/**
		 * Init WooCommerce Product Builder.
		 * @return void
		 */
		public function init() {
			/* SESSION ACTIONS */
			$this->session_start();
			// add_action( 'init', array( &$this, 'session_start' ) );				// Start session if none is started yet.
			add_action( 'wp_login', array( &$this, 'session_destroy' ) );		// Destroy session on wp_login
			add_action( 'wp_logout', array( &$this, 'session_destroy' ) );		// Destroy session on wp_logout
			$this->session_update_data();
			
			/* LOCALIZATION */
			$this->load_localization();
			
			/* BACKEND ACTIONS */
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );	// Create backend menu links.
			$this->refresh_settings();

			/* FRONTEND ACTIONS */
			add_action( 'wcpb_before_product_builder', array( &$this, 'user_actions' ) );
			add_action( 'wcpb_before_product_builder', array( &$this, 'product_actions' ) );
			add_action( 'wcpb_before_product_builder', array( &$this, 'show_messages' ) );
			add_action( 'wcpb_include_template', array( &$this, 'include_template' ) );
			
			/* BACKEND INCLUDES */
			if ( is_admin() ) $this->admin_includes();
			
			/* FRONTEND INCLUDES */
			$this->frontend_includes();
		}
		
		/**
		 * Install upon activation.
		 * Check if new version is available and install / update WooCommerce Product Builder
		 * @return void
		 */
		public function install() {
			if ( get_option( 'wcpb_version' ) != $this->str_version ) {
				include( 'admin/wcpb-install.php' );	
				add_action( 'init', 'install_wc_product_builder', 1 );
			}
		}
		
		/**
		 * Refresh Settings
		 * @return void
		 */
		public function refresh_settings() {
			if ( false !== get_option( 'wcpb_settings' ) ) {
				$this->arr_settings = get_option( 'wcpb_settings' );			// Get WooCommerce Product Builder Settings
				$this->arr_optioncat_amounts = $this->arr_settings['optioncat_amounts'];	// Get how many options may be chosen (per category) by the user
				$this->arr_optioncat_titles	= $this->arr_settings['optioncat_titles'];		// Get custom titles for product builder subcategories
			}
			else $this->arr_settings = array();
		}
		
		/**
		 * Load Localization.
		 * @return void
		 */
		public function load_localization() {
			load_plugin_textdomain( 'wcpb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * Include frontend files.
		 * @return void
		 */
		public function frontend_includes() {
			include( 'wcpb-shortcodes.php' );	// Initializes shortcodes
			include( 'wcpb-frontend.php' );		// Provides frontend functions
		}
		
		/**
		 * Include admin files.
		 * @return void
		 */
		public function admin_includes() {
			// nothing right now
		}
		
		/**
		 * Start session.
		 * @return void
		 */
		public function session_start() {
			if ( session_id() === "" )
				session_start();
		}
		
		/**
		 * Destroy session.
		 * @return void
		 */
		public function session_destroy() {
			if ( session_id() != "" ) {
				session_unset();	// destroys variables
				session_destroy();	// destroys session
			}
		}
		
		/**
		 * Clear WooCommerce Product Builder Session.
		 * @return void
		 */
		public function session_clear() {
			if ( isset( $_SESSION['wcpb'] ) )
				unset( $_SESSION['wcpb'], $this->arr_session_data );	// destroys variables from WCPB plugin only
		}
		
		/**
		 * Update Session Data.
		 * @return void
		 */
		public function session_update_data() {
			if ( isset( $_SESSION['wcpb'] ) && is_array( $_SESSION['wcpb'] ) )
				$this->arr_session_data = &$_SESSION['wcpb'];
			else {
				$_SESSION['wcpb'] = array(
					"current_product" => array(),
					"options" => array(),
				);
				$this->arr_session_data = &$_SESSION['wcpb'];
			}
		}
		
		/**
		 * Build the Admin Menu Entries in Wordpress backend.
		 * @return void
		 */
		public function admin_menu() {
			add_menu_page( 'WooCommerce Product Builder', 'Product Builder', 'manage_woocommerce', 'wcpb-admin', array( &$this, 'admin_menu_main' ), null, 58 );
			add_submenu_page( 'wcpb-admin', 'Export Orders', 'Export Orders', 'view_woocommerce_reports', 'wcpb-export', array( &$this, 'admin_menu_export' ), null );
		}
		
		/**
		 * Include WCPB Main Settings Menu.
		 * @return void
		 */
		public function admin_menu_main() {
			if ( ! current_user_can( 'manage_woocommerce' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			include( 'admin/wcpb-main.php' );
		}
		
		/**
		 * Include WCPB Export Menu.
		 * @return void
		 */
		public function admin_menu_export() {
			if ( ! current_user_can( 'manage_woocommerce' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}
			include( 'admin/wcpb-export.php' );
		}
		
		/**
		 * Add messages to message stack
		 * @param string $str_message
		 */
		public function add_message( $str_message ) {
			$this->arr_messages[] = $str_message;
		}

		/**
		 * Add error message to error stack
		 * @param string $str_error
		 */
		public function add_error( $str_error ) {
			$this->arr_errors[] = $str_error;
		}

		public function show_messages()	{
			// Show Messages
			if ( count( $this->arr_messages ) > 0 )
				$this->include_template( plugin_dir_path( __FILE__ ) . 'templates/wcpb-show-messages.php', array( 'messages' => $this->arr_messages ) );

			// Show Errors
			if ( count( $this->arr_errors ) > 0 )
				$this->include_template( plugin_dir_path( __FILE__ ) . 'templates/wcpb-show-messages.php', array( 'errors' => $this->arr_errors ) );
		}

		/**
		 * Handle user actions (uses $_POST or $_GET for args normally).
		 * @param  array $args
		 * @return void
		 */
		public function user_actions( $args ) {
			if ( ! empty( $args['action'] ) ) {
				switch ( $args['action'] ) {
					case "restart":
						$this->session_clear();
						$this->session_update_data();
						break;
					case "add_to_cart":
						if ( count( $this->arr_session_data['current_product'] ) > 0 )
							$this->add_to_cart();
						else
							$this->add_error( __( 'Please create your custom product first.', 'wcpb' ) );
						break;
					case "add_option":
						// Check if category is in session_data, if not create it
						if ( ! isset( $this->arr_session_data['current_product'][$args['option_cat']] ) )
							$this->arr_session_data['current_product'][$args['option_cat']] = array();
						if ( count( $this->arr_session_data['current_product'][$args['option_cat']] ) < $this->arr_optioncat_amounts[$args['option_cat']] ) {
							for ( $i = 0; $i < $args['option_qty']; $i++ ) {
								$this->arr_session_data['current_product'][$args['option_cat']][] = (int) $args['option_id'];

								$this->product_update();
								$this->add_message( __( 'Option "' . $this->arr_session_data['options'][$args['option_id']]['the_title'] . '" added!', 'wcpb' ) );
							}
						}
						else
							$this->add_error( __( 'You can only choose ' . $this->arr_optioncat_amounts[$args['option_cat']] . ' option(s) from "' . $this->arr_optioncat_titles[$args['option_cat']] . '"', 'wcpb' ) );
						break;
					case "remove_option":
						foreach ( $this->arr_session_data['current_product'] as $str_optioncat_slug => $arr_optioncat ) {
							foreach ( $arr_optioncat as $int_key => $int_option_id ) {
								if ( $args['optionid'] == $int_option_id ) {
									if ( isset( $this->arr_session_data['options'][$args['optionid']] ) ) {
										$this->add_message( 'Option "' . __( $this->arr_session_data['options'][$args['optionid']]['the_title'] . '" removed!', 'wcpb'));
										unset( $this->arr_session_data['current_product'][$str_optioncat_slug][$int_key], $this->arr_session_data['options'][$int_option_id] );
									}
								}
							}
						}
						if ( count( $this->arr_session_data['options']) == 0 ) {
							$this->session_clear();
							$this->session_update_data();
						}
						break;
				}
			}
		}
		
		/**
		 * Handle product actions.
		 * @return void
		 */
		public function product_actions() {
			$this->product_sort();
			$this->product_update();

			// DEBUG
			// var_dump($this->arr_session_data['current_product']);
		}

		/**
		 * Sorts current product.
		 * @return void
		 */
		public function product_sort() {
			$arr_temp = array();
			foreach ( $this->arr_optioncat_titles as $key => $value ) {
				if ( ! empty( $this->arr_session_data['current_product'][$key] ) )
					$arr_temp[$key] = $this->arr_session_data['current_product'][$key];
			}
			$this->arr_session_data['current_product'] = $arr_temp;
		}

		/**
		 * Update the current product.
		 * @return void
		 */
		public function product_update() {
			if ( ! empty( $this->arr_session_data['current_product'] ) ) {
				$arr_temp = array();
				foreach ($this->arr_session_data['current_product'] as $arr_optioncat) {
					foreach ($arr_optioncat as $key => $value) {
						// Get postdata and post metadata
						$arr_option_postdata = get_post( $value, ARRAY_A );
						$arr_option_postmeta = get_post_meta( $value );

						// Get thumbnail guid
						$arr_thumb_postdata = null;
						$str_thumb_guid = plugins_url( 'assets/img/thumb-placeholder.png', __FILE__ );
						if ( ! empty( $arr_option_postmeta['_thumbnail_id'][0] ) ) {
							$arr_thumb_postdata = get_post( $arr_option_postmeta['_thumbnail_id'][0], ARRAY_A );
							$str_thumb_guid = $arr_thumb_postdata['guid'];
						}

						// Create option info array
						$arr_temp[$value] = array(
							'ID' => $value,
							'slug' => $arr_option_postdata['post_name'],
							'the_title' => $arr_option_postdata['post_title'],
							'thumbnail_guid' => $str_thumb_guid,
							'raw_arr_option_postdata' => $arr_option_postdata,
							'raw_arr_option_postmeta' => $arr_option_postmeta,
							'raw_arr_thumb_postdata' => $arr_thumb_postdata,
						);
					}
				}
				$this->arr_session_data['options'] = $arr_temp;
			}
		}

		/**
		 * Includes given template.
		 * @param  string $template_file path to / and template filename
		 * @return void
		 */
		public function include_template( $template_file, $args = null ) {
			ob_start();
			include $template_file;
			echo ob_get_clean();
		}
		
		/**
		 * Activate the plugin.
		 * @return void
		 */
		public static function activate() {
			// do nothing
		}
		
		/**
		 * Deactivate the plugin.
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