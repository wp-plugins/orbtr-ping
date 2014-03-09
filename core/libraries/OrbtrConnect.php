<?php

class OrbtrConnect extends OrbtrPluginObject
{
	
	public function __construct($file) 
	{
		$this->_file = $file;
		$this->_pluginName = ORBTR_PLUGIN_NAME;
		$this->_version = ORBTR_PLUGIN_VERSION;
		$this->_dbVersion = ORBTR_DATABASE_VERSION;
		$this->_contentFolder = $this->_pluginName;
		
		$this->settings = new Orbtr_SettingsController();
		$this->dashboard = new Orbtr_DashboardController();
		$this->online = new Orbtr_OnlineController();
		$this->leads = new Orbtr_LeadsController();
		
		$this->models = new stdClass();
		$this->models->settings = new Orbtr_SettingsModel('orbtrping_settings');
		
		add_action('admin_init', array($this, 'load_scripts'));
		add_action('grunion_pre_message_sent', array('OrbtrJetpackTracking', 'formHandler'), 10, 3);
       add_action('media_buttons', array($this, 'add_form_button'), 20);
       
		$track_comments = $this->models->settings->getSetting('orbtr_track_comments');
		
		if ($track_comments)
		{
			add_action('wp_insert_comment', array('OrbtrCommentTracking', 'commentHandler'), 10, 2);
		}
		
		add_action('wp_ajax_orbtr-dashboard', array($this->dashboard, 'ajaxDashboard'));
		add_action('admin_init', array($this->settings, 'saveForm'));

		parent::__construct();
    }
    
