<?php
/**
 * Wordpress Plugin Framework Abstract Class 
 *
 * Framework for building Wordpress Plugins ... 
 *
 * @copyright  2012 GraphicTEN, LLC.
 *
 * @version    1.0.0
 *
 * @author     Michael Shihinski&lt;michael@graphicten.com&gt;
 *
 */

if (!class_exists('OrbtrPluginObject')):

	abstract class OrbtrPluginObject {
		
		/**
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var Database variable
		 */
		protected 	$_db;
		
		/**
		 * @acess protected
		 *
		 * @since 1.0
		 *
		 * @var Content Folder Name
		 */
		protected 	$_contentFolder;
		
		/**
		 *
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var Database tables array for storing used table names
		 */
		protected	$_tables;
		
		/**
		 *
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var version string
		 */
		protected	$_version;
		
		/**
		 *
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var database version string
		 */
		protected	$_dbVersion;
		
		/**
		 *
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var settings page object
		 */
		protected	$_settings;
		
		/**
		 *
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var plugin name id (ie. plugin_example)
		 */
		protected	$_pluginName;
		
		/**
		 *
		 * @access protected
		 *
		 * @since 1.0
		 *
		 * @var plugin filename
		 */
		protected	$_file;
		protected	$models;
		
		/**
		 * __construct
		 *
		 * Initialize class object
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 */
		public function __construct() {
			
			//Add commonly used actions
			add_action('admin_menu', array(&$this, 'adminMenu'));
			add_action('wp_head', array(&$this, 'wpHead'));
			add_action('wp_footer', array(&$this, 'wpFooter'));
			add_action('init', array(&$this, 'init'));

			add_action('activate_' . plugin_basename($this->_file), array(&$this, 'installInit') );
			add_action('activate_' . plugin_basename($this->_file), array(&$this, 'flush_rules') );
			add_action('deactivate_' . plugin_basename($this->_file), array(&$this, 'flush_rules') );
			
		}
		
		/**
		 * Get Plugin Directory
		 *
		 *retrieve plug-in location (dir path)
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @return string containing plugin directory
		 */
		final public function getPluginDir() {
			return dirname($this->_file);
		}
		
		/**
		 * Get Plugin URL
		 *
		 * rerieve plug-in location (url path)
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @return string containing plugin url
		 */
		final public function getPluginUrl() {
			return plugins_url().'/'.dirname(plugin_basename($this->_file));
		}
		
		/**
		 * Get Content Directory
		 *
		 * retrieve plugins content directory
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @updated 01/26/2012 - added wp_upload_dir to get directories
		 *
		 * @return string containing plugin content directory
		 */
		final public function getContentDir() {
			$upload_dir = wp_upload_dir();
			return $upload_dir['basedir'].'/'.$this->_contentFolder;	
		}
		
		/**
		 * Get Content URL
		 *
		 * retrieve plugins content location (url path)
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @updated 01/26/2012 - added wp_upload_dir to get directories
		 *
		 * @return string containing plugin content url
		 */
		final public function getContentUrl() {
			$upload_dir = wp_upload_dir();
			return $upload_dir['baseurl'].'/'.$this->_contentFolder;	
		}
		
		/**
		 * Create Content Directory
		 *
		 * create plugins content location (dir path)
		 *
		 * @since 1.0
		 *
		 * @access protected
		 */
		final protected function createContentDir() {
			if ( !is_dir($this->getContentDir()) && !file_exists($this->getContentDir())) {	
					return mkdir($this->getContentDir(), 0775, true); 
			}
		}
		
		/**
		 * Generic Template
		 * 
		 * quick templater code
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @return string containing parsed template code
		 */
		final public function tpl($tpl, $data) {
			$replace = '';
			$search = '';
			foreach ($data as $key=>$val) {
					$search[] = '{'.$key.'}';
					$replace[] = stripslashes($val);
			}
			return str_replace($search, $replace, $tpl);
		}
		
		/**
		 *  Load Template
		 * 
		 * load a template for parsing
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @return string containing unparsed template code
		 */
		final public function loadtpl($filename) {
			if (empty($filename)) { return ''; }
			ob_start();
			require($filename);
			$contents = ob_get_contents();
			ob_end_clean();
			return $contents;
		}
		
		/**
		 *  Install Initialization
		 * 
		 * install plugin options, settings, filestructures, etc.
		 *
		 * @access public
		 *
		 * @since 1.0
		 *
		 * @return string containing unparsed template code
		 */
		final public function installInit() {
			if ( !current_user_can('manage_options') ) { wp_die( __('You do not have permissions to access this page.') ); }
			
			$current_db_version = $this->models->settings->getSetting($this->_pluginName.'_db_version');
			
			if (version_compare($current_db_version, $this->_dbVersion, "<")) {
				//call installer
				$this->install();
				$this->models->settings->setSetting($this->_pluginName.'_db_version', $this->_dbVersion);
			}	
		}
		
		public function flush_rules()
		{
			flush_rewrite_rules();
		}
		
		abstract public function install();
		public function adminMenu() {}
		public function init() {}
		public function wpHead() {}
		public function wpFooter() {}
		
	}

endif;