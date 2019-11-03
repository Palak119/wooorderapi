<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              patelpalak119@gmail.com
 * @since             1.0.0
 * @package           Woo_orders_api
 *
 * @wordpress-plugin
 * Plugin Name:       Woo Orders API
 * Plugin URI:        localhost/wooorderapi
 * Description:       The plugin for customer order api.
 * Version:           1.0.0
 * Author:            Palak Patel
 * Author URI:        patelpalak119@gmail.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woo_orders_api
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'WOO_ORDERS_API_VERSION', '1.0.0' );

function activate_woo_orders_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo_orders_api-activator.php';
	Woo_orders_api_Activator::activate();
}

function deactivate_woo_orders_api() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woo_orders_api-deactivator.php';
	Woo_orders_api_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woo_orders_api' );
register_deactivation_hook( __FILE__, 'deactivate_woo_orders_api' );

require plugin_dir_path( __FILE__ ) . 'includes/class-woo_orders_api.php';

function run_woo_orders_api() {

	$plugin = new Woo_orders_api();
	$plugin->run();

}
run_woo_orders_api();
