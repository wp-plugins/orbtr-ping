<?php

class ORBTRDashboardWidget
{
    public function __construct()
    {
        add_action('wp_dashboard_setup', array($this, 'add_widget'), 9999); 
        
        $this->settings = new Orbtr_SettingsModel('orbtrping_settings');
		$key = $this->settings->getSetting('orbtr_api_key');
		$account = $this->settings->getSetting('orbtr_account_id');
		$this->api = new OrbtrApi($key, $account);  
    }
    
    public function add_widget()
    {
        wp_add_dashboard_widget(
                 'orbtr_dashboard_widget',
                 'ORBTR Ping Dashboard',
                 array($this, 'display_widget')
        );	
        
        global $wp_meta_boxes;
 
        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];
        $orbtr_widget_backup = array( 'orbtr_dashboard_widget' => $normal_dashboard['orbtr_dashboard_widget'] );
        
        unset( $normal_dashboard['orbtr_dashboard_widget'] );
        
        $sorted_dashboard = array_merge( $orbtr_widget_backup, $normal_dashboard );
     
        $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
    }
    
    public function display_widget()
    {
        try 
        {
            $stats = $this->api->getStats();
            //print_r($leads);
        }
        catch (Orbtr_Exception $e)
        {
            echo __(ORBTR_ERROR_API, 'orbtr_ping');
            return;
        }
        
        require(ORBTR_CONNECT_PATH.'/core/views/dashboard/widget.php');	  
    }
    
}