<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       patelpalak119@gmail.com
 * @since      1.0.0
 *
 * @package    Woo_orders_api
 * @subpackage Woo_orders_api/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woo_orders_api
 * @subpackage Woo_orders_api/admin
 * @author     Palak Patel <patelpalak119@gmail.com>
 */
class Woo_orders_api_Admin {

	private $plugin_name;
	private $version;
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo_orders_api-admin.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo_orders_api-admin.js', array( 'jquery' ), $this->version, false );

	}

	function addNewUserRole () {

		$capability = array(

			'read' => true, // true allows this capability
			'edit_posts' => false, // Allows user to edit their own posts
			'edit_pages' => false, // Allows user to edit pages
			'edit_others_posts' => true, // Allows user to edit others posts not just their own
			'create_posts' => false, // Allows user to create new posts
			'manage_categories' => false, // Allows user to manage post categories
			'publish_posts' => false, // Allows the user to publish, otherwise posts stays in draft mode
			);

		add_role( 'API_user', __('API User' ), $capability );
			
	}

	public function admin_init_callback()
	{
		$this->addNewUserRole();
	}

}
