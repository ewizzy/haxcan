<?php

/**
 * Fired during plugin activation
 *
 * @link       https://haxcan.com
 * @since      1.0.0
 *
 * @package    Haxcan
 * @subpackage Haxcan/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Haxcan
 * @subpackage Haxcan/includes
 * @author     Haxcan <mushex@gmail.com>
 */
global $haxcan_db_version;
$haxcan_db_version = '1.0';
class Haxcan_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
		global $haxcan_db_version;
		if(!Haxcan_Activator::db_exist()){
			//no tables, let's create hxquarantine, hxsafeplugins, hxcron
			//table for all plugin options
			$charset_collate = $wpdb->get_charset_collate();
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			
			$table_name = $wpdb->prefix . 'haxquarantine';
			$sql = "CREATE TABLE $table_name (
				ID mediumint(9) NOT NULL AUTO_INCREMENT,
				time_added datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
				filepath tinytext NOT NULL,				
				reason varchar(355) DEFAULT '' NOT NULL,
				atline tinytext NOT NULL,
				PRIMARY KEY  (ID)
			) $charset_collate;";
			dbDelta( $sql );
			

			//add db version to options
			add_option( 'haxcan_db_version', $haxcan_db_version );
			//google safe browsing - malware monitoring - enable right away
			add_option('haxcan_gsb_monitoring', 1);
			//cronjob setup right away...next scan in 12 hours
			add_option('haxcan_next_scan', strtotime("+12 hours"));
			
			
			
		}
	}
	public static function db_exist(){
		
		global $wpdb;
		$table_name = $wpdb->prefix."hxquarantine";

		if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
			return true;
		}
		else {
			return false;
		}
		
	}

}
