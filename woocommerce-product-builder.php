<?php
/**
 * Plugin Name: WooCommerce Product Builder
 * Plugin URI: https://github.com/philippjbauer/WoocommerceProductBuilder
 * Description: Lets users build their own products.
 * Version: 0.6
 * Author: Philipp Bauer
 * Author URI: https://github.com/philippjbauer
 *
 * Text Domain: wcpb
 * Domain Path: /languages/
 *
 * @package WooCommerce Product Builder
 * @category Extension
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.6
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
		private $str_version = "0.6";
		
		/**
		 * Contains the WooCommerce Product Builder options fetched from the database
		 * @var array
		 */
		private $arr_settings;
		
		/**
		 * Contains the max amounts of options a customer can choose per category
		 * @var array
		 */
		private $arr_optioncat_amounts;
		
		/**
		 * Contains the category titles ('slug' => 'post_title')
		 * @var array
		 */
		private $arr_optioncat_titles;
		
		/**
		 * Contains the raw session data from $_SESSION['wcpb']
		 * @var array
		 */
		private $arr_session_data;

		/**
		 * Message Stack
		 * @var array
		 */
		private $arr_messages;

		/**
		 * Error Message Stack
		 * @var array
		 */
		private $arr_errors;

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
			$this->session_update();
			add_action( 'wp_login', array( &$this, 'session_destroy' ) );		// Destroy session on wp_login
			add_action( 'wp_logout', array( &$this, 'session_destroy' ) );		// Destroy session on wp_logout
			
			/* LOCALIZATION */
			$this->localization_load();
			
			/* BACKEND ACTIONS */
			$this->settings_refresh();
			add_action( 'admin_menu', array( &$this, 'admin_menu' ) );	// Create backend menu links.

			/* FRONTEND ACTIONS */
			add_action( 'wcpb_before_product_builder', array( &$this, 'user_actions' ) );
			add_action( 'wcpb_before_product_builder', array( &$this, 'product_actions' ) );
			add_action( 'wcpb_before_product_builder', array( &$this, 'show_messages' ) );
			add_action( 'wcpb_include_template', array( &$this, 'include_template' ) );
			
			/* BACKEND INCLUDES */
			if ( is_admin() ) $this->backend_includes();

			/* FRONTEND INCLUDES */
			$this->frontend_includes();
		}
		
		/**
		 * Install upon activation.
		 * Check if new version is available and install / update WooCommerce Product Builder
		 * @return void
		 */
		public function install() {
			if ( get_option( 'wcpb_version' ) != $this->get_version() ) {
				include( 'admin/wcpb-install.php' );	
				add_action( 'init', 'install_wc_product_builder', 1 );
			}
		}
		
		/**
		 * Refresh Settings
		 * @return void
		 */
		public function settings_refresh() {
			if ( false !== get_option( 'wcpb_settings' ) ) {
				$this->set_settings( get_option( 'wcpb_settings' ) );					// Set WooCommerce Product Builder Settings
				$arr_settings = $this->get_settings();									// Get Settings
				$this->set_optioncat_amounts( $arr_settings['optioncat_amounts'] );		// Set how many options may be chosen (per category) by the user
				$this->set_optioncat_titles( $arr_settings['optioncat_titles'] );		// Set custom titles for product builder subcategories
			}
			else $this->set_settings( array() );
		}
		
		/**
		 * Load Localization.
		 * @return void
		 */
		public function localization_load() {
			load_plugin_textdomain( 'wcpb', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}
		
		/**
		 * Include frontend files.
		 * @return void
		 */
		public function frontend_includes() {
			include( 'wcpb-frontend.php' );		// Provides frontend functions
		}

		/**
		 * Include backend files.
		 * @return void
		 */
		public function backend_includes() {
			include( 'wcpb-backend.php' );		// Provides backend functions
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
			if ( isset( $_SESSION['wcpb'] ) ) {
				unset( $_SESSION['wcpb'] );	// destroys variables from WCPB plugin only
				$this->set_session_data( array() );
			}
		}
		
		/**
		 * Update Session Data.
		 * @return void
		 */
		public function session_update() {
			// if ( isset( $_SESSION['wcpb'] ) && is_array( $_SESSION['wcpb'] ) )
			$arr_session_data = $this->get_session_data();
			if ( ! empty( $arr_session_data ) )
				$_SESSION['wcpb'] = $arr_session_data;
			elseif ( empty( $arr_session_data ) && ! empty( $_SESSION['wcpb'] ) )
				$this->set_session_data( $_SESSION['wcpb'] );
			else {
				$_SESSION['wcpb'] = array(
					"current_product" => array(),
					"options" => array(),
				);
				$this->set_session_data( $_SESSION['wcpb'] );
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
			$arr_messages = $this->get_messages();
			$arr_messages[] = $str_message;
			$this->set_messages( $arr_messages );
		}

		/**
		 * Add error message to error stack
		 * @param string $str_error
		 */
		public function add_error( $str_error ) {
			$arr_errors = $this->get_errors();
			$arr_errors[] = $str_error;
			$this->set_errors( $arr_errors );
		}

		public function show_messages()	{
			// Show Messages
			if ( count( $this->get_messages() ) > 0 )
				$this->include_template( 'templates/wcpb-show-messages.php', array( 'messages' => $this->get_messages() ) );

			// Show Errors
			if ( count( $this->get_errors() ) > 0 )
				$this->include_template( 'templates/wcpb-show-messages.php', array( 'errors' => $this->get_errors() ) );
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
						$this->session_update();
						$this->add_message( __( 'All options have been removed!', 'wcpb' ));
						break;
					case "add_to_cart":
						$this->product_add_to_cart();
						break;
					case "add_option":
						$this->product_add_option( $args );
						break;
					case "remove_option":
						$this->product_remove_option( $args );
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
			// var_dump($this->get_session_data());
		}

		/**
		 * Sorts current product.
		 * @return void
		 */
		public function product_sort() {
			$arr_temp = array();
			$arr_optioncat_titles = $this->get_optioncat_titles();
			$arr_session_data = $this->get_session_data();

			foreach ( $arr_optioncat_titles as $key => $value ) {
				if ( ! empty( $arr_session_data['current_product'][$key] ) )
					$arr_temp[$key] = $arr_session_data['current_product'][$key];
			}

			$arr_session_data['current_product'] = $arr_temp;
			$this->set_session_data( $arr_session_data );
		}

		/**
		 * Update the current product.
		 * @return void
		 */
		public function product_update() {
			$arr_session_data = $this->get_session_data();
			if ( ! empty( $arr_session_data['current_product'] ) ) {
				$arr_temp = array();
				foreach ($arr_session_data['current_product'] as $arr_optioncat) {
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
							'ID'					=> $value,
							'slug'					=> $arr_option_postdata['post_name'],
							'title'					=> $arr_option_postdata['post_title'],
							'price'					=> floatval( $arr_option_postmeta['_price'][0] ),
							'thumbnail_guid'		=> $str_thumb_guid,
							'raw_option_postdata'	=> $arr_option_postdata,
							'raw_option_postmeta'	=> $arr_option_postmeta,
							'raw_thumb_postdata'	=> $arr_thumb_postdata,
						);
					}
				}
				$arr_session_data['options'] = $arr_temp;
				$this->set_session_data( $arr_session_data );
			}
		}

		/**
		 * Return total product price.
		 * @return float
		 */
		public function product_price() {
			$arr_session_data = $this->get_session_data();
			$flt_product_price = 0;

			if ( ! empty( $arr_session_data['options'] ) )
				foreach ( $arr_session_data['options'] as $arr_option )
					$flt_product_price += $arr_option['price'];

			return $flt_product_price;
		}

		/**
		 * Add product option to product.
		 * @param  array $args
		 * @return void
		 */
		public function product_add_option( $args ) {
			$arr_session_data = $this->get_session_data();
			$arr_optioncat_amounts = $this->get_optioncat_amounts();
			
			if ( count( $arr_session_data['options'] ) >= $arr_optioncat_amounts['total'] ) {
				$this->add_error( __( 'Maximum amount of options reached!', 'wcpb' ) );
				break;
			}

			// Check if category is in session_data, if not create it
			if ( ! isset( $arr_session_data['current_product'][$args['option_cat']] ) ) {
				$arr_session_data['current_product'][$args['option_cat']] = array();
				$this->set_session_data( $arr_session_data );
			}

			// Check if max amount of allowed options is already in the category, if not: add product
			if ( count( $arr_session_data['current_product'][$args['option_cat']] ) < $arr_optioncat_amounts[$args['option_cat']] ) {
				// for ( $i = 0; $i < $args['option_qty']; $i++ ) {
				if ( false === array_search( $args['option_id'], $arr_session_data['current_product'][$args['option_cat']] ) ) {
					$arr_session_data['current_product'][$args['option_cat']][] = (int) $args['option_id'];
					$this->set_session_data( $arr_session_data );
					$this->product_update();
					$this->session_update();
					$arr_session_data = $this->get_session_data();
					$this->add_message( __( 'Option "' . $arr_session_data['options'][$args['option_id']]['title'] . '" added!', 'wcpb' ) );
				}
				else
					$this->add_error( __( 'You can add "' . $arr_session_data['options'][$args['option_id']]['title'] . '" only once!', 'wcpb' ) );
				// }
			}
			else
				$this->add_error( __( 'You can only choose ' . $this->arr_optioncat_amounts[$args['option_cat']] . ' option(s) from "' . $this->arr_optioncat_titles[$args['option_cat']] . '"', 'wcpb' ) );
		}

		/**
		 * Remove product option from product.
		 * @param  array $args
		 * @return void
		 */
		public function product_remove_option( $args ) {
			$arr_session_data = $this->get_session_data();
			
			foreach ( $arr_session_data['current_product'] as $str_optioncat_slug => $arr_optioncat ) {
				foreach ( $arr_optioncat as $int_key => $int_option_id ) {
					if ( $args['optionid'] == $int_option_id ) {
						$this->add_message( 'Option "' . __( $arr_session_data['options'][$args['optionid']]['title'] . '" removed!', 'wcpb'));
						unset( $arr_session_data['current_product'][$str_optioncat_slug][$int_key], $arr_session_data['options'][$int_option_id] );
						$this->set_session_data( $arr_session_data );
						$this->session_update();
					}
				}
			}
			
			if ( count( $arr_session_data['options']) == 0 ) {
				$this->session_clear();
				$this->session_update();
			}
		}

		public function product_add_to_cart( $args ) {
			$arr_session_data = $this->get_session_data();
			if ( count( $arr_session_data['current_product'] ) > 0 )
				$this->add_message( __( 'Your product has been added to the cart!', 'wcpb' ) );
			else
				$this->add_error( __( 'Please create your custom product first!', 'wcpb' ) );
		}

		/**
		 * Includes given template.
		 * @param  string $template_file path to / and template filename
		 * @return void
		 */
		public function include_template( $template_file, $args = null ) {
			ob_start();
			include plugin_dir_path( __FILE__ ) . $template_file;
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
		

		/* SETTER */

		/**
		 * Set settings
		 * @param array $value
		 */
		public function set_settings( $value ) {
			$this->arr_settings = $value;
		}

		/**
		 * Set optioncat_amounts
		 * @param array $value
		 */
		public function set_optioncat_amounts( $value ) {
			$this->arr_optioncat_amounts = $value;
		}
		
		/**
		 * Set optioncat_titles
		 * @param array $value
		 */
		public function set_optioncat_titles( $value ) {
			$this->arr_optioncat_titles = $value;
		}
		
		/**
		 * Set session_data
		 * @param array $value
		 */
		public function set_session_data( $value ) {
			$this->arr_session_data = $value;
		}
		
		/**
		 * Set messages
		 * @param array $value
		 */
		public function set_messages( $value ) {
			$this->arr_messages = $value;
		}
		
		/**
		 * Set errors
		 * @param array $value
		 */
		public function set_errors( $value ) {
			$this->arr_errors = $value;
		}


		/* GETTER */

		/**
		 * Get version
		 * @return string
		 */
		public function get_version() {
			return $this->str_version;
		}

		/**
		 * Get settings
		 * @return array
		 */
		public function get_settings() {
			return $this->arr_settings;
		}
		
		/**
		 * Get optioncat_amounts
		 * @return array
		 */
		public function get_optioncat_amounts() {
			return $this->arr_optioncat_amounts;
		}
		
		/**
		 * Get optioncat_titles
		 * @return array
		 */
		public function get_optioncat_titles() {
			return $this->arr_optioncat_titles;
		}
		
		/**
		 * Get session_data
		 * @return array
		 */
		public function get_session_data() {
			return $this->arr_session_data;
		}
		
		/**
		 * Get messages
		 * @return array
		 */
		public function get_messages() {
			return $this->arr_messages;
		}
		
		/**
		 * Get arr_errors
		 * @return array
		 */
		public function get_errors() {
			return $this->arr_errors;
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