<?php
/**
 * Plugin Name: WooCommerce Product Builder
 * Plugin URI: https://github.com/philippjbauer/WoocommerceProductBuilder
 * Description: Lets users build their own products.
 * Version: 0.9
 * Author: Philipp Bauer
 * Author URI: https://github.com/philippjbauer
 *
 * Text Domain: wcpb
 * Domain Path: /languages/
 *
 * @package WooCommerce Product Builder
 * @category Extension
 * @author Philipp Bauer <philipp.bauer@vividdesign.de>
 * @version 0.9
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
		private $str_version = "0.9";
		
		/**
		 * Version Option Name (wp_options)
		 * @var string
		 */
		private $str_version_option_name = 'wcpb_version';
		
		/**
		 * Page ID Option Name (wp_options)
		 * @var string
		 */
		private $str_pageid_option_name = 'wcpb_page_id';
		
		/**
		 * Settings Option Name (wp_options)
		 * @var string
		 */
		private $str_settings_option_name = 'wcpb_settings';
		
		/**
		 * WCPB Product Term Option Name (wp_options)
		 * @var string
		 */
		private $str_productcat_term_id_option_name = 'wcpb_productcat_term_id';
		
		/**
		 * WCPB Product Category Term (taxonomy)
		 * @var string
		 */
		private $str_productcat_term = 'WCPB Custom Product';
		
		/**
		 * WCPB Product Category Slug (taxonomy)
		 * @var string
		 */
		private $str_productcat_slug = 'wcpb-custom-product';

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
			global $woocommerce;

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
			add_action( 'wcpb_before_product_builder', array( $woocommerce, 'show_messages' ) );
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
			if ( get_option( $this->get_version_option_name() ) != $this->get_version() ) {
				include( 'admin/wcpb-install.php' );	
				add_action( 'init', 'install_wc_product_builder', 1 );
			}
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
		 * Includes given template.
		 * @param  string $template_file path to file relative to plugin root
		 * @return void
		 */
		public function include_template( $template_file, $args = null ) {
			ob_start();
			include plugin_dir_path( __FILE__ ) . $template_file;
			echo ob_get_clean();
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
				$this->session_update();
				//$this->add_message( 'session cleared!' );
			}
			//else
				//$this->add_error('session not cleared!');
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
			// $this->debug();
		}

		/**
		 * Sorts current product.
		 * @return void
		 */
		public function product_sort() {
			$arr_session_data = $this->get_session_data();
			if ( ! empty( $arr_session_data['current_product'] ) ) {
				$arr_temp = array();
				$arr_optioncat_titles = $this->get_optioncat_titles();
				foreach ( $arr_optioncat_titles as $key => $value )
					if ( ! empty( $arr_session_data['current_product'][$key] ) )
						$arr_temp[$key] = $arr_session_data['current_product'][$key];
				$arr_session_data['current_product'] = $arr_temp;
				$this->set_session_data( $arr_session_data );
			}
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
					$this->add_message( sprintf( __( 'Option "%s" added!', 'wcpb' ), $arr_session_data['options'][$args['option_id']]['title'] ) );
				}
				else
					$this->add_error( sprintf( __( 'You can add "%s" only once!', 'wcpb' ), $arr_session_data['options'][$args['option_id']]['title'] ) );
				// }
			}
			else
				$this->add_error( sprintf( __( 'You can only choose %d option(s) from "%s"', 'wcpb' ), $this->arr_optioncat_amounts[$args['option_cat']], $this->arr_optioncat_titles[$args['option_cat']] ) );
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
						$this->add_message( sprintf( __( 'Option "%s" removed!', 'wcpb'), $arr_session_data['options'][$args['optionid']]['title'] ) );
						unset( $arr_session_data['current_product'][$str_optioncat_slug][$int_key], $arr_session_data['options'][$int_option_id] );
						$this->set_session_data( $arr_session_data );
						$this->session_update();
					}
				}
			}
			
			if ( count( $arr_session_data['options']) == 0 ) {
				$this->session_clear();
			}
		}
		
		/**
		 * Creates custom product.
		 * @param  array $arr_product
		 * @return mixed
		 */
		public function product_create( $arr_product ) {
			if ( is_array( $arr_product ) ) {
				global $wpdb;
				$arr_settings = $this->get_settings();

				// make sure there is a custom product name and slug
				$arr_settings['custom_product_name'] = isset( $arr_settings['custom_product_name'] ) ? $arr_settings['custom_product_name'] : "Your Product";
				$arr_settings['custom_product_slug'] = isset( $arr_settings['custom_product_slug'] ) ? $arr_settings['custom_product_slug'] : "your-product";

				// create random product_sku and check if it exists
				do {
					$str_product_sku = strval( substr( sha1( mt_rand() . microtime() ), mt_rand( 0, 35 ), 5 ) );
				}
				while ( $wpdb->get_row( "SELECT post_id FROM $wpdb->postmeta WHERE '_sku' = '" . $str_product_sku . "'", ARRAY_N ) != null );

				// create product object
				$arr_product_postdata = array(
					"post_author"		=> 1,
					"post_title"		=> $arr_settings['custom_product_name'] . ' (' . $str_product_sku . ')',
					"post_name"			=> $arr_settings['custom_product_slug'] . '-' . $str_product_sku,
					"post_type"			=> "product",
					"post_status"		=> "publish",
					"comment_status"	=> "closed",
					"ping_status"		=> "closed",
				);

				// create product postmeta object
				$arr_product_postmeta = array(
					"_manage_stock"		=> "no",
					"_price"			=> $this->product_price(),
					"_sku"				=> $str_product_sku,
					"_stock"			=> 0,
					"_stock_status"		=> "instock",
					"_tax_status"		=> "taxable",
					"_visibility"		=> "hidden",
					"_product_options"	=> $arr_product['current_product'],
					"_product_custom"	=> true,
				);

				// insert product object in db:wp_posts
				$int_post_id = wp_insert_post( $arr_product_postdata );
				if ( $int_post_id != 0 ) {
					// insert product postmeta in db:wp_postmeta
					foreach ( $arr_product_postmeta as $meta_key => $meta_value )
						update_post_meta( $int_post_id, $meta_key, $meta_value );

					// insert product into parent product_cat in db:wp_term_relationships
					$mix_term_id = get_option( $this->get_productcat_term_id_option_name() );
					if ( false !== $mix_term_id )
						$mix_term_result = wp_set_object_terms( $int_post_id, intval( $mix_term_id ), 'product_cat' );
					else
						return false;

					return $int_post_id;
				}
				else return false;
			}
			else return false;
		}

		/**
		 * Add product to WooCommerce cart
		 * @param  int $int_post_id
		 * @return void
		 */
		public function product_add_to_cart( $int_post_id = false ) {
			// if no product (post_id) is given, create product and set post_id
			if ( false === $int_post_id ) {
				$arr_session_data = $this->get_session_data();
				if ( count( $arr_session_data['current_product'] ) > 0 ) {
					$int_post_id = $this->product_create( $arr_session_data );
					if ( 0 == $int_post_id )
						$this->add_error( __( 'Couldn\'t insert product into database! Please try again.', 'wcpb' ) );
				}
				else
					$this->add_error( __( 'Please create your custom product first!', 'wcpb' ) );
			}

			// add product to cart
			if ( is_int( $int_post_id ) ) {
				global $woocommerce;
				if ( $woocommerce->cart->add_to_cart( $int_post_id, 1 ) ) {
					$this->session_clear();
					$this->add_message( 'Added product to cart!', 'wcpb' );
				}
			}
		}

		public function debug() {
			echo "Get:<br><pre>";
			var_dump( $_GET );
			echo "</pre><br>Post:<br><pre>";
			var_dump( $_POST );
			echo "</pre><br>Request:<br><pre>";
			var_dump( $_REQUEST );
			echo "</pre><br>Session:<br><pre>";
			var_dump( $_SESSION );
			echo "</pre><br>Session Data:<br><pre>";
			var_dump( $this->get_session_data() );
			echo "</pre>";
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
		 * Get version option name
		 * @return string
		 */
		public function get_version_option_name() {
			return $this->str_version_option_name;
		}

		/**
		 * Get pageid option name
		 * @return string
		 */
		public function get_pageid_option_name() {
			return $this->str_pageid_option_name;
		}

		/**
		 * Get settings option name
		 * @return string
		 */
		public function get_settings_option_name() {
			return $this->str_settings_option_name;
		}

		/**
		 * Get productcat_term_id option name
		 * @return string
		 */
		public function get_productcat_term_id_option_name() {
			return $this->str_productcat_term_id_option_name;
		}

		/**
		 * Get product category term
		 * @return string
		 */
		public function get_productcat_term() {
			return $this->str_productcat_term;
		}

		/**
		 * Get product category slug
		 * @return string
		 */
		public function get_productcat_slug() {
			return $this->str_productcat_slug;
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