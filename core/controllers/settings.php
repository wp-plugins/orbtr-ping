<?php

class Orbtr_SettingsController
{
	private $uploader;
	private $models;
	private $forms;
	private $api;
	
	public function __construct()
	{
        //$this->uploader = new FileUploader('', '');
        $this->models = new stdClass();
        $this->models->settings = new Orbtr_SettingsModel('orbtrping_settings');
        $this->forms = new OrbtrHtmlForm();
        
        $key = $this->models->settings->getSetting('orbtr_api_key');
        $account = $this->models->settings->getSetting('orbtr_account_id');
        $this->api = new OrbtrApi($key, $account);
        
        $setup = get_option('digest');
        if (!$setup) {
            try {
                $results = $this->api->getAccountDefaults();
            } catch (Orbtr_Exception $e){
                return;    
            }
            try {
                $this->api->updateAccountDefaults($results->email, 1, $results->email_new, $results->email_returning, 1);
             } catch (Orbtr_Exception $e){ return; }
            update_option('digest', 1);
        }
	}
	
	public function pageCallback()
	{
        if ( !current_user_can('manage_options') ) { wp_die( __('You do not have permissions to access this page.') ); }
        
        global $orbtr_errors;
        
        $options = $this->models->settings->getOptions();
        
        try {
            $results = $this->api->getAccountDefaults();
        } catch (Orbtr_Exception $e){}
        
        $options['email'] = isset($results->email) ? $results->email : '';
        //$options['status'] = $results->default_status;
        $options['email_new'] = isset($results->email_new) ? $results->email_new : '';
        $options['email_returning'] = isset($results->email_returning) ? $results->email_returning: '';
        $options['digest_email'] = isset($results->digest_email) ? $results->digest_email: '';
        
        //echo $this->api->updateCheck('version_num');
        
        require(ORBTR_CONNECT_PATH.'/core/views/settings.php');
	}
	
	public function saveForm($post='')
	{
        global $orbtr_errors;
        
		$post = empty($post) ? $_POST : $post;
			
		do_action('orbtr_update_settings', $post);
		
		if (isset($post['action']) && $post['action'] === 'updateOptions')
		{
        
			$options = array(
				'orbtr_api_key' => $post['orbtr_api_key'],
				'orbtr_account_id' => $post['orbtr_account_id'],
				'orbtr_enable_mobile' => $post['orbtr_enable_mobile'],
				'orbtr_enable_mobile_custom' => $post['orbtr_enable_mobile_custom'],
				'orbtr_enable_tablets' => $post['orbtr_enable_tablets'],
				'orbtr_track_comments' => $post['orbtr_track_comments']
			);
			
			$this->models->settings->setOptions($options);
			$this->api = new OrbtrApi($post['orbtr_api_key'], $post['orbtr_account_id']);
			try {
			$this->api->updateAccountDefaults(urlencode($post['orbtr_notify_emails']), 1, (int)$post['email_new'], (int)$post['email_returning'], (int)$post['digest_email']);
			} catch (Orbtr_Exception $e){}
			//echo '<div id="message" class="updated fade">Options have been saved.</div>';
			$orbtr_errors->enqueueMessage('Options have been saved.');
			
			$key = $this->models->settings->getSetting('orbtr_api_key');
			$account = $this->models->settings->getSetting('orbtr_account_id');
			
			if (!empty($key) && !empty($account))
			{
				$api = new OrbtrApi($key, $account);
				try {
					$check = (array)$api->getAccountDefaults();
				} catch (Orbtr_Exception $e) {}
				
				if (!isset($check['ERROR']) && empty($check['ERROR']))
				{
					wp_redirect(admin_url('admin.php?page=orbtrping-settings'));
				}
			}
		}
	}
}