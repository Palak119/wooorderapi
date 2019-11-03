<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       patelpalak119@gmail.com
 * @since      1.0.0
 *
 * @package    Woo_orders_api
 * @subpackage Woo_orders_api/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Woo_orders_api
 * @subpackage Woo_orders_api/includes
 * @author     Palak Patel <patelpalak119@gmail.com>
 */
class Woo_orders_api {

	protected $loader;
	protected $plugin_name;
	protected $version;

	public function __construct() {
		if ( defined( 'WOO_ORDERS_API_VERSION' ) ) {
			$this->version = WOO_ORDERS_API_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'woo_orders_api';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_api_hooks();
	}

	private function load_dependencies() {

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo_orders_api-loader.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woo_orders_api-i18n.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woo_orders_api-admin.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woo_orders_api-public.php';
		
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/vendor/autoload.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'api/class-wooorders-api.php';

		$this->loader = new Woo_orders_api_Loader();

	}

	private function set_locale() {

		$plugin_i18n = new Woo_orders_api_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	public function define_api_hooks() {

		$plugin_api = new WooOrders_API( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action('rest_api_init', $plugin_api, 'add_api_routes');
		$this->loader->add_filter('rest_api_init', $plugin_api, 'add_cors_support');
 	 	$this->loader->add_filter('determine_current_user', $plugin_api, 'determine_current_user', 10);
    $this->loader->add_filter('rest_pre_dispatch', $plugin_api, 'rest_pre_dispatch', 10, 2 );
	}

	private function define_admin_hooks() {

		$plugin_admin = new Woo_orders_api_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_init', $plugin_admin, 'admin_init_callback' );

	}

	private function define_public_hooks() {

		$plugin_public = new Woo_orders_api_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}

	public function run() {
		$this->loader->run();
	}

	public function get_plugin_name() {
		return $this->plugin_name;
	}

	public function get_loader() {
		return $this->loader;
	}

	public function get_version() {
		return $this->version;
	}

}
