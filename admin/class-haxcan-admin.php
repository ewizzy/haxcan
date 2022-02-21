<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://haxcan.com
 * @since      1.0.0
 *
 * @package    Haxcan
 * @subpackage Haxcan/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Haxcan
 * @subpackage Haxcan/admin
 * @author     Haxcan <mushex@gmail.com>
 */
class Haxcan_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Haxcan_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Haxcan_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/haxcan-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Haxcan_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Haxcan_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_script('haxcan-donutty', plugin_dir_url( __FILE__ ) . 'js/circliful.js', array( 'jquery' ), '1.0', false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/haxcan-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script('colorbox', plugin_dir_url( __FILE__ ) . 'js/jquery.colorbox.js', array( 'jquery' ), '1.0', false );
		wp_enqueue_script('editarea', plugin_dir_url( __FILE__ ) . 'js/edit_area/edit_area_full.js', array( 'jquery' ), '1.3', false );
		wp_localize_script(
			$this->plugin_name,
			'haxcan_settings',
			array(
				'nonce' => wp_create_nonce( 'av_ajax_nonce' ),
				'msg_1' => esc_js( __( 'Dismiss', 'antivirus' ) ),
				'msg_3' => esc_js( __( 'Scan finished', 'antivirus' ) ),
				'msg_4' => esc_js( __( 'Dismiss false positive virus detection', 'antivirus' ) ),
			)
		);

	}

}