    public function add_form_button()
    {
        echo '<style>.orbtr_media_icon{
                    background:url(' . $this->getPluginUrl() . '/assets/admin/images/orbtr.png) left -38px no-repeat;
                    display: inline-block;
                    height: 16px;
                    margin: 0 2px 0 0;
                    vertical-align: text-top;
                    width: 16px;
                    }
                    .wp-core-ui a.orbtr_media_link{
                     padding-left: 0.4em;
                    }
                 </style>
                  <a href="#TB_inline?width=480&inlineId=select_orbit_widget" class="thickbox button orbtr_media_link" id="add_orbit_widget" title="' . __("Add Orbit Widget", 'orbtr_ping') . '"><span class="orbtr_media_icon "></span> ' . __("Add Orbit Widget", "orbtr_ping") . '</a>';
    }
    
	public function load_scripts()
	{
		wp_enqueue_script('orbtr', $this->getPluginUrl().'/assets/admin/js/orbtr.js', array('jquery'));
		wp_enqueue_style('orbtr', $this->getPluginUrl().'/assets/admin/css/orbtr.css');
	}
	
	public function flush_rules()
	{
		do_action('orbtr_flush_rules');
		
		parent::flush_rules();
	}
   
	public function install()
	{
		global $wpdb;
		$this->createContentDir();
		GFOrbtrLeads::add_permissions();
		
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		
		//create settings table
		$table_name = $wpdb->prefix.'orbtrping_settings';  
		$sql = "CREATE TABLE " . $table_name . " (
		  id BIGINT(20) NOT NULL AUTO_INCREMENT,
		  setting VARCHAR(255) NOT NULL,
		  value LONGTEXT NOT NULL,
		  UNIQUE KEY id (id)
		);";
		dbDelta($sql);
		
		do_action('orbtr_install');
	}
	
	public function adminMenu()
	{
		if( function_exists('add_options_page') ) {
            $base_page = $this->_pluginName;
			
            #>Add Main Menu Page
            if ( function_exists('add_object_page') ) {
                    add_object_page(__(ORBTR_MENU_MAIN, 'orbtr_ping'), __(ORBTR_MENU_MAIN, 'orbtr_ping'), 'manage_options', $base_page, array());
            } else {
                    add_menu_page(__(ORBTR_MENU_MAIN, 'orbtr_ping'), __(ORBTR_MENU_MAIN, 'orbtr_ping'), 'manage_options', $base_page);
            }
			
			$key = $this->models->settings->getSetting('orbtr_api_key');
			$account = $this->models->settings->getSetting('orbtr_account_id');
			
			$valid_account = false;
			
			if (!empty($key) && !empty($account))
			{
				$api = new OrbtrApi($key, $account);
				
				try {
					$check = (array) $api->getAccountDefaults();
				} catch (Orbtr_Exception $e){}
				
				if (!isset($check['ERROR']) && empty($check['ERROR']))
				{
					$valid_account = true;
				}
				else
				{
					global $orbtr_errors;
					$orbtr_errors->enqueueMessage(ORBTR_ERROR_INVALID_KEY, 'error');
				}
			}
			
			if ($valid_account)
			{
                $this->dash_page = add_submenu_page(
                    $base_page, 
                    __(ORBTR_MENU_DASHBOARD, 'orbtr_ping'), 
                    __(ORBTR_MENU_DASHBOARD, 'orbtr_ping'), 
                    'manage_options', 
                    $base_page, 
                    array(
                        $this->dashboard, 
                        'pageCallback'
                    )
                );
                $this->online_page = add_submenu_page(
                    $base_page, 
                    __(ORBTR_MENU_ONLINE, 'orbtr_ping'), 
                    __(ORBTR_MENU_ONLINE, 'orbtr_ping'), 
                    'manage_options', 
                    $base_page.'-onlineleads', 
                    array(
                        $this->online, 
                        'pageCallback'
                    )
                );
                $this->leads_page = add_submenu_page(
                    $base_page, 
                    __(ORBTR_MENU_RECORDS, 'orbtr_ping'), 
                    __(ORBTR_MENU_RECORDS, 'orbtr_ping'), 
                    'manage_options', 
                    $base_page.'-allleads', 
                    array(
                        $this->leads, 
                        'pageCallback'
                    )
                );
                
                $this->settings_page = add_submenu_page(
                    $base_page, 
                    __(ORBTR_MENU_CONFIG, 'orbtr_ping'), 
                    __(ORBTR_MENU_CONFIG, 'orbtr_ping'), 
                    'manage_options', 
                    $base_page.'-settings', 
                    array(
                        $this->settings, 
                        'pageCallback'
                    )
                );
			}
			else
			{
				$this->settings_page = add_submenu_page(
                    $base_page, 
                    __(ORBTR_MENU_CONFIG, 'orbtr_ping'), 
                    __(ORBTR_MENU_CONFIG, 'orbtr_ping'), 
                    'manage_options', 
                    $base_page, 
                    array(
                        $this->settings, 
                        'pageCallback'
                    )
                );
			}
			
			add_action('load-'.$this->dash_page, array($this, 'adminLoad'));
			add_action('load-'.$this->online_page, array($this, 'adminLoad'));
			add_action('load-'.$this->leads_page, array($this, 'adminLoad'));
			add_action('load-'.$this->settings_page, array($this, 'adminLoad'));
        }
	}
	
	public function adminLoad()
	{	
		ob_start();
		remove_all_actions( 'admin_notices');
		
		wp_enqueue_style('dashboard');
		wp_enqueue_script('dashboard');
		wp_enqueue_script('common');
		wp_enqueue_script('post');
		wp_enqueue_script('postbox');
		wp_enqueue_script('thickbox');
		wp_enqueue_script('jquery');
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-effects-core');
		wp_enqueue_style('thickbox');
	}
	
	public function wpFooter()
	{
		$account = $this->models->settings->getSetting('orbtr_account_id');
		
		if (!$account) return;
		
		echo '
			<script type="text/javascript">	
			  var orbtr_account = "'.$account.'";
			
			  (function() {
				var trk = document.createElement("script"); trk.type = "text/javascript"; trk.async = true;
				trk.src = ("https:" == document.location.protocol ? "https://" : "http://") + "ping.orbtr.net/v2/trk.v2.js?r='.time().'";
				var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(trk, s);
			  })();
			</script>
		';
	}
	
}