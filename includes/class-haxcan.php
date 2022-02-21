<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://haxcan.com
 * @since      1.0.0
 *
 * @package    Haxcan
 * @subpackage Haxcan/includes
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
 * @package    Haxcan
 * @subpackage Haxcan/includes
 * @author     Haxcan <mushex@gmail.com>
 */
class Haxcan {
	private $haxcan_options;
	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Haxcan_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'HAXCAN_VERSION' ) ) {
			$this->version = HAXCAN_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'haxcan';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Haxcan_Loader. Orchestrates the hooks of the plugin.
	 * - Haxcan_i18n. Defines internationalization functionality.
	 * - Haxcan_Admin. Defines all hooks for the admin area.
	 * - Haxcan_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-haxcan-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-haxcan-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-haxcan-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-haxcan-public.php';

		$this->loader = new Haxcan_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Haxcan_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Haxcan_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		//skip running during autosave or xmlrpc
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST ) ) {
			return;
		}
		if ( defined( 'DOING_AJAX' ) ) {
			add_action( 'wp_ajax_get_ajax_response', array( __CLASS__, 'get_ajax_response' ) );
		} else {
		$plugin_admin = new Haxcan_Admin( $this->get_plugin_name(), $this->get_version() );
		add_action( 'admin_menu', array( $this, 'haxcan_add_plugin_page' ) );
		add_action( 'admin_init', array( $this, 'haxcan_page_init' ) );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private static function _get_theme_data( $theme ) {
		// Break recursion if no valid (parent) theme is given.
		print_r($theme);die();
		if ( ! $theme ) {
			return false;
		}

		// Extract data.
		$name  = $theme->get( 'Name' );
		$slug  = $theme->get_stylesheet();
		$files = $theme->get_files( 'php', 1 );

		// Append parent's data, if we got a child theme.
		$parent = self::_get_theme_data( $theme->parent() );

		// Return false if there are no files in current theme and no parent.
		if ( empty( $files ) && ! $parent ) {
			return false;
		}

		return array(
			'Name'           => $name,
			'Slug'           => $slug,
			'Template Files' => $files,
			'Parent'         => $parent,
		);
	}
	protected static function _get_theme_files() {
		//echo 'f';die();
		// Check if the theme exists.
		$theme = self::_get_theme_data( wp_get_theme() );
		//print_r($theme);die();
		if ( ! $theme ) {
			return false;
		}

		$files = $theme['Template Files'];

		// Append parent files, if available.
		$parent = $theme['Parent'];
		while ( false !== $parent ) {
			$files  = array_merge( $files, $parent['Template Files'] );
			$parent = $parent['Parent'];
		}
		//print_r($files);die();
		// Check its files.
		if ( empty( $files ) ) {
			return false;
		}
		print_r($files);die();

		// Returns the files, stripping out the content dir from the paths.

		array_unique($files);
		wp_send_json($files); 
	}
	public static function get_ajax_response() {		
		// Check referer.
		check_ajax_referer( 'haxcan_ajax_nonce', '_ajax_nonce', false );

		// Check if there really is some data.
		if ( empty( $_POST['_action_request'] ) ) {
			exit();
		}

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$values = array();

		// Get value based on request.
		switch ( $_POST['_action_request'] ) {
			case 'get_haxfile':
			//its not file, just string named haxfile
			$haxfile = esc_html($_POST['haxfile']);
			$fajl = file($haxfile);
			wp_send_json($fajl);
			break;
			case 'save_scan':
			if(!is_numeric($_POST['theme_files_scanned'])){ break;}
			update_option('haxcan_last_scan', current_time('mysql'));
			//already escaped above
			update_option('haxcan_tfs', $_POST['theme_files_scanned']);
			break;
			case 'get_quarantined':
			global $wpdb;
			$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'haxquarantine LIMIT 100');
			if(count($result)){
				$allres = array();
				$ar = 0;
				foreach($result as $quar){
					$allres[$ar] = '<div class="haxcan-quarantine-item"><a href="#">'.$quar->filepath.'</a><span class="haxcan-time">Scan time: '.$quar->time_added.'</span><span class="haxcan-time">Line: '.$quar->atline.'</span><span class="haxcan-time">Code found: <div style="color:red;display:inline">'.$quar->reason.'</div></span></div>';
						$ar++;
				}
				wp_send_json($allres);
			}
			else {
				return false;
			}
			break;
			case 'save_plugin_scan':
			if(!is_numeric($_POST['plugin_files_scanned'])){ break;}
			update_option('haxcan_last_scan', current_time('mysql'));
			update_option('haxcan_pfs', $_POST['plugin_files_scanned']);
			break;
			case 'add_to_quarantine':
			global $wpdb;
			$file = esc_html($_POST['theme_file']);
			$reason = esc_html($_POST['reason']);
			$lajn = esc_html($_POST['line']);
			$wpdb->insert( 
				$wpdb->prefix.'haxquarantine', 
				array( 
					'time_added' => current_time( 'mysql' ), 
					'filepath' => $file, 
					'atline' => $lajn,
					'reason' => $reason 
				) 
			);
			
			break;
			case 'get_all_plugins':
			
			$plugin_array = get_plugins();
			$plugincount = count($plugin_array);
			$pluginfilescount = 0;
			$county = 0;
			if($plugincount){
			    foreach ( $plugin_array as $pluginfile => $plugindata) {
					$hfname = self::haxcan_get_plugin_file($plugindata['Name']);
					$arr['plugins'][$county]['name'] = $plugindata['Name'];
					$filteredar = array_filter(get_plugin_files($hfname),function($val){ 
						$ext = substr(strrchr($val, '.'), 1);
						return $ext=='php';
					});
					$pluginfilescount += count($filteredar);
					$arr['plugins'][$county]['files_count'] = count($filteredar);
					$arr['plugins'][$county]['files'] = $filteredar;
					$county++;
			    }
			}
			$arr['total_plugin_files'] = $pluginfilescount;
			wp_send_json(array('data'  => $arr));
			break;
			case 'get_all_themes':
			$allthemes = wp_get_themes();
			$arr['num_of_themes']= count($allthemes);
			$count = $totalfilescount = 0;
			foreach($allthemes as $currtheme){
				$numoffiles = $currtheme->get_files( array('php','js'), 1 );
				$arr['themes'][$count]['name'] = $currtheme->get('Name');
				$arr['themes'][$count]['version'] = $currtheme->get('Version');
				$arr['themes'][$count]['files_count'] = count($numoffiles);
				$arr['themes'][$count]['files'] = $numoffiles;
				$totalfilescount += count($numoffiles);
				$count++;
			}
			$arr['total_files'] = $totalfilescount;
			wp_send_json(array('data'  => $arr));
			break;
			case 'check_theme_file':
				if ( ! empty( $_POST['_theme_file'] ) ) {
					$theme_file = wp_unslash( $_POST['_theme_file'] );
					$shellz = self::haxcan_check_file( $theme_file );
					$kei = '';
					if($shellz){
					foreach($shellz as $key=>$value)
					{
						$kei = $key;
					}
					if($kei){
						$fullstring = '';
						foreach($shellz[$kei] as $vall){
							$fullstring .= $vall.',';
						}
					}
					//print_r($shellz);die();
						//if we have some $results with line bla bla
						wp_send_json(array('haveholes' => 1,'line'  => $kei,'reason'=>$fullstring, 'theme_file'=>$theme_file));
					}
					else {
						wp_send_json(array('haveholes' => 0));
					}
				}
				break;
			default:
				break;
		}

		// Send response.
		if ( $values ) {
			if ( isset( $_POST['_ajax_nonce'] ) ) {
				$nonce = sanitize_text_field( wp_unslash( $_POST['_ajax_nonce'] ) );
			} else {
				$nonce = '';
			}

			wp_send_json(
				array(
					'data'  => array_values( $values ),
					'nonce' => $nonce,
				)
			);
		}

		exit();
	}
	private static function haxcan_file_content( $file ) {
		return file( $file );
	}
	public static function haxcan_get_plugin_file( $plugin_name ) {
	    $plugins = get_plugins();
	    foreach( $plugins as $plugin_file => $plugin_info ) {
	        if ( $plugin_info['Name'] == $plugin_name ) return $plugin_file;
	    }
	    return null;
	}
	public static function haxcan_check_file( $file ) {
		// Simple file path check.
		if ( filter_var( $file, FILTER_SANITIZE_URL ) !== $file ) {
			return false;
		}

		// Sanitize file string.
		if ( validate_file( $file ) !== 0 ) {
			return false;
		}

		// No file?
		if ( ! $file ) {
			return false;
		}

		// Get file content.
		$content = self::haxcan_file_content( $file );
		if ( ! $content ) {
			return false;
		}
		$results = array();

		// Loop through lines.
		foreach ( $content as $num => $line ) {
			$result = self::haxcan_check_line( $line, $num );
			if ( $result ) {
				$results[ $num ] = $result;
			}
		}

		// Return results if found.
		if ( ! empty( $results ) ) {
			return $results;
		}

		return false;
	}
	private static function haxcan_pattern() {
		return '/\b(assert|file_get_contents|curl_exec|popen|proc_open|unserialize|eval|base64_encode|base64_decode|create_function|exec|shell_exec|system|passthru|ob_get_contents|file|curl_init|readfile|fopen|fsockopen|pfsockopen|fclose|fread|file_put_contents)\b\s*?\(/';
	}

	/**
	 * Check a specific line number.
	 *
	 * @param string $line The line to check.
	 * @param int    $num  Line number.
	 *
	 * @return array|bool An array of matched lines or false on failure.
	 */
	private static function haxcan_check_line( $line = '', $num ) {
		// Trim value.
		$line = trim( (string) $line );

		// Make sure the values aren't empty.
		if ( ! $line || ! isset( $num ) ) {
			return false;
		}

		$results = array();
		$output  = array();

		// Check if the regex matches.
		preg_match_all(
			self::haxcan_pattern(),
			$line,
			$matches
		);
		//print_r($matches);die();
		// Save matches.
		if ( $matches[1] ) {
			$results = $matches[1];
		}
		
		if ( $results ) {
			// Remove duplicates.
			$results = array_unique( $results );
			return $results;
		}

		return false;
	}
	private function define_public_hooks() {

		$plugin_public = new Haxcan_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

	}
	public function haxcan_add_plugin_page() {
		add_menu_page(
			'Haxcan', // page_title
			'Haxcan', // menu_title
			'manage_options', // capability
			'haxcan', // menu_slug
			array( $this, 'haxcan_create_admin_page' ), // function
			'dashicons-shield-alt', // icon_url
			61 // position
		);
	}

	public function haxcan_create_admin_page() {
		$this->haxcan_options = get_option( 'haxcan_option_name' ); 
		//do we have any scans in db?
		$lastscanid = get_option('haxcan_last_scan',0);
		global $wpdb;
		$lastqid = $wpdb->get_var('SELECT COUNT(*) FROM '.$wpdb->prefix.'haxquarantine');
		?>

		<div class="wrap">
			<h2>Haxcan</h2>
			<?php settings_errors(); ?>
			
			<div class="haxcan-wrap">
				<div class="haxcan-header">
					
					<div class="haxcan-logo haxcan-panel-change" id="nav-01">
						<img src="<?php echo plugins_url();?>/haxcan/admin/images/haxcan-logo.svg" alt="haxcan" />
						<span>Version: <b>1.0</b></span>
					</div>
					
					<div class="haxcan-infoboxes">
						<div class="haxcan-infobox" id="haxcan-status">
							<h5>STATUS</h5>
							<h2 class="haxcan-active" id="haxcan-general-status">
								<?php if(!$lastscanid&&!$lastqid){
									echo '<div style="color:#f78c00">Scan Needed</div>';
									} 
									if($lastqid){
										echo '<div style="color:#fc1303">Insecure</div>';
									}
									if($lastscanid&&!$lastqid){
										echo '<div>Secure</div>';
									}
									?>
								</h2>
						</div>
						<div class="haxcan-infobox">
							<h5>LAST SCAN</h5>
							<h2 id="haxcan-last-scans"><?php if(!$lastscanid){ echo '-';} else { echo self::time_elapsed_string(get_option('haxcan_last_scan'));}?></h2>
						</div>
						
						<div class="haxcan-infobox haxcan-hide">
							<h5>NEXT SCAN</h5>
							<h2>in 7h</h2>
						</div>
						
						<div class="haxcan-infobox">
							<h5>HTTPS</h5>
							<h2 class="haxcan-active"><?php if(is_ssl()){ echo 'Enabled';} else { echo '<div style="color:#f78c00">Disabled</div>';}?></h2>
						</div>
						
					</div>
					<div class="haxcan-full-scan">
						<a href="#" class="haxcan-button">Start Full Scan</a>
					</div>
					<div class="haxcan-icon-settings">
						<div class="haxcan-quarantin haxcan-tooltip haxcan-panel-change" id="nav-03">
							<span id="haxcan-notifs" <?php if($lastqid){ echo 'style="display:block;"';}?>><?php if($lastqid){ echo $lastqid;} else { echo '0';}?></span>
							<img src="<?php echo plugins_url();?>/haxcan/admin/images/quarantin.svg" alt="quarantin" />
							<span class="haxcan-tt">Quarantine</span>
						</div>
						<div class="haxcan-settings haxcan-tooltip haxcan-panel-change" id="nav-02">
							<img src="<?php echo plugins_url();?>/haxcan/admin/images/settings.svg" alt="quarantin" />
							<span class="haxcan-tt">Settings</span>
						</div>
					</div>
					
				</div>
				
				<div class="haxcan-content">
					<div class="haxcan-nav-wrap">
						<div class="haxcan-dropdown">
				<button class="haxcan-dropbtn"><span id="haxcan-dashboard">Dashboard</span> <img src="<?php echo plugins_url();?>/haxcan/admin/images/chevron-down.svg" alt="" /></button>
						  <div class="haxcan-dropdown-content">
						    <a href="javascript:;" class="haxcan-panel-change" id="nav-01">Dashboard</a>
						    <a href="javascript:;" class="haxcan-panel-change" id="nav-03">Quarantine</a>
						    <a href="javascript:;" class="haxcan-panel-change" id="nav-02">Settings</a>
						  </div>
						</div>
					</div>
				    <div id="haxcan-display-content">
				    </div>
					<div class="haxcan-content-container" id="haxcan-content-01">
					    
						<div class="haxcan-widget" id="haxcan-theme-scan">
							<h2 class="haxcan-widget-title"><img src="<?php echo plugins_url();?>/haxcan/admin/images/file.svg" alt="quarantin" /> Themes Scanned</h2>
							<div class="haxcan-widget-content">
								<?php
								if(!$lastscanid){
									echo '<div class="haxcan-no-scans"><img class="tpDanger" src="'.plugins_url().'/haxcan/admin/images/danger.svg" alt="" /><p>No scans found. Press button under to start themes scan or start a full scan by pressing big green button.</p></div>';?>
							
								<div class="haxcan-loading-data">
									<div class="haxcan-wrap-top">
									<div id="haxcan-total-plugins" class="haxcan-tp">
									-/-
									</div>
									<div id="haxcan-inner-status" class="haxcan-is">
										SCANNING
									</div>
								</div>
									<div id="haxcan-donut">
										<div id="haxcan-themes-donut">
											
										</div>
										<div class="haxcan-legend">
											<div id="haxcan-themes-fs"><div class="haxcan-green-dot"></div> <span id="haxcan-files-scanned">0</span> Scanned files</div>
											<div id="haxcan-themes-fr"><div class="haxcan-gray-dot"></div> <span id="haxcan-files-remaining">-</span> Files remaining</div>
										</div>	
										
									</div>
									
									<div class="haxcan-current-scan">
										<h3>CURRENTLY PROCESSING <span id="haxcan-ctp"></span></h3>
										<div id="haxcan-current-tf">
											
										</div>
									</div>
									
								</div>
								<?php } 
								else {
									$allthemes = wp_get_themes();
									$arr['num_of_themes']= count($allthemes);
									$count = $totalfilescount = 0;
									foreach($allthemes as $currtheme){
										$numoffiles = $currtheme->get_files( array('php','js'), 1 );
										$arr['themes'][$count]['name'] = $currtheme->get('Name');
										$arr['themes'][$count]['version'] = $currtheme->get('Version');
										$arr['themes'][$count]['files_count'] = count($numoffiles);
										$arr['themes'][$count]['files'] = $numoffiles;
										$totalfilescount += count($numoffiles);
										$count++;
									}
									?>
								<div class="haxcan-loading-data" style="display:block">
									<div class="haxcan-wrap-top">
									<div id="haxcan-total-plugins" class="haxcan-tp">
								<?php echo $arr['num_of_themes'].'/'.$arr['num_of_themes'];?>
									</div>
									<?php if($lastqid){?><div id="haxcan-inner-status" style="background-color:#fc1303" class="haxcan-is">CHECK QUARANTINE</div><?php } 
									else {?>
									<div id="haxcan-inner-status" class="haxcan-is">SECURE</div>	
										<?php } ?>
								</div>
									<div id="haxcan-donut">
										<div id="haxcan-themes-donut-done">
											
										</div>
										<div id="haxcan-themes-donut">
											
										</div>
										<div class="haxcan-legend">
											<div id="haxcan-themes-fs"><div class="haxcan-green-dot"></div> <span id="haxcan-files-scanned"><?php echo $totalfilescount;?></span> Scanned files</div>
											<div id="haxcan-themes-fr"><div class="haxcan-gray-dot"></div> <span id="haxcan-files-remaining">0</span> Files remaining</div>
										</div>	
										
									</div>
									
									<div class="haxcan-current-scan">
										<h3>THEME NAME <span id="haxcan-ctp">SCANNED</span></h3>
										<div id="haxcan-current-tf" <?php if($arr['num_of_themes']>1){ echo 'class="haxcan-mt"';}?>>
											<?php
											$curr = 0;
											foreach($allthemes as $currtheme){
												$out = strlen($currtheme->get('Name')) > 40 ? substr($currtheme->get('Name'),0,40)."..." : $currtheme->get('Name');
												echo '<h4>'.$currtheme->get('Name').' <img src="'.plugins_url().'/haxcan/admin/images/check.svg" alt="" /></h4>';
												if($curr==1){
													echo '<img src="'.plugins_url().'/haxcan/admin/images/expand.png" alt="expand" onClick="haxcanThemeExpand()" class="haxcan-expander"/>';
												}
												$curr++;
											}
											?>
										</div>
									</div>
									
								</div>	
									
									<?php
								}
								?>
								<div class="haxcan-footer">
									<a href="javascript:themescan()" class="haxcan-button" id="haxcan-start-plugins-scan">
									<?php if(!$lastscanid){ echo 'Start Scan';} else { echo 'Scan Again';}?>
								</a>
							</div>
							</div>
						</div>
						<?php
						//plugin based calc here
						$plugin_array = get_plugins();
						$plugincount = count($plugin_array);
						$pluginfilescount = 0;
						if($plugincount){
						    foreach ( $plugin_array as $pluginfile => $plugindata) {
								$hfname = self::haxcan_get_plugin_file($plugindata['Name']);
								//print_r(get_plugin_files($hfname));
								$filteredar = array_filter(get_plugin_files($hfname),function($val){ 
									$ext = substr(strrchr($val, '.'), 1);
									return $ext=='php';
								});
								$pluginfilescount += count($filteredar);
						    }
						}
						?>
						<div class="haxcan-widget" id="haxcan-plugin-scan">
							<h2 class="haxcan-widget-title"><img src="<?php echo plugins_url();?>/haxcan/admin/images/file.svg" alt="quarantin" /> Plugins Scanned</h2>
							<div class="haxcan-widget-content">
								<?php
								if(!$lastscanid){
									echo '<div class="haxcan-no-scans"><img class="tpDanger" src="'.plugins_url().'/haxcan/admin/images/danger.svg" alt="" /><p>No scans found. Press button under to start plugin scan or start a full scan by pressing big green button.</p></div>';?>
							
								<div class="haxcan-loading-data">
									<div class="haxcan-wrap-top">
									<div id="haxcan-total-plugins-2" class="haxcan-tp">
									-/-
									</div>
									<div id="haxcan-inner-status-2" class="haxcan-is">
										SCANNING
									</div>
								</div>
									<div id="haxcan-donut">
										<div id="haxcan-themes-donut-4">
											
										</div>
										<div class="haxcan-legend">
											<div id="haxcan-themes-fs"><div class="haxcan-green-dot"></div> <span id="haxcan-files-scanned-2">0</span> Scanned files</div>
											<div id="haxcan-themes-fr"><div class="haxcan-gray-dot"></div> <span id="haxcan-files-remaining-2">0</span> Files remaining</div>
										</div>	
										
									</div>
									
									<div class="haxcan-current-scan">
										<h3>CURRENTLY PROCESSING <span id="haxcan-ctp-2"></span></h3>
										<div id="haxcan-current-tf-2">
											
										</div>
									</div>
									
								</div>
								<?php } 
								else {
									?>
								<div class="haxcan-loading-data-2" style="display:block">
									<div class="haxcan-wrap-top">
									<div id="haxcan-total-plugins-2" class="haxcan-tp">
								<?php echo $plugincount.'/'.$plugincount;?>
									</div>
									<?php if($lastqid){?><div id="haxcan-inner-status-2" style="background-color:#fc1303" class="haxcan-is">CHECK QUARANTINE</div><?php } 
									else {?>
									<div id="haxcan-inner-status-2" class="haxcan-is">SECURE</div>	
										<?php } ?>
								</div>
									<div id="haxcan-donut-2">
										<div id="haxcan-themes-donut-done-4">
											
										</div>
										<div id="haxcan-themes-donut-4">
											
										</div>
										<div class="haxcan-legend">
											<div id="haxcan-themes-fs"><div class="haxcan-green-dot"></div> <span id="haxcan-files-scanned-2"><?php echo $pluginfilescount;?></span> Scanned files</div>
											<div id="haxcan-themes-fr"><div class="haxcan-gray-dot"></div> <span id="haxcan-files-remaining-2">0</span> Files remaining</div>
										</div>	
										
									</div>
									
									<div class="haxcan-current-scan">
										<h3>PLUGIN NAME <span id="haxcan-ctp">SCANNED</span></h3>
										<div id="haxcan-current-tf-2" <?php if($arr['num_of_themes']>1){ echo 'class="haxcan-mt"';}?>>
											<?php
											$curr = 0;
											if($plugincount){
											    foreach ( $plugin_array as $pluginfile => $plugindata) {
													$out = strlen($plugindata['Name']) > 40 ? substr($plugindata['Name'],0,40)."..." : $plugindata['Name'];
													echo '<h4>'.$out.' <img src="'.plugins_url().'/haxcan/admin/images/check.svg" alt="" /></h4>';
													
													if($curr==1){
														echo '<img src="'.plugins_url().'/haxcan/admin/images/expand.png" alt="expand" onClick="haxcanPluginExpand()" class="haxcan-expander"/>';
													}
													$curr++;
											    }
												//die();
											}
											?>
										</div>
									</div>
									
								</div>	
									
									<?php
								}
								?>
								<div class="haxcan-footer">
									<a href="javascript:pluginscan()" class="haxcan-button" id="haxcan-start-plugins-scan-2">
									<?php if(!$lastscanid){ echo 'Start Scan';} else { echo 'Scan Again';}?>
								</a>
							</div>
							</div>
						</div>
						
						
						
					</div>
					<div class="haxcan-content-container" id="haxcan-content-02">
						<form class="haxcan-form" method="post" action="options.php">
							<?php
								settings_fields( 'haxcan_option_group' );
								do_settings_sections( 'haxcan-admin' );
								submit_button();
							?>
						</form>
					</div>
					<div class="haxcan-content-container" id="haxcan-content-03">
						<?php if(!$lastqid){?>
					    <h2>Quarantine List</h2>
						<div id="haxcan-nfiq"><p>No files in quarantine. So far so good.</p></div>
						<?php } 
						else {
							global $wpdb;
							$result = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'haxquarantine LIMIT 120');
							if(count($result)){
								foreach($result as $quar){
									echo '<div class="haxcan-quarantine-item">';
									?>
									<a href="javascript:haxcanLoadfile('<?php echo $quar->filepath;?>')"><?php echo $quar->filepath;?></a>
									<?php
									echo '<span class="haxcan-time">Scan time: '.$quar->time_added.'</span><span class="haxcan-time">Line: '.$quar->atline.'</span><span class="haxcan-time">Code found: <div style="color:red;display:inline">'.$quar->reason.'</div></span></div>';
								}
						}
						?>
					</div>
					
				</div>
				
			</div>
		</div>
	<?php }
}
	public function haxcan_page_init() {
		register_setting(
			'haxcan_option_group', // option_group
			'haxcan_option_name', // option_name
			array( $this, 'haxcan_sanitize' ) // sanitize_callback
		);

		add_settings_section(
			'haxcan_setting_section', // id
			'Settings', // title
			array( $this, 'haxcan_section_info' ), // callback
			'haxcan-admin' // page
		);

		add_settings_field(
			'google_safe_browsing_api_key_0', // id
			'Google Safe Browsing API Key', // title
			array( $this, 'google_safe_browsing_api_key_0_callback' ), // callback
			'haxcan-admin', // page
			'haxcan_setting_section' // section
		);

		add_settings_field(
			'autoscan_type_1', // id
			'Autoscan Type', // title
			array( $this, 'autoscan_type_1_callback' ), // callback
			'haxcan-admin', // page
			'haxcan_setting_section' // section
		);

		add_settings_field(
			'enable_autoscan_2', // id
			'Enable Autoscan', // title
			array( $this, 'enable_autoscan_2_callback' ), // callback
			'haxcan-admin', // page
			'haxcan_setting_section' // section
		);

		add_settings_field(
			'enable_autoscan_3', // id
			'Enable Autoscan', // title
			array( $this, 'enable_autoscan_3_callback' ), // callback
			'haxcan-admin', // page
			'haxcan_setting_section' // section
		);
	}

	public function haxcan_sanitize($input) {
		$sanitary_values = array();
		if ( isset( $input['google_safe_browsing_api_key_0'] ) ) {
			$sanitary_values['google_safe_browsing_api_key_0'] = sanitize_text_field( $input['google_safe_browsing_api_key_0'] );
		}

		if ( isset( $input['autoscan_type_1'] ) ) {
			$sanitary_values['autoscan_type_1'] = $input['autoscan_type_1'];
		}

		if ( isset( $input['enable_autoscan_2'] ) ) {
			$sanitary_values['enable_autoscan_2'] = $input['enable_autoscan_2'];
		}

		if ( isset( $input['enable_autoscan_3'] ) ) {
			$sanitary_values['enable_autoscan_3'] = sanitize_text_field( $input['enable_autoscan_3'] );
		}

		return $sanitary_values;
	}

	public function haxcan_section_info() {
		
	}
	function time_elapsed_string($datetime, $full = false) {
	    $now = new DateTime;
	    $ago = new DateTime($datetime);
	    $diff = $now->diff($ago);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'y',
	        'm' => 'm',
	        'w' => 'w',
	        'd' => 'd',
	        'h' => 'h',
	        'i' => 'min'
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' ago' : 'Now';
	}
	public function google_safe_browsing_api_key_0_callback() {
		printf(
			'<input class="regular-text" type="text" name="haxcan_option_name[google_safe_browsing_api_key_0]" id="google_safe_browsing_api_key_0" value="%s">',
			isset( $this->haxcan_options['google_safe_browsing_api_key_0'] ) ? esc_attr( $this->haxcan_options['google_safe_browsing_api_key_0']) : ''
		);
	}

	public function autoscan_type_1_callback() {
		?> <select name="haxcan_option_name[autoscan_type_1]" id="autoscan_type_1">
			<?php $selected = (isset( $this->haxcan_options['autoscan_type_1'] ) && $this->haxcan_options['autoscan_type_1'] === 'full') ? 'selected' : '' ; ?>
			<option value="full" <?php echo $selected; ?>>Full Scan</option>
			<?php $selected = (isset( $this->haxcan_options['autoscan_type_1'] ) && $this->haxcan_options['autoscan_type_1'] === 'plugins') ? 'selected' : '' ; ?>
			<option value="plugins" <?php echo $selected; ?>>Plugins Scan</option>
			<?php $selected = (isset( $this->haxcan_options['autoscan_type_1'] ) && $this->haxcan_options['autoscan_type_1'] === 'themes') ? 'selected' : '' ; ?>
			<option value="themes" <?php echo $selected; ?>>Themes Scan</option>
		</select> <?php
	}

	public function enable_autoscan_2_callback() {
		printf(
			'<input type="checkbox" name="haxcan_option_name[enable_autoscan_2]" id="enable_autoscan_2" value="enable_autoscan_2" %s>',
			( isset( $this->haxcan_options['enable_autoscan_2'] ) && $this->haxcan_options['enable_autoscan_2'] === 'enable_autoscan_2' ) ? 'checked' : ''
		);
	}

	public function enable_autoscan_3_callback() {
		printf(
			'<input class="regular-text" type="text" name="haxcan_option_name[enable_autoscan_3]" id="enable_autoscan_3" value="%s">',
			isset( $this->haxcan_options['enable_autoscan_3'] ) ? esc_attr( $this->haxcan_options['enable_autoscan_3']) : ''
		);
	}
	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Haxcan_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
